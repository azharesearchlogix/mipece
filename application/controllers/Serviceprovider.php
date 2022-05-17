<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Serviceprovider extends CI_Controller {
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



public function teamListMatching()
{
	
	$this->load->model('Common_model');
	
	//$spid  = $this->input->post('spid');
	
	$experience  = $this->input->post('experience');
	$industry  = $this->input->post('industry');
	$skills  = $this->input->post('skills');
		 
		 //echo "SELECT * FROM `myteams` WHERE experience='".$experience."' OR industry='".$industry."' OR skills LIKE  '%".$skills."%' ";
	$expsk = explode(',',$skills);
	$i=0;
	foreach($expsk as $expskills){
		
		   $query   = $this->db->query("SELECT * FROM `myteams` WHERE experience='".$experience."' OR industry='".$industry."' OR skills LIKE  '%".$expskills[$i]."%' ");	
		
	$i++; }
				   
	$record  = $query->result();
	$total   = $query->num_rows();	
	if($total>0)
	 {  				   
 
        foreach($record as $row){	
            $basepath=base_url();				
			$teamphoto = $row->image;

			 if($teamphoto!=''){
				   $teamimg = $basepath.'upload/users/'.$teamphoto;
			  }									 
			  else
			  {
				   $teamimg = $basepath."upload/users/photo.png";
			  } 
			    $data_array[]  = array(
				                'teamid'         => $row->id,
								'userid'         => $row->userid,
								'teamname'       => $row->teamName,
								'image'          => $teamimg,
								
								'numofservice'   => $row->numOfservice,		
								'serviceprovide' => $row->serviceProvide,								
								'experience'     => $row->experience,
                                'industry'       => $row->industry,
								'skills'         => $row->skills,
                                'requiredcondition'=> $row->requiredCondition,								
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
	//die;
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
