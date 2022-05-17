
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $title; ?>

        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url('admin/dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
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
                        <div class="pull-right"> <a href="<?php echo base_url('admin/unsubscribe'); ?>"><button type="button" class="btn btn-success btn-flat"> Back</button></a></div>
                   
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
<!--                                        <th>Package</th>
                                        <th>Reason</th>
                                        <th>Comments</th>
                                        <th>Date</th>-->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
//                                    echo '<pre>';
//                                    print_r($result);
                                    ?>

                                    <tr>
                                        <td>User</td>
                                        <td><?php echo $result->user_name; ?></td>
                                    </tr> 

                                    <tr>
                                        <td>Package</td>
                                        <td><?php echo $result->package_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Question</td>
                                        <td><?php echo $result->question; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Comments</td>
                                        <td><?php echo $result->comments; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Date</td>
                                        <td><?php echo $result->created_at; ?></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

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

