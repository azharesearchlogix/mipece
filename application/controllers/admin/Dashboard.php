<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	
	function __construct() {
		parent::__construct();
		$this->load->model('admin_model');  
		 $this->admin_model->CheckLoginSession();

        	
	}
	
	function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

function random_strings($length_of_string) 
{ 
  
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
 
    return substr(str_shuffle($str_result),0, $length_of_string); 
}
	
	public function index()	{
	    $provider = $this->db->query('SELECT COUNT(id) as total, create_date FROM `logincr` WHERE create_date is not null AND usertype="0" AND status="1" AND YEAR(create_date) = YEAR(CURDATE()) GROUP BY MONTH(create_date)')->result();
        $user = $this->db->query('SELECT COUNT(id) as total, create_date FROM `logincr` WHERE create_date is not null AND usertype="1" AND status="1" AND YEAR(create_date) = YEAR(CURDATE()) GROUP BY MONTH(create_date)')->result();
          $sc = $this->db->query('SELECT COUNT(id) as total, create_date FROM `logincr` WHERE create_date is not null AND usertype="2" AND status="1" AND YEAR(create_date) = YEAR(CURDATE()) GROUP BY MONTH(create_date)')->result();
        for ($i = 0; $i <= 11; $i++) {
            $months[]=  date('F', mktime(0,0,0,($i+1), 1, date('Y')));  
            if (key_exists($i, $provider)) {
                $providers[] = $provider[$i]->total;
            } else {
                $providers[] = 0;
            }
            if (key_exists($i, $user)) {
                $users[] = $user[$i]->total;
            } else {
                $users[] = 0;
            }
            if (key_exists($i, $sc)) {
                $staffingcompanies[] = $sc[$i]->total;
            } else {
                $staffingcompanies[] = 0;
            }
        }
        $data = [
            'content' => 'home',
            'title' => 'Dashboard',
            'providers' => $providers,
            'users' => $users,
            'staffingcompanies' => $staffingcompanies,
            'months' => $months,
        ];
        $this->load->view('admin/template/index', $data);
	   
	}



 
      function check_email_avalibility()  
      {  
           if(!filter_var($_POST["uemail"], FILTER_VALIDATE_EMAIL))  
           {  
                echo '<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> Invalid Email</span></span>';  
           }  
           else  
           {  
                $this->load->model("admin_model");  
                if($this->admin_model->is_email_available($_POST["uemail"]))  
                {  
                     echo '<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> Email Already register</span>';  
                }  
                else  
                {  
                     echo '<span class="text-success"><i class="glyphicon glyphicon-ok"></i> Email Available</span>';  
                }  
           }  
      }       
	  
	  
	  function check_email_vendor()  
      {  
           if(!filter_var($_POST["vemail"], FILTER_VALIDATE_EMAIL))  
           {  
                echo '<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> Invalid Email</span></span>';  
           }  
           else  
           {  
                $this->load->model("admin_model");  
                if($this->admin_model->is_email_vendor($_POST["vemail"]))  
                {  
                     echo '<span class="text-danger"><i class="glyphicon glyphicon-remove"></i> Email Already register</span>';  
                }  
                else  
                {  
                     echo '<span class="text-success"><i class="glyphicon glyphicon-ok"></i> Email Available</span>';  
                }  
           }  
      }  
	  
	public function forgotPassword(){ 
	       
		   $this->load->view("admin/forgotPassword");  
		 
      }
	  
	  public function sentMail(){  
	       $this->load->model("admin_model");  
		   
		   $email = $this->input->post('useremail');
           $checkmail = $this->admin_model->getData('admin', 'admin_email="'.$email.'" ');
		   
		   //print_r($checkmail);
		   
		   
		   if($checkmail){
			   
			   $password = $this->random_strings(7);
			   $name = $checkmail[0]->admin_name;
			   $message ="
				  <!DOCTYPE html>
					<html>
						<head>
							<meta charset='utf-8' />
							<title>My Team</title>
							<meta name='viewport' content='width=device-width, initial-scale=1.0' />
					   
				   <style>
					table, th, td {
						border: 0px solid black;
						border-collapse: collapse;
					}
					th, td {
						padding: 5px;
						text-align: left;
					}
					</style>
					
					 </head>
						<body>
							<table>
								<tr>
									<td><h4>Dear $name,</h4></td>
								</tr>
								
								<tr>
									<td><p>Your Password is: <b> $password</b></p></td>
								</tr>
								
							
								
								
								<tr>
								   <th><h4>Sincerely, <br> My Team</h4></th>
								</tr> 
						</table>

					</body>
					</html>
					";
					$headers = "From:no-reply@esearchlogix.in\r\n";
					$headers.= "Mime-Version: 1.0\r\n";
					$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";

					$toEmail = $email;	
					
					$subject="Forgot Password";
					$mail_sent = mail($toEmail, $subject, $message, $headers);
					
					if($mail_sent){
						$this->admin_model->updateData('admin', array('admin_pass'=>md5($password), 'password'=>$password), "admin_email='".$email."' ");
						
						$this->session->set_flashdata('msg','Please check your mail!');
					}else{
						$this->session->set_flashdata('msg','Sorry! server error');
					}
			   
			   
		   }else{
			   $this->session->set_flashdata('msg','Your email is not registered.');
		   }
		   
		  $this->load->view("admin/forgotPassword");  
      }	    
	  
   public function myprofile(){  
	       $data['user'] = $this->session->userdata('admin_name');
		   $data['userid'] = $this->session->userdata('admin_id');	
          // $id = $this->uri->segment(4);  
		   //die;
           $this->load->model("admin_model");  
           $data["data"] = $this->admin_model->fetch_profile($data['userid']);  
          
		   
		   $this->load->view('admin/common/header', $data);
		   $this->load->view('admin/common/sidebar', $data);
		   $this->load->view("admin/myprofile", $data);  
		   $this->load->view('admin/common/footer');
		   
		    
      }
	  
	 function updatemyprofile() {
		    $data['user'] = $this->session->userdata('admin_name');
			$id= $this->input->post('vendorid');
			$curdate = date("Y-m-d");
			 $image = $_FILES['image']['name'];
			 if($image!=''){
				 $target = "upload/users/".basename($image);
				 
				 if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
					$msg = "Image uploaded successfully";
				}else{
					$msg = "Failed to upload image";
				}
				
			 }else{
				 $image = $this->input->post('images');
			 }
			$data1 = array(
			    //'usertype' => $this->input->post('usertype'),
				'image' => $image,
				'shopname' => $this->input->post('shopname'),
				'name' => $this->input->post('uname'),
				'email' => $this->input->post('uemail'),
				'contact' => $this->input->post('ucontact'),
				'password' => $this->input->post('upassword'),
				'address' => $this->input->post('uaddress'),
				'latitude' => $this->input->post('ulatitude'),
			    'longitude' => $this->input->post('ulongitude'),
				
				'update_date' => $curdate,			
				//'status' => $this->input->post('status')
			);
			$this->admin_model->updatemyprofile($id,$data1);
			
		   $this->session->set_flashdata('myprofile','Successfully Update!');
		   redirect('admin/dashboard/myprofile/','refresh');
		}	  
	  
	
	 public function changepassword(){  
	    // echo password_hash('@!@#$%^',PASSWORD_DEFAULT);
	    // exit;
	       $data['user'] = $this->session->userdata('admin_name');
		   $data['userid'] = $this->session->userdata('admin_id');	
          // $id = $this->uri->segment(4);  
		   //die;
           $this->load->model("admin_model");  
           //$data["data"] = $this->admin_model->getData('admin', 'id="'.$data['userid'].'" ');  
          
		   
		   $this->load->view('admin/common/header', $data);
		   $this->load->view('admin/common/sidebar', $data);
		   $this->load->view("admin/changepassword");  
		   $this->load->view('admin/common/footer');
		   
		    
      }
	
	
	function check_password() {
		    $data['user'] = $this->session->userdata('admin_name');
			$data['userid'] = $this->session->userdata('admin_id');	
			
			$currentPassword = $this->input->post('currentPassword');
			
			$checkold = $this->admin_model->checkPass($currentPassword, $data['userid']);  
			if($checkold){
				
				echo '1';
				
			}else{
				echo '2';
			}
		}
	

		
	function change_password() {
		    $data['user'] = $this->session->userdata('admin_name');
			$data['userid'] = $this->session->userdata('admin_id');	
			
			$currentPassword = $this->input->post('currentPassword');			
			$newPassword= $this->input->post('newPassword');
			$confirmPassword= $this->input->post('confirmPassword');
			 	
			  if($newPassword!='' && $confirmPassword!='' ){
				if($newPassword==$confirmPassword){
					$data1 = array(
						'password' => password_hash($newPassword, PASSWORD_DEFAULT),
					);
						if($this->admin_model->checkPass($data['userid'] , $currentPassword))
						{
					$this->admin_model->change_password($data['userid'],$data1);
					$this->session->set_flashdata('password','Successfully Update!');
						}else{
						    echo "0";
						}
		            
				}else{
					echo '2';
				}
			  
			}else{
				echo '3';
			}
			
			redirect('admin/changepassword','refresh');
			
			//$this->admin_model->updatemyprofile($id,$data1);
			
		   //$this->session->set_flashdata('password','Successfully Update!');
		   //redirect('admin/changepassword','refresh');
		}	
	

   public function registeredUser()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
        // $data['data'] = $this->admin_model->getData('logincr',"status='0' OR status='1' AND usertype='user'");
         $data['data'] = $this->db->select('*')->from('logincr')->where('usertype','1')->where("(status='0' OR status= '1')")->order_by('update_date','DESC')->get()->result();
        
       
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/registeredUser', $data); 
		$this->load->view('admin/common/footer');
	}
	
	public function unregisteredUser()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
        $data['data'] = $this->admin_model->getData('logincr',"status='2' AND usertype='1'");
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/unregisteredUser', $data);
		$this->load->view('admin/common/footer');
	}
	
	
	
	public function registeredServiceProvider()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
