<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Api extends CI_Controller {
function __construct() {
		parent::__construct();

		date_default_timezone_set('Asia/Kolkata'); 
		$militime =round(microtime(true) * 1000);
		$datetime =date('Y-m-d h:i:s');
		define('militime', $militime);
		define('datetime', $datetime);
		
	}
	
	
	
	function _remap($method)
    {
        if(method_exists($this,$method))
        {
            call_user_func(array($this, $method));
            return false;
        }
        else
        {
			
			$dataa_array['methdodcheck'][]  = array(
					    			 					'status'=>'failed',	
							                  			'message'=>'Method not found',
														'responsecode'=>'404'
							                  			
										            );
			
            
        }
		header("content-type: application/json");
		echo json_encode($dataa_array);
    }



	
function random_password($length = 8) 
{
$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
$password = substr( str_shuffle( $chars ), 0, $length );
return $password;
}	 





	
public function userSignup()
{
	$this->load->model('Common_model');
	$device_token = $this->input->post('device_token');
	$device_type  = $this->input->post('device_type');
	
	//$device_id = $this->input->post('device_id');
	
	$fname    = $this->input->post('firstname');
	$lname    = $this->input->post('lastname');
	
	$name = $fname.' '.$lname;
	
	$email    = $this->input->post('useremail');
	$phoneno  = $this->input->post('usercontact');
	$ssn      = $this->input->post('ssnnum');
	$password = $this->input->post('password');
	
	$address    = $this->input->post('address');
	$city      = $this->input->post('city');
	$country    = $this->input->post('country');
	$postalcode = $this->input->post('postalcode');
	$terms      = $this->input->post('termscondition');
	$usertype   = $this->input->post('usertype');
	
	$latitude   = $this->input->post('latitude');
	$longitude  = $this->input->post('longitude');
	
	$images      = $this->input->post('image');	
	$data  = trim($images);
	$data = str_replace('data:image/png;base64,', '', $data);
	$data = str_replace(' ', '+', $data);
	
	$data1 = base64_decode($data); // base64 decoded image data
	
	$imgname = uniqid().'.png';
	$file_paths = $imgname;
	$file = 'upload/users/'.$imgname;
	$success = file_put_contents($file, $data1);
	
	
	
	$date = date("Y-m-d")." ".date("H:i:s");
	$otp = sprintf("%06d", mt_rand(1, 999999));
	$status=0;
	 
   if(($device_type == 'iOS' || $device_type == 'AndroidApp'))
    { 
        $device_token = $device_token;
    }

    $auth_key = $this->rand_string(40);

    $final_output = array();
	
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      
			   if(!empty($email))
				{
				  
								$check_email = $this->Common_model->common_getRow('logincr',array('email'=>$email));
								
								if($check_email!="")
								{
									  
										$final_output['status'] = 'success';
										$final_output['message'] = 'User already registered.';	
										$final_output['responsecode'] = '403';
								
								}
								else
								{ 
							
									  $insert_array = array();
									  									
									  $insert_array['token_security']=$auth_key;
									  $insert_array['sourcemedia']=$device_type;									  
									  $insert_array['tokenid']=$device_token;
									  
									  $insert_array['usertype']=$usertype;
									  $insert_array['image']=$file_paths;
									  $insert_array['firstname']=$fname;
									  $insert_array['lastname']=$lname;
									  
									  $insert_array['email']=$email;
									  $insert_array['contact']=$phoneno;									  
									  $insert_array['ssnnum']=$ssn;
									  $insert_array['password']=password_hash($password, PASSWORD_DEFAULT);//md5($password);
									  $insert_array['otp']=$otp;
									  
									  $insert_array['address']=$address;
									  $insert_array['city']=$city;
									  $insert_array['country']=$country;
									  $insert_array['postalcode']=$postalcode;
									  $insert_array['terms']=$terms;
									   									  
									  $insert_array['latitude']=$latitude;									  
									  $insert_array['longitude']=$longitude;		
									  
									  $insert_array['create_date']=$date;
									  $insert_array['update_date']=$date;									  
									  $insert_array['status']='2';			
								
						              $insertId = $this->db->insert('logincr', $insert_array);						
						 
						             $lastid =$this->db->insert_id();
						if($insertId)
						{		
					   
							
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
													<td><h4>Dear '".$name."',</h4></td>
												</tr>
												
												<tr>
													<td><p>You have initiated account creation on My Team. Here is your OTP <b>$otp</b>.</p></td>
												</tr>
												
												<tr>
													<td><p>Never share your OTP, User ID or Password with anyone. Sharing these details can lead to unauthorised access to your account.<br>
													Looking forward to more opportunities to be of service to you.</p></td>
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
									
									$subject="Thank You for Signing Up with My Team";
									 $res = $this->Mail->sendmail($toEmail, $subject, $message);
									//$mail_sent = mail($toEmail, $subject, $message, $headers);	 
										   
									
									$final_output['responsecode'] = '200';
									$final_output['status'] = 'success';
									//$final_output['message'] = 'Thank you for registration. We have sent OTP to your email id for verification. Please confirm your email id to login into the app.';
									
									$final_output['message'] = 'Thank you for registration';
									$final_output['data'] = "$lastid";
									
						
							}else
							{ 
									$final_output['status'] = 'failed';
									$final_output['message'] = 'Something went wrong! please try again.';
									$final_output['responsecode'] = '400';
									
							}	
							
				  }	
						
				}else
				{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Required parameter not found';
					$final_output['responsecode'] = '403';
				}
		  }	else{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Invalid email format please check again.';
					$final_output['responsecode'] = '403';
		  }
 	header("content-type: application/json");
    echo json_encode($final_output);
}



  public function verifyOTP()
	{
		$this->load->model('Common_model');
		
		$email = $this->input->post('useremail');
		$otp   = $this->input->post('otp');
		
		$check_otp = $this->Common_model->verify_otp($email, $otp);
				
				
				if($check_otp==0)
				{
						$final_output['status'] = 'Failed';
						$final_output['message'] = 'Please enter valid OTP.';
						$final_output['responsecode'] = '400';						
						
				}
				else
				{ 
					$otpstatus = $this->Common_model->otp_status($email, $otp);
					$check_all_record = $this->Common_model->common_getRow('logincr',array('email'=>$email));
					
					$uids = $check_all_record->id;
					
				 $basepath=base_url();								
				 $photo = $check_all_record->image;
				 
				  if($photo=='')
				  {
					   $uphoto = $basepath."upload/users/photo.png";
				  }else{
					  $uphoto = $basepath."upload/users/".$photo;
				  }
				
					$data_array = array(
								
								'id'             => $check_all_record->id,
								'token_security' => $check_all_record->token_security,
								'photo'           => $uphoto,
								'firstname'      => $check_all_record->firstname,
								'lastname'     => $check_all_record->lastname,		
								'email'    => $check_all_record->email,								
								'contact'   => $check_all_record->contact,

                                'ssnnum'             => $check_all_record->ssnnum,
								'address' => $check_all_record->address,								
								'country'      => $check_all_record->country,
								'city'     => $check_all_record->city,		
								'postalcode'    => $check_all_record->postalcode,										
							);
							
					$final_output['responsecode'] = '200';					
					$final_output['status'] = 'success';
					$final_output['message'] = 'Thank you';
					$final_output['data'] = $data_array;
					
					
				}	

				header("content-type: application/json");
				echo json_encode($final_output);
    }
	
	
	
