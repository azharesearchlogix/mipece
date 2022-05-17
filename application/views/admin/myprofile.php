
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>My Account</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
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
                            <?php if ($this->session->flashdata('myprofile')): echo $this->session->flashdata('myprofile');
                            endif; ?>
                        </h4>
                    </div>
                    <!-- /.box-header --> <!-- form start -->

                    <?php
//                    echo '<pre>';
//                    print_r($data->result());
//                    die;
                    foreach ($data->result() as $row) {
                        ?>   		  
                        <form name="insertform" role="form" action="<?php echo base_url(); ?>admin/dashboard/updatemyprofile/<?php echo $row->id; ?>" method="post" enctype="multipart/form-data">
                            <div class="box-body">

                                <div class="form-group col-md-6">
                                    <label for="banner">Shop/Warehouse Name</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="shopname" placeholder="Shop/Warehouse Name" value="<?php //echo $row->shopname; ?>" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="banner">Name</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="uname" placeholder="Name" value="<?php echo $row->firstname; ?>" required="">
                                    <input type="hidden" class="form-control" id="exampleInputEmail1" name="vendorid" value="<?php echo $row->id; ?>">
                                    <input type="hidden" class="form-control" name="usertype" id="usertype" value="Vendor">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="banner">Email</label>
                                    <input type="email" class="form-control" id="exampleInputEmail1" name="uemail" placeholder="Email ID" value="<?php echo $row->email; ?>" readonly>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="banner">Contact No</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="ucontact" placeholder="Contact No" value="<?php echo $row->contact; ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" minlength="8" maxlength="20" required="">
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="banner">Password(Min. 6 Character)</label>
                                    <input type="password" class="form-control" id="exampleInputEmail1" minlength="6" maxlength="10" name="upassword" placeholder="Password" value="<?php //echo $row->password; ?>" required="">
                                </div>



                                <div class="form-group col-md-6">
                                    <label for="banner">Status</label>

                                    <select name="status" class="form-control" readonly>
                                        <option value="<?php echo $row->status; ?>"><?php echo $row->status; ?></option>

                                    </select>				  
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="banner">Address</label>
                                    <textarea class="form-control" name="uaddress" id="uaddress" placeholder="Address" rows="4" onchange="vendorAddress()"><?php echo $row->address; ?></textarea>

                                    <input type="hidden" class="form-control" id="ulatitude" name="ulatitude" placeholder="Latitude" value="<?php echo $row->latitude; ?>">
                                    <input type="hidden" class="form-control" id="ulongitude" name="ulongitude" placeholder="Longitude" value="<?php echo $row->longitude; ?>">
                                </div>

                                <div class="form-group col-md-6">
                                    <img src="<?php echo base_url() . 'upload/users/' . $row->image; ?>" style="width:70px; border-radius:50%"> 
                                    <input type="hidden" name="images" value="<?php echo $row->image; ?>">
                                    <input type="file" class="form-control" id="exampleInputEmail1" name="image">
                                </div>


                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-danger">Update</button>
                            </div>
                        </form>

                    <?php } ?>	

                </div>
                <!-- /.box -->


            </div>



        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
</div>