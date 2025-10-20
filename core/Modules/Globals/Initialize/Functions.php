<?php

declare(strict_types=1);

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

$itemName = PROJECT_NAME;
error_reporting(0);
$action = isset($_GET['action']) ? $_GET['action'] : '';

function appUrl()
{
    $current = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $exp = explode('?action', $current);
    $url = str_replace('index.php', '', $exp[0]);
    $url = substr($url, 0, -9);
    return $url;
}

if ($action == 'requirements') {
    $passed = [];
    $failed = [];
    $requiredPHP = REQUIRED_PHP_VERSION;
    $currentPHP = explode('.', PHP_VERSION)[0] . '.' . explode('.', PHP_VERSION)[1];

    if ($requiredPHP == $currentPHP) {
        $passed[] = "PHP version $requiredPHP is required";
    } else {
        // $failed[] = "PHP version $requiredPHP is required. Your current PHP version is $currentPHP";
    }

    $extensions = ['BCMath', 'Ctype', 'cURL', 'DOM', 'Fileinfo', 'GD', 'JSON', 'Mbstring', 'OpenSSL', 'PCRE', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'Filter', 'Hash', 'Session', 'zip'];
    foreach ($extensions as $extension) {
        if (extension_loaded($extension)) {
            $passed[] = strtoupper($extension) . ' PHP Extension is required';
        } else {
            $failed[] = strtoupper($extension) . ' PHP Extension is required';
        }
    }

    if (function_exists('curl_version')) {
        $passed[] = 'Curl via PHP is required';
    } else {
        $failed[] = 'Curl via PHP is required';
    }

    if (file_get_contents(__FILE__)) {
        $passed[] = 'file_get_contents() is required';
    } else {
        $failed[] = 'file_get_contents() is required';
    }

    if (ini_get('allow_url_fopen')) {
        $passed[] = 'allow_url_fopen() is required';
    } else {
        $failed[] = 'allow_url_fopen() is required';
    }

    $dirs = [
        VISION_DIR . '/system/storage/cache/',
        VISION_DIR . '/system/storage/',
        VISION_DIR . '/system/storage/logging/',
        VISION_DIR . '/system/framework/',
    ];

    foreach ($dirs as $dir) {
        $perm = substr(sprintf('%o', fileperms($dir)), -4);
        if ($perm >= '0775') {
            $passed[] = str_replace("../", "", $dir) . ' is required 0775 permission';
        } else {
            $failed[] = str_replace("../", "", $dir) . ' is required 0775 permission. Current Permission is ' . $perm;
        }
    }

    if (file_exists(VISION_DIR . '/system/framework/Fragments/Resource/' . PROJECT_NAME . '.sql')) {
        // $passed[] = 'database.sql should be available';
    } else {
        $failed[] = 'database should be available';
    }

    if (file_exists(VISION_DIR . '/public/.htaccess')) {
        $passed[] = '".htaccess" should be available in root directory';
    } else {
        $failed[] = '".htaccess" should be available in root directory';
    }
}