public function userSkill()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');
	
	$experience    = $this->input->post('experience');
	$industry  = $this->input->post('industry');
	$skills      = $this->input->post('skills');
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	 if($userid !=''){
			  $insert_array = array();
			
			  $insert_array['userid']=$userid;
			  $insert_array['experience']=$experience;
			  $insert_array['industry']=$industry;
			  $insert_array['skills']=$skills;
			 	
			  $insert_array['create_at']=$date;
			  $insert_array['update_at']=$date;	
		
			  $insertId = $this->db->insert('userskill', $insert_array);						
			 
				if($insertId)
				{		
						$final_output['responsecode'] = '200';
						$final_output['status'] = 'success';						
						$final_output['message'] = 'Thank you for skill.';
						

				}else
				{ 
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Something went wrong! please try again.';
						$final_output['responsecode'] = '400';
						
				}	
	
          }else{
			  
			    $final_output['status'] = 'failed';
			    $final_output['message'] = 'Userid not found';
			    $final_output['responsecode'] = '402';

		  }			  
				
		 
 	header("content-type: application/json");
    echo json_encode($final_output);
}




public function userEducation()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');
	
	$education    = $this->input->post('education');	
	$collegename  = $this->input->post('collegename');
	$passingyear  = $this->input->post('passingyear');
	/*$certificate  = $this->input->post('certificate');	
	
	$data  = trim($certificate);
	$data = str_replace('data:image/png;base64,', '', $data);
	$data = str_replace(' ', '+', $data);
	
	$data1 = base64_decode($data); // base64 decoded image data
	
	$imgname = uniqid().'.png';
	$file_paths = $imgname;
	$file = 'upload/users/'.$imgname;
	$success = file_put_contents($file, $data1);*/
	
 	$certificate  = $_FILES['certificate']['name'];
 	$file_path = "upload/users/";     
     $file_path = $file_path . basename($_FILES['certificate']['name']);
     move_uploaded_file($_FILES['certificate']['tmp_name'], $file_path);
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	if($userid !=''){
      
			  $insert_array = array();
												
			  $insert_array['userid']=$userid;
			  $insert_array['education']=$education;									  
			  $insert_array['collegename']=$collegename;
			  
			  $insert_array['passingyear']=$passingyear;
			  $insert_array['certificate']= $file_path;
			  
			  $insert_array['create_at']=$date;
			  $insert_array['update_at']=$date;									  
			 
			    $insertId = $this->db->insert('usereducation', $insert_array);						
 
				if($insertId)
				{		
					$final_output['responsecode'] = '200';
					$final_output['status'] = 'success';
					$final_output['message'] = 'Thank you for education';
					
				}else
				{ 
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Something went wrong! please try again.';
						$final_output['responsecode'] = '400';
						
				}	
					
						
			
		  }	else{
					$final_output['status'] = 'failed';
			        $final_output['message'] = 'Userid not found';
			        $final_output['responsecode'] = '402';
		  }
 	header("content-type: application/json");
    echo json_encode($final_output);
}
//education new api
public function userEducationNew()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');
	
	$education    = $this->input->post('education');	
	$collegename  = $this->input->post('collegename');
	$passingyear  = $this->input->post('passingyear');
	$ref1  = $this->input->post('ref1');
	$ref2  = $this->input->post('ref2');
	$ref3  = $this->input->post('ref3');
	/*$certificate  = $this->input->post('certificate');	
	
	$data  = trim($certificate);
	$data = str_replace('data:image/png;base64,', '', $data);
	$data = str_replace(' ', '+', $data);
	
	$data1 = base64_decode($data); // base64 decoded image data
	
	$imgname = uniqid().'.png';
	$file_paths = $imgname;
	$file = 'upload/users/'.$imgname;
	$success = file_put_contents($file, $data1);*/
	
	$certificate  = $_FILES['certificate']['name'];
 	$file_path = "upload/users/";     
    $file_path = $file_path . basename($_FILES['certificate']['name']);
    move_uploaded_file($_FILES['certificate']['tmp_name'], $file_path);
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	if($userid !=''){
      
			  $insert_array = array();
												
			  $insert_array['userid']=$userid;
			  $insert_array['education']=$education;									  
			  $insert_array['collegename']=$collegename;
			  
			  $insert_array['passingyear']=$passingyear;
			  $insert_array['certificate']= $file_path;
			  
			  $insert_array['create_at']=$date;
			  $insert_array['update_at']=$date;									  
			 
			    $insertId = $this->db->insert('usereducation', $insert_array);						
 
				if($insertId)
				{
				if(!empty($ref1))
				{
					$exp = explode(',',$ref1);
					$name = $exp[0];
					$email = $exp[1];
					$mobile = $exp[2];
					$this->db->insert('tbl_reference',['education_id'=>$insertId , 'name'=>$name , 'email'=>$email , 'contact'=>$mobile]);
				}
				if(!empty($ref2))
				{
					$exp1 = explode(',',$ref2);
					$name1 = $exp1[0];
					$email1 = $exp1[1];
					$mobile1 = $exp1[2];
					$this->db->insert('tbl_reference',['education_id'=>$insertId , 'name'=>$name1 , 'email'=>$email1 , 'contact'=>$mobile1]);
				}
				if(!empty($ref3))
				{
					$exp2 = explode(',',$ref3);
					$name2 = $exp2[0];
					$email2 = $exp2[1];
					$mobile2 = $exp2[2];
					$this->db->insert('tbl_reference',['education_id'=>$insertId , 'name'=>$name2 , 'email'=>$email2 , 'contact'=>$mobile2]);
				}		
					$final_output['responsecode'] = '200';
					$final_output['status'] = 'success';
					$final_output['message'] = 'Thank you for education';
					
				}else
				{ 
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Something went wrong! please try again.';
						$final_output['responsecode'] = '400';
						
				}	
					
						
			
		  }	else{
					$final_output['status'] = 'failed';
			        $final_output['message'] = 'Userid not found';
			        $final_output['responsecode'] = '402';
		  }
 	header("content-type: application/json");
    echo json_encode($final_output);
}

