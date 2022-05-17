<style>
    .btn-group, .btn-group-vertical{
        float: right;
    }
    #myEditor_icon{
        position: absolute;
        right: -73px; margin-top: -34px;
    }
</style>    
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?php echo $title ?>
            
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Main row -->
        <div class="row">

            <div class="col-xs-12">
                <div class="col-md-6">
                    <div class="box box-success">
                        <div class="box-header">
                            <h3 class="box-title">Menu</h3>
                            <div class="pull-right">
                                <button id="btnOutput" type="button" class="btn btn-success"><i class="fas fa-check-square"></i> Save</button>
                            </div>

                            <div class="clearfix"></div>
                            <hr>
                            <div class="card-body">
                                <ul id="myEditor" class="sortableLists list-group">
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-6">

                    <div class="box box-success">
                        <div class="box-header">
                            <h3 class="box-title">Edit Menu</h3>
                            <div class="clearfix"></div>
                            <hr>
                            <div class="card-body">
                                <div class="card-body">
                                    <form id="frmEdit" class="form-horizontal">

                                        <div class="col-md-10">
                                            <div class="form-group">
                                                <label>Text</label>
                                                <input type="text" class="form-control item-menu" name="text" id="text" placeholder="Text">
                                                <div class="input-group-append">
                                                    <button type="button" id="myEditor_icon" class="btn btn-outline-secondary"></button>
                                                </div>
                                                <input type="hidden" name="icon" class="item-menu">
                                            </div>
                                        </div>


                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>URL</label>
                                                <input type="text" class="form-control item-menu" id="href" name="href" placeholder="URL">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="target">Target</label>
                                                <select name="target" id="target" class="form-control item-menu">
                                                    <option value="_self">Self</option>
                                                    <option value="_blank">Blank</option>
                                                    <option value="_top">Top</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="title">Tooltip</label>
                                                <input type="text" name="title" class="form-control item-menu" id="title" placeholder="Tooltip">
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div class="clearfix"></div>
                                <div class="card-footer">
                                    <button type="button" id="btnUpdate" class="btn btn-primary" disabled><i class="fas fa-sync-alt"></i> Update</button>
                                    <button type="button" id="btnAdd" class="btn btn-success"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </section>
    <!-- /.content -->
</div>
<script type="text/javascript" src="<?php echo base_url(); ?>design/js/jquery-menu-editor.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>design/js/iconset/fontawesome5-3-1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>design/js/bootstrap-iconpicker.min.js"></script>
<script>
    jQuery(document).ready(function () {
        /* =============== DEMO =============== */
        // menu items
//        var arrayjson = [{"href": "http://home.com", "icon": "fas fa-home", "text": "Home", "target": "_top", "title": "My Home"}, {"icon": "fas fa-chart-bar", "text": "Opcion2"}, {"icon": "fas fa-bell", "text": "Opcion3"}, {"icon": "fas fa-crop", "text": "Opcion4"}, {"icon": "fas fa-flask", "text": "Opcion5"}, {"icon": "fas fa-map-marker", "text": "Opcion6"}, {"icon": "fas fa-search", "text": "Opcion7", "children": [{"icon": "fas fa-plug", "text": "Opcion7-1", "children": [{"icon": "fas fa-filter", "text": "Opcion7-1-1"}]}]}];
        var arrayjson = '<?php echo $result->data; ?>';
                
        // icon picker options
        var iconPickerOptions = {searchText: "Buscar...", labelHeader: "{0}/{1}"};
        // sortable list options
        var sortableListOptions = {
            placeholderCss: {'background-color': "#cccccc"}
        };

        var editor = new MenuEditor('myEditor', {listOptions: sortableListOptions, iconPicker: iconPickerOptions});
        editor.setForm($('#frmEdit'));
        editor.setUpdateButton($('#btnUpdate'));
        // $('#btnReload').on('click', function () {
        editor.setData(arrayjson);
        //  });

        $('#btnOutput').on('click', function () {
            var upstr = editor.getString();
            forajax(upstr);
        });

        $("#btnUpdate").click(function () {
            editor.update();
            var upstr = editor.getString();
//            alert(upstr);
            forajax(upstr);
        });
        $('#btnAdd').click(function () {
            var textval = $('#text').val();
            if ($('#text').val() != '') {
                editor.add();
                var upstr = editor.getString();
                forajax(upstr);
            } else {
                $('#text').prop('required', true);
                alert('Menu name is required!');
            }

        });

        function forajax(e) {
//         alert(e);
            $.ajax({
                type: "POST",
                url: "<?php echo base_url(); ?>admin/menu/menu",
                data: {
                    "data": e,
                },
                success: function (data) {
                   if (data == 1) {
                        $.notify({
                            title: '<strong>Success!</strong>',
                            message: 'Menu updated successfully!'
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
                            message: 'Something went wrong!'
                        },
                                {
                                    type: 'danger',
                                    placement: {
                                        from: 'bottom',
                                        align: 'right'
                                    },
                                }, );
                    }

                }

            });
        }
    });
</script>
