
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
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <i class="fa fa-warning"></i>

                        <h3 class="box-title"><?php echo $title; ?></h3>
                        <div class="pull-right"><a href="#" onclick="window.history.back()" class="btn btn-success btn-flat">Back</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="20%">Title</th>
                                    <th>Description</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
//                                echo '<pre>';
//                                print_r($result);
//                                die;
                                ?>
                                <tr>
                                    <td>User Name</td>
                                    <td><?php echo $result->user_name; ?></td>
                                </tr>
                                
                                <tr>
                                    <td>Team Name</td>
                                    <td><?php echo $result->teamname; ?></td>
                                </tr>

                                <tr>
                                    <td>Team Image</td>
                                    <td><img src="<?php echo $result->teamimage ? base_url($result->teamimage) : base_url('upload/users/photo.png'); ?>" width="30%"></td>
                                </tr>

                                <tr>
                                    <td>Description</td>
                                    <td><?php echo $result->description; ?></td>
                                </tr>

                                <tr>
                                    <td>Members</td>
                                    <td><?php echo $result->members; ?></td>
                                </tr>

                                <tr>
                                    <td>Zip code</td>
                                    <td><?php echo $result->zipcode; ?></td>
                                </tr>

                                <tr>
                                    <td>Language</td>
                                    <td><?php echo $result->name; ?></td>
                                </tr>

                                <tr>
                                    <td>Created Date</td>
                                    <td><?php echo $result->created_at; ?></td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                    <!-- /.box-body -->

                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->


            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <i class="fa fa-warning"></i>

                        <h3 class="box-title">Team Requirement</h3>                       
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Industry Name</th>
                                    <th>Skill Name</th>
                                    <th>Experience Name</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($requirement) {
                                    $i = 1;
                                    foreach ($requirement as $val) {
                                        ?>
                                        <tr>                                       
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $val->industry_name; ?></td>
                                            <td><?php echo $val->skill_name; ?></td>
                                            <td><?php echo $val->experience_name; ?></td>
                                        </tr>
                                        <?php
                                        $i++;
                                    }
                                }else{
                                    echo '<tr><td colspan="5"> Record not found!</td></tr>';
                                }
                                ?>

                            </tbody>
                        </table>

                    </div>
                    <!-- /.box-body -->

                </div>
                <!-- /.box -->
            </div>

        </div>
    </section>
    <!-- /.content -->
</div>

