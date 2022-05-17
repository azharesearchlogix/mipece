
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/questions'); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <?php
                        $action = (isset($result->id)) ? 'update/'.$result->id : 'create';
                        echo form_open('admin/'.$this->uri->segment(2) . '/' . $action, array('id' => 'myForm'));
                        ?>
                        <div class="box-body">

                            <div class="form-group col-md-12">
                                <label>Industry Name</label>

                                <?php
                                $selected = (isset($result->industry_id)) ? $result->industry_id : $this->input->post('industry_id');
                                $industries = array('' => '---Select--');

                                foreach ($industry as $sval) {
                                    $industries[$sval->id] = $sval->name;
                                }
                                echo form_dropdown('industry_id', $industries, $selected, array('class' => 'form-control', 'required' => TRUE));
                                ?>
                                <span class="help-block"> <?php echo form_error('industry_id'); ?> </span>     

                            </div>

                            <div class=" col-md-12">
                                <div class="form-group">
                                    <label>Question </label>

                                    <?php
                                    $data = array(
                                        'name' => 'question',
                                        'id' => 'question',
                                        'value' => (isset($result->question)) ? $result->question : set_value('question'),
                                        'cols' => '10',
                                        'class' => 'form-control',
                                        'required' => TRUE
                                    );

                                    echo form_textarea($data);
                                    ?>
                                    <span class="help-block"> <?php echo form_error('question'); ?> </span>                                
                                </div>
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

