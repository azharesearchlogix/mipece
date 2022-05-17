
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $title; ?>

        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active"><?php echo $title; ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <i class="fa fa-warning"></i>

                        <h3 class="box-title"><?php echo $title; ?></h3>
                        <div class="pull-right"><a href="<?php echo base_url('admin/location'); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <?php
                        $action = (isset($result->id)) ? 'update/' . $result->id : 'create';
                        echo form_open('admin/' . $this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                        ?>
                        <div class="box-body">

                            <div class=" col-md-6">
                                <div class="form-group">
                                    <label>Zipcode </label>
                                    <?php echo form_input(array('name' => 'zip', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'zipcode', 'value' => isset($result->zip) ? set_value("zip", $result->zip) : set_value("zip"))); ?>
                                    <span class="help-block"> <?php echo form_error('zip'); ?> </span> 
                                </div>
                            </div>

                            <div class=" col-md-6">
                                <div class="form-group">
                                    <label>State Name </label>
                                    <?php echo form_input(array('name' => 'state_name', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'state_name', 'value' => isset($result->state_name) ? set_value("state_name", $result->state_name) : set_value("state_name"))); ?>
                                    <span class="help-block"> <?php echo form_error('state_name'); ?> </span> 
                                </div>
                            </div>
                            
                             <div class=" col-md-6">
                                <div class="form-group">
                                    <label>City </label>
                                    <?php echo form_input(array('name' => 'city', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'City', 'value' => isset($result->city) ? set_value("city", $result->city) : set_value("city"))); ?>
                                    <span class="help-block"> <?php echo form_error('city'); ?> </span> 
                                </div>
                            </div>


                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <?php
                                $selected = (isset($result->status)) ? $result->status : $this->input->post('status');
                                $status = array('' => '---Select--', '0' => 'Active', '1' => 'In-Active');

                                echo form_dropdown('status', $status, $selected, array('class' => 'form-control', 'required' => TRUE));
                                ?>
                                <span class="help-block"> <?php echo form_error('status'); ?> </span>     

                            </div>

                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-success btn-flat"><?php echo isset($result->id) ? 'Update' : 'Submit' ?></button>
                        </div>
                        </form> <?php echo form_close(); ?>
                    </div>
                    <!-- /.box-body -->

                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->


        </div>





    </section>
    <!-- /.content -->
</div>
<script>
            $(function () {

                $('#myForm').on('submit').validate({

                    rules: {

                        zip: {
                            required: true,
                            number: true,
                        },                       
                        state_name: {
                            required: true,                            
                        },
                        city: {
                            required: true,                            
                        },
                                               
                        status: {
                            required: true,
                        },

                    },
                    submitHandler: function (form) {
                        form.submit();
                    }
                });
            });
        </script>

