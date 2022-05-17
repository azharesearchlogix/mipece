
<!-- /.content-wrapper -->
<footer class="main-footer">
    <div class="pull-right hidden-xs">

    </div>
    <strong>Copyright &copy; 2020 <a href="#">eSearchlogix.com</a>.</strong> All rights
    reserved.
</footer>
<!-- /.control-sidebar -->
<!-- Add the sidebar's background. This div must be placed
     immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->


<script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/moment.min.js"></script> 
<script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/bootstrap.min.js"></script> 
<script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/bootstrap-datetimepicker.min.js"></script>

<script type='text/javascript'>
    $(document).ready(function () {
        $('#datetimepicker1').datetimepicker();
    });
</script>



<!-- DataTables -->
<script src="<?php echo base_url(); ?>design/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>design/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="<?php echo base_url(); ?>design/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo base_url(); ?>design/bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo base_url(); ?>design/dist/js/adminlte.min.js"></script>

<!-- bootstrap color picker -->
<script src="https://adminlte.io/themes/AdminLTE/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- bootstrap datepicker -->
<script src="https://adminlte.io/themes/AdminLTE/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<!-- bootstrap color picker -->
<script src="https://adminlte.io/themes/AdminLTE/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="https://adminlte.io/themes/AdminLTE/plugins/timepicker/bootstrap-timepicker.min.js"></script>

<!-- Select2 -->
<script src="<?php echo base_url(); ?>design/bower_components/select2/dist/js/select2.full.min.js"></script>
<!-- InputMask -->
<script src="<?php echo base_url(); ?>design/plugins/input-mask/jquery.inputmask.js"></script>
<!-- date-range-picker -->

<!-- iCheck 1.0.1 -->
<script src="<?php echo base_url(); ?>design/plugins/iCheck/icheck.min.js"></script>


<script src="<?php echo base_url(); ?>design/ckeditor/ckeditor.js"></script>





<script>
    CKEDITOR.replace('editortext');
</script> 

<script>
    $(document).ready(function () {
        $('#example1').DataTable({
            "lengthMenu": [[25, 50, 75, 100, 150, 200, -1], [25, 50, 75, 100, 150, 200, "All"]]
        });
    });

    $(function () {
        $('#example1').DataTable()
        $('#example2').DataTable({
            'paging': true,
            'lengthChange': false,
            'searching': false,
            'ordering': true,
            'info': true,
            'autoWidth': false
        })
    })
</script>

<script>
    $(function () {
        //Initialize Select2 Elements
        $('.select2').select2()

        //Datemask dd/mm/yyyy
        $('#datemask').inputmask('dd/mm/yyyy', {'placeholder': 'dd/mm/yyyy'})
        //Datemask2 mm/dd/yyyy
        $('#datemask2').inputmask('mm/dd/yyyy', {'placeholder': 'mm/dd/yyyy'})
        //Money Euro
        $('[data-mask]').inputmask()

        //Date range picker
        $('#reservation').daterangepicker()
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, locale: {format: 'MM/DD/YYYY hh:mm A'}})
        //Date range as a button
        $('#daterange-btn').daterangepicker(
                {
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment()
                },
                function (start, end) {
                    $('#daterange-btn span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
                }
        )

        //Date picker
        $('#datepicker').datepicker({
            autoclose: true
        })

        //Date picker
        $('#datepicker1').datepicker({
            autoclose: true
        })


    })
</script>



<script>
    $(function () {
        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        })
        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
            checkboxClass: 'icheckbox_minimal-red',
            radioClass: 'iradio_minimal-red'
        })
        //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        })

        //Colorpicker
        $('.my-colorpicker1').colorpicker()
        //color picker with addon
        $('.my-colorpicker2').colorpicker()

        //Timepicker
        $('.timepicker').timepicker({
            showInputs: false
        })
    })
</script> 


<script type="text/javascript">
    $(function () {
        // this will get the full URL at the address bar
        var url = window.location.href;
        //alert(url);
        // passes on every "a" tag 
        $("#sub-headermenu a").each(function () {
            // checks if its the same on the address bar
            if (url == (this.href)) {
                $(this).closest("li").addClass("active");
            }
        });
    });
</script>





<script type="text/javascript">
    $(document).on('click', '.adminstatus_checks', function () {
        var status = ($(this).hasClass("btn-success")) ? '0' : '1';
        // alert(status);
        var msg = (status == '0') ? 'Accept' : 'Accept';
        if (confirm("Are you sure to  change status" )) {
            var current_element = $(this);
            url = "<?php echo site_url('admin/dashboard/userstatus'); ?>";
            $.ajax({
                type: "POST",
                url: url,
                data: {id: $(current_element).attr('data'), status: status},
                success: function (data)
                {
                    location.reload();
                }
            });
        }
    });
