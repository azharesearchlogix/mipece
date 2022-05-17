
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Category</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Category List</h3>
			  
			  <span id="msg" style="color: green; font-weight: bold; text-align:center"><?php if($this->session->flashdata('message')): echo $this->session->flashdata('message'); endif; ?></span>
			  
			  <a href="" class="btn btn-success btn-sm" title="" style="float: right;" data-toggle="modal" data-target="#modalCat"><i class="fa fa-plus"> </i> Add Category</a>
            </div>
            <!-- /.box-header -->
			
			
			<!-- /.modal -->
			<div class="modal fade" id="modalCat" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add Category</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/categorypost');?>" enctype="multipart/form-data">
					  
						<div class="modal-body">
						  
						  <div class="row">
						  
						   <div class="form-group col-md-6">
							  <label for="category">Category Name</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="categoryname" placeholder="Category Name">
							  <input type="hidden" name="action" id="action" class="form-control" value="insert"/>
							</div>
							
							<div class="form-group col-md-6">
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
			
			
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
                  <th>Category Name</th>
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
					 <td><?php echo $post->catname;?></td>
					 
					  <td width="100px">
					      
					      <?php if($post->status=='1'){ ?>
										   
							   <i data="<?php echo $post->id;?>" class="btn btn-block btn-success btn-sm">Activated</i>
							   
						  <?php }else{ ?>
						  
							   <i data="<?php echo $post->id;?>" class="category_status btn btn-block btn-success btn-sm">Active</i>
							   
						  <?php } ?>
				  
						  <?php if($post->status=='0'){ ?>
							   
							   <i data="<?php echo $post->id;?>" class="category_status btn btn-block btn-danger btn-sm">Inactive</i>
							   
						  <?php }else{ ?>
						  
							   <i data="<?php echo $post->id;?>" class="category_status btn btn-block btn-danger btn-sm">Inactive</i>
							   
						  <?php } ?>
					 
					 </td>
					 
					 <td>
					 
					 <a href="<?= base_url(); ?>admin/subcategory/<?php echo $post->id;?>" class="btn btn-block1 btn-success btn-sm" title="Add Subcategory" data-toggle="modal" data-target=""><i class="fa fa-plus"> </i></a>
					 
					 <a href="#" class="btn btn-block1 btn-success btn-sm" title="Edit" data-toggle="modal" data-target="#modalcatupdate<?php echo $post->id;?>"><i class="fa fa-edit"> </i></a>
					 
				     <a href="<?php echo base_url().'admin/dashboard/categorydelete/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>
					 </td>
				  </tr>

				  
				  <!-- /.modal -->
			<div class="modal fade" id="modalcatupdate<?php echo $post->id;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Edit Category</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/categorypost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						     <div class="form-group col-md-6">
							  <label for="category">Category Name</label>
							  <input type="text" class="form-control" id="exampleInputEmail1" name="categoryname" value="<?php echo $post->catname;?>">
							  
							    <input type="hidden" name="cid" id="cid" class="form-control" value="<?php echo $post->id;?>"/>
                                <input type="hidden" name="action" id="action" class="form-control" value="update"/>
							</div>
							
							<div class="form-group col-md-6">
							  <label for="category">Status</label>							  
							  <select name="status" class="form-control">
								<option value="1" <?php if($post->status=='1'){ echo 'selected'; }else{ }; ?>>Active</option>
								<option value="0" <?php if($post->status=='0'){ echo 'selected'; }else{ }; ?>>Inactive</option>
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
  <script>
    $(document).ready(function () {
        setTimeout(function () {
            $('#msg').fadeOut('fast');
        }, 4000);
    });

</script>
   