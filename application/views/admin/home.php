<?php
$users_count = $this->db->get_where('logincr', ['usertype' => 'user', 'status' => '1'])->num_rows();
$providers_count = $this->db->get_where('logincr', ['usertype' => 'serviceprovider', 'status' => '1'])->num_rows();
$survey_count = $this->db->get_where('tbl_survey_type', ['status' => '0'])->num_rows();
$mebers_count = $this->db->get_where('admin', ['status' => '1'])->num_rows();
?>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<style>
    .highcharts-credits {
        display: none;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">


        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Users</span>
                        <span class="info-box-number"><?php echo $users_count ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-handshake-o" aria-hidden="true"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Providers</span>
                        <span class="info-box-number"><?php echo $providers_count ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-podcast" aria-hidden="true"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Survey</span>
                        <span class="info-box-number"><?php echo $survey_count ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="ion ion-ios-people-outline"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Members</span>
                        <span class="info-box-number"><?php echo $mebers_count ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>

        <div id="container"></div>
    </section>
    <!-- /.content -->
</div>

<script type="text/javascript">
    Highcharts.chart('container', {

        title: {
            text: 'Yearly Registration Chart , <?php echo date('Y'); ?>'
        },

        subtitle: {
            text: 'Source: Mipece.com'
        },

        yAxis: {
            title: {
                text: 'Number of User'
            }
        },

        xAxis: {
            categories: <?php echo json_encode($months) ?>
        },

        plotOptions: {
            line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: true
            }
        },

        series: [{
                name: 'Users',
                data: <?php echo json_encode($providers, JSON_NUMERIC_CHECK) ?>
            }, {
                name: 'Providers',
                data: <?php echo json_encode($users, JSON_NUMERIC_CHECK) ?>
            },
            {
                name: 'Staffing Companies',
                data: <?php echo json_encode($staffingcompanies, JSON_NUMERIC_CHECK) ?>
            }],

    });

</script>