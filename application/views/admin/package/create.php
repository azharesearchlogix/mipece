
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/package'); ?>" class="btn btn-success btn-flat">Back</a></div>
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
                                    <label>Name </label>
                                    <?php echo form_input(array('name' => 'name', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Name', 'value' => isset($result->name) ? set_value("name", $result->name) : set_value("name"))); ?>
                                    <span class="help-block"> <?php echo form_error('name'); ?> </span> 
                                </div>
                            </div>

                            <div class=" col-md-6">
                                <div class="form-group">
                                    <label>Rate </label>
                                    <?php echo form_input(array('name' => 'rate', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'rate', 'value' => isset($result->rate) ? set_value("rate", $result->rate) : set_value("rate"))); ?>
                                    <span class="help-block"> <?php echo form_error('rate'); ?> </span> 
                                </div>
                            </div>
                            
                             <div class=" col-md-6">
                                <div class="form-group">
                                    <label>Discount Rate </label>
                                    <?php echo form_input(array('name' => 'discount_rate', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Discount rate', 'value' => isset($result->discount_rate) ? set_value("discount_rate", $result->discount_rate) : set_value("discount_rate"))); ?>
                                    <span class="help-block"> <?php echo form_error('discount_rate'); ?> </span> 
                                </div>
                            </div>

                            <div class=" col-md-6">
                                <div class="form-group">
                                    <label>Days </label>
                                    <?php echo form_input(array('name' => 'days', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'days', 'value' => isset($result->days) ? set_value("days", $result->days) : set_value("days"))); ?>
                                    <span class="help-block"> <?php echo form_error('days'); ?> </span> 
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

                        name: {
                            required: true,
                        },
                       
                        rate: {
                            required: true,
                            number: true,
                        },
                        days: {
                            required: true,
                             number: true,
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

