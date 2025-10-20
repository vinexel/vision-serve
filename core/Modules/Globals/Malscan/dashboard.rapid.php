<?php
require("core.php");
head();
?>
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0"><a href="/"><i class="fa fa-home"></i> <?= PROJECT_NAME ?></a></h4>
                    <?php
                    if (!function_exists("view_size")) {
                        function view_size($size)
                        {
                            if (!is_numeric($size)) {
                                return FALSE;
                            } else {
                                if ($size >= 1073741824) {
                                    $size = round($size / 1073741824 * 100) / 100 . " GB";
                                } elseif ($size >= 1048576) {
                                    $size = round($size / 1048576 * 100) / 100 . " MB";
                                } elseif ($size >= 1024) {
                                    $size = round($size / 1024 * 100) / 100 . " KB";
                                } else {
                                    $size = $size . " B";
                                }
                                return $size;
                            }
                        }
                    }

                    $total        = '';
                    $free         = '';
                    $used         = '';
                    $free_percent = '';
                    $used_percent = '';
                    if (is_callable("disk_free_space") && is_callable("disk_total_space")) {
                        $storstat_disabled = 0;
                        $directory = '/';

                        @$free = disk_free_space($directory);
                        @$total = disk_total_space($directory);

                        if ($total === FALSE || $total <= 0) {
                            $total = 0;
                            $storstat_disabled = 1;
                        }
                        if ($free === FALSE || $free < 0) {
                            $free = 0;
                        }

                        @$used = $total - $free;
                        @$free_percent = round(100 / ($total / $free), 2);
                        @$used_percent = round(100 / ($total / $used), 2);
                    }

                    $files   = 0;
                    $folders = 0;
                    $images  = 0;
                    $php     = 0;
                    $html    = 0;
                    $css     = 0;
                    $js      = 0;
                    $py      = 0;
                    $dir     = glob("../");
                    foreach ($dir as $obj) {
                        if (is_dir($obj)) {
                            $folders++;
                            $scan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($obj, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
                            foreach ($scan as $file) {
                                if (is_file($file)) {
                                    $files++;
                                    $exp = explode(".", $file);
                                    if (@array_search("png", $exp) || @array_search("jpg", $exp) || @array_search("svg", $exp) || @array_search("jpeg", $exp) || @array_search("gif", $exp) || @array_search("webp", $exp)) {
                                        $images++;
                                    } else if (array_search("php", $exp)) {
                                        $php++;
                                    } else if (array_search("html", $exp) || array_search("htm", $exp)) {
                                        $html++;
                                    } else if (array_search("css", $exp)) {
                                        $css++;
                                    } else if (array_search("js", $exp)) {
                                        $js++;
                                    } else if (array_search("py", $exp)) {
                                        $py++;
                                    }
                                } else {
                                    $folders++;
                                }
                            }
                        } else {
                            $files++;
                        }
                    }
                    ?>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Malscan</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body pb-0">
                        <h4 class="card-title mb-4"><i class="fa fa-hdd"></i> STORAGE TOTAL SPACE</h4>
                        <div class="pt-3 pb-3">
                            <div class="row">
                                <div class="info-box-content">
                                    <span class="text-muted text-truncate mb-0">Total Space:</span>
                                    <span class="info-box-number"><?= view_size($total); ?></span>
                                    <span class="text-muted text-truncate mb-0">Used:</span>
                                    <?= view_size($used); ?>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?= $used_percent; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body pb-0">

                        <h4 class="card-title mb-4"><i class="fa fa-hdd"></i> STORAGE FREE SPACE</h4>

                        <div class="pb-3 pt-3">
                            <div class="row">
                                <div class="info-box-content">
                                    <span class="text-muted text-truncate mb-0">Free Space:</span>
                                    <span class="info-box-number">
                                        <?= view_size($free); ?>
                                    </span>
                                    <span class="progress-description">| <span class="text-semibold">
                                            <?= $free_percent; ?>%
                                        </span> of
                                        <span class="text-semibold">
                                            <?= view_size($total); ?></span>
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?= $free_percent; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Files</p>
                                <h4 class="mb-2">
                                    <?= $files; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="fa fa-file"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Folders</p>
                                <h4 class="mb-2">
                                    <?= $folders; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fa fa-folder"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">PHP Files</p>
                                <h4 class="mb-2">
                                    <?= $php; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-primary rounded-3">
                                    <i class="fab fa-php"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">HTML Files</p>
                                <h4 class="mb-2">
                                    <?= $html; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fab fa-html5"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">CSS Files</p>
                                <h4 class="mb-2">
                                    <?= $css; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fab fa-css3-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">JS Files</p>
                                <h4 class="mb-2">
                                    <?= $js; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fab fa-js"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Python Files</p>
                                <h4 class="mb-2">
                                    <?= $py; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fab fa-python"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-truncate font-size-14 mb-2">Image Files</p>
                                <h4 class="mb-2">
                                    <?= $images; ?>
                                </h4>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-light text-success rounded-3">
                                    <i class="fab fa-windows"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#datepicker-d').datepicker({
        todayHighlight: true,
        calendarWeeks: true
    });
</script>
<?php
footer();
?>