<?php
include VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';

$config = include VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';

if ($config['username'] == '' || $config['password'] == '') {
  echo '<meta http-equiv="refresh" content="0; url=malscan-install" />';
  exit();
}

// session_start();

if (isset($_SESSION['sec-username'])) {
  $uname = $_SESSION['sec-username'];
  if ($uname != $config['username']) {
    echo '<meta http-equiv="refresh" content="0; url=malscan-auth" />';
    exit;
  }
} else {
  echo '<meta http-equiv="refresh" content="0; url=malscan-auth" />';
  exit;
}

function head()
{
  include VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';

  $config = include VISION_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . PROJECT_NAME . '/Libraries/Config/Malscan.php';
?>
  <!DOCTYPE html>
  <html lang="en">

  <head>

    <meta charset="utf-8">
    <title>Dashboard | Malscan - Admin &amp; Visioniconic Dashboard Manager</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">

    <link rel="shortcut icon" href="favicon.ico">
    <link href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/r-2.5.0/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker3.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
      integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
      crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.en-CA.min.js"></script>
    <link href="/static/assets_do_not_delete/malscan/css/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css">
    <link href="/static/assets_do_not_delete/malscan/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <link href="/static/assets_do_not_delete/malscan/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <link href="/static/assets_do_not_delete/malscan/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css">
    <link href="/static/assets_do_not_delete/malscan/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="/static/assets_do_not_delete/malscan/css/app.min.css" id="app-style" rel="stylesheet" type="text/css">

  </head>

  <body data-topbar="light">
    <div id="layout-wrapper">
      <header id="page-topbar">
        <div class="navbar-header">
          <div class="d-flex">
            <div class="navbar-brand-box">
              <a href="malscan" class="logo logo-dark">
                <span class="logo-sm">
                  <img src="/static/assets_do_not_delete/general/img/logo.png" alt="logo-sm" height="22">
                </span>
                <span class="logo-lg">
                  <img src="/static/assets_do_not_delete/general/img/logo.png" alt="logo-dark" height="30">
                </span>
              </a>

              <a href="index.html" class="logo logo-light">
                <span class="logo-sm">
                  <img src="/static/assets_do_not_delete/malscan/images/logo-sm.png" alt="logo-sm-light" height="22">
                </span>
                <span class="logo-lg">
                  <img src="/static/assets_do_not_delete/malscan/images/logo-light.png" alt="logo-light" height="20">
                </span>
              </a>
            </div>
            <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect" id="vertical-menu-btn">
              <i class="ri-menu-2-line align-middle"></i>
            </button>
          </div>

          <div class="d-flex">

            <div class="dropdown d-none d-lg-inline-block ms-1">
              <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ri-apps-2-line"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <div class="px-lg-2">
                  <div class="row g-0">
                    <div class="col">
                      <a class="dropdown-icon-item" href="https://github.com/vinexel/vinexel">
                        <img src="/static/assets_do_not_delete/malscan/images/github.png" alt="Github">
                        <span>GitHub</span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="dropdown d-none d-lg-inline-block ms-1">
              <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="fullscreen">
                <i class="ri-fullscreen-line"></i>
              </button>
            </div>


            <div class="dropdown d-inline-block user-dropdown">
              <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="rounded-circle header-profile-user" src="/static/assets_do_not_delete/malscan/images/avatar-1.png" alt="Header Avatar">

              </button>
              <div class="dropdown-menu dropdown-menu-end">

                <a class="dropdown-item"><i class="ri-user-line align-middle me-1"></i><?= $_SESSION['sec-username']; ?></a>

                <a class="dropdown-item d-block" href="malscan-account"><i class="ri-settings-2-line align-middle me-1"></i> Setting</a>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="malscan-logout"><i class="ri-shut-down-line align-middle me-1 text-danger"></i> Logout</a>
              </div>
            </div>

          </div>
        </div>
      </header>
      <div class="vertical-menu">

        <div data-simplebar="" class="h-100">
          <div class="user-profile text-center mt-3">
            <div class="">
              <img src="/static/assets_do_not_delete/malscan/images/avatar-1.png" alt="" class="avatar-md rounded-circle">
            </div>
            <div class="mt-3">
              <h4 class="font-size-16 mb-1">
                <?= $_SESSION['sec-username']; ?></h4>
            </div>
          </div>
          <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
              <li class="menu-title">Menu</li>

              <li <?php
                  if (basename($_SERVER['SCRIPT_NAME']) == 'dashboard.php') {
                    echo 'class="active"';
                  }
                  ?>>
                <a href="malscan" class="waves-effect">
                  <i class="ri-dashboard-line"></i>
                  <span>Dashboard</span>
                </a>
              </li>

              <li <?php
                  if (basename($_SERVER['SCRIPT_NAME']) == 'account.php') {
                    echo 'class="active"';
                  }
                  ?>>
                <a href="malscan-account" class=" waves-effect">
                  <i class="fa fa-user"></i>
                  <span>Account</span>
                </a>
              </li>
              <li <?php
                  if (basename($_SERVER['SCRIPT_NAME']) == 'malware-scanner.php') {
                    echo 'class="active"';
                  }
                  ?>>
                <a href="malscan-scanner" class=" waves-effect">
                  <i class="fa fa-virus"></i>
                  <span>Malware Scanner</span>
                </a>
              </li>
              <li <?php
                  if (basename($_SERVER['SCRIPT_NAME']) == 'security-check.php') {
                    echo 'class="active"';
                  }
                  ?>>
                <a href="malscan-check" class=" waves-effect">
                  <i class="fa fa-check"></i>
                  <span>Security Check</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="main-content">

      <?php
    }

    function footer()
    {
      ?>
        <!-- <footer class="main-footer">
          <strong>&copy; <?php
                          echo date("Y");
                          ?> <a href="https://codecanyon.net/item/shell-scanner/5609275?ref=Antonov_WEB" target="_blank">MalScan by VisionIconic</a></strong>

        </footer> -->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <?php
                echo date("Y");
                ?> © Vision Iconic.
              </div>
              <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                  Crafted with <i class="mdi mdi-heart text-danger"></i> by Iconic Group
                </div>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.8/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.8/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs/jszip-3.10.1/dt-1.13.8/b-2.4.2/b-html5-2.4.2/r-2.5.0/datatables.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/jquery.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/bootstrap.bundle.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/metisMenu.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/simplebar.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/waves.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/apexcharts.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/jquery-jvectormap-us-merc-en.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/jquery.dataTables.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/dataTables.bootstrap4.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/dataTables.responsive.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/responsive.bootstrap4.min.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/dashboard.init.js"></script>
    <script src="/static/assets_do_not_delete/malscan/js/app.js"></script>
  </body>

  </html>
<?php
    }
?>