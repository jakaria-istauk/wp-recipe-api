<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>RECIPE APP</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
$src_file = WPRA_PLUGIN_PATH.'/asset/bundle.js';
if ( file_exists($src_file) ){
	$src_file = WPRA_PLUGIN_URL.'asset/bundle.js';
    echo "<div id='root'></div>
    <script src='{$src_file}'></script>";
}
else{
?>
<div class="container">
	<div class="d-flex align-items-center justify-content-center vh-100">
		<div class="text-center">
			<h1 class="display-1 fw-bold">Cooming Soon</h1>
			<p class="fs-3"> Page Under Development.</p>
			<p class="lead">
				The page you’re looking for is under development.
			</p>
		</div>
	</div>
</div>
<?php } ?>
</body>
</html>