// 		$data['data'] = $this->admin_model->getData('logincr',"status='0' OR status='1' AND usertype='serviceprovider'");
		$data['data'] = $this->db->select('*')->from('logincr')->where('usertype','0')->where("(status='0' OR status= '1')")->order_by('update_date','DESC')->get()->result();
		
// 		echo '<pre>';
// 		$d = $this->db->select('*')->from('logincr')->where('usertype','serviceprovider')->where(['status'=>'0','status'=>'1'])->get()->result();
// 		print_r($this->admin_model->getData('logincr',"status='0' OR status='1' AND usertype='serviceprovider'"));
// 	    print_r($d);
// 		die;
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/registeredServiceProvider', $data);
		$this->load->view('admin/common/footer');
	}
	
	public function unregisteredServiceProvider()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        
		$data['data'] = $this->admin_model->getData('logincr',"status='0' AND usertype='serviceprovider'");
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/unregisteredServiceProvider', $data);
		$this->load->view('admin/common/footer');
	}
	
	public function userview(){  
	       $data['user'] = $this->session->userdata('admin_name');
           $id = $this->uri->segment(4);  
		   
		  
           $this->load->model("admin_model");  
           $data["data"] = $this->admin_model->getData('logincr', 'id ='.$id);  
		   $data["teamlist"] = $this->admin_model->getData('myteams', 'user_id ='.$id);	
		   
		   $this->load->view('admin/common/header', $data);
		   $this->load->view('admin/common/sidebar', $data);
		   $this->load->view("admin/userview", $data);  
		   $this->load->view('admin/common/footer');
		    
      }
	
	
	
	public function userstatus(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d");
		   
		   $id = $this->input->post('id');		   
		   $status = $this->input->post('status');
		   
		   $userdetail = $this->admin_model->getData('logincr', 'id ='.$id);
		   
		   $name = $userdetail[0]->firstname.' '.$userdetail[0]->lastname;
		   $email = $userdetail[0]->email;
		   
		   if($status=='1'){
			   
			   $msg = 'Your account has been REJECTED. Please contact to support team.';
			   $subj = 'Account Reject.';
			   
		   }else{
			   $msg = 'Welcome to Myteam and Your account is activate.';
			   $subj = 'Account Accept.';
		   }
			   
			   $message ="
								  <!DOCTYPE html>
									<html>
										<head>
											<meta charset='utf-8' />
											<title>My Team</title>
											<meta name='viewport' content='width=device-width, initial-scale=1.0' />
									   
								   <style>
									table, th, td {
										border: 0px solid black;
										border-collapse: collapse;
									}
									th, td {
										padding: 5px;
										text-align: left;
									}
									</style>
									
									 </head>
										<body>
											<table>
												<tr>
													<td><h4>Dear $name,</h4></td>
												</tr>
												
												
												<tr>
													<td><p>$msg</p></td>
												</tr>
												
												
												<tr>
												   <th><h4>Sincerely, <br> MyTeam</h4></th>
												</tr> 
										</table>

									</body>
									</html>
									";
									$headers = "From:no-reply@esearchlogix.in\r\n";
									$headers.= "Mime-Version: 1.0\r\n";
									$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";

									$toEmail = $email;	
									
									$subject=$subj;
									$mail_sent = mail($toEmail, $subject, $message, $headers);
		    $St = $this->input->post('status');
            if ($this->input->post('status') == '1') {
                $St = '0';
            }
            if ($this->input->post('status') == '0') {
                $St = '1';
            }
		   $data = array(
			    'status' => $St,				
				'update_date' => $curdate,			
			);
			
			$this->admin_model->userstatus($id, $data); 			
		    redirect('admin/registeredUser','refresh');
		 }
		 
		 
		 
		 
		 public function verifyLink(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d h:i");
		   
		   $id = $this->input->post('id');
		   
			$getData = $this->admin_model->getData('logincr', "id='".$id."' AND status='2' ");
			if($getData[0]->email!=''){
				
				$name   = $getData[0]->firstname;
				$phoneno  = $getData[0]->contact;
				$email   = $getData[0]->email;
				
			    $verifymail = base_url().'admin/dashboard/verifymail/'.base64_encode($id);
				
			             $message ="
								  <!DOCTYPE html>
									<html>
										<head>
											<meta charset='utf-8' />
											<title>My Team</title>
											<meta name='viewport' content='width=device-width, initial-scale=1.0' />
									   
								   <style>
									table, th, td {
										border: 0px solid black;
										border-collapse: collapse;
									}
									th, td {
										padding: 5px;
										text-align: left;
									}
									</style>
									
									 </head>
										<body>
											<table>
												<tr>
													<td><h4>Dear $name,</h4></td>
												</tr>
												
												<tr>
													<td><p>Please <a href=".$verifymail.">Click here</a> to verify account. </p></td>
												</tr>
												
												
												
												<tr>
												   <th><h4>Sincerely, <br> MyTeam</h4></th>
												</tr> 
										</table>

									</body>
									</html>
									";
									$headers = "From:no-reply@esearchlogix.in\r\n";
									$headers.= "Mime-Version: 1.0\r\n";
									$headers.= "Content-Type: text/html; charset=ISO-8859-1\r\n";

									$toEmail = $email;	
									
									$subject="Verify Email";
									$mail_sent = mail($toEmail, $subject, $message, $headers); 
									
							if($mail_sent){
                                echo '1';
							}else{
                                echo '2';
							}	

			}else{
                echo '0';
			}				
			   
	   }


     public function verifymail(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d h:i");
		   
		     $id = $this->uri->segment(4);  
		    $decodeid = base64_decode($id);
		   
		   //die;
		   
		   $data = array(
			    'status' => '0',				
				'update_date' => $curdate,			
			);
			
			$response = $this->admin_model->updateData('logincr', $data, 'id="'.$decodeid.'"');
            if($response){
				
				$this->load->view('admin/verifymail');
			   
			}else{
			    
			    echo '0';
			}			
			
			
	   }	   		 
		 
		 
		 
	
	public function userdelete(){  
	       
		   $url = $this->uri->segment(4);
           $id = $this->uri->segment(5);          
		   $this->admin_model->deleteData('logincr','id='.$id);		   
           redirect('admin/'.$url,'refresh'); 
		   
      }
      
      
     public function services()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        $data['data'] = $this->admin_model->fullData('services');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/services', $data);
		$this->load->view('admin/common/footer');
	} 	  
	
	
	public function servicepost(){
		$curdate = date("Y-m-d h:i");
		$this->load->model("admin_model");  
			  
			$action = $this->input->post('action');
            if($action =='update'){	 
                 $id = $this->input->post('sid');  			
				 $formval = array(
				        'serviceName' => $this->input->post('title'),
						'create_at' => $curdate,			
						'status' => $this->input->post('status')
					);
					
			       $this->admin_model->updateData('services', $formval, 'id='.$id);				   
				   $this->session->set_flashdata('message','Successfully! Data Updated');
			}else{				  
				   $formval = array(				 
						'serviceName' => $this->input->post('title'),
						'create_at' => $curdate,
                        'update_at' => $curdate,  						
						'status' => $this->input->post('status')
					);
				  
                  $this->admin_model->common_insert('services', $formval);				   
				  $this->session->set_flashdata('message','Successfully! Data Add');
			  }		  
		
           redirect('admin/services');
		
		
	}
    
   public function servicestatus(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d h:i");
		   
		   $id = $this->input->post('id');
		   $data = array(
			    'status' => $this->input->post('status'),				
				'update_at' => $curdate,			
			);
			
			$this->admin_model->updateData('services', $data, 'id='.$id);			
		    redirect('admin/services');
		 }
	
	 public function servicedelete(){  
	       
           $id = $this->uri->segment(4);          
		   $this->admin_model->deleteData('services','id='.$id);		   
           redirect('admin/services'); 
		   
      } 	 
 
	  
	public function question()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        $data['data'] = $this->admin_model->fullData('question');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/question', $data);
		$this->load->view('admin/common/footer');
	} 
	
	
	public function questionpost(){
		$curdate = date("Y-m-d");
		$this->load->model("admin_model");  
			  
			$action = $this->input->post('action');
            if($action =='update'){	 
                 $id = $this->input->post('qid');  			
				 $formval = array(
				        'question' => $this->input->post('title'),
						'qoption' => $this->input->post('option'),
						'answer' => $this->input->post('answer'),
						'usertype' => $this->input->post('usertype'),
						'create_at' => $curdate,			
						'qstatus' => '1'
					);
					
			       $this->admin_model->updateData('question', $formval, 'qid='.$id);				   
				   $this->session->set_flashdata('question','Successfully! Data Update');
			}else{				  
				   $formval = array(				 
						'question' => $this->input->post('title'),
						'qoption' => $this->input->post('option'),
						'answer' => $this->input->post('answer'),
						'usertype' => $this->input->post('usertype'),
						'create_at' => $curdate,			
						'qstatus' => '1'
					);
				  
                  $this->admin_model->common_insert('question', $formval);				   
				  $this->session->set_flashdata('question','Successfully! Data Inserted');
			  }		  
		
           redirect('admin/question');
		
		
	}
	
	
	  public function questionstatus(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d");
		   
		   $id = $this->input->post('id');
		   $data = array(
			    'status' => $this->input->post('status'),				
				'bupdate_at' => $curdate,			
			);
			
			$this->admin_model->questionstatus($id, $data); 			
		    redirect('admin/question');
		 }
	
	 public function questiondelete(){  
	       
           $id = $this->uri->segment(4);          
		   $this->admin_model->deleteData('question','qid='.$id);		   
           redirect('admin/question'); 
		   
      }  
      
      
      
      
    public function charges()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
        $data['data'] = $this->admin_model->getData('usercharge');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/charges', $data);
		$this->load->view('admin/common/footer');
	}


    public function teams()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
    	$result = $this->db->select('a.*,b.name,CONCAT(c.firstname, " "  , c.lastname) AS user_name,c.image as user_image')->from('myteams as a')
                        ->join('tbl_language as b', 'a.language = b.id', 'left')
                        ->join('logincr as c', 'a.user_id = c.id', 'left')
                        ->where('c.id IS NOT NULL')
                       ->get()->result();
                       $data['data'] = $result;
		
        //$data['data'] = $this->admin_model->getData('myteams', "1 ORDER BY id DESC");
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/teams', $data);
		$this->load->view('admin/common/footer');
	}	  
	  
	
    public function teamDelete(){  
	       
           $id = $this->uri->segment(4);          
		   $this->admin_model->deleteData('myteams','id='.$id);		   
           redirect('admin/teams'); 
		   
      } 
	
    public function chargepost(){
		$curdate = date("Y-m-d h:i");
		$this->load->model("admin_model");  
			  
			$action = $this->input->post('action');
            if($action =='update'){	 
                 $id = $this->input->post('cid');  			
				 $formval = array(
				        'num_member' => $this->input->post('member'),
						'amount' => $this->input->post('amount'),
						'startdate' => $this->input->post('startdate'),
						'enddate' => $this->input->post('enddate'),
									
						'update_at' => $curdate
					);
					
			       $this->admin_model->updateData('usercharge', $formval, 'id='.$id);				   
				   $this->session->set_flashdata('message','Successfully! Data Updated');
			}else{				  
				   $formval = array(				 
						'num_member' => $this->input->post('member'),
						'amount' => $this->input->post('amount'),
						'startdate' => $this->input->post('startdate'),
						'enddate' => $this->input->post('enddate'),
						
						'create_at' => $curdate,			
						'update_at' => $curdate
					);
				  
                  $this->admin_model->common_insert('usercharge', $formval);				   
				  $this->session->set_flashdata('message','Successfully! Data Add');
			  }		  
		
           redirect('admin/charges');
		
		
	}	
	
	
	public function chargeDelete(){  
	       
           $id = $this->uri->segment(4);          
		   $this->admin_model->deleteData('usercharge','id='.$id);		   
           redirect('admin/charges'); 
		   
      }   
	  

    public function category()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
    	
        $data['data'] = $this->admin_model->getData('category', "catid='0'");
        
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/category', $data);
		$this->load->view('admin/common/footer');
	}
	
	
	public function categorypost(){
		$curdate = date("Y-m-d");
		$this->load->model("admin_model");

            $action = $this->input->post('action');
            if($action =='update'){	 
                 $id = $this->input->post('cid');  			
				 $formval = array(			
						'catname' => $this->input->post('categoryname'),
						'update_at' => $curdate,			
						'status' => $this->input->post('status')
					);
					
			       $this->admin_model->categoryupdate($id, $formval);				   
				   $this->session->set_flashdata('message','Successfully! Data Update.');
			}else{				  
				   $formval = array(				 
						'catname' => $this->input->post('categoryname'),
						'create_at' => $curdate,
						'update_at' => $curdate,			
						'status' => $this->input->post('status')
					);
				  
				  $this->admin_model->categoryadd($formval);				   
				  $this->session->set_flashdata('message','Successfully! Data Add.');
			  }			
						  
		   
			
		  redirect('admin/category');
		
		
	}
	
	
	public function categorystatus(){  
	       $data['user'] = $this->session->userdata('admin_name');          
           $this->load->model("admin_model");  
		   $curdate = date("Y-m-d");
		   
		   $id = $this->input->post('id');
		   $data = array(
			    'status' => $this->input->post('status'),				
				'update_at' => $curdate,			
			);
			
			$this->admin_model->categorystatus($id, $data); 			
		    redirect('admin/category');
		 }
	
	public function categorydelete(){  
	       
           $id = $this->uri->segment(4);          
		   $this->admin_model->deleteData('category','id='.$id);

		   if($this->uri->segment(5)!=''){
			   
			   $catid = $this->uri->segment(5); 
			   redirect('admin/subcategory/'.$catid);			   
                 
		   }else{
			   redirect('admin/category');
		   }
		   
      }  
	
	
	public function subcategory()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
		
		$id = $this->uri->segment(3);
		
        $data['catlist'] = $this->admin_model->getData('category','catid='.$id);
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/subcategory', $data);
		$this->load->view('admin/common/footer');
	}
	
	
	
	public function subcategorypost(){
		$curdate = date("Y-m-d h:i");
		$this->load->model("admin_model");

            $catid = $this->input->post('catid');
			
            $action = $this->input->post('action');
            if($action =='update'){	 
                 $id = $this->input->post('scid');  			
				 $formval = array(			
						'catname' => $this->input->post('categoryname'),
						'update_at' => $curdate,			
						'status' => $this->input->post('status')
					);
					
			       $this->admin_model->categoryupdate($id, $formval);				   
				   $this->session->set_flashdata('message','Successfully! Data Updated.');
			}else{				  
				   $formval = array(				 
						'catname' => $this->input->post('categoryname'),
						'catid' => $catid,
						'create_at' => $curdate,
						'update_at' => $curdate,			
						'status' => $this->input->post('status')
					);
				  
				  $this->admin_model->categoryadd($formval);				   
				  $this->session->set_flashdata('message','Successfully! Data Add.');
			  }			
						  
		   
			
		  redirect('admin/subcategory/'.$catid);
		
		
	}
	
	

	
	public function community()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        //$data['data'] = $this->admin_model->fullData('category');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/community');
		$this->load->view('admin/common/footer');
	}
	
	public function communitycomment()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        //$data['data'] = $this->admin_model->fullData('category');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/communityComment');
		$this->load->view('admin/common/footer');
	}
	
	
   public function registrationPayments()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        //$data['data'] = $this->admin_model->fullData('category');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/registrationPayments');
		$this->load->view('admin/common/footer');
	}
	
	public function servicePayments()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        //$data['data'] = $this->admin_model->fullData('category');
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/servicePayments');
		$this->load->view('admin/common/footer');
	}
   
	
	
	
	
	
	public function termscondition()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        $data['data'] = $this->admin_model->termscondition();
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/termscondition', $data);
		$this->load->view('admin/common/footer');
	}
	
	
	public function updatecompanyprofile(){

      // POST values
        $title = $this->input->post('title');	 
		
		if($title=='Contact Us'){
			$contactno = $this->input->post('contactno');	  
		    $emailid = $this->input->post('emailid');
			
			$content = $contactno.','.$emailid;
		}else{
			$content = $this->input->post('content');
		}
		
	    $this->admin_model->updatecompanyprofile($title, $content);
		
		if($title=='Terms & Condition'){
			
			redirect('admin/termscondition','refresh');
			
		}else if($title=='About Us'){
			
			redirect('admin/aboutus','refresh');
			
		}else{
			
			redirect('admin/contactus','refresh');
			
		}
	  
        
   }
	
	public function aboutus()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        $data['data'] = $this->admin_model->aboutus();
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/aboutus', $data);
		$this->load->view('admin/common/footer');
	}
	
	public function contactus()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
        $data['data'] = $this->admin_model->contactus();
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/contactus', $data);
		$this->load->view('admin/common/footer');
	}
	
	
 public function registeredSc()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
        // $data['data'] = $this->admin_model->getData('logincr',"status='0' OR status='1' AND usertype='user'");
         $data['data'] = $this->db->select('*')->from('logincr')->where('usertype','2')->where("(status='0' OR status= '1')")->order_by('update_date','DESC')->get()->result();
        
       
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/registeredSc', $data); 
		$this->load->view('admin/common/footer');
	}
	
	public function unregisteredSc()
	{
		$this->admin_model->CheckLoginSession();
    	$data['user'] = $this->session->userdata('admin_name');
		
        $data['data'] = $this->admin_model->getData('logincr',"status='2' AND usertype='2'");
		
		$this->load->view('admin/common/header', $data);
		$this->load->view('admin/common/sidebar', $data);
		$this->load->view('admin/unregisteredSc', $data);
		$this->load->view('admin/common/footer');
	}
	public function logout()
	{   
		$this->session->sess_destroy();
		redirect('admin/login','refresh');
	}
}
