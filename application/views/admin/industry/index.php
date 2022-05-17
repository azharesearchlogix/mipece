
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/industry/create'); ?>" class="btn btn-success btn-flat">Create Industry</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <div class="table-responsive">
                            <table id="example" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th> 
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>

                                    </tr>
                                </thead>
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

<script type="text/javascript">
    var mytable;
    $(document).ready(function () {
        mytable = $('#example').DataTable({
            "processing": true,
            "serverSide": true,
            "ordering": false,
            "ajax": {
                "url": "<?php echo site_url('admin/industry/industrylist') ?>",
                "type": "POST"
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $(document).on('click', '.del', function () {
            var id = $(this).data('delete');
//            alert(id);
            $.ajax({
                type: 'POST',
                url: '<?php echo site_url('admin/industry/delete') ?>',
                data: {id: id},
                success: function (data) {
                    //alert(data);
                    if (data == 1) {
                        mytable.draw();
                        $.notify({
                            title: '<strong>Success!</strong>',
                            message: 'Data Delete Suceessfully!'
                        },
                                {
                                    type: 'success',
                                    placement: {
                                        from: 'bottom',
                                        align: 'right'
                                    },
                                }, );

                    } else {
                        $.notify({
                            title: '<strong>Error!</strong>',
                            message: 'Some things went wrong!'
                        },
                                {
                                    type: 'success',
                                    placement: {
                                        from: 'bottom',
                                        align: 'right'
                                    },
                                }, );
                    }

                }

            })
        })

        $(document).on('click', '.change', function () {
            var id = $(this).data('change');
//            alert(id);
            $.ajax({
                type: 'POST',
                url: '<?php echo site_url('admin/industry/change') ?>',
                data: {id: id},
                success: function (data) {
                    //alert(data);
                    if (data == 1) {
                        mytable.draw();
                        $.notify({
                            title: '<strong>Success!</strong>',
                            message: 'Status Changed Suceessfully!'
                        },
                                {
                                    type: 'success',
                                    placement: {
                                        from: 'bottom',
                                        align: 'right'
                                    },
                                }, );

                    } else {
                        $.notify({
                            title: '<strong>Error!</strong>',
                            message: 'Some things went wrong!'
                        },
                                {
                                    type: 'success',
                                    placement: {
                                        from: 'bottom',
                                        align: 'right'
                                    },
                                }, );
                    }

                }

            })
        })



    });

</script>