public function aboutYourself()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');	
	$about    = $this->input->post('about');	
	$ques_answer    = $this->input->post('ques_answer');
	$check_value    = $this->input->post('check_value');
	$other    = $this->input->post('other');
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	if($userid !=''){
      
		    //audio file upload
		$audio_file = '';
		if (!empty($_FILES['audio_file']['name'])) {
                $file = $_FILES['audio_file']['name'];
                $name = 'audio_file';
                $path = 'audio_file';
                $type = 'mid|midi|mpga|mp2|mp3|aif|aiff|aifc|ram|rm|rpm|ra|wav|m4a|aac|au|ac3|flac|ogg|wma';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $audio_file = $file_data['file'];
                }
            }
            
           if(!empty($audio_file))
            {
            	$update_value = $this->Common_model->updateData('logincr',array('about'=>$about , 'audio_file' => $audio_file ,'ques_answer'=>$ques_answer , 'check_value'=>$check_value , 'other'=>$other),array('id'=>$userid));
            }else{
            	$update_value = $this->Common_model->updateData('logincr',array('about'=>$about ,'ques_answer'=>$ques_answer , 'check_value'=>$check_value , 'other'=>$other),array('id'=>$userid));
            }
			 
			if($update_value)
			{		
				$final_output['responsecode'] = '200';
				$final_output['status'] = 'success';
				$final_output['message'] = 'Thank you for about.';
				
			}else
			{ 
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Something went wrong! please try again.';
					$final_output['responsecode'] = '400';
					
			}	
					
						
			
		  }	else{
					$final_output['status'] = 'failed';
			        $final_output['message'] = 'Userid not found';
			        $final_output['responsecode'] = '402';
		  }
 	header("content-type: application/json");
    echo json_encode($final_output);
}
	
	
public function services()
{
	
	$this->load->model('Common_model');
	
	//$usertype  = $this->input->post('usertype');	  
    $query   = $this->db->query("SELECT * FROM `services` WHERE  `status`='1' ");				   
	$record  = $query->result();
	$total   = $query->num_rows();	
	
	if($total>0)
	 {  
		foreach($record as $list){	  
		
		
	    $data_array[] =array(
				'serviceid'    => $list->id,
				'servicename'  => $list->serviceName				
				);
		}
		
			$final_output['responsecode'] = '200';				
			$final_output['status'] = 'success';
			$final_output['data'] = $data_array;
		
	 }else{
		    $final_output['responsecode'] = '402';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Record not found';	
		 
	 }
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}	
	
	
public function userServices()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');
	$servicetype    = $this->input->post('servicetype');
	$fromtime  = $this->input->post('fromtime');
	$totime      = $this->input->post('totime');	
	$fees = $this->input->post('fees');	
	
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	
			   if(!empty($userid))
				{
				  
					  $insert_array = array();
														
					  $insert_array['userid']=$userid;
					  $insert_array['servicetype']=$servicetype;									  
					  $insert_array['fromtime']=$fromtime;					  
					  $insert_array['totime']=$totime;
					  $insert_array['fees']=$fees;
					 
					  $insert_array['create_at']=$date;
					  $insert_array['update_at']=$date;									  
					 
					  $insertId = $this->db->insert('userservice', $insert_array);						
		 
						if($insertId)
						{		
									$final_output['responsecode'] = '200';
									$final_output['status'] = 'success';									
									$final_output['message'] = 'Thank you for services';
									
						
							}else
							{ 
									$final_output['status'] = 'failed';
									$final_output['message'] = 'Something went wrong! please try again.';
									$final_output['responsecode'] = '400';
									
							}	
						
				}else
				{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Userid not found';
					$final_output['responsecode'] = '403';
				}
		 
 	header("content-type: application/json");
    echo json_encode($final_output);
}


