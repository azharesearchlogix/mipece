
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
                        <div class="pull-right"><a href="<?php echo base_url('admin/skill/create'); ?>" class="btn btn-success btn-flat">Create Skill</a></div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <div class="table-responsive">
                            <table id="example" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Industry</th>
                                        <th>Name</th>
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
                "url": "<?php echo site_url('admin/skill/datalist') ?>",
                "type": "POST"
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $(document).on('click', '.del', function () {
            var id = $(this).data('delete');
            //alert(id);
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo site_url('admin/skill/delete') ?>',
                        data: {id: id},
                        success: function (data) {
                            //alert(data);
                            if (data == 1) {
                                mytable.draw();
                                Swal.fire(
                                        'Deleted!',
                                        'Your data has been deleted.',
                                        'success'
                                        )
                            } else {
                                Swal.fire(
                                        'Error!',
                                        'Something went wrong!',
                                        'error'
                                        )
                            }

                        }

                    })
                } else if (
                        result.dismiss === Swal.DismissReason.cancel
                        ) {
                    swal.fire(
                            'Cancelled',
                            'Your imaginary data is safe :)',
                            'error'
                            )
                }
            })

        })


    });

</script>

