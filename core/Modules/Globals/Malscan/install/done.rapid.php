<?php

include "core.php";
head();

?>
<center>
    <div class="alert alert-success">
        <p>Malware Scanner has been successfully installed for <?= PROJECT_NAME ?>!</p>
    </div>

    <a href="malscan" class="btn-success btn btn-block"><i class="fa fa-arrow-circle-o-right"></i> Continue to Malware Scanner</a>
</center>

<form method="post" action="" class="form-horizontal row-border">
    <div class="form-group">
        <p class="col-sm-3">Pin: </p>
        <div class="col-sm-12">
            <div class="input-group input-group-prepend">
                <span class="input-group-text">
                    <i class="fa fa-key"></i>
                </span>
                <input type="text" name="pin" class="form-control" placeholder="" value="" required>
            </div>
        </div>
    </div>

    <?php
    if (isset($_POST['submit'])) {
        $pin = $_POST['pin'];
        if ($pin == MALSCAN_PIN) {

            $username = $_SESSION['username'];
            $password = hash('sha256', $_SESSION['password']);

            $config             = include VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';
            $config['username'] = $username;
            $config['password'] = $password;

            $_SESSION['sec-username'] = $username;
            file_put_contents(VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php', '<?php return ' . var_export($config, true) . '; ?>');
            echo '<meta http-equiv="refresh" content="0; url=malscan-auth" />';
        } else {
            echo '<br />
<div class="callout callout-danger">
	  <i class="fa fa-exclamation-circle"></i> The entered credential requirement is incorrect.
</div>';
            $error = "Yes";
        }
    }
    ?>

    <br />
    <input class="btn-primary btn btn-block" type="submit" name="submit" value="Next" />

    </div>
</form>
<?php
footer();
?>