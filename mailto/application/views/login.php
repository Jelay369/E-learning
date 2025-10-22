<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>mailTo</title>
	<link rel="stylesheet" href="<?= base_url('public/css/bootstrap.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('public/css/mdb.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('public/fonts/FAS/css/all.min.css') ?>">
	<link rel="stylesheet" href="<?= base_url('public/css/login.css') ?>">
</head>
<body>
	<div class="login-container">
		<form id="form-login" class="shadow bg-white" method="post">
			<h4 class="text-center">
				<img src="<?= base_url('public/img/logo.png') ?>" alt="MAILTO" width="50%">
			</h4>
			<hr class="mb-4">
			<div class="form-group">
				<label class="text-info mb-0">Identifiant</label>
				<input class="form-control" type="text" autocomplete="off" name="username">
			</div>

			<div class="form-group">
				<label class="text-info mb-0">Mot de passe</label>
				<input class="form-control" type="password" autocomplete="off" name="password">
			</div>
			<div class="form-group mt-4">
				<button type="submit" class="btn btn-block btn-info">
					<i class="fas fa-sign-in-alt"></i>
					<span class="ml-2">Se connecter</span>
				</button>
			</div>
		</form>
	</div>

	<script src="<?= base_url('public/js/jquery.min.js') ?>"></script>
	<script src="<?= base_url('public/js/popper.min.js') ?>"></script>
	<script src="<?= base_url('public/js/bootstrap.min.js') ?>"></script>
	<script src="<?= base_url('public/js/mdb.min.js') ?>"></script>
	<script src="<?= base_url('public/fonts/FAS/js/all.min.js') ?>"></script>
	<script src="<?= base_url('public/js/sweetalert2.all.js') ?>"></script>
	<script src="<?= base_url('public/js/utility.js') ?>"></script>
	<script src="<?= base_url('public/js/login.js') ?>"></script>
</body>
</html>