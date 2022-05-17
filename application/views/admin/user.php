
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
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">User List</h3>
			  
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>SR. No.</th>
				  <th>Image</th>
                  <th>Name</th>
                  <th>Email/Contact</th>
                  <th>SSN No</th>
				  <th>Address</th>
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
					 
					 
					 <td><img src="<?php echo base_url().'upload/users/'.$post->image;?>" style="width:60px"></td>
					
					 <td><?php echo $post->firstname.' '.$post->lastname; ?></td>
					 
					 <td><?php echo '<b>Email: </b>'.$post->email.'<br>'.'<b>Contact No: </b>'.$post->contact; ?></td>
					 <td><?php echo $post->ssnnum;?></td>
					 
					 <td><?php echo $post->address.', '.$post->city.',<br>'.$post->country.'-'.$post->postalcode ;?></td>
					 
					 
					 <td><i data="<?php echo $post->id;?>" class="status_checks btn
					  <?php echo ($post->status)?
					  'btn btn-block btn-success btn-sm': 'btn btn-block btn-danger btn-sm'?>"><?php echo ($post->status)? 'Active' : 'Inactive'?>
					 </i></td>
					 
					 <td width="80px">					 
				     <a href="<?php echo base_url().'admin/dashboard/userdelete/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm delete_data" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i></a>
					 
					 <a href="" class="btn btn-block1 btn-danger btn-sm delete_data" title="Block"><i class="fa fa-trash"></i></a>
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
    