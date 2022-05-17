<!DOCTYPE html>
<html lang="en">
<head>
	<title>Forgot Password</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/fontawesome-all.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/util.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/main.css">
<!--===============================================================================================-->
</head>
<body>
	
	<div class="limiter">
		<div class="container-login100" style="background-image: url('<?php echo base_url(); ?>design/newdesign/images/img-01.jpg');">
			<div class="wrap-login100 p-t-70 p-b-30">
			
				<form action="<?= base_url() ?>admin/dashboard/sentMail" method="post" class="login100-form validate-form">
					<div class="login100-form-avatar">
						<img src="<?php echo base_url(); ?>design/newdesign/images/logo.png" alt="My Team" style="width:250px">
					</div>

		

					<div class="wrap-input100 validate-input m-b-10" data-validate = "Username is required">
						<input class="input100" type="email" name="useremail" placeholder="Email Address" required="">
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fas fa-envelope"></i>
						</span>
					</div>

				

					<div class="container-login100-form-btn p-t-10">
						<button type="submit" class="login100-form-btn">
							Submit
						</button>
						<p style="color: #fff;"><?php if($this->session->flashdata('msg')): echo $this->session->flashdata('msg'); endif; ?></p>
					</div>

					<div class="text-center w-full p-t-25 p-b-10">
						<a href="<?= base_url() ?>admin" class="txt1">
							<i class="fa fa-arrow-left text-muted"></i> Back
						</a>
					</div>
					
				</form>
				
			</div>
		</div>
	</div>
	
</body>
</html>
