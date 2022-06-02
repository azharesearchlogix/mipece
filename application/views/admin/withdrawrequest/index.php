
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">All Withdrawal Request</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">All Withdrawal Request</h3>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
                  <th>User Type</th>
                  <th>Name</th>
                  <th>Contact Details</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Comment</th>
                  <th>Status</th>
				          <th>Action</th>
                </tr>
                </thead>
                <tbody>
				
                <?php 
				 $i=1;
				foreach($alldata as $post){
					?>
				 <tr>
					 <td><?php echo $i;?></td>
					 <td><?= ($post->request_as_a=='2')?'Staffing Company':'Service Provider';?></td>
					 
					
					 <td><?php echo $post->name; ?></td>
					 
					 <td><?php echo '<b>Email: </b>'.$post->email.'<br>'.'<b>Contact No: </b>'.$post->contact; ?></td>
					 <td><?php echo $post->amount;?></td>
					 <td><?php echo date('d-m-Y H:i', strtotime($post->created_at));?></td>
					 <td><?php echo $post->comments; ?></td>
					 
					 
					 
					 <td>
					    <?php if($post->payment_status=='1'){ ?>
										   
							   <span style="color: green; font-weight: bold;">Payment Paid</span>
							   
						  <?php }else if($post->payment_status=='2'){ ?>
						  	<span style="color: orange; font-weight: bold;">Payment Declined</span>

						  <?php }else{?>
						  
							  <span style="color: red; font-weight: bold;">Pending</span>
							   
						  <?php } ?>
				  
					 </td>
					 
					 <td>		
					 	 <?php if($post->payment_status=='0'){ ?>
          <button class="btn btn-warning btn-xs" data-toggle="modal" data-target="#myModalWithdraw" onclick="changeStatus(<?php echo $post->id.','.$post->payment_status.','.$post->request_as_a;?>)"><i class="fas fa-ban"></i> Change Payment Status</button>
          	  <?php }else{ ?>

          	  <?php }
          	  ?>
					 
					 
					 </td>
				  </tr>     
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
    
  <!-- Modal -->
<div id="myModalWithdraw" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Payment Status</h4>
      </div>
      <form method="post" action="<?=site_url('admin/withdrawrequest/changestatus');?>">
      <div class="modal-body">
      	<input type="hidden" name="pid" id="pid" required>
      	<input type="hidden" name="usertype" id="usertype" required>
        <p><label for="status">Status</label>
        	<select name="status" id="status" class="form-control">
        		<option value="">Select Status</option>
        		<option value="1">Approved</option>
        		<option value="2">Declined</option>
        	</select>
        </p>
         <p><label for="comment">Comment</label>
        	<textarea name="comment" id="comment" class="form-control" placeholder="Comment"></textarea>
        	</select>
        </p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </div>
  </form>

  </div>
</div>

