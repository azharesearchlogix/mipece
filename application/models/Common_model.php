<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

	class Common_model extends CI_Model {
	public function __construct()
    {
        parent::__construct();
	}
	
function decodeEmoticons($src) {
    $replaced = preg_replace("/\\\\u([0-9A-F]{1,4})/i", "&#x$1;", $src);
    $result = mb_convert_encoding($replaced, "UTF-16", "HTML-ENTITIES");
    $result = mb_convert_encoding($result, 'utf-8', 'utf-16');
    return $result;
}	

	
public function common_insert($tbl_name = false, $data_array = false)
{
	$ins_data = $this->db->insert($tbl_name, $data_array);
	if($ins_data){
		return $last_id = $this->db->insert_id();
	}
	else{
		return false;
	}
}
	
	
   public function verify_otp($email, $otp) {


        $this->db->select('email, otp');
        $this->db->from('logincr');
        $this->db->where('email',$email);
        $this->db->where('otp',$otp);
       
        $query = $this->db->get();
		
		if($query->num_rows() == 1){
		  return $query->result();
		}else{
		  return false;
		}
    }

   public function otp_status($email, $otp)
	{ 
	    $this->db->set('status', '1');             //value that used to update column  
		$this->db->where('email', $email); //which row want to upgrade  
		$this->db->where('otp', $otp);
		$query = $this->db->update('logincr');         //table name
	
		if($query){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	
	public function forgot_otpstatus($username, $otp)
	{ 
	    $this->db->set('status', '1');             //value that used to update column  
		$this->db->where('email', $username); //which row want to upgrade 	
		$this->db->where('otp', $otp);
		$query = $this->db->update('logincr');         //table name
	
		if($query){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	public function Email_otp($email) {


        $this->db->select('email, otp');
        $this->db->from('logincr');
        $this->db->where('email',$email);
        
       
        $query = $this->db->get();
		//print_r($this->db->last_query()); exit;
		if($query->num_rows() == 1){
		  return $query->result();
		}else{
		  return false;
		}
    }
	
	function Updateotp($email, $otp)
	{
		$updateArray =array();
		$updateArray['otp']=$otp;
		$this->db->where('email', $email); //which row want to upgrade  
		
		$query = $this->db->update('logincr',$updateArray);         //table name
	
		if($query){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	
	function checkuserlogin($email)
	{
		$this->db->select('*');
        $this->db->from('logincr');
        $this->db->where('email', $email);
	   // $this->db->where('password', $password);
	   // $this->db->where('usertype', $usertype);
		//$this->db->where('status', '1');
		//$this->db->where('adminstatus', '1');
		
        $query = $this->db->get();        
		$data_array = $query->result_array();	
		$cnt=$query->num_rows();
		
	    if($query->num_rows > 0)
		{
			return $data_array;
		}
		else
		{
			return 0;
		}
		
	}


   public function verify_mail($userid) {
	   
        $this->db->select('id');
        $this->db->from('logincr');
		$this->db->where('id',$userid);
        
        $query = $this->db->get();
		//print_r($this->db->last_query()); exit;
		if($query->num_rows() == 1){
		  return $query->result();
		}else{
		  return false;
		}
    }
	
	public function mail_status($userid)
	{ 
	    $this->db->set('status', 'Active');             //value that used to update column  
		$this->db->where('id',$userid);
		$query = $this->db->update('logincr');         //table name
	
		if($query){
			return true;
		}
		else{
			return false;
		}
	}
	

	function checkexistRecord($oauid)
	{
		$this->db->select('*');

        $this->db->from('logincr');
        $this->db->where('oauth_uid', $oauid);
	   
        $query = $this->db->get();
        
		//print_r($this->db->last_query()); exit;
		$data_array = $query->row();
		$cnt=$query->num_rows();
	    if($query->num_rows > 0)
		{
			return $data_array;
		}
		else
		{
			return false;
		}
	}
	
	
	
	function checkemailexistRecord($emailid)
	{
		
		
		$this->db->select('*');

        $this->db->from('logincr');
       
	    $this->db->where('email', $emailid);
       
        $query = $this->db->get();
        
		//print_r($this->db->last_query()); exit;
		$data_array = $query->row();
		$cnt=$query->num_rows();
	    if($query->num_rows > 0)
		{
			return $data_array;
		}
		else
		{
			return false;
		}
	}
	
	
	

	
	function updateprofile($userid, $data)
	{
		$updateArray =array();
		
		$this->db->where('id', $userid); //which row want to upgrade  
		$query = $this->db->update('logincr',$data);         //table name
	
	   
		if($query){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	
	  function common_insert_new($UserRecord) {

        $this->db->trans_start();
        $this->db->insert('logincr', $UserRecord);

        $insert_id = $this->db->insert_id();

        $this->db->trans_complete();

        return $insert_id;
    }
	
	
	

	public function updateData($table,$data,$where_array)
	{ 
	    $this->db->where($where_array);
		if($this->db->update($table,$data)){
			
			return true;
		}
		else{
			return false;
		}
	}
	public function sqlcount($table = false,$where = false)
	{
		$this->db->select('*');	
		$this->db->from($table); 
		if(isset($where) && !empty($where))
		{
			$this->db->where($where);	
		}
		//$this->db->limit($limit, $start);       
		$query = $this->db->get();
		//print_r($this->db->last_query()); exit;
		return $query->num_rows(); 
	}

	// Function for select data
	public function getData($table,$where='', $order_by = false, $order = false, $join_array = false, $limit = false)
	{
		//$this->db->select('*');
		$this->db->from($table);

		if(!empty($where))
		{
			$this->db->where($where);
		}
		
		if(!empty($order_by))
		{
			$this->db->order_by($order_by, $order); 	
		}



		if(!empty($join_array))
		{
			foreach ($join_array as $key => $value) {

				$this->db->join($key, $value); 	
			}
			
		}

		if(!empty($limit))
		{
			$this->db->limit($limit); 	
		}

		$result = $this->db->get();
		

		//print_r($this->db->last_query()); exit;
		return $result->result();
		//return $result;
	}

	// Function for select data
	public function getDataField($field = false, $table, $where='', $order_by = false, $order = false, $join_array = false, $limit = false)
	{
		$this->db->select($field);

		$this->db->from($table);

		if(!empty($where))
		{
			$this->db->where($where);
		}
		
		if(!empty($order_by))
		{
			$this->db->order_by($order_by, $order); 	
		}



		if(!empty($join_array))
		{
			foreach ($join_array as $key => $value) {

				$this->db->join($key, $value); 	
			}
			
		}

		if(!empty($limit))
		{
			$this->db->limit($limit); 	
		}

		$result = $this->db->get();
		

		//print_r($this->db->last_query()); exit;
		return $result->result();
		//return $result;
	}

	public function common_getRow($tbl_name = false, $where = false, $join_array = false)
	{
		$this->db->select('*');
		$this->db->from($tbl_name);
		
		if(isset($where) && !empty($where))
		{
			$this->db->where($where);	
		}
		
		if(!empty($join_array))
		{
			foreach($join_array as $key=>$value){
				$this->db->join($key,$value);
			}	
		}
		
		$query = $this->db->get();
// 		echo $this->db->last_query(); die;
		
		$data_array = $query->row();
		//print_r($this->db->last_query()); exit;
		
		
		if($data_array)
		{
			return $data_array;
		}
		else{
			return false;
		}
	}
	public function deleteData($table,$where)
	{ 
		$this->db->where($where);
		if($this->db->delete($table))
		{
			return true;
		}
		else{
			return false;
		}
	}
	

	public function get_date($create_at)
	{
		 $seconds = $create_at / 1000;

         $date = date('d-M-Y',$seconds);

         return $date;
	}
	
	public function getrating($id) {
        $ratio = 0;
        $ratio_res = $this->db->select('rating, SUM(rating) AS total', FALSE)->where('provider_id', $id)->group_by("rating")->get('tbl_user_ratings')->result();
        if (!empty($ratio_res)) {
            $devident = 0;
            $devisor = 0;
            foreach ($ratio_res as $r) {
                $devident += ($r->rating * $r->total);
                $devisor += $r->total;
            }

            $ratio = round($devident / $devisor, 2);
        }
        return "$ratio";
    }

    public function get_SkillIxperienceIndustry($spid) {
        $exp_record = $this->db->select('b.name as experience, c.name as industry, d.name as skills')->from('tbl_xai_matching as a')
                        ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                        ->join('tbl_industries as c', 'a.industry_id = c.id ', 'left')
                        ->join('tbl_skill as d', 'a.skill_id = d.id ', 'left')
                        ->where('a.user_id', $spid)->get()->row();
        if ($exp_record) {
           return  $data = [
                'experience' => $exp_record->experience,
                'industry' => $exp_record->industry,
                'skills' => $exp_record->skills,
            ];
        } else {
           return $data = [];
        }
    }
    public function Crimknaltoken() {

        $headers = [
            'Content-Type: application/json'
        ];
        $auth_data = array(
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://apitest.microbilt.com/OAuth/GetAccessToken');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($auth_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
//        echo '<pre>';
//        print_r(json_decode($result)); die;
        if (property_exists(json_decode($result), 'access_token')) {
            return (object) ['access_token' => json_decode($result)->access_token];
        } else {
            return json_decode($result);
        }
    }

    public function verification($token, $auth_data) {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $token->access_token . '',
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://apitest.microbilt.com/CriminalSearch/GetReport');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($auth_data, JSON_PRETTY_PRINT));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        return $result;
    }
    function generatestring($length = 50) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function fileupload($path = NULL, $type = NULL, $file = NULL, $name = NULL) {
        $config = [
            'upload_path' => './upload/' . $path,
            'allowed_types' => $type,
            'max_size' => 50600,
        ];
        $this->load->library('upload', $config);
        if ($file) {
            if (!$this->upload->do_upload($name)) {
                return ['error' => strip_tags($this->upload->display_errors())];
            } else {
                $data = array('upload_data' => $this->upload->data());
                return ['file'=>'upload/' . $path . '/' . $this->upload->data('file_name')];
            }
        } else {
           return ['error' => strip_tags($this->upload->display_errors())];
        }
    }
    
    public function Access($id = NULL, $token = NULL) {
        if ($id && $token) {
            $res = $this->db->get_where('logincr', ['id' => $id, 'token_security' => $token])->row();
            if ($res) {
                return ['success' => $res];
            } else {
                return ['error' => 'Invalid security token!'];
            }
        } else {
            return ['error' => 'Unauthorised Access!'];
        }
    }
    public function getuserdata($user_id) {
        $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
        if ($user) {
            $user_service = '';
            $xaistatus = '0';
            $industry = '';
            

           /* if ($user->usertype == '1') {
                $check_qusans = $this->common_getRow('userans', array('userid' => $user->id));
            } else   if ($user->usertype == '0') {
                $user_services = $this->db->get_where('userservice', ['userid' => $user->id])->row();
                if ($user_services) {
                    $user_service = $user_services->servicetype;
                }
                $check_qusans = $this->common_getRow('tbl_answer', array('user_id' => $user->id));
                $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $user->id, 'language IS NOT NULL'])->row();
                // print_r($xai);
                if (!empty($xai)) {
                    $xaistatus = '1';
                    $industry = $xai->industry_id;
                }
            }*/
            if ($user->switch_account == '1') {
                $check_qusans = ($user->usertype=='1')?$this->common_getRow('userans', array('userid' => $user->id)):$this->common_getRow('tbl_answer', array('user_id' => $user->id));
                $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $user->id, 'language IS NOT NULL' ,'type'=>'0'])->row();
                // print_r($xai);
                if (!empty($xai)) {
                    $xaistatus = '1';
                    $industry = $xai->industry_id;
                }
            } else   if ($user->switch_account == '0') {
                $user_services = $this->db->get_where('userservice', ['userid' => $user->id])->row();
                if ($user_services) {
                    $user_service = $user_services->servicetype;
                }
                //$check_qusans = $this->common_getRow('tbl_answer', array('user_id' => $user->id));
                $check_qusans = ($user->usertype=='1')?$this->common_getRow('userans', array('userid' => $user->id)):$this->common_getRow('tbl_answer', array('user_id' => $user->id));
                $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $user->id, 'language IS NOT NULL' ,'type'=>'0'])->row();
                // print_r($xai);
                if (!empty($xai)) {
                    $xaistatus = '1';
                    $industry = $xai->industry_id;
                }
            }else if ($user->switch_account == '2'){
                $check_qusans = '1';
                $xaistatus = '1';
            }

            if (isset($check_qusans) && $check_qusans) {
                $questionStatus = '1';
            } else {
                $questionStatus = '0';
            }

            $check_account = $this->common_getRow('accountdetails', array('userid' => $user->id));
            if ($check_account) {
                if ($check_account->userid) {
                    $accountStatus = '1';
                } else {
                    $accountStatus = '0';
                }
            } else {
                $accountStatus = '0';
            }
            $subscription = $this->db->get_where('tbl_subscription', ['user_id' => $user->id, 'status' => '0'])->row();
            $education = $this->db->get_where('usereducation', ['userid' => $user->id])->row();
            return [ 
                'id' => $user->id,
                'token_security' => $user->token_security,
                'photo' => $user->image ? base_url($user->image) : base_url('upload/users/photo.png'),
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'contact' => $user->contact,
                'ssnnum' => $user->ssnnum,
                'address' => $user->address,
                'country' => $user->country,
                'city' => $user->city,
                'postalcode' => $user->postalcode,
                'questionstatus' => $questionStatus,
                'accountstatus' => $accountStatus,
                'type' => $user->usertype,
                'user_service' => $user_service,
                'xaistatus' => $xaistatus,
                'industry' => $industry,
                'subscription' => $subscription ? '1' : '0',
                'education' => $education ? '1' : '0',
                'describe' => $user->about ? '1' : '0',
                'background_verification' => $user->background_verification_status,
                'switch_account' => $user->switch_account,
                'min_commission' => ($user->min_commission)?$user->min_commission:'',
                'max_commission' => ($user->max_commission)?$user->max_commission:'',
                'aboutus' => ($user->aboutus)?$user->aboutus:'',
            ];
        } else {
            return [];
            
        }
    }
	
  }



?>