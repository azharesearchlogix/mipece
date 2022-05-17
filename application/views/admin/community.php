
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Community</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Community</h3>
			  
			  
            </div>
            <!-- /.box-header -->
			
		
			
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
                  <th>Story Title</th>
				  <th>Description</th>
				  <th>Comment</th>
				  <th>Action</th>
                </tr>
                </thead>
                <tbody>
				
                <?php 
				 $i=1;
				//foreach($data as $post){
					?>
				 <tr>
					 <td>1</td>
					 <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit</td>				
					 <td>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</td>
					 <td><u><b><a href="<?= base_url(); ?>admin/communityComment">Comment</a></b></u></td>
					 <td>
					 
				     <a href="" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>
					 </td>
				  </tr>

				  
				  
				 <?php //$i++; }?>  
                
                
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
  
   