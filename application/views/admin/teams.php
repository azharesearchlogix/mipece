<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Teams</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">

                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Teams</h3>

 <!--<a href="" class="btn btn-success btn-sm" title="Add Podcast" style="float: right;" data-toggle="modal" data-target="#modalq"><i class="fa fa-plus"> </i> Add New</a>-->
                    </div>

                    <!-- /.box-header -->
                    <div class="box-body">
                        <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>SR. No.</th>
                                            <th>Image</th>
                                            <th>Team name</th>
                                            <th>Members</th>
                                            <th>Zip code</th>
                                            <th>Description</th>
                                            <th width="12%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $i = 1;
                                        foreach ($data as $post) {
                                            ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><img src="<?php echo $post->teamimage ? base_url($post->teamimage) : base_url('upload/users/photo.png'); ?>" width="25%"></td>
                                                <td><?php echo $post->teamname; ?></td>
                                                <td><?php echo $post->members; ?></td>
                                                <td><?php echo $post->zipcode; ?></td>
                                                <td><?php echo $post->description; ?></td>
                                                <td>
                                                    <a href="<?php echo base_url() . 'admin/teamdetails/' . $post->id; ?>" class="btn btn-block1 btn-primary btn-sm" title="View"><i class="fa fa-eye"> </i></a>
                                                    <a href="<?php echo base_url() . 'admin/dashboard/teamDelete/' . $post->id; ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>

                                                </td>
                                            </tr> 





                                            <?php
                                            $i++;
                                        }
                                        ?>  


                                    </tbody>

                                </table>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
    <!-- /.content -->
</div>