</script>


<script type="text/javascript">
    function handleFiles(event) {
        var files = event.target.files;
        $("#rlly").attr("src", URL.createObjectURL(files[0]));
        document.getElementById("rllly").load();
    }

    document.getElementById("rll").addEventListener("change", handleFiles, false);


    $(document).on('click', '.podcast_status', function () {
        var status = ($(this).hasClass("btn-success")) ? '0' : '1';
        var msg = (status == '0') ? 'Deactivate' : 'Activate';
        if (confirm("Are you sure to " + msg)) {
            var current_element = $(this);
            url = "<?php echo site_url('admin/dashboard/podcaststatus'); ?>";
            $.ajax({
                type: "POST",
                url: url,
                data: {id: $(current_element).attr('data'), status: status},
                success: function (data)
                {
                    location.reload();
                }
            });
        }
    });


</script>

<script>
    $(document).on('click', '.category_status', function () {
        var status = ($(this).hasClass("btn-success")) ? '0' : '1';
        var msg = (status == '0') ? 'Deactivate' : 'Activate';
        if (confirm("Are you sure to " + msg)) {
            var current_element = $(this);
            url = "<?php echo site_url('admin/dashboard/categorystatus'); ?>";
            $.ajax({
                type: "POST",
                url: url,
                data: {id: $(current_element).attr('data'), status: status},
                success: function (data)
                {
                    location.reload();
                }
            });
        }
    });


    $(document).on('click', '.banner_status', function () {
        var status = ($(this).hasClass("btn-success")) ? '0' : '1';
        var msg = (status == '0') ? 'Deactivate' : 'Activate';
        if (confirm("Are you sure to " + msg)) {
            var current_element = $(this);
            url = "<?php echo site_url('admin/dashboard/bannerstatus'); ?>";
            $.ajax({
                type: "POST",
                url: url,
                data: {id: $(current_element).attr('data'), status: status},
                success: function (data)
                {
                    location.reload();
                }
            });
        }
    });

</script>



<script>
    function checkPassword(str)
    {
        var str = str;
        $.ajax({
            data: {currentPassword: str}, // get the form data
            type: "POST", // GET or POST
            url: "<?php echo site_url('admin/dashboard/check_password'); ?>", // the file to call
            success: function (data) { // on success..
                //$('#currentPassword).html(response); // update the DIV
                //alert(data);
                if (data == 1) {
                    document.getElementById("currentPass").innerHTML = "Successfully match.";
                    //alert(data);
                }
                if (data == 2) {
                    document.getElementById("currentPass").innerHTML = "Current Password is not same";

                }
            },

        });
    }


    function pass()
    {
        var newPassword = document.getElementById("newPassword").value;
        var confirmPassword = document.getElementById("confirmPassword").value;
        //alert(newPassword);
        if (newPassword != '' && confirmPassword != '') {
            if (newPassword == confirmPassword) {
                document.getElementById('confirmPass').innerHTML = "Password Match";
            } else {
                document.getElementById('confirmPass').innerHTML = "Password does not Match";
                //document.form.password.focus();
            }

        }
    }


    $('#change_pass_submit').click(function () {
        var $this = $(this);
        var currentPassword = document.getElementById("currentPassword").value;
        var newPassword = document.getElementById("newPassword").value;
        var confirmPassword = document.getElementById("confirmPassword").value;
        //alert(newPassword)
        $.ajax({

            type: "POST", // GET or POST
            url: "<?php echo site_url('admin/dashboard/change_password'); ?>", // the file to call
            data: {currentPassword: currentPassword, newPassword: newPassword, confirmPassword: confirmPassword}, // get the form data
            success: function (data) { // on success..
                if (data == 1) {
                    //document.getElementById("confirmPass").innerHTML = "Current Password same";
                    //alert(data);
                }
                if (data == 2) {
                    document.getElementById("confirmPass").innerHTML = "Current Password is not match";

                }

                if (data == 3) {
                    document.getElementById("confirmPass").innerHTML = "Fill Information";

                }
            },

        });
        return false; //so it doesn't refresh when submitting the page
    });

</script>
<script>
    $(document).ready(function () {
 $('body').tooltip({selector: '[data-toggle="tooltip"]'});
        $('[data-toggle="tooltip"]').tooltip();
        $('.timepicker').timepicker({
            showInputs: false
        })
    });
</script>

</body>

</html>

