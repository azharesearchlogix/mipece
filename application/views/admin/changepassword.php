
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Change Password</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Main row -->
        <div class="row">

            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 style="color: green; font-weight: bold;">
                            <?php
                            if ($this->session->flashdata('password')): echo $this->session->flashdata('password');
                            endif;
                            ?>
                        </h4>
                    </div>
                    <!-- /.box-header --> <!-- form start -->

                    <?php
                    //foreach($data as $row)  
                    // {  
                    ?>   		  
                    <form name="frmChange" role="form" action="" method="post" enctype="multipart/form-data" autocomplete="off" >
                        <div class="box-body">

                            <div class="form-group col-md-4">
                                <label for="banner">Old Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required="" autocomplete="off" placeholder="Current Password" onChange="checkPassword(this.value)">
                                <input type="hidden" class="form-control" id="adminid" name="adminid" value="<?php //echo $row->id;         ?>">
                                <span id="currentPass" style="color:#F00;"></span>
                            </div>


                            <div class="form-group col-md-4">
                                <label for="banner">Password(Min. 6 Character)</label>
                                <input type="password" class="form-control" id="newPassword" minlength="6" maxlength="10" name="newPassword" placeholder="New Password" value="" required="">
                                <span id="newPassword" class="required"></span>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="banner">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmPassword" minlength="6" maxlength="10" name="confirmPassword" placeholder="Confirm Password" required="" onChange="return pass()">
                                <span id="confirmPass" style="color:#F00;"></span>

                                <span id='message'></span>
                            </div>



                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-danger" name="submit" id="change_pass_submit">Update</button>
                        </div>
                    </form>

                    <?php //}   ?>	

                </div>
                <!-- /.box -->


            </div>



        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
</div>