public function userLogin()
{
	
	$this->load->model('Common_model');
	
	$email       = $this->input->post('useremail');
	$password    = $this->input->post('password');
	
	$device_token = $this->input->post('device_token');
	$device_type  = $this->input->post('device_type');
	$user_type  = $this->input->post('usertype');
	
	
	$created = date('Y-m-d h:m:s');
	$modified = date('Y-m-d h:m:s');
	$otp = sprintf("%06d", mt_rand(1, 999999));
	

    if($device_type == 'iOS' || $device_type == 'AndroidApp')
    { 
        //$device_token = '';
    }

    $auth_key = $this->rand_string(40);

    $final_output = array();

    if(!empty($email && $password))
    {  
   
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) 
		  {
		 	//$check_record = $this->Common_model->checkuserlogin($email, md5($password), $user_type);
			//echo $check_record;
			$check_record = $this->Common_model->checkuserlogin($email);
			
		  if($check_record!=''){
			
			$status=$check_record[0]['status'];
			$adminstatus=$check_record[0]['adminstatus'];
		  if(!password_verify($password , $check_record[0]['password']))
		  	{
		  		$final_output['responsecode'] = '404';				
				$final_output['status'] = 'failed';
				$final_output['message'] = 'Your password wrong!';	
		  	}
		  	else if($status=='2')
			{
				$final_output['responsecode'] = '404';				
				$final_output['status'] = 'failed';
				$final_output['message'] = 'Your username is not verify.';	
		
		    }elseif($status=='0')
			{
				$final_output['responsecode'] = '405';				
				$final_output['status'] = 'failed';
				$final_output['message'] = 'Your account is not active.';	
			}
			
			else{
				
			$update_value = $this->Common_model->updateData('logincr',array('token_security'=>$auth_key, 'sourcemedia'=>$device_type, 'tokenid'=>$device_token, 'update_date'=>$modified),array('email'=>$email));
				
				$check_recordD = $this->Common_model->common_getRow('logincr',array('email'=>$email));
				
				$basepath=base_url();
				$photo = $check_recordD->image;
				$uids = $check_recordD->id;
				
				 if($photo!=''){
					   $uphoto = $basepath.'upload/users/'.$photo;
				  }
				  else
				  {
					   $uphoto = $basepath."upload/users/photo.png";
				  }
				  
		
				
				  $user_service = '';
                            $xaistatus = '0';
                            $industry = '0';

                            if ($check_recordD->usertype == '1') {
                                $check_qusans = $this->Common_model->common_getRow('userans', array('userid' => $uids));
                            } else {
                                $user_services = $this->db->get_where('userservice', ['userid' => $uids])->row();
                                if ($user_services) {
                                    $user_service = $user_services->servicetype;
                                }
                                $check_qusans = $this->Common_model->common_getRow('tbl_answer', array('user_id' => $uids));
                                $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $uids, 'language IS NOT NULL'])->row();
                                if (!empty($xai)) {
                                    $xaistatus = '1';
                                    $industry = $xai->industry_id;
                                }
                            }
		
				 if($check_qusans){
					$questionStatus = '1';
				 }else{
					 $questionStatus = '0';
				 }
				 
				 $check_account = $this->Common_model->common_getRow('accountdetails',array('userid'=>$uids));
				 if($check_account){
					 if($check_account->userid){
						 $accountStatus = '1';
					 }else{
						 $accountStatus = '0';
					 }
				 }else{
					 $accountStatus = '0';
				 }
				
				 $dataa_array  = array(	
								'id'             => $check_recordD->id,
								'token_security' => $check_recordD->token_security,
								'photo'           => $uphoto,
								'firstname'      => $check_recordD->firstname,
								'lastname'     => $check_recordD->lastname,		
								'email'    => $check_recordD->email,								
								'contact'   => $check_recordD->contact,

                                'ssnnum'             => $check_recordD->ssnnum,
								'address' => $check_recordD->address,								
								'country'      => $check_recordD->country,
								'city'     => $check_recordD->city,		
								'postalcode'    => $check_recordD->postalcode,
								'questionstatus'     => $questionStatus,
								'accountstatus'   => $accountStatus,	
								'type'    => $check_recordD->usertype,	
								'user_service' => $user_service,
								'xaistatus' => $xaistatus,
                                'industry' => $industry,
											
							);
							$final_output['responsecode'] = '200';				
							$final_output['status'] = 'success';
							$final_output['message'] = 'You have logged-in successfully.';	
							$final_output['data'] = $dataa_array;
									
				
			  }
		 
		  }
		    else{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Invalid credentials';
					$final_output['responsecode'] = '403';
					unset($final_output['data']);
			   }
			   
		  }
		   
		   			  
			else{
				$final_output['status'] = 'Failed';
				$final_output['message'] = 'Invalid email format please check again.';
				$final_output['responsecode'] = '403';
			}
	       
			
	}
	else
	{
		$final_output['status'] = 'failed';
	 	$final_output['message'] = 'Required parameters not found';
		$final_output['responsecode'] = '403';
	 	unset($final_output['data']);
	}
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}	
	
	
	public function forgotPassword()
	{
		
		$this->load->model('Common_model');
		$email = $this->input->post('useremail');
		
			
			if(filter_var($email, FILTER_VALIDATE_EMAIL)) {		   
				      
			 $otp = sprintf("%06d", mt_rand(1, 999999));	
			 
					    $check_email = $this->Common_model->common_getRow('logincr',array('email'=>$email));
						if($check_email!=''){
							
						
							$update_value = $this->Common_model->updateData('logincr',array('otp'=>$otp),array('email'=>$email));
							
							$userid = $check_email->id;
							$name   = $check_email->firstname;
							
							
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
													<td><p>To update your password, Please use the OTP <b>$otp</b>.</p></td>
												</tr>
												
												<tr>
													<td><p>Never share your OTP, User ID or Password with anyone. Sharing these details can lead to unauthorised access to your account.<br>
													Looking forward to more opportunities to be of service to you.</p></td>
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
										   
									//$this->mailsendf($subject,$message);
														
														
									$final_output['responsecode'] = '200';					
									$final_output['status'] = 'success';
									$final_output['message'] = 'Otp has been sent to your email id. Please use that otp to reset your password.';	
									
							
						}else{
							        $final_output['responsecode'] = '403';					
									$final_output['status'] = 'Failed';
									$final_output['message'] = 'Your email is not register.';	
						}
						
				}	
			else{
				$final_output['status'] = 'Failed';
				$final_output['message'] = 'Invalid email format please check again.';
				$final_output['responsecode'] = '403';
			 }

				header("content-type: application/json");
				echo json_encode($final_output);
    }
    
    
    
    
    public function forgotVerifyOTP()
	{
		$this->load->model('Common_model');
		
		$email = $this->input->post('useremail');
		$otp   = $this->input->post('otp');
		
		$check_otp = $this->Common_model->verify_otp($email, $otp);
				
				
				if($check_otp==0)
				{
						$final_output['status'] = 'Failed';
						$final_output['message'] = 'Please enter valid OTP.';
						$final_output['responseCode'] = '400';						
						
				}
				else
				{ 
					$otpstatus = $this->Common_model->forgot_otpstatus($email, $otp);
					$check_all_record = $this->Common_model->common_getRow('logincr',array('email'=>$email));
					
					$uids = $check_all_record->id;
					
				 $basepath=base_url();								
				 $photo = $check_all_record->image;
				 
				  if($photo=='')
				  {
					   $uphoto = $basepath."upload/users/photo.png";
				  }else{
					  $uphoto = $basepath."upload/users/".$photo;
				  }
				
					$data_array = array(
								
								'id'             => $check_all_record->id,
								'token_security' => $check_all_record->token_security,
								'photo'           => $uphoto,
								'firstname'      => $check_all_record->firstname,
								'lastname'     => $check_all_record->lastname,		
								'email'    => $check_all_record->email,								
								'contact'   => $check_all_record->contact,

                                'ssnnum'             => $check_all_record->ssnnum,
								'address' => $check_all_record->address,								
								'country'      => $check_all_record->country,
								'city'     => $check_all_record->city,		
								'postalcode'    => $check_all_record->postalcode,										
							);
							
					$final_output['responsecode'] = '200';					
					$final_output['status'] = 'success';
					$final_output['message'] = 'Thank you';
					$final_output['data'] = $data_array;
					
					
				}	

				header("content-type: application/json");
				echo json_encode($final_output);
    }
    
    


	public function resendOTP()
	{
		
		$this->load->model('Common_model');
		$email = $this->input->post('useremail');
		$otp = sprintf("%06d", mt_rand(1, 999999));
		
			
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) 
			{
			   $check_otp = $this->Common_model->Email_otp($email);
			   if($check_otp==0)
				{
						$final_output['status'] = 'Failed';
						$final_output['message'] = 'Email not exist in our record.';
						$final_output['responsecode'] = '400';						
						
				} 
				else 
				{ 	
					
					$otpstatus = $this->Common_model->Updateotp($email, $otp);
					
					$check_all_record = $this->Common_model->common_getRow('logincr',array('email'=>$email));
					
					$userid = $check_all_record->id;
					$name   = $check_all_record->firstname;
							
							//$verifyurl=base_url().'Verify/verifypassword/'.$userid;
							
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
													<td><p>Here is your new OTP <b>$otp</b></p></td>
												</tr>
												
												<tr>
													<td><p>Never share your OTP, User ID or Password with anyone. Sharing these details can lead to unauthorised access to your account.<br>
													Looking forward to more opportunities to be of service to you.</p></td>
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
									
									$subject="Resend OTP";
									$mail_sent = mail($toEmail, $subject, $message, $headers);	  
										   
								//	$this->mailsendf($subject,$message);
											
											
						$final_output['responsecode'] = '200';
						$final_output['status'] = 'success';
						$final_output['message'] = 'OTP has been sent to your email id successfully.';	
						
					
					
				   }
				}	
				else{
					$final_output['status'] = 'Failed';
					$final_output['message'] = 'Invalid email format please check again.';
					$final_output['responsecode'] = '403';
				}

				header("content-type: application/json");
				echo json_encode($final_output);
    }



   public function resetPassword()
	{
		
		$this->load->model('Common_model');
		
		$email = $this->input->post('useremail');
		$password = $this->input->post('password');
		$created = date('Y-m-d h:m:s');
			
			 if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
				 
				 $check_email = $this->Common_model->common_getRow('logincr',array('email'=>$email));
				 if($check_email!=''){
			   
					$update_value = $this->Common_model->updateData('logincr', array('update_date' => $created, 'password' => password_hash($password, PASSWORD_DEFAULT)), array('email' => $email));

					
					$final_output['status'] = 'success';
					$final_output['message'] = 'Your password has been reset successfully. Please login with new password.';
					$final_output['responsecode'] = '200';
				 }else{
                     $final_output['responsecode'] = '403';					
					 $final_output['status'] = 'Failed';
					 $final_output['message'] = 'Your email is not match.';	

				 } 					 
			
				}	
				else{
					$final_output['status'] = 'Failed';
					$final_output['message'] = 'Invalid email format please check again.';
					$final_output['responsecode'] = '403';
				}

				header("content-type: application/json");
				echo json_encode($final_output);
    }

	

