
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
			
			  <a href="<?= base_url(); ?>admin/termscondition" class="btn btn-danger">
                Terms & Conditions
              </a>
			  
              <a href="<?= base_url(); ?>admin/aboutus" class="btn btn-default">
                About Us
              </a>
              
              <a href="<?= base_url(); ?>admin/contactus" class="btn btn-default">
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

              <h3 class="box-title">TERMS & CONDITIONS</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			
			 <?php
               foreach($data as $post) 
                {  
             ?>   	
              
			  <?php echo $post->content; ?>
			  
			  
			    <div class="modal fade" id="modal-info">
				  <div class="modal-dialog" style="width: 60%;">
					<div class="modal-content">
					  <div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						  <span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Terms & Conditions</h4>
					  </div>
					  
					 <form action="<?php echo base_url(); ?>admin/updatecompanyprofile" method="POST"> 
					  
						  <div class="modal-body">
							<input type="hidden" class="form-control" id="title" name="title" value="Terms & Condition">
							<textarea class="form-control" id="editortext" name="content" placeholder="Content" required=""><?php if($post->content){ echo $post->content; } else { echo ''; } ?></textarea>
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
  
  