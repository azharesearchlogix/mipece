<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Admin <?php echo isset($title) ? '|  '. $title : ''; ?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <link rel="manifest" href="<?php echo base_url(); ?>design/manifest.json">
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <link rel="icon" href="<?php echo base_url(); ?>design/images/fav.png" type="image/gif" sizes="16x16"> 
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
   <!-- Google Font -->
        <link rel="stylesheet"   href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"/>
  
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newcss/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>design/newcss/bootstrap-datetimepicker.min.css">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/Ionicons/css/ionicons.min.css">
  <!-- daterange picker -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/bootstrap-daterangepicker/daterangepicker.css">

  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/plugins/iCheck/all.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
  <!-- Bootstrap time Picker -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/plugins/timepicker/bootstrap-timepicker.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/select2/dist/css/select2.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/css/custom.css">
  
  
  <!-- DataTables -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>design/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">	
  <link rel="stylesheet" href="https://adminlte.io/themes/AdminLTE/plugins/timepicker/bootstrap-timepicker.min.css">
  
  <script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/jquery.min.js"></script> 
  <script type="text/javascript" src="<?php echo base_url(); ?>design/js/bootstrap-notify.min.js"></script> 
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css" id="theme-styles">
  <script type="text/javascript" src="<?php echo base_url('design/js/validation.js'); ?>"></script>
  
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

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
 <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo base_url(); ?>admin/dashboard" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels --
      <span class="logo-mini"><b><?php echo ucwords($user); ?></b></span>
      <!-- logo for regular state and mobile devices --
      <span class="logo-lg"><b><?php echo ucwords($user); ?> </b></span>-->
	  
	  <span class="logo-mini"><b>MIPE</b></span>
	  
	  <span class="logo-lg"><b>Mipeace</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          
         
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="<?php echo base_url(); ?>design/dist/img/admin.png" class="user-image" alt="User Image">
              <span class="hidden-xs"><?php echo "My Profile"; ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="<?php echo base_url(); ?>design/dist/img/admin.png" class="img-circle" alt="User Image">

                <p>
                  <?php echo ucwords($user); ?><!--  - Web Developer
                  <small>Member since Nov. 2012</small> -->
                </p>
              </li>
              <!-- Menu Body -->
      
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" class="btn btn-success btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="<?php echo base_url(); ?>admin/dashboard/logout" class="btn btn-danger btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <!-- <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li> -->
        </ul>
      </div>
    </nav>
  </header>