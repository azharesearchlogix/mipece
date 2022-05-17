
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Charges</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Charges</h3>
			  
			  <a href="" class="btn btn-success btn-sm" title="Add Podcast" style="float: right;" data-toggle="modal" data-target="#modalcharge"><i class="fa fa-plus"> </i> Add New</a>
            </div>
			
			<!-- /.modal -->
			<div class="modal fade" id="modalcharge" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add New</h3>
						 </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/chargepost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						    <div class="form-group col-md-6">
							  <label for="category">No. of Member</label>
							  <input type="number" class="form-control" id="exampleInputEmail1" name="member" placeholder="" required="">
							  <input type="hidden" name="action" id="action" class="form-control" value="insert"/>
							</div>
							
						  <div class="form-group col-md-6">
							  <label for="category">Amount</label>	
							  <div class="input-group">
								<span class="input-group-addon">$</span>
								<input type="text" class="form-control" name="amount" placeholder="Amount" required="">
								<span class="input-group-addon">.00</span>
							  </div>
						   </div>	
						   
						   
						     <div class="form-group col-md-6">
								<label>Start Date:</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="date" class="form-control pull-right" id="datepicker" name="startdate">
								</div>
								<!-- /.input group -->
							  </div>
							  
							  <div class="form-group col-md-6">
								<label>End Date:</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="date" class="form-control pull-right" id="datepicker1" name="enddate">
								</div>
								<!-- /.input group -->
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
                  <th>No. of Member</th>
				  <th>Price</th>
				  <th>Timeline</th>
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
					 <td><?php echo $post->num_member;?></td>
					
					 <td>$ <?php echo $post->amount;?></td>
					 
					 <td><?php echo '<b>Start Date: </b>'.$post->startdate.'<br><b>End Date: </b>'.$post->enddate; ?></td>
					 
					 <td>					 
					 <a href="#" class="btn btn-block1 btn-success btn-sm" title="Edit" data-toggle="modal" data-target="#modalupdate<?php echo $post->id;?>"><i class="fa fa-edit"> </i></a> 
					 
				     <a href="<?php echo base_url().'admin/dashboard/chargeDelete/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>					 
					 </td>
				  </tr>

                 <!-- /.modal -->
			<div class="modal fade" id="modalupdate<?php echo $post->id;?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							  <span aria-hidden="true">&times;</span></button>
							<h3 class="modal-title">Add New</h3>
						 </div>
						
						
					  <form method="POST" action="<?php echo site_url('admin/dashboard/chargepost');?>" enctype="multipart/form-data">
						<div class="modal-body">
						  
						  <div class="row">
						  
						    <div class="form-group col-md-6">
							  <label for="category">No. of Member</label>
							  <input type="number" class="form-control" id="exampleInputEmail1" name="member" placeholder="" required="" value="<?php echo $post->num_member;?>">
							  
							  <input type="hidden" name="cid" id="cid" class="form-control" value="<?php echo $post->id;?>"/>
							  <input type="hidden" name="action" id="action" class="form-control" value="update"/>
							</div>
							
						  <div class="form-group col-md-6">
							  <label for="category">Amount</label>	
							  <div class="input-group">
								<span class="input-group-addon">$</span>
								<input type="text" class="form-control" name="amount" placeholder="Amount" required="" value="<?php echo $post->amount;?>">
								<span class="input-group-addon">.00</span>
							  </div>
						   </div>	
						   
						   
						     <div class="form-group col-md-6">
								<label>Start Date:</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="date" class="form-control pull-right" id="datepicker" name="startdate" value="<?php echo $post->startdate;?>">
								</div>
								<!-- /.input group -->
							  </div>
							  
							  <div class="form-group col-md-6">
								<label>End Date:</label>

								<div class="input-group date">
								  <div class="input-group-addon">
									<i class="fa fa-calendar"></i>
								  </div>
								  <input type="date" class="form-control pull-right" id="datepicker1" name="enddate" value="<?php echo $post->enddate;?>">
								</div>
								<!-- /.input group -->
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
  
   