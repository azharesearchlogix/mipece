
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

        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $title; ?></h3>
                        <a href="<?php echo base_url() . 'admin/' . $this->uri->segment(2); ?>" class="btn btn-success btn-sm" title="Back" style="float: right;" data-toggle="tooltip" >Back</a>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <?php
                    
                    $action = (isset($result->id)) ? 'update/' . base64_encode($result->id) : 'create';
                    echo form_open('admin/' . $this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                    ?>
                    <div class="box-body">

                        <div class="form-group col-md-4">
                            <label>Name</label>
                            <?php echo form_input(array('name' => 'name', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Name', 'value' => isset($result->name) ? set_value("name", $result->name) : set_value("name"))); ?>
                            <span class="help-block"> <?php echo form_error('name'); ?> </span> 
                        </div>

                        <div class="form-group col-md-4">
                            <label>Education</label>
                            <?php
                            $selected = isset($result->education_id) ? $result->education_id : $this->input->post('education_id');
                            $edu = array('' => '---Select Education--');
                            foreach ($education as $sval) {
                                $edu[$sval->id] = $sval->name;
                            }
                            echo form_dropdown('education_id', $edu, $selected, array('class' => 'form-control', 'required' => TRUE));
                            ?>
                            <span class="help-block"> <?php echo form_error('education_id'); ?> </span> 
                        </div>




                        <div class="form-group col-md-4">
                            <label>Experience</label>
                            <?php
                            $selected_exp = isset($result->experience) ? $result->experience : $this->input->post('experience');
                            $exp = array('' => '---Select Experience--');
                            for ($x = 1; $x <= 10; $x++) {
                                if ($x == 1) {
                                    $exp[$x] = $x . ' Year';
                                } else {
                                    $exp[$x] = $x . ' Years';
                                }
                            }
                            echo form_dropdown('experience', $exp, $selected_exp, array('class' => 'form-control', 'required' => TRUE));
                            ?>
                            <span class="help-block"> <?php echo form_error('experience'); ?> </span> 
                        </div>

                        <div class="bootstrap-timepicker">
                            <div class="form-group col-md-4">
                                <label>Availability(Start Time):</label>

                                <div class="input-group">

                                    <?php echo form_input(array('name' => 'start_time', 'type' => 'text', 'class' => 'form-control timepicker', 'required' => TRUE, 'value' => isset($result->start_time) ? set_value("start_time", $result->start_time) : set_value("start_time"))); ?>
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <span class="help-block"> <?php echo form_error('start_time'); ?> </span> 
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                        </div>

                        <div class="bootstrap-timepicker">
                            <div class="form-group col-md-4">
                                <label>Availability(End Time):</label>

                                <div class="input-group">                                   
                                    <?php echo form_input(array('name' => 'end_time', 'type' => 'text', 'class' => 'form-control timepicker', 'required' => TRUE, 'value' => isset($result->end_time) ? set_value("end_time", $result->end_time) : set_value("end_time"))); ?>
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <span class="help-block"> <?php echo form_error('end_time'); ?> </span> 
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                        </div>

                        <div class="form-group col-md-4">
                            <label>Fees</label>
                            <?php echo form_input(array('name' => 'fees', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Fees', 'value' => isset($result->fees) ? set_value("fees", $result->fees) : set_value("fees"))); ?>
                            <span class="help-block"> <?php echo form_error('fees'); ?> </span> 
                        </div>

                        <div class="form-group col-md-4">
                            <label>Phone No.</label>
                            <?php echo form_input(array('name' => 'phone', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Phone No.', 'value' => isset($result->phone) ? set_value("phone", $result->phone) : set_value("phone"))); ?>
                            <span class="help-block"> <?php echo form_error('phone'); ?> </span> 
                        </div>

                        <div class="form-group col-md-4">
                            <label>Profile Image</label>
                            <input type="file" name="profile_img" class="form-control" style="padding: 0">
                            <span class="help-block"> <?php echo form_error('profile_img'); ?> </span> 
                        </div>

                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <?php
                            $selected = (isset($result->status)) ? $result->status : $this->input->post('status');
                            $status = array('' => '---Select--', '0' => 'Active', '1' => 'In-Active');

                            echo form_dropdown('status', $status, $selected, array('class' => 'form-control', 'required' => TRUE));
                            ?>
                            <span class="help-block"> <?php echo form_error('status'); ?> </span>     

                        </div>

                        <div class="form-group col-md-12">
                            <label>Description</label>
                            <?php
                            $data = array(
                                'name' => 'description',
                                'id' => 'vc_desc',
                                'value' => isset($result->description) ? set_value("description", $result->description) : set_value("description"),
                                'rows' => '4',
                                'cols' => '10',
                                'class' => 'form-control'
                            );

                            echo form_textarea($data);
                            ?>
                        </div>

                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success btn-flat"><?php echo isset($result->id) ? 'Update' : 'Submit' ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div>


            </div>
        </div>
        <!-- /.row (main row) -->

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
                description: {
                    required: true,
                },
                fees: {
                    required: true,
                    number: true,
                },
                phone: {
                    required: true,
                    number: true,
                },
                profile_img: {
                    required: true,
                },
               
            },

//            messages: {
//                technology: {
//                    required: "This field is required",
//                    decimal: "only digits are alowed or digits with decimal number",
//                },
//                
//            }
//            ,
            submitHandler: function (form) {
                form.submit();
            }
        });
//        jQuery.validator.addMethod("decimal", function (value, element) {
//            return this.optional(element) || /^\d{0,4}(\.\d{0,2})?$/i.test(value);
//        });
    });
</script>


