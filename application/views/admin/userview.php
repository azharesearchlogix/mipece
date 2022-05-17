
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Profile
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Profile</a></li>
            <li class="active">Profile</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-3">

                <!-- Profile Image -->
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <img class="profile-user-img img-responsive img-circle" src="<?= $data[0]->image ? base_url($data[0]->image) : base_url('upload/users/photo.png') ?>" alt="<?php echo $data[0]->firstname . ' ' . $data[0]->lastname; ?>">

                        <h3 class="profile-username text-center"><?php echo $data[0]->firstname . ' ' . $data[0]->lastname; ?></h3>

              <!--<p class="text-muted text-center"></p>-->

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Email ID</b> <a class=""><?php echo $data[0]->email; ?></a>
                            </li>
                            <li class="list-group-item">
                                <b>Contact No</b> <a class="pull-right"><?php echo $data[0]->contact; ?></a>
                            </li>

                            <li class="list-group-item">
                                <b>SSN Number</b> <a class="pull-right"><?php echo $data[0]->ssnnum; ?></a>
                            </li>

                        </ul>

                        <?php if ($data[0]->status == '2') { ?>

                            <i class="btn btn-block1 btn-danger btn-xs"> Unverify</i>

                        <?php } else if ($data[0]->status == '0') { ?>

                            <i class="btn btn-block1 btn-danger btn-xs"> Inactive</i>

                        <?php } else { ?>
                            <i class="btn btn-block1 btn-success btn-xs"> Activated</i>
                        <?php } ?>	
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

                <!-- About Me Box -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">About Me</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                      <!--<strong><i class="fa fa-book margin-r-5"></i> Education</strong>-->

                        <p class="text-muted">
                            <?php echo $data[0]->about; ?>
                        </p>

                        <hr>

                        <strong><i class="fa fa-map-marker margin-r-5"></i> Address</strong>

                        <p class="text-muted"><?php echo $data[0]->address . ', ' . $data[0]->city . ',' . $data[0]->country . '-' . $data[0]->postalcode; ?></p>

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>


            <!-- /.col -->
            <div class="col-md-9">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#activity" data-toggle="tab">Team List</a></li>
                        <!--<li><a href="#timeline" data-toggle="tab">Timeline</a></li>
                        <li><a href="#settings" data-toggle="tab">Settings</a></li>-->
                        <div class="pull-right"><a href="<?php echo base_url('admin/registeredUser'); ?>" class="btn btn-success btn-sm">Back</a></div>
                    </ul>

                    <div class="tab-content">
                        <div class="active tab-pane" id="activity">


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
                                        foreach ($teamlist as $post) {
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
                                                    <a href="<?php echo base_url() . 'admin/deleteteam/' . $post->id.'/'.$this->uri->segment(4); ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>

                                                </td>
                                            </tr> 
                                            <?php
                                            $i++;
                                        }
                                        ?>  
                                    </tbody>

                                </table>
                            </div>
                        </div>

                    </div>
                    <!-- /.tab-content -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
            <!-- /.col -->




        </div>
        <!-- /.row -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper --> 