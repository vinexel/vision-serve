<?php
include_once VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';
if (!isset($_SESSION)) {
    session_start();
}
session_destroy();
echo '<meta http-equiv="refresh" content="0; url=/" />';
exit();
