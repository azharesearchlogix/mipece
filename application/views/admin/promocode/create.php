
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/skill'); ?>" class="btn btn-success btn-flat">Back</a></div>
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
                                    <label>Promocode </label>
                                    <?php echo form_input(array('name' => 'name', 'type' => 'text', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Promocode','id'=>'name' , 'value' => isset($result->promocode) ? set_value("name", $result->promocode) : set_value("name"))); ?>
                                    <a href="javascript:void(0)" onclick="makeid(15)"><b>Generate Promocode</b></a>
                                    <span class="help-block"> <?php echo form_error('name'); ?> </span>

                                </div>
                            </div>
                             <div class="form-group col-md-4">
                               <label>Discount </label>
                                    <?php echo form_input(array('name' => 'discount', 'type' => 'number', 'class' => 'form-control', 'required' => TRUE, 'placeholder' => 'Discount', 'value' => isset($result->discount) ? set_value("discount", $result->discount) : set_value("discount"))); ?>
                                    <span class="help-block"> <?php echo form_error('discount'); ?> </span> 
                            </div>
                            <div class="form-group col-md-4">
                                <label>Status</label>
                                <?php
                                $selected = (isset($result->status)) ? $result->status : $this->input->post('status');
                                $status = array( '0' => 'Active', '1' => 'In-Active');

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

<script type="text/javascript">
  function makeid(length) {

   var result           = '';
   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
   var charactersLength = characters.length;
   for ( var i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() * charactersLength));
   }
   var coupan = result;
  $("#name").val(coupan);
}
    
</script>