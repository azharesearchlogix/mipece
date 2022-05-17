  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Services</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Services</h3>
			  
			   <a href="" class="btn btn-success btn-sm" title="Add Podcast" style="float: right;" data-toggle="modal" data-target="#modalq"><i class="fa fa-plus"> </i> Add New</a>
            </div>
			
			<!-- /.modal -->
			<div class="modal fade" id="modalq" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add New</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/servicepost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						    <div class="form-group col-md-12">
							  <label for="category">Service Name</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="title" placeholder="Title" required="">
							  <input type="hidden" name="action" id="action" class="form-control" value="insert"/>
							</div>
							
							
							
							<!--<div class="form-group col-md-12">
							  <label for="category">User Type</label>
							  <select name="usertype" class="form-control">
							    <option value="user">User</option>
								<option value="serviceprovider">Service Provider</option>
							  </select>
							</div>-->
							
							 <div class="form-group col-md-12">
							  <label for="category">Status</label>
							  <select name="status" class="form-control">
							    <option value="1">Active</option>
								<option value="0">Inactive</option>
							  </select>
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
                <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
				  <th  width="50%">Name</th>
				  <th>Status</th>
				  <th>Action</th>
                </tr>
                </thead>
                <tbody>
				
                <?php 
				 $i=1;
				foreach($data as $post){
					
					//print_r($post);
					?>
				 <tr>
					 <td><?php echo $i;?></td>
					 <td><?php echo $post->serviceName;?></td>
					
					 <td width="100px"><i data="<?php echo $post->id;?>" class="service_status btn
					  <?php echo ($post->status)?
					  'btn btn-block btn-success btn-sm': 'btn btn-block btn-danger btn-sm'?>"><?php echo ($post->status)? 'Active' : 'Inactive'?>
					 </i></td>
					 
					 <td>
					 
					 <a href="#" class="btn btn-block1 btn-success btn-sm" title="Edit" data-toggle="modal" data-target="#modalqupdate<?php echo $post->id;?>"><i class="fa fa-edit"> </i></a> 
					 
				     <a href="<?php echo base_url().'admin/dashboard/servicedelete/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>
					 
					 </td>
				  </tr> 

				  
			<!-- /.modal -->
			<div class="modal fade" id="modalqupdate<?php echo $post->id;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Edit Service</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/servicepost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						     <div class="form-group col-md-12">
							  <label for="category">Service</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="title" placeholder="Title" value="<?php echo $post->serviceName;?>">
							  <input type="hidden" name="sid" id="sid" class="form-control" value="<?php echo $post->id;?>"/>
                                <input type="hidden" name="action" id="action" class="form-control" value="update"/>
							</div>
							
							<div class="form-group col-md-12">
							  <label for="category">Status</label>
							  <select name="status" class="form-control">
							    <option value="1" <?php if($post->status=='1'){ echo 'selected'; } else{ echo ''; } ?>>Active</option>
								<option value="0" <?php if($post->status=='0'){ echo 'selected'; } else{ echo ''; } ?>>Inactive</option>
							  </select>
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
			           
				  
				 <?php $i++; }?>  
                
                
                </tbody>
                
              </table>
              </div>
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
  
<script type="text/javascript" src="<?php echo base_url(); ?>design/newjs/jquery.min.js"></script>

<script type="text/javascript">
$(document).on('click','.service_status',function(){
      var status = ($(this).hasClass("btn-success")) ? '0' : '1';
	  
      var msg = (status=='0')? 'Deactivate' : 'Activate';
      if(confirm("Are you sure to "+ msg)){
        var current_element = $(this);
        url = "<?php echo site_url('admin/dashboard/servicestatus');?>";
        $.ajax({
          type:"POST",
          url: url,
          data: {id:$(current_element).attr('data'),status:status},
          success: function(data)
          {   
            location.reload();
          }
        });
      }      
    });	
		
 </script>