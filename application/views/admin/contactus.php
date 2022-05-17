
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
       
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
          <div class="box box-default">
            
            <div class="box-body">
			
			  <a href="<?= base_url(); ?>admin/termscondition" class="btn btn-default">
                Terms & Conditions
              </a>
			  
              <a href="<?= base_url(); ?>admin/aboutus" class="btn btn-default">
                About Us
              </a>
              
              <a href="<?= base_url(); ?>admin/contactus" class="btn btn-danger">
                Contact Us
              </a>
			  
			    <span style="float: right;">
				   <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal-info">
					Edit Content
				  </button>
				</span>	
           
            </div>
          </div>
        </div>
      </div>
      <!-- /.row (main row) -->
	  
	  
	  
	   <div class="row">
        <div class="col-md-12">
          <div class="box box-default">
            <div class="box-header with-border">
              <i class="fa fa-warning"></i>

              <h3 class="box-title">CONTACT US</h3>
            </div>
            <!-- /.box-header -->
           <div class="box-body">
			
			 <?php
			   $i=0;
               foreach($data as $post) 
                {  
				   $exval = explode(',', $post->content);
				 
             ?>   	    
              
			   <?php echo '<p>Contact Us: '.$exval[0].'</p>'; ?>
			   <?php echo '<p>Email ID: '.$exval[1].'</p>'; ?>
			  
			  
			    <div class="modal fade" id="modal-info">
				  <div class="modal-dialog" style="width: 60%;">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Contact Us</h4>
					  </div>
					  
					 <form action="<?php echo base_url(); ?>admin/updatecompanyprofile" method="POST"> 
					  
						  <div class="modal-body">
						    <label>Contact No.</label>
							<input type="hidden" class="form-control" id="title" name="title" value="Contact Us">
							<input type="text" class="form-control" id="contactno" name="contactno" Placeholder="Contact No" value="<?php if($exval[0]){ echo $exval[0]; } else { echo ''; } ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" minlength="6" maxlength="15" required="">
						  </div>
						  
						  <div class="modal-body">
						    <label>Email ID</label>
							
							 <input type="email" class="form-control" id="emailid" name="emailid" Placeholder="Email ID" value="<?php if($exval[1]){ echo $exval[1]; } else { echo ''; } ?>" required="">
							 <!-- <textarea class="form-control" id="editortext" name="content" placeholder="Content" required=""><?php //if($post->content){ echo $post->content; } else { echo ''; } ?></textarea>-->
						  </div>
						  
						
					   
						  <div class="modal-footer">
							<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Save changes</button>
						  </div>
					  
					 </form> 
					 
					  
					</div>
					<!-- /.modal-content -->
				  </div>
				  <!-- /.modal-dialog -->
				</div>
				<!-- /.modal -->
			  
			  <?php 
					
					$i++;
				}
			  ?>
			  
			  
              
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
  
  