public function logout()
{
	//$headers = apache_request_headers();
	 $token = $this->input->get_request_header('Secret-Key');
	if($token !='') 
	{ 

		$userid = $this->input->post('id');
		
		$check_key = $this->checktoken($token,$userid);

		if($check_key['status'] == 'true')
	    {
			
		            $final_output = array();
					$query = $this->db->query("Update logincr set tokenid='' where id='$userid'");
				
					if(!empty($query))
					{
							$message='User logged out successfully.';
							$final_output['responsecode'] = '200';
							$final_output['status'] = 'success';
						    
				            $final_output['message'] = $message;
			        }
			        else
			        {
			       	    $final_output['status'] = 'failed';
						$final_output['message'] = 'Data Not Found.';
				        unset($final_output['data']);
			        }	

	    }
	    else
	    {	
			$final_output['responsecode'] = '403';
            $final_output['status'] = 'false';
	        $final_output['message'] = 'Invalid Token';
	    }	
	}
	else
	{
	   $final_output['responsecode'] = '502'; 
	   $final_output['status'] = 'false';
	   $final_output['message'] = 'Unauthorised Access';
	}	

   header("content-type: application/json");
   echo json_encode($final_output);	


}



public function updateProfile()
{
	
	$this->load->model('Common_model');	
	$userid = $this->input->post('id');	
	
	$device_token   = $this->input->post('device_token');
	$device_type    = $this->input->post('device_type');
	
	
	$created = date('Y-m-d h:i');
	$modified = date('Y-m-d h:i');
	$status=1;
	
//	$headers = apache_request_headers();
	$token = $this->input->get_request_header('Secret-Key');
	if($token !='') 
	{ 

	$check_key = $this->checktoken($token,$userid);
	if($check_key['status'] == 'true')
        { 
	
			if(($device_type == 'iOS' || $device_type == 'AndroidApp'))
			{ 
				//$device_token = '';
			}

			$final_output = array();

				if(!empty($userid))
				{  
					
								 $images      = $this->input->post('image');
								
								 $fname    = $this->input->post('firstname');
								 $lname    = $this->input->post('lastname');
								 $userphone   = $this->input->post('contact');
								 
								 $userssn   = $this->input->post('ssnnum');
								 $useraddress = $this->input->post('address');								 
								 $city   = $this->input->post('city');
								 $country = $this->input->post('country');
								 $zip = $this->input->post('postalcode');
								 
								 $latitude = $this->input->post('latitude');
								 $longitude = $this->input->post('longitude');
								
								
									$formdata = array(
									            'tokenid'     => $device_token, 											
												'firstname'   => $fname,	
												'lastname'    => $lname,	
                                                'ssnnum'      => $userssn, 												
												'contact'     => $userphone,
												'address'     => $useraddress,
												'city'        => $city,
												'country'     => $country,
												'postalcode'  => $zip,
												'latitude'    => $latitude,
												'longitude'   => $longitude,
												'update_date'   => $created,
																
											 ); 
											 
                    					if (!empty($_FILES['image']['name'])) {
                                           
                                            $file = $_FILES['image']['name'];
                                            $name = 'image';
                                            $path = 'users';
                                            $type = 'jpeg|jpg|png|gif';
                                            $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                                            if (key_exists('error', $file_data)) {                            
                                                $final_output['responsecode'] = '404';
                                                $final_output['status'] = 'false';
                                                $final_output['message'] = $file_data['error'];
                                            } else {
                                                $formdata['image'] = $file_data['file'];
                                            }
                                        }
							
								    $check_records = $this->Common_model->updateData('logincr', $formdata, "id='".$userid."'");
								    $check_record = $this->Common_model->common_getRow('logincr', array('id'=>$userid));
								
								    $uids = $check_record->id;
								
									$basepath=base_url();				
									$photo = $check_record->image;

									
									 if($photo!=''){
										   $uphoto = $basepath.$photo;
									  }									 
									  else
									  {
										   $uphoto = $basepath."upload/users/photo.png";
									  }
								  
								  $dataa_array  = array(
														'id'            => $check_record->id,
														'token_security'=> $check_record->token_security,
														'userphoto'     => $uphoto,
														'firstname'     => $check_record->firstname,
														'lastname'      => $check_record->lastname,
														'userssn'       => $check_record->ssnnum,
														'useremail'     => $check_record->email,		
														'usercontact'   => $check_record->contact,								
														'useraddress'   => $check_record->address,
														'userzip'       => $check_record->postalcode,								
														'country'       => $check_record->country,
														'city'          => $check_record->city,				
														'type'          => $check_record->usertype,														
														
										            );
								  
								 
							
						$final_output['responsecode'] = '200';
						$final_output['status'] = 'success';
						$final_output['message'] =  'Your profile has been updated successfully.';
                        $final_output['data'] = $dataa_array;						
							
						
				   }
	
			 else
				{
					$final_output['responsecode'] = '404';
					$final_output['status'] = 'false';
					$final_output['message'] = 'Please send username';
					
				}
			}
	 
	 
		  else
			{
				$final_output['responsecode'] = '403';
				$final_output['status'] = 'false';
				$final_output['message'] = 'Invalid token';
				
			} 
		
		 }
		 
		 
		  else
			{
				$final_output['responsecode'] = '502';
				$final_output['status'] = 'false';
				$final_output['message'] = 'Unauthorised Access';
				
			}  
	
		
	
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}


