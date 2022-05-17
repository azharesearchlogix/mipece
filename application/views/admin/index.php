<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo base_url(); ?>design/images/fav.png" type="image/gif" sizes="16x16"> 
        <!--===============================================================================================-->
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/fontawesome-all.css">
        <!--===============================================================================================-->	
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/util.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newdesign/css/main.css">
         <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newcss/bootstrap.min.css">
        
         <script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/jquery.min.js"></script> 
          <script type="text/javascript" src="<?php echo base_url(); ?>design/js/bootstrap-notify.min.js"></script> 
       
        <!--===============================================================================================-->
    </head>
    <body>
        <?php if ($this->session->flashdata('success')) { ?>
            <script>
                $.notify({
                    title: '<strong>Success!</strong>',
                    message: '<?php echo $this->session->flashdata('success') ?>'
                },
                        {
                            type: 'success',
                            placement: {
                                from: 'bottom',
                                align: 'right'
                            },
                        }, );</script>
        <?php } ?>
        <?php if ($this->session->flashdata('error')) { ?>
            <script>
                $.notify({
                    title: '<strong>Error!</strong>',
                    message: '<?php echo $this->session->flashdata('error') ?>'
                },
                        {
                            type: 'danger',
                            placement: {
                                from: 'bottom',
                                align: 'right'
                            },
                        }, );</script>
        <?php } ?>

        <div class="limiter">
            <div class="container-login100" style="background-image: url('<?php echo base_url(); ?>design/newdesign/images/img-01.jpg');">
                <div class="wrap-login100 p-t-70 p-b-30">
                    <form id="Login" method="post" class="login100-form validate-form">
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

                        <div class="wrap-input100 validate-input m-b-10" data-validate = "Password is required">
                            <input class="input100" type="password" name="userpassword" placeholder="Password" required="">
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>


                        <div class="wrap-input100 validate-input m-b-10" data-validate = "" hidden>
                            <select class="input100" name="usertype" required="">
                                <option value="admin">Admin</option>
                            </select>
                        </div>


                        <div class="container-login100-form-btn p-t-10">
                            <button type="submit" class="login100-form-btn">
                                Login
                            </button>
                            
                        </div>

                        <div class="text-center w-full p-t-25 p-b-10">
                            <a href="<?= base_url() ?>admin/forgotPassword" class="txt1">
                                Forgot Password?
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </body>
</html>
