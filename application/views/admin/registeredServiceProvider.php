
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Dashboard
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Registered Service Provider</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Registered Service Provider List</h3>
			  
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
                  <th>Criminal Record</th>
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
					 <td><?php echo (($post->verificationstatus == '0') ? '<span class="btn-danger btn-sm">Criminal</span>' : '<span class="btn-success btn-sm">Not Criminal</span>') ?></td>
					 
					 <td>
					    <?php if($post->status=='1'){ ?>
										   
							   <i data="<?php echo $post->id;?>" class="btn btn-block btn-success btn-sm">Activated</i>
							   
						  <?php }else{ ?>
						  
							   <i data="<?php echo $post->id;?>" class="adminstatus_checks btn btn-block btn-success btn-sm">Active</i>
							   
						  <?php } ?>
				  
						  <?php if($post->status=='0'){ ?>
							   
							   <i data="<?php echo $post->id;?>" class="adminstatus_checks btn btn-block btn-danger btn-sm">Inactive</i>
							   
						  <?php }else{ ?>
						  
							   <i data="<?php echo $post->id;?>" class="adminstatus_checks btn btn-block btn-danger btn-sm">Inactive</i>
							   
						  <?php } ?>
					 </td>
					 
					 <td width="130px">		

                     <a href="<?php echo base_url().'admin/dashboard/userview/'.$post->id; ?>" class="btn btn-block1 btn-success btn-sm" title="View"><i class="fa fa-eye"></i> View</a>
					 
				     <a href="<?php echo base_url().'admin/dashboard/userdelete/registeredServiceProvider/'.$post->id; ?>" class="btn btn-block1 btn-danger btn-sm delete_data" onClick="return confirm('Are you sure you want to delete?')" title="Delete"><i class="fa fa-trash"></i> Delete</a>
					 
					 
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
    