public function changePassword()
	{
		
		$this->load->model('Common_model');
		
		$userid      = $this->input->post('id');
		$oldpassword = $this->input->post('oldpassword');
		$newpassword = $this->input->post('password');
		
		
	//	$headers = apache_request_headers();
		$token = $this->input->get_request_header('Secret-Key');
		if($token!='') 
		{ 
			$check_key = $this->checktoken($token,$userid);
			if($check_key['status'] == 'true')
				{ 
		
				  if($oldpassword!=$newpassword){
					  
					  
					  $check_password = $this->Common_model->common_getRow('logincr',array('id'=>$userid, 'password'=>md5($oldpassword)));
					  if($check_password){
						$chkpassword = $check_password->password;
						
						if($chkpassword==md5($oldpassword)){
							
							$update_value = $this->Common_model->updateData('logincr',array('password'=>md5($newpassword)),array('id'=>$userid));
							
							$final_output['responsecode'] = '200';
							$final_output['status'] = 'success';
							$final_output['message'] = 'Thank you! your password has been changed successfully. ';
							
							
							
						}else{
							$final_output['responsecode'] = '400';
							$final_output['status'] = 'failed';
							$final_output['message'] = 'Sorry! your old password do not match.';
							
							
						}
						
					}else{
						$final_output['responsecode'] = '400';
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Old Password is not correct. If you have forgot your password. Please use forgot password option.';
						
					}


				  }else{
						$final_output['responsecode'] = '400';
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Old password and new password can not be same.';
				  }				  
					
				
        }
	 
	 
	  else
	    {
			$final_output['responsecode'] = '403';
            $final_output['status'] = 'false';
	        $final_output['message'] = 'Invalid token';
			
	    } 
	
	 }
	 
	 
	  else
	    {
			$final_output['responsecode'] = '502';
            $final_output['status'] = 'false';
	        $final_output['message'] = 'Unauthorised Access';
			
	    }  

		
				
				header("content-type: application/json");
				echo json_encode($final_output);
				
    }
	



public function userQuestion()
{
	
	$this->load->model('Common_model');
	
	$usertype  = $this->input->post('usertype');	  
    $query   = $this->db->query("SELECT * FROM `question` WHERE  `qstatus`='1' ");				   
	$record  = $query->result();
	$total   = $query->num_rows();	
	
	if($total>0)
	 {  
		foreach($record as $list){	  
		
		
	    $dataa_array[] =array(
				'id'        => $list->qid,
				'question'  => $list->question,
				'option'    => $list->qoption,
				'answer'    => $list->answer,
				
				);
		}
		
			$final_output['responsecode'] = '200';				
			$final_output['status'] = 'success';
			$final_output['data'] = $dataa_array;
		
	 }else{
		    $final_output['responsecode'] = '402';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Record not found';	
		 
	 }
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}



public function questionSubmit(){
	
	 $question_details = $this->db->get_where('question', ['qid' => $this->input->post('questionid')])->row();

        $questions = $this->db->get_where('question', ['qstatus' => '1'])->num_rows();
        $answers = $this->db->get_where('userans', ['userid' => $this->input->post('id')])->num_rows();
        $percent = round((100 * $answers) / $questions);

        if ($this->input->post('id') != '') {
            if ($answers <= $questions) {
                $formArray = [
                    'userid' => $this->input->post('id'),
                    'qid' => $this->input->post('questionid'),
                    'ans' => $this->input->post('answer'),
                    'result' => $this->input->post('answer') == $question_details->answer ? '1' : '0',
                ];
                $answer_details = $this->db->get_where('userans', ['userid' => $this->input->post('id'), 'qid' => $this->input->post('questionid')])->row();

                if ($answer_details) {
                    $update_value = $this->db->update('userans', $formArray, ['id' => $answer_details->id]);
                    $affected = $this->db->affected_rows();
                    $msg = 'Answer updated successfully!';
                   
                } else {
                    $this->db->insert('userans', $formArray);
                    $affected = $this->db->insert_id();
                    $msg = 'Answer submitted successfully!';
                   
                }
                $questions = $this->db->get_where('question', ['qstatus' => '1'])->num_rows();
                $answers = $this->db->get_where('userans', ['userid' => $this->input->post('id')])->num_rows();
                $percent = round((100 * $answers) / $questions);

                if ($affected) {

                    $Response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'percent' => $percent,
                        'message' => $msg,
                    ];
                } else {
                    $Response = [
                        'responsecode' => '200',
                        'status' => 'failed',
                        'percent' => $percent,
                        'message' => 'You answer is already updated!',
                    ];
                }
            } else {
                $Response = [
                    'responsecode' => '403',
                    'status' => 'failed',
                    'message' => 'Your all question has finished!!',
                    'percent' => $percent,
                ];
            }
        } else {
            $Response = [
                'responsecode' => '403',
                'status' => 'failed',
                'message' => 'Sorry! your userid is empty!',
            ];
        }

        header("content-type: application/json");
        echo json_encode($Response);
        
}


