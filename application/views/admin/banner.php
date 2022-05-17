  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Banner</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Banner</h3>
			  
			  <a href="" class="btn btn-success btn-sm" title="Add Podcast" style="float: right;" data-toggle="modal" data-target="#modalbanner"><i class="fa fa-plus"> </i> Add New</a>
            </div>
			
			<!-- /.modal -->
			<div class="modal fade" id="modalbanner" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add New</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/bannerpost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						    <div class="form-group col-md-12">
							  <label for="category">Name</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="title" placeholder="Title">
							  <input type="hidden" name="action" id="action" class="form-control" value="insert"/>
							</div>
							
							  <div class="form-group col-md-12">
									  <label class="control-label">Live Streaming Date & Time</label>
									  <div class='input-group date' id='datetimepicker1'>
										 <input type='text' class="form-control" name="exdatetime"/>
										 <span class="input-group-addon">
										 <span class="glyphicon glyphicon-calendar"></span>
										 </span>
									  </div>									  
							  </div>
							  
							
							<div class="form-group col-md-12">
							  <label for="category">Image</label>
							  <input type="file" class="form-control" id="exampleInputEmail1" name="image1">
							</div>						
							
							
						</div>		
									
						</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								<input type="submit" class="btn btn-primary" value="Submit">
							</div>
						</form>
					</div>
				</div>
			</div>
			
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
				  <th>Image</th>
                  <th>Name</th>
				  <th>Expire Date & Time</th>
                  <th>Status</th>
				  <th>Action</th>
                </tr>
                </thead>
                <tbody>
				
                <?php 
				 $i=1;
				foreach($data as $post){
					?>
				 <tr>
					 <td><?php echo $i;?></td>
					 <td><img src="<?php echo base_url().'upload/banner/'.$post->image;?>" style="width:80px"></td>
					 <td><?php echo $post->name;?></td>
					 
					 <td><?php echo $post->bdate;?></td>
					 
					 <td width="100px"><i data="<?php echo $post->id;?>" class="banner_status btn
					  <?php echo ($post->status)?
					  'btn btn-block btn-success btn-sm': 'btn btn-block btn-danger btn-sm'?>"><?php echo ($post->status)? 'Active' : 'Inactive'?>
					 </i></td>
					 
					 <td>
					 
					 <a href="#" class="btn btn-block1 btn-success btn-sm" title="Edit" data-toggle="modal" data-target="#modalbannerupdate<?php echo $post->id;?>"><i class="fa fa-edit"> </i></a> 
					 
				     <a href="<?php echo base_url().'admin/dashboard/bannerdelete/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>
					 
					 </td>
				  </tr> 

				  
			<!-- /.modal -->
			<div class="modal fade" id="modalbannerupdate<?php echo $post->id;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Edit Banner</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/bannerpost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						     <div class="form-group col-md-12">
							  <label for="category">Name</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="title" value="<?php echo $post->name;?>">
							  
							    <input type="hidden" name="bid" id="bid" class="form-control" value="<?php echo $post->id;?>"/>
                                <input type="hidden" name="action" id="action" class="form-control" value="update"/>
							</div>
							
							  <div class="form-group col-md-12">
								  <label class="control-label">Live Streaming Date & Time</label>
								  <div class='input-group date' id='datetimepicker1<?php echo $post->id;?>'>
									 <input type='text' class="form-control" name="exdatetime" value="<?php echo $post->id;?>"/>
									 <span class="input-group-addon">
									 <span class="glyphicon glyphicon-calendar"></span>
									 </span>
								  </div>								  
							  </div>
							
							<div class="form-group col-md-12">
							  <label for="category">Image</label>							   
							  <input type="hidden" name="oldimg" value="<?php echo $post->image; ?>">
							  <input type="file" class="form-control" id="exampleInputEmail1" name="image1">
							  <img src="<?php echo base_url().'upload/banner/'.$post->image;?>" style="width:80px">
							</div>								
							
							
						</div>		
									
						</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
								<input type="submit" class="btn btn-primary" value="Update">
							</div>
						</form>
					</div>
				</div>
			</div>
			                   <script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/jquery.min.js"></script> 
				                <script type='text/javascript'>
									$( document ).ready(function() {
										$('#datetimepicker1<?php echo $post->id;?>').datetimepicker();
									});
								</script>
				  
				 <?php $i++; }?>  
                
                
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
  
