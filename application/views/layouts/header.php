<!DOCTYPE html>
<html lang="en">
<head>
	<title>Dashboard</title>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.standalone.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-tagsinput.css')?>">
	<link rel="stylesheet" href="<?=base_url('assets/css/style.css')?>">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
	<script src="<?=base_url('assets/script/bootstrap-tagsinput.min.js')?>"></script>
	<script src="<?=base_url('assets/script/pdf/html2pdf.bundle.min.js')?>"></script>
</head>
<body onload="hideSpinner()">
	<div id="loader"></div>
	<div id="content" class="container-fulid" style="display:none;">
		<div class="header d-flex mb-3">
			<div><img src="<?=$logo_img?>" height="75px"></div>
			<div class="pl-4">
				<h6 class="mt-3 mb-0 text-primary">Group:</h6>
				<h5 class="text-primary"><?=$group_name?></h5>
			</div>
			<div class="ml-auto">
				<a class="menu" href="<?php echo base_url('index.php/index') ?>">
					<span class="fa-stack fa-lg">
					  	<i class="fa fa-circle-thin fa-stack-2x"></i>
					  	<i class="fa fa-ellipsis-v fa-stack-1x"></i>
					</span>
				</a>
			</div>
		</div>