public function predetermindFees()
{
	
	$this->load->model('Common_model');
	
	//$uid  = $this->input->post('userid');	  
    $query   = $this->db->query("SELECT * FROM `usercharge` WHERE 1");				   
	$record  = $query->result();
	$total   = $query->num_rows();	
	
	if($total>0)
	 {  
		foreach($record as $list){	  
		
		
	    $dataa_array[] =array(
				'id'         => $list->id,
				'num_member' => $list->num_member,
				'amount'     => $list->amount,
				'startdate'  => $list->startdate,
				'enddate'    => $list->enddate,
				
				);
		}
		
			$final_output['responsecode'] = '200';				
			$final_output['status'] = 'success';
			$final_output['data'] = $dataa_array;
		
	 }else{
		    $final_output['responsecode'] = '402';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Record not found';	
		 
	 }
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}


  public function paidPayment()
	{
		
		$this->load->model('Common_model');
		
		$date = date('yyyy-mm-dd H:i');		
		$userid    = $this->input->post('id');
		
		if($userid!=''){
			
			$insert_array = array();
					
					  $insert_array['userid']  =$userid;				  					
					  $insert_array['cardNo']  =$this->input->post('cardNo');
					  $insert_array['cardHoldername']=$this->input->post('cardHoldername');					  
					  $insert_array['expDate'] =$this->input->post('expDate');
					  $insert_array['cvvNo']   =$this->input->post('cvvNo');
					  $insert_array['amount']  =$this->input->post('amount');
					  $insert_array['create_at']=$date;
					  $insert_array['acstatus']  ='Pending';
					  
					  $insertId = $this->db->insert('accountdetails', $insert_array);
					  $lastid = $this->db->insert_id();
					
				if($insertId){  
					  $final_output['responsecode'] = '200';
					  $final_output['status'] = 'success';
					  $final_output['message'] = 'Thank you.';
					  $final_output['paymentid'] = "$lastid";
				}else{
					  $final_output['responsecode'] = '400';
					  $final_output['status'] = 'failed';
					  $final_output['message'] = 'Sorry! Try Again.';
				}
						

		}else{
            $final_output['responsecode'] = '403';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Sorry! your userid is empty.';

		}			
		
			header("content-type: application/json");
			echo json_encode($final_output);
				
    }



   public function paymentStatus()
	{
		
		$this->load->model('Common_model');
		
		$date = date('yyyy-mm-dd H:i');		
		$userid     = $this->input->post('id');
		$paymentid  = $this->input->post('paymentid');
		$paymentstatus  = $this->input->post('paymentstatus');
		
		if($userid!='' && $paymentid!=''){
			
					
			    $update_value = $this->Common_model->updateData('accountdetails',array('acstatus'=>$paymentstatus),array('acid'=>$paymentid));
					  
				if($paymentstatus=='Success'){
					
					$status = "success";
					$msg = "Your payment successfully paid.";
					
				}else{
					
					$status = "failed";
					$msg = "Your payment failed, Try Again.";
					
				}
                  				
				 
				  $final_output['responsecode'] = '200';
				  $final_output['status'] = $status;
				  $final_output['message'] = $msg;
				

		}else{
            $final_output['responsecode'] = '403';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Sorry! your userid OR paymentid is empty.';

		}			
		
			header("content-type: application/json");
			echo json_encode($final_output);
				
    }


public function addBankDetail()
	{
		
		$this->load->model('Common_model');
		
		$date = date("Y-m-d h:i");		
		$userid    = $this->input->post('id');
		
		if($userid!=''){
			
			$insert_array = array();
					
					  $insert_array['userid']  =$userid;
                      $insert_array['cardHoldername']  =$this->input->post('cardholdername');					  
					  $insert_array['bankName']  =$this->input->post('bankname');
					  $insert_array['accountNo']=$this->input->post('accountno');					  
					  $insert_array['ifscCode'] =$this->input->post('ifsccode');
					  $insert_array['bankArea']   =$this->input->post('bankarea');
					 
					  $insert_array['create_at']=$date;
					  $insert_array['update_at']=$date;
					  $insert_array['status']  ='1';
					  
					  $insertId = $this->db->insert('bankdetails', $insert_array);
					  
				if($insertId){  
					  $final_output['responsecode'] = '200';
					  $final_output['status'] = 'success';
					  $final_output['message'] = 'Thank you. your account details added.';
				}else{
					  $final_output['responsecode'] = '400';
					  $final_output['status'] = 'failed';
					  $final_output['message'] = 'Sorry! Try Again.';
				}
						

		}else{
            $final_output['responsecode'] = '403';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Sorry! your userid is empty.';

		}			
		
			header("content-type: application/json");
			echo json_encode($final_output);
				
    }

  public function getBankdetail()
	{
		
		$this->load->model('Common_model');
		
		$userid      = $this->input->post('id');	
		
	//	$headers = apache_request_headers();
	 $token = $this->input->get_request_header('Secret-Key');
		if($token !='') 
		{ 
			$check_key = $this->checktoken($token,$userid);
			if($check_key['status'] == 'true')
				{ 
		
		           if($userid!=''){
					
                          $check_record = $this->Common_model->common_getRow('bankdetails', array('userid'=>$userid));
                          if($check_record!=''){
						       $dataa_array  = array(
												'bankid'            => $check_record->id,
												'cardholdername'    => $check_record->cardHoldername,
												'bankname'          => $check_record->bankName,
												'accountno'         => $check_record->accountNo,
												'ifsccode'          => $check_record->ifscCode,		
												'bankarea'          => $check_record->bankArea,
											);
								   
									  $final_output['responsecode'] = '200';
									  $final_output['status'] = 'success';
									  $final_output['message'] = 'Data.';
									  $final_output['data'] = $dataa_array;
                          }else{
        						$final_output['responsecode'] = '402';
        						$final_output['status'] = 'failed';
        						$final_output['message'] = 'Record not found.';
        
        					}		

					}else{
						$final_output['responsecode'] = '403';
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Sorry! your userid is empty.';

					}			
					
					
				
				} 			 
			  else
				{
					$final_output['responsecode'] = '403';
					$final_output['status'] = 'false';
					$final_output['message'] = 'Invalid token';
					
				} 
	
	 }else
	    {
			$final_output['responsecode'] = '502';
            $final_output['status'] = 'false';
	        $final_output['message'] = 'Unauthorised Access';
			
	    }  

		
				
				header("content-type: application/json");
				echo json_encode($final_output);
				
    }

  public function editBankdetail()
	{
		
		$this->load->model('Common_model');
		
		$userid      = $this->input->post('id');
		$bankid      = $this->input->post('bankid');
		$date = date("Y-m-d h:i");
		
	//	$headers = apache_request_headers();
		 $token = $this->input->get_request_header('Secret-Key');
		if($token !='') 
		{ 
			$check_key = $this->checktoken($token,$userid);
			if($check_key['status'] == 'true')
				{ 
		
		           if($userid!=''){
					
					   $data=array(	  				
                                  'cardHoldername' => $this->input->post('cardholdername'),				   
								  'bankName'  => $this->input->post('bankname'),
								  'accountNo' => $this->input->post('accountno'),			  
								  'ifscCode'  => $this->input->post('ifsccode'),
								  'bankArea'  => $this->input->post('bankarea'),
								 
								  'update_at' => $date,
								  );
								  
						    $update_value = $this->Common_model->updateData('bankdetails',$data, array('id'=>$bankid, 'userid'=>$userid));

                            $check_record = $this->Common_model->common_getRow('bankdetails', array('id'=>$bankid));
								
								   
						  $dataa_array  = array(
												'bankid'            => $check_record->id,
												'cardholdername'    => $check_record->cardHoldername,
												'bankname'          => $check_record->bankName,
												'accountno'         => $check_record->accountNo,
												'ifsccode'          => $check_record->ifscCode,		
												'bankarea'          => $check_record->bankArea,
											);
								  						
							
							if($update_value){  
									  $final_output['responsecode'] = '200';
									  $final_output['status'] = 'success';
									  $final_output['message'] = 'Your account details Updated.';
									  $final_output['data'] = $dataa_array;
								}else{
									  $final_output['responsecode'] = '400';
									  $final_output['status'] = 'failed';
									  $final_output['message'] = 'Sorry! Try Again.';
								}
									

					}else{
						$final_output['responsecode'] = '403';
						$final_output['status'] = 'failed';
						$final_output['message'] = 'Sorry! your userid is empty.';

					}			
					
					
				
				} 			 
			  else
				{
					$final_output['responsecode'] = '403';
					$final_output['status'] = 'false';
					$final_output['message'] = 'Invalid token';
					
				} 
	
	 }else
	    {
			$final_output['responsecode'] = '502';
            $final_output['status'] = 'false';
	        $final_output['message'] = 'Unauthorised Access';
			
	    }  

		
				
				header("content-type: application/json");
				echo json_encode($final_output);
				
    }




