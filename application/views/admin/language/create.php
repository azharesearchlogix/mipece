
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/'.$this->uri->segment(2).''); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <?php
                        $action = (isset($result->id)) ? 'update/' . $result->id : 'create';
                        echo form_open('admin/' . $this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                        ?>
                        <div class="box-body">                            

                            <div class=" col-md-4">
                                <div class="form-group">
                                    <label>Language </label>
                                    <?php echo form_input(array('name' => 'name', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Language Name', 'value' => isset($result->name) ? set_value("name", $result->name) : set_value("name"))); ?>
                                    <span class="help-block"> <?php echo form_error('name'); ?> </span> 
                                </div>
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