if ($action == 'result') {
    $response = ['error' => 'ok'];

    try {
        $db = new PDO(
            "mysql:host={$_POST['db_host']};dbname={$_POST['db_name']}",
            $_POST['db_user'],
            $_POST['db_pass']
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $dbinfo = $db->query('SELECT VERSION()')->fetchColumn();
        $engine = @explode('-', $dbinfo)[1];
        $version = @explode('.', $dbinfo)[0] . '.' . @explode('.', $dbinfo)[1];

        if (strtolower($engine) == 'mariadb') {
            if (!version_compare($version, '10.4', '>=')) {
                $response['error'] = 'error';
                $response['message'] = 'MariaDB 10.6+ Or MySQL 8.0+ Required. <br> Your current version is MariaDB ' . $version;
            }
        } else {
            if (!version_compare($version, '8.0', '>=')) {
                $response['error'] = 'error';
                $response['message'] = 'MariaDB 10.6+ Or MySQL 8.0+ Required. <br> Your current version is MySQL ' . $version;
            }
        }
    } catch (Exception $e) {
        $response['error'] = 'error';
        $response['message'] = ($_POST['db_type'] == 'create-new-database')
            ? 'There is a problem with creating the database.'
            : 'Database Credential is Not Valid. Error: ' . $e->getMessage();
    }

    if ($response['error'] == 'ok') {
        try {
            $query = file_get_contents(VISION_DIR . '/system/framework/Fragments/Resource/' . PROJECT_NAME . '.sql');
            $stmt = $db->prepare($query);
            $stmt->execute();
            $stmt->closeCursor();
        } catch (Exception $e) {
            $response['error'] = 'error';
            $response['message'] = 'Problem Occurred When Importing Database!<br>Please Make Sure The Database is Empty (Fresh Database).<br>' . $e->getMessage();
        }
    }

    if ($response['error'] == 'ok') {
        try {
            $db_name = $_POST['db_name'];
            $db_host = $_POST['db_host'];
            $db_user = $_POST['db_user'];
            $db_pass = $_POST['db_pass'];
            $email = $_POST['email'];
            $siteurl = appUrl();
            $app_key = base64_encode(random_bytes(32));

            $envcontent =
                "## General Configuration.
APP_NAME={$itemName}
APP_KEY=base64:{$app_key}
APP_URL={$siteurl}
API_URL=http://127.0.0.1:9000

## development, testing, or production
APP_MODE=production

## true or false value.
MAINTENANCE_MODE=false
APP_DEBUG=false
APP_VENDOR=false
IS_CACHED=false

## Malscan Integrated PIN.
MALSCAN_PIN=1234

## Golang Restapi if needed.
# IS_GOLANG_API=false

## Database Connection.
DB_DRIVER=mysql
DB_PORT=3306
DB_HOST={$db_host}
DB_NAME={$db_name}
DB_USERNAME={$db_user}
DB_PASSWORD={$db_pass}
";

            $envpath = VISION_DIR . '/app/' . PROJECT_NAME . '/.env';
            file_put_contents($envpath, $envcontent);
        } catch (Exception $e) {
            $response['error'] = 'error';
            $response['message'] = 'Problem Occurred When Writing Environment File.<br>' . $e->getMessage();
        }
    }

    // if ($response['error'] == 'ok') {
    //     try {
    //         $db->query("UPDATE admins SET email='" . $_POST['email'] . "', username='" . $_POST['admin_user'] . "', password='" . password_hash($_POST['admin_pass'], PASSWORD_DEFAULT) . "' WHERE username='admin'");
    //     } catch (Exception $e) {
    //         $response['message'] = 'Installer was unable to set the credentials of admin. Error: ' . $e->getMessage();
    //     }
    // }

    if (@$response['error'] == 'ok') {
        try {
            $queryClass = "\\{$itemName}\\Models\\BaseModel";

            // Check if "installer" method is available
            if (class_exists($queryClass) && method_exists($queryClass, 'installer')) {
                // Call installer method and send all POST data
                $queryClass::installer($db, $_POST);
            } else {
                throw new Exception("Query installer model not found for this project at BaseModel.");
            }
        } catch (Exception $e) {
            $response['message'] = 'Vision Installer was unable to set the credentials of admin(owner).';
        }
    }

    if (@$response['error'] == 'ok') {
        try {
            $installFile = VISION_DIR . '/app/' . PROJECT_NAME . '/Libraries/Service/' . PROJECT_NAME;

            if (file_exists($installFile)) {
                if (!unlink($installFile)) {
                    $response['message'] = 'Vision Installer was unable to delete the installer';
                } else {
                    $response['message'] = 'Installation completed and installer deleted successfully.';
                }
            } else {
                $response['message'] = 'installer not found. It may have already been deleted.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Vision Installer encountered an error while deleting installer: ' . $e->getMessage();
        }
    }

    if (@$response['error'] == 'ok') {
        $licenseKey = $_POST['license_key'] ?? '';
        $hashedKey = base64_encode(hash('sha256', $licenseKey, true));
        $configLicense = [
            'vinexel_status'    => true,
            'framework_license' => 'envato',
            'item_id'           => $_POST['item_id'] ?? '',
            'license_key'       => $hashedKey,
        ];

        $content = "<?php\n\nreturn " . var_export($configLicense, true) . ";\n";

        $file = VISION_DIR . '/system/framework/Fragments/Config/License.php';

        try {
            file_put_contents($file, $content);
            $response['message'] = 'License configuration saved successfully.';
        } catch (Exception $e) {
            $response['error'] = true;
            $response['message'] = 'Failed to write license file: ' . $e->getMessage();
        }
    }
}

$sectionTitle = empty($action) ? 'Terms of Use' : $action;