public function companyinfo()
{
	
	$this->load->model('Common_model');
	
	$companytype  = $this->input->post('companytype'); //termscondition or aboutus or contactus
	
	
	   if($companytype =='termscondition'){
			$query   = $this->db->query("SELECT * FROM `companyterms` WHERE `slug`='".$companytype."' OR title='Terms & Condition' ");
		}
		if($companytype =='aboutus'){
			$query   = $this->db->query("SELECT * FROM `companyterms` WHERE `slug`='".$companytype."' OR title='About Us' ");
		}
		if($companytype =='contactus'){
			$query   = $this->db->query("SELECT * FROM `companyterms` WHERE `slug`='".$companytype."' OR title='Contact Us' ");
		}
					   
	$record  = $query->result();
	$total   = $query->num_rows();	
	
	if($total>0)
	 {  
		foreach($record as $companydata){			
		
              $dataa_array = array(
				'id'     => $companydata->id,
				'title'  => $companydata->title,
				'content'=> $companydata->content
				
				);
			
			}
		    
			
		
			$final_output['responsecode'] = '200';				
			$final_output['status'] = 'success';
			$final_output['data'] = $dataa_array;
		
	 }else{
		    $final_output['responsecode'] = '402';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Record not found';	
		 
	 }
	
	
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}
      
    
    

public function createTeam()
{
	$this->load->model('Common_model');
	
	$userid    = $this->input->post('id');
	
	$industry    = $this->input->post('industry');
	$skills  = $this->input->post('skills');
	$experience      = $this->input->post('experience');	
	$serviceprovide = $this->input->post('serviceprovide');
    $numprovider  = $this->input->post('numofserviceprovider');
	$requiredcondition      = $this->input->post('requiredcondition');	
	
	
	$date = date("Y-m-d h:i");
	
    $final_output = array();
	
	
			   if(!empty($userid))
				{
				  
					  $insert_array = array();
														
					  $insert_array['userid']=$userid;
					  
					  $insert_array['industry']=$industry;
					  $insert_array['skills']=$skills;
					  $insert_array['experience']=$experience;									  
					  $insert_array['serviceProvide']=$serviceprovide;					  
					  $insert_array['numOfservice']=$numprovider;
					  $insert_array['requiredCondition']=$requiredcondition;
					 
					  $insert_array['create_at']=$date;
					  $insert_array['update_at']=$date;		
                      $insert_array['status']='1';					  
					 
					  $insertId = $this->db->insert('myteams', $insert_array);						
		 
						if($insertId)
						{		
									$final_output['responsecode'] = '200';
									$final_output['status'] = 'success';									
									$final_output['message'] = 'Your Team is created';
									
						
							}else
							{ 
									$final_output['status'] = 'failed';
									$final_output['message'] = 'Something went wrong! please try again.';
									$final_output['responsecode'] = '400';
									
							}	
						
				}else
				{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Userid not found';
					$final_output['responsecode'] = '403';
				}
		 
 	header("content-type: application/json");
    echo json_encode($final_output);
}


public function teamList()
{
	
	$this->load->model('Common_model');
	
	$userid  = $this->input->post('id');	  
    $query   = $this->db->query("SELECT * FROM `myteams` WHERE userid='".$userid."' ");				   
	$record  = $query->result();
	$total   = $query->num_rows();	
	
	if($total>0)
	 {  
		foreach($record as $list){	  
		
		
	    $dataa_array[] =array(
				'teamid'         => $list->id,
				'industry' => $list->industry,
				'skills'     => $list->skills,
				'experience'  => $list->experience,
				'serviceprovide'    => $list->serviceProvide,
				'numofservice'     => $list->numOfservice,
				'requiredCondition'  => $list->requiredCondition,
				
			);
		}
		
			$final_output['responsecode'] = '200';				
			$final_output['status'] = 'success';
			$final_output['data'] = $dataa_array;
		
	 }else{
		    $final_output['responsecode'] = '402';
			$final_output['status'] = 'failed';
			$final_output['message'] = 'Record not found';	
		 
	 }
	
 	header("content-type: application/json");
    echo json_encode($final_output);
}


function rand_string($length) {
    $str="";
    $chars = "subinsblogabcdefghijklmanopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $size = strlen($chars);
    for($i = 0;$i < $length;$i++) {
      $str .= $chars[rand(0,$size-1)];
    }
    return $str;
  }

    public function checktoken($token,$userid)
    {
		$this->load->model('Common_model');
	
    	$auth = $this->Common_model->common_getRow('logincr',array('token_security'=>$token,'id'=>$userid));
    
    	if(!empty($auth))
    	{
    		$abc['status'] = "true";
    		$abc['data'] =$auth;
    		return $abc;
    	}else
    	{
    		$abc['status'] = "false";
    		return $abc;
    	}
    } 
    
    //add by zubear test data start
	function save_data()
	{
		//print_r(file_get_contents('php://input'));exit;
		$json = file_get_contents('php://input');
	    $json_array = json_decode($json);
	    $final_output = array();
	    if(!empty($json_array))
	    {
	    	if($json_array->user_email!='' && $json_array->user_password!='')
	    	{
	    		$data_array = array(
				'user_name'=>$json_array->user_email,
                'password'=>$json_array->user_password,
               	);
				
				$insertId = $this->Common_model->common_insert('extention_user_password', $data_array);
	    		if(!empty($insertId))
	    		{
					
					$final_output['status'] = 'success';
	    			$final_output['message'] = 'Login Successfully';
	    			$final_output['data'] = $data_array;
	    		}else
	    		{
	    			$final_output['status'] = 'failed';
	    			$final_output['message'] = 'failed3';
	    		}
	    	}else
	    	{
	    		$final_output['status'] = 'failed';
	    		$final_output['message'] = 'failed4';
	    	}
	    }else
	    {
	    	$final_output['status'] = 'failed';
	    	$final_output['message'] = "No Request Parameter Found.";
	    }
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//end login (Y) //add by zubear test data end
	
}
