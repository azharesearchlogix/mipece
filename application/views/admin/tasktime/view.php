
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
                <div id="success"></div>
                <div id="failed"></div>


                <div class="box box-primary">
                    <div class="box-header with-border">
                        <i class="fa fa-warning"></i>

                        <h3 class="box-title"><?php echo $title; ?></h3>
                        <div class="pull-right"><a href="<?php echo base_url('admin/tasktime'); ?>" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <div class="table-responsive">
                            <table id="example" class="table table-bordered table-striped">
                                <thead>
                                    <tr>                                                                        
                                        <th>Title</th>
                                        <th>Description</th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Title</td>
                                        <td><?php echo $result->title; ?></td>
                                    </tr>

                                    <tr>
                                        <td>Description</td>
                                        <td><?php echo $result->describe; ?></td>
                                    </tr>


                                    <tr>
                                        <td>Comments</td>
                                        <td><?php echo $result->comments; ?></td>
                                    </tr>


                                    <tr>
                                        <td>Task status</td>
                                        <td><span class="label label-success"><?php echo $result->taskstatus; ?></span></td>
                                    </tr>


                                    <tr>
                                        <td>Task date</td>
                                        <td><?php echo $result->taskdate; ?></td>
                                    </tr>


                                    <tr>
                                        <td>Start time</td>
                                        <td><?php echo $result->start_time; ?></td>
                                    </tr>


                                    <tr>
                                        <td>End Time</td>
                                        <td><?php echo $result->end_time; ?></td>
                                    </tr>

                                    <?php
                                    $start = date('h:i:s', strtotime($result->start_time));
                                    $end = date('h:i:s', strtotime($result->end_time));
                                    $hours = round(abs(strtotime($end) - strtotime($start)) / 3600, 2);
                                    ?>
                                    <tr>
                                        <td>Working Hours</td>
                                        <td><?php echo $hours. ' Hours'; ?></td>
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
