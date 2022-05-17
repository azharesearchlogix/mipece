
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/interviewsurvey'); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <?php
                        $action = (isset($result->id)) ? 'update/' . $result->id : 'create';
                        echo form_open('admin/' . $this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                        ?>
                        <div class="box-body">

                            <div class="form-group col-md-12">
                                <label>User Type</label>
                                <?php
                                $select = (isset($result->user_type)) ? $result->user_type : $this->input->post('user_type');
                                $industries = array('' => '---Select---', '0' => 'User', '1' => 'Provider');
                                echo form_dropdown('user_type', $industries, $select, array('class' => 'form-control select2', 'required' => TRUE));
                                ?>
                                <span class="help-block"> <?php echo form_error('user_type'); ?> </span>     

                            </div>

                            <div class="form-group col-md-12">
                                <label>Question</label>
                                <?php echo form_input(array('name' => 'question', 'type' => 'text', 'class' => 'form-control', 'placeholder' => 'Question', 'value' => isset($result->question) ? set_value("name", html_entity_decode($result->question)) : set_value("question"))); ?>
                                <span class="help-block"> <?php echo form_error('question'); ?> </span> 
                            </div>

                            <div class="form-group col-md-6">
                                <label>Option A</label>
                                <?php echo form_input(array('name' => 'options[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Question', 'value' => isset($result->options) ? set_value("name", json_decode($result->options)[0]) : set_value("options[]"))); ?>
                                <span class="help-block"> <?php echo form_error('options[]'); ?> </span> 
                            </div>

                            <div class="form-group col-md-6">
                                <label>Option B</label>
                                <?php echo form_input(array('name' => 'options[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Question', 'value' => isset($result->options) ? set_value("name", json_decode($result->options)[1]) : set_value("options[]"))); ?>
                                <span class="help-block"> <?php echo form_error('options[]'); ?> </span> 
                            </div>

                            <div class="form-group col-md-6">
                                <label>Option C</label>
                                <?php echo form_input(array('name' => 'options[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Question', 'value' => isset($result->options) ? set_value("name", json_decode($result->options)[2]) : set_value("options[]"))); ?>
                                <span class="help-block"> <?php echo form_error('options[]'); ?> </span> 
                            </div>                            

                            <div class="form-group col-md-6">
                                <label>Option D</label>
                                <?php echo form_input(array('name' => 'options[]', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Question', 'value' => isset($result->options) ? set_value("name", json_decode($result->options)[3]) : set_value("options[]"))); ?>
                                <span class="help-block"> <?php echo form_error('options[]'); ?> </span> 
                            </div>



                            <div class="form-group col-md-4">
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
                question: {
                    required: true,
                },
                user_type: {
                    required: true,
                },
                "options[]": {
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

