
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Podcast</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Podcast List</h3>
			  
			  <a href="" class="btn btn-success btn-sm" title="Add Podcast" style="float: right;" data-toggle="modal" data-target="#modalPodcast"><i class="fa fa-plus"> </i> Add Podcast</a>
            </div>
			
		
			<!-- /.modal -->
			<div class="modal fade" id="modalPodcast" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document" style="width: 60%;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add Podcast</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/podcastpost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						   <div class="form-group col-md-6">
							  <label for="category">Category</label>
                               <select name="category" class="form-control">
                                   <option value="">Choose Category</option>							   
							  <?php
							    $query = $this->db->query("SELECT * FROM category WHERE status='1'");
								  foreach($query->result_array() as $row)
									{
									 ?>
								         <option value="<?php echo $row['id']; ?>"><?php echo $row['catname']; ?></option>
									<?php  } ?>
							  </select>							  
							</div>
						  
							<div class="form-group col-md-6">
								<label for="category">Title</label> 
								<input type="text" name="title" id="title" class="form-control" placeholder="Title" value="" required=""/><input type="hidden" name="action" id="action" class="form-control" value=""/>									
							</div>	
							
							<div class="form-group col-md-12">
								<label for="category">Description</label> 
								<textarea class="form-control" id="editortext" name="content" placeholder="Content" required=""></textarea>
							</div>	
							
							<div class="form-group col-md-6">
							  <label for="category">Image</label>
							  <input type="file" class="form-control" name="image1" id="image1">
							</div>

							<div class="form-group col-md-6">
							  <label for="category">Audio</label>
							  <input type="file" class="form-control" name="fileToUpload" id="rll" action=".mp3">
							  
							  <audio id="rllly" controls style="width: 100%; height: 40px; top:20px">
							    <source src="" id="rlly" />
							  </audio>
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
				  <th>Category Name</th>
				  <th>Audio</th>
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
					 
					 
					 <!--<td><img src="<?php echo base_url().'upload/images/'.$post->pimg;?>" style="width:60px"></td>-->
					 <td>
					   <?php
						  $query = $this->db->query("SELECT * FROM category WHERE  id='".$post->pcatid."' ");
						  foreach($query->result_array() as $row)
							{
							   echo '<b>'.$row['catname'].'</b>'; 
						   } 
						 ?>
					 </td>
					 <td><?php echo '<b>Title: ' . $post->pname.'</b>';?><br>
					          <audio controls style="height: 40px;">
							    <source src="<?php echo base_url().'upload/images/'.$post->pfile;?>"/>
							  </audio>
					</td>
				
					 <td width="100px"><i data="<?php echo $post->pid;?>" class="podcast_status btn
					  <?php echo ($post->pstatus)?
					  'btn btn-block btn-success btn-sm': 'btn btn-block btn-danger btn-sm'?>"><?php echo ($post->pstatus)? 'Active' : 'Inactive'?>
					 </i></td>
					 
					 <td width="80px">		
                        <a href="#" class="btn btn-block1 btn-success btn-sm" title="Edit Podcast" data-toggle="modal" data-target="#modalPodcastupdate<?php echo $post->pid;?>"><i class="fa fa-edit"> </i></a>
						
					 
				        <a href="<?php echo base_url().'admin/dashboard/podcastdelete/'.$post->pid; ?>" class="btn btn-block1 btn-danger btn-sm delete_data" onClick="return confirm('Are you sure you want to delete?')" title="Delete Podcast"><i class="fa fa-trash"></i></a>
					  </td>
				  </tr>     
				  
				  
				  <!-- /.modal -->
			<div class="modal fade" id="modalPodcastupdate<?php echo $post->pid;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document" style="width: 60%;">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Edit Podcast</h3>
						  </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/podcastpost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						    <div class="form-group col-md-6">
							  <label for="category">Category</label>
                               <select name="category" class="form-control">							  
							  <?php
							    $query = $this->db->query("SELECT * FROM category WHERE status='1'");
								  foreach($query->result_array() as $row)
									{
									 ?>
								         <option value="<?php echo $row['id']; ?>" <?php if($post->pcatid==$row['id']){ echo 'selected'; }else{ }; ?>><?php echo $row['catname']; ?></option>
									<?php  } ?>
							  </select>							  
							</div>
						  
							<div class="form-group col-md-6">
								<label for="category">Title</label> 
								<input type="text" name="title" id="title" class="form-control" placeholder="Title" value="<?php echo $post->pname;?>" required=""/>
								<input type="hidden" name="pid" id="pid" class="form-control" value="<?php echo $post->pid;?>"/>
                                <input type="hidden" name="action" id="action" class="form-control" value="update"/>								
							</div>	
							
							<div class="form-group col-md-12">
								<label for="category">Description</label> 
								<textarea class="form-control" id="editortext<?php echo $post->pid;?>" name="content" placeholder="Content" required=""><?php echo $post->pcontent; ?></textarea>
							</div>	
							
							<div class="form-group col-md-6">
							  <label for="category">Image</label>
							   <input type="file" class="form-control" name="image1" id="image1">
							   <input type="hidden" name="oldimg" value="<?php echo $post->pimg; ?>"> 
							   
							   <img src="<?php echo base_url().'upload/images/'.$post->pimg;?>" style="width:70px; border-radius:50%"> 
				 
							</div>

							<div class="form-group col-md-6">
							  <label for="category">Audio</label>
							  <input type="file" class="form-control" name="fileToUpload" id="rll">
							  <input type="hidden" name="oldfile" value="<?php echo $post->pfile; ?>"> 
							  
							  <audio id="rllly" controls style="width: 100%; height: 40px; top:20px">
							    <source src="<?php echo base_url().'upload/images/'.$post->pfile;?>" id="rlly" />
							  </audio>
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
				  <script src="<?php echo base_url(); ?>design/ckeditor/ckeditor.js"></script>
				  <script>
				   CKEDITOR.replace('editortext<?php echo $post->pid;?>');
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
    