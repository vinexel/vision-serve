<?php
$projectNamespace = PROJECT_NAME;
$formConfigClass = "\\{$projectNamespace}\\Libraries\\Helper\\InstallForms";
include VISION_DIR
	. DIRECTORY_SEPARATOR
	. 'system'
	. DIRECTORY_SEPARATOR
	. 'vendor'
	. DIRECTORY_SEPARATOR
	. 'plugins'
	. DIRECTORY_SEPARATOR
	. 'vinexel'
	. DIRECTORY_SEPARATOR
	. 'vision-serve'
	. DIRECTORY_SEPARATOR
	. 'core'
	. DIRECTORY_SEPARATOR
	. 'Modules'
	. DIRECTORY_SEPARATOR
	. 'Globals'
	. DIRECTORY_SEPARATOR
	. 'Initialize'
	. DIRECTORY_SEPARATOR
	. 'Functions.php';
if (class_exists($formConfigClass)) {
	$fields = $formConfigClass::installFormFields();
} else {
	$fields = []; // Use default if configuration not set.
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Install Panel by Vision</title>
	<link rel="stylesheet" href="/static/assets_do_not_delete/general/css/bootstrap.min.css">
	<link rel="stylesheet" href="/static/assets_do_not_delete/general/css/all.min.css">
	<link rel="stylesheet" href="/static/assets_do_not_delete/general/css/installer.css">
</head>

<body>
	<header class="py-3 border-bottom border-primary bg--dark">
		<div class="container">
			<div class="d-flex align-items-center justify-content-between header gap-3">
				<img class="logo" src="/static/assets_do_not_delete/general/img/logo.png" alt="Vinexel" width="125">
				<h6 class="head-title"><?= PROJECT_NAME ?> Install Panel</h6>
			</div>
		</div>
	</header>
	<div class="installation-section padding-bottom padding-top">
		<div class="container">
			<div class="installation-wrapper">
				<div class="install-content-area">
					<div class="install-item">
						<h6 class="title text-center"><?= $sectionTitle; ?> - <?= PROJECT_NAME ?></h6>
						<div class="box-item">
							<?php
							if ($action == 'result') {
								echo '<div class="success-area text-center">';
								if (@$response['error'] == 'ok') {
									echo '<h2 class="text-success text-uppercase mb-3">' . PROJECT_NAME . ' installed successfully!</h2>';
									if (@$response['message']) {
										echo '<h5 class="text-warning mb-3">' . $response['message'] . '</h5>';
									}
									echo '<p class="text-primary lead my-5 review-alert">Thank you so much for choosing our product! If you’re happy with the system, we’d be truly grateful if you could leave us a 5-star rating on CodeCanyon. Your feedback means a lot to us! :)</p>
';

									echo '<p class="text-warning lead my-5">If you encounter any issues in the future, feel free to reach out to our support team. We’re here to help!</p>
';
									echo '<div class="warning"><a href="/" class="theme-button choto">Go to website and Activate</a></div>';
								} else {
									if (@$response['message']) {
										echo '<h3 class="text-danger mb-3">' . $response['message'] . '</h3>';
									} else {
										echo '<h3 class="text-danger mb-3">Your Server is not Capable to Handle the Request.</h3>';
									}
									echo '<div class="warning mt-2"><h5 class="mb-4 fw-normal">Try again. Or you can ask for support by creating a support ticket.</h5><a href="?action=information" class="theme-button choto me-1 mb-3">Try Again</a> <a href="https://visioniconic.com" target="_blank" class="theme-button choto ms-1">create  ticket</a></div>';
								}
								echo '</div>';
							} elseif ($action == 'information') {
							?>
								<form action="?action=result" method="post" class="information-form-area mb--20">
									<div class="info-item">
										<h5 class="font-weight-normal mb-2">General Information</h5>
										<div class="row">
											<div class="information-form-group col-12">
												<input name="url" value="<?php echo appUrl(); ?>" type="text" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="item_id" placeholder="Envato Item ID" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="license_key" placeholder="Purchase Code" required>
											</div>
											<span class="text-danger mt-2">
												<i class="fas fa-exclamation-triangle"></i>
												Make sure to enter valid license information. If the license remains invalid for more than 7 days,
												the VINEXEL Auto Softkill System will be triggered, which may gradually degrade system functionality.
											</span>
											<span class="text-warning mt-2">
												Please note that technical support and warranty will not be provided for systems running without a valid license.
												<strong>[1 License = 1 Vinexel Instance]</strong>
											</span>
										</div>
									</div>
									<div class="info-item">
										<h5 class="font-weight-normal mb-2">Database Details</h5>
										<div class="row">
											<div class="information-form-group col-sm-12">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="db_type" value="existing-database" id="existing-database" checked>
													<label for="existing-database">Fresh Existing Database Only</label>
												</div>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_name" placeholder="Database Name" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_host" placeholder="Database Host" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_user" placeholder="Database User" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input class="secure-password" type="text" name="db_pass" placeholder="Database Password">
												<small class="d-none text-danger weak-password-error"> Week password detected</small>
											</div>
										</div>
									</div>
									<div class="info-item">
										<h5 class="font-weight-normal mb-3">Admin Credential</h5>
										<div class="row">
											<div class="information-form-group col-lg-3 col-sm-6">
												<label>Username</label>
												<!-- <input name="admin_user" type="text" placeholder="Admin Username" required> -->
												<input id="username" name="<?= $fields['username'] ?? 'username'; ?>" type="text" placeholder="Admin Username" required>
											</div>
											<div class="information-form-group col-lg-3 col-sm-6">
												<label>Password</label>
												<!-- <input name="admin_pass" type="text" placeholder="Admin Password" required> -->
												<input id="password" name="<?= $fields['password'] ?? 'password'; ?>" type="text" placeholder="Admin Password" required>
											</div>
											<div class="information-form-group col-lg-6">
												<label>Email Address</label>
												<!-- <input name="email" placeholder="Your Email address" type="email" required> -->
												<input id="email" name="<?= $fields['email'] ?? 'email'; ?>" placeholder="Your Email address" type="email" required>
											</div>
										</div>
									</div>
									<div class="info-item">
										<div class="information-form-group text-end">
											<button type="submit" class="theme-button choto">Install Now</button>
										</div>
									</div>
								</form>
							<?php
							} elseif ($action == 'requirements') {
								$btnText = 'View Detailed Check Result';
								if (count($failed)) {
									$btnText = 'View Passed Check';
									echo '<div class="item table-area"><table class="requirment-table">';
									foreach ($failed as $fail) {
										echo "<tr><td>$fail</td><td><i class='fas fa-times'></i></td></tr>";
									}
									echo '</table></div>';
								}
								if (!count($failed)) {
									echo '<div class="text-center"><i class="far fa-check-circle success-icon text-success"></i><h5 class="my-3">Requirements Check Passed!</h5></div>';
								}
								if (count($passed)) {
									echo '<div class="text-center my-3"><button class="btn passed-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePassed" aria-expanded="false" aria-controls="collapsePassed">' . $btnText . '</button></div>';
									echo '<div class="collapse mb-4" id="collapsePassed"><div class="item table-area"><table class="requirment-table">';
									foreach ($passed as $pass) {
										echo "<tr><td>$pass</td><td><i class='fas fa-check'></i></td></tr>";
									}
									echo '</table></div></div>';
								}
								echo '<div class="item text-end mt-3">';
								if (count($failed)) {
									echo '<a class="theme-button btn-warning choto" href="?action=requirements">ReCheck <i class="fas fa-sync-alt"></i></a>';
								} else {
									echo '<a class="theme-button choto" href="?action=information">Next Step <i class="fas fa-angle-double-right"></i></a>';
								}
								echo '</div>';
							} else {
							?>
								<div class="item">
									<h4 class="subtitle">License is valid per domain per project</h4>
									<p>
										Although this framework supports multiple projects with different domains,
										a Regular License must be purchased for each domain or client project that uses this framework.
										(1 project/domain = 1 license). This ensures full compliance with Envato's licensing policy.
									</p>
								</div>

								<div class="item">
									<h5 class="subtitle font-weight-bold">You Can:</h5>
									<ul class="check-list">
										<li> Use on one (1) project or domain per license.</li>
										<li> Customize, modify, and translate the framework for each licensed project.</li>
										<li> Manage multiple projects within the framework structure, provided each domain has a valid license.</li>
									</ul>
									<span class="text-warning">
										<i class="fas fa-exclamation-triangle"></i>
										If issues arise from any code or database modification, we are not responsible for the outcome.
									</span>
								</div>

								<div class="item">
									<h5 class="subtitle font-weight-bold">You Cannot:</h5>
									<ul class="check-list">
										<li class="no"> Use a single license for multiple domains or client projects.</li>
										<li class="no"> Distribute or resell the framework in any form.</li>
										<li class="no"> Bundle the framework in products for resale or redistribution.</li>
									</ul>
								</div>
								<h4 class="subtitle">NOTE:</h4>
								<p>
									This framework supports multi-project development, which allows developers to maintain multiple codebases from a single installation. However, each deployed domain or production environment must be covered by its own license according to Envato’s licensing terms.
								</p>
								<div class="item">
									<p class="info">
										For more details, please review the official
										<a href="https://codecanyon.net/licenses/faq" target="_blank">Envato License FAQ</a>.
									</p>
								</div>

								<div class="item text-end">
									<a href="?action=requirements" class="theme-button choto">I Agree, Next Step</a>
								</div>
							<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="py-3 text-center bg--dark border-top border-primary">
		<div class="container">
			<p class="m-0 font-weight-bold">&copy;<?php echo Date('Y') ?> - All Right Reserved by <a href="https://visioniconic.com/">Visioniconic</a></p>
		</div>
	</footer>
	<script src="/static/assets_do_not_delete/general/js/bootstrap.bundle.min.js"></script>
</body>

</html>