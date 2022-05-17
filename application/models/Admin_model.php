<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

public function __construct()
{
    parent::__construct();
	
	    date_default_timezone_set('Asia/Calcutta'); 
		$militime =round(microtime(true) * 1000);
		$datetime =date('Y-m-d h:i:s');
		define('militime', $militime);
		define('datetime', $datetime);
}


function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

	public function CheckLoginSession(){   
	  $admin_id = $this->session->userdata('admin_id');
	  if(empty($admin_id)){
		redirect('admin/login','refresh');
	  }
	  else{
		return 1;
	  }
	}
	 public function AdminLogin($email) {
        $query = $this->db->get_where('admin', ['email' => $email]);
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }
	
	public function AdminLogin1() 
	{
		$login=$this->input->post();
		
		$usertype = $login['usertype'];
		if($usertype =='admin'){
			
				$active=1;
				$email=$login['useremail'];
				$password=md5($login['userpassword']);
				$array = array('admin_email' => $email, 'admin_pass' => $password, 'status' => $active);
				$this->db->where($array); 
				$query = $this->db->get('admin');
				$rowCount=$query->num_rows();

				if($rowCount>0)
				{
				$result=$query->row();
				$id=$result->id;
				$userdata = array(
				'admin_id'    => $result->id,
				'admin_name'    => $result->admin_name,
				'admin_email' => $result->admin_email
				);

				$this->session->set_userdata($userdata);
				return 1;
				}
				else{
				return 0;
				}
				
		}else{
                $active='Active';
				$email   =$login['useremail'];
				$password=$login['userpassword'];
				
				$array = array('email' => $email, 'password' => $password, 'usertype' =>'Vendor', 'status' => $active);
				$this->db->where($array); 
				$query = $this->db->get('logincr');
				$rowCount=$query->num_rows();

				if($rowCount>0)
				{
					$result=$query->row();
					$id=$result->id;
					$userdata = array(
					'admin_id'    => $result->id,
					'admin_name'    => $result->name,
					'admin_email' => $result->email
				);

				  $this->session->set_userdata($userdata);
				  return 1;
				}
				else{
				  return 0;
				}

		}			
				
	}
	
	function is_email_available($email)  
      {  
           $this->db->where('email', $email);  
           $query = $this->db->get("logincr");  
           if($query->num_rows() > 0)  
           {  
                return true;  
           }  
           else  
           {  
                return false;  
           }  
      }  
	
	
	function checkPass($pass, $id)  
      {  
           $this->db->where('id', $id);
           //$this->db->where('admin_pass', md5($pass));		   
           $query = $this->db->get("admin");  
           if($query->num_rows() > 0)  
           {  $g = $query->row();
           if(password_verify($pass, $g->password))
           {
                return true;  
           }else{
               return false;
           }  
           }
           else  
           {  
                return false;  
           }  
      } 

    public function change_password($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('admin', $data);
	}	  
	  
	  
	  function is_email_vendor($email)  
      {  
           $this->db->where('email', $email);  
           $query = $this->db->get("logincr");  
           if($query->num_rows() > 0)  
           {  
                return true;  
           }  
           else  
           {  
                return false;  
           }  
      }  
	
	
	function fetch_profile($id)  
      {  
           $this->db->where("id", $id);  
           $query = $this->db->get("logincr");  
           return $query;  
      }  
   
	public function updatemyprofile($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('logincr', $data);
	}
	
	
	public function userstatus($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('logincr', $data);
	}
	
	
	
	public function bannerpost($data){
		 
		$this->db->insert('banner',$data);				
		return true; 
	}
	
	public function bannerupdate($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('banner', $data);
	}
	
	
	public function bannerstatus($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('banner', $data);
	}	
	
	
	public function categoryadd($data){
		 
		$this->db->insert('category',$data);				
		return true; 
	}
	
	public function categoryupdate($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('category', $data);
	}
	
	public function categorystatus($id, $data) {
		$this->db->where('id', $id);
		$this->db->update('category', $data);
	}
	
	
	
	public function podcastpost($data){
		 
		$this->db->insert('podcast',$data);				
		return true; 
	}
	
	public function podcastupdate($id, $data) {
		$this->db->where('pid', $id);
		$this->db->update('podcast', $data);
	}
	
	
	public function podcaststatus($id, $data) {
		$this->db->where('pid', $id);
		$this->db->update('podcast', $data);
	}
	
	
	
	
	
	
	
	
	
   
   public function termscondition(){
		  $this->db->select("*"); 
		  $this->db->from('companyterms');
		  $this->db->where('title =','Terms & Condition');
		  $query = $this->db->get();
		  return $query->result();
	}
	
	public function aboutus(){
		  $this->db->select("*"); 
		  $this->db->from('companyterms');
		  $this->db->where('title =','About Us');
		  $query = $this->db->get();
		  return $query->result();
	}
	
	public function contactus(){
		  $this->db->select("*"); 
		  $this->db->from('companyterms');
		  $this->db->where('title =','Contact Us');
		  $query = $this->db->get();
		  return $query->result();
	}
	
	public function updatecompanyprofile($title, $content){
		
		 $this->db->set('content', $content);
		 $this->db->where('title', $title); 
         $this->db->update('companyterms');
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
   
   public function getData($table, $where='', $order_by = false, $order = false, $join_array = false, $limit = false)
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
   
   
   
    public function fullData($table, $order_by = false, $order = false, $join_array = false, $limit = false)
    //public function fullData($table, $order_by = false, $order = false, $limit = false)
	{
		//$this->db->select('*');
		$this->db->from($table);

		
		if(!empty($order_by))
		{
			$this->db->order_by($order_by, $order); 	
		}
		
		if(!empty($limit))
		{
			$this->db->limit($limit); 	
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
		
		return $result->result();
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
   

}
?>