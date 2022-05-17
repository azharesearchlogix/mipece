
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">UnRegistered Service Provider</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">UnRegistered Service Provider List</h3>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
                  <th>Name</th>
                  <th>Email/Contact</th>
                  <th>SSN No</th>
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
					 
					 
					
					 <td><?php echo $post->firstname.' '.$post->lastname; ?></td>
					 
					 <td><?php echo '<b>Email: </b>'.$post->email.'<br>'.'<b>Contact No: </b>'.$post->contact; ?></td>
					 <td><?php echo $post->ssnnum;?></td>
					 
				
					<td>
						 <i class="btn btn-block btn-danger btn-sm" onClick="send_verifymail(<?php echo $post->id;?>)"><?php if($post->status=='2'){ echo 'Verify';}  ?>
						 </i>
					 </td>
					 
					 <td width="130px">		

                     <a href="<?php echo base_url().'admin/dashboard/userview/'.$post->id; ?>" class="btn btn-block1 btn-success btn-sm" title="View"><i class="fa fa-eye"></i> View</a>
					 
					  <a href="<?php echo base_url().'admin/dashboard/userdelete/unregisteredServiceProvider/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm delete_data" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i> Delete</a>
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
    