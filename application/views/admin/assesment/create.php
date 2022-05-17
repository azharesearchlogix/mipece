
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/assesment'); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <?php
                        $action = (isset($result->id)) ? 'update/' . $result->id : 'create';
                        echo form_open('admin/' . $this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                        ?>
                        <div class="box-body">

                            <div class="form-group col-md-12">
                                <label>Location</label>
                                <?php
                                $select = (isset($result->location_id)) ? $result->location_id : $this->input->post('location_id');
                                $locations = array('' => '---Select---');
                                foreach ($location as $val) {
                                    $locations[$val->id] = $val->zip;
                                }

                                echo form_dropdown('location_id', $locations, $select, array('class' => 'form-control select2', 'required' => TRUE));
                                ?>
                                <span class="help-block"> <?php echo form_error('location_id'); ?> </span>     

                            </div>
                            <?php
                            foreach ($industry as $key => $val) {
                                if (isset($result->assesment)) {
                                    $ass = json_decode($result->assesment);
                                }

//                                echo $ass[$key]->min;
//                                echo '<pre>';
//                                print_r(json_decode($result->assesment));
//                                die;
                                ?>
                                <div class=" col-md-4">
                                    <div class="form-group">
                                        <label>Industry </label>
                                        <?php echo form_input(array('name' => 'industry[]', 'type' => 'text', 'class' => 'form-control', 'readonly' => 'true', 'required' => TRUE, 'placeholder' => 'Industry', 'value' => $val->name)); ?>
                                        <input type="hidden" name="industry_id[]" value="<?php echo $val->id ?>">
                                        <span class="help-block"> <?php echo form_error('industry'); ?> </span> 
                                    </div>
                                </div>

                                <div class=" col-md-4">
                                    <div class="form-group">
                                        <label>Min Rate </label>
                                        <?php echo form_input(array('name' => 'min[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Min', 'value' => (isset($ass) ? array_key_exists($key, $ass) ? $ass[$key]->min :'' : ''))); ?>
                                        <span class="help-block"> <?php echo form_error('min'); ?> </span> 
                                    </div>
                                </div>
                                <div class=" col-md-4">
                                    <div class="form-group">
                                        <label>Max Rate </label>
                                        <?php echo form_input(array('name' => 'max[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Max', 'value' => (isset($ass) ? array_key_exists($key, $ass) ? $ass[$key]->max :'' : ''))); ?>
                                        <span class="help-block"> <?php echo form_error('max'); ?> </span> 
                                    </div>
                                </div>
                            <?php } ?>



                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <?php
                                $selected = (isset($result->status)) ? $result->status : $this->input->post('status');
                                $status = array('' => '---Select---', '0' => 'Active', '1' => 'In-Active');

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

               
                "min[]": {
                    required: true,
                    number: true,
                },
                "max[]": {
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

