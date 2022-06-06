<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH.'/third_party/vendor/autoload.php';

class Backup extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model', 'Mail']);
    }
    public function authentication($user_id = NULL, $token = NULL) {
        $result = $this->Common_model->Access($user_id, $token);
        if (!key_exists('error', $result)) {
            return $result;
        } else {
            $this->response(
                    ['status' => 'Failed',
                        'message' => $result['error'],
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    public function findAllSp_get($userid = '')
    {
        $tokenid = $this->security->xss_clean($this->input->get_request_header('Secret-Key'));
        $check_key = $this->authentication($userid , $tokenid);
            if(empty($userid))
            {
            return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'errors' => "User id is required!",

            ]);
            }else{
               $industry_id = $this->db->select('industry_id')->get_where('tbl_xai_matching',['type'=>'0' , 'user_id'=>$userid])->row('industry_id'); 
                $getSp1 = $this->db->select('a.* , b.industry_id , c.name as industry_name')
                                   ->from('logincr as a')
                                   ->join('tbl_xai_matching as b' ,'b.user_id = a.id')
                                   ->join('tbl_industries as c' ,'c.id = b.industry_id')
                                   ->where(['a.usertype'=>'0' , 'a.status'=>'1' ,'a.interested_in_backup'=>'1' ,'a.id!='=>$userid , 'b.industry_id'=>$industry_id , 'b.type'=>'0'])
                                  ->order_by('id','DESC')
                                  ->get('logincr')
                                  ->result();
                $getSp2 = $this->db->select('a.* , b.industry_id , c.name as industry_name')
                                   ->from('logincr as a')
                                   ->join('tbl_xai_matching as b' ,'b.user_id = a.id')
                                   ->join('tbl_industries as c' ,'c.id = b.industry_id')
                                   ->where(['a.switch_account'=>'0' , 'a.status'=>'1' ,'a.interested_in_backup'=>'1' ,'a.id!='=>$userid , 'b.industry_id'=>$industry_id , 'b.type'=>'0'])
                ->order_by('id','DESC')
                ->get('logincr')
                ->result();
             $getSp = array_merge($getSp1 , $getSp2);
             $getSp = array_unique($getSp,SORT_REGULAR);        
             if($getSp)
             {
                  foreach($getSp as $check_record)
                    {
                     $userData[] = [
                    'user_id' => $check_record->id,
                    'industry_id' => $check_record->industry_id,
                    'industry_name' => $check_record->industry_name,
                    'profile_img' => $check_record->image ? base_url().$check_record->image : base_url('upload/users/photo.png'),
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'bio' => $check_record->about,
                    
                    
                        ];
                    }
                     $this->response(
                                ['status' => 'success',
                                    'message' => 'Data found successfully',
                                    'data' => $userData,
                                    'responsecode' => REST_Controller::HTTP_OK,
                        ]);
             }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No data found!',
                                
                    ]); 
             }  
            }
    
    }
//all client list
    public function allClientList_get($userid = '')
    {
        $tokenid = $this->security->xss_clean($this->input->get_request_header('Secret-Key'));
        $check_key = $this->authentication($userid , $tokenid);
            if(empty($userid))
            {
            return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'errors' => "User id is required!",

            ]);
            }else{
               $getClient = $this->db->select('b.id as user_id , CONCAT(b.firstname , " " , b.lastname) as client_name, a.provider_id , a.team_id')
                                     ->from('tbl_offer_letter as a')
                                     ->join('logincr as b' , 'b.id = a.user_id')
                                     ->where(['a.status'=>'2' , 'a.provider_id'=>$userid , 'a.user_id!='=>$userid])  
                                     ->order_by('b.firstname ASC')
                                     ->get()
                                     ->result();   
             if($getClient)
             {
                  foreach($getClient as $check_record)
                    {
                     $userData[] = [
                    'provider_id' => $userid,
                    'teamid' => $check_record->team_id,
                    'user_id' => $check_record->user_id,
                    'client_name' => $check_record->client_name,
                    
                        ];
                    }
                     $this->response(
                                ['status' => 'success',
                                    'message' => 'Data found successfully',
                                    'data' => $userData,
                                    'responsecode' => REST_Controller::HTTP_OK,
                        ]);
             }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No data found!',
                                
                    ]); 
             }  
            }
    
    }
    //get all task list
    public function allTaskList_get($provider_id = '' , $user_id = '' , $team_id = '')
    {
        $tokenid = $this->security->xss_clean($this->input->get_request_header('Secret-Key'));
        $check_key = $this->authentication($provider_id , $tokenid);
            if(empty($provider_id))
            {
            return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'errors' => "Sp id is required!",

            ]);
            }else{
               $getTasks = $this->db->select('a.* , b.teamname , b.id as teamid')
                                     ->from('assigntask a')
                                     ->join('myteams as b' , 'b.id = a.teamid')
                                     ->where(['a.spid'=>$provider_id , 'a.teamid'=>$team_id , 'a.userid'=>$user_id , 'a.taskstatus'=>''])  
                                     ->order_by('b.id DESC')
                                     ->get()
                                     ->result();   
             if($getTasks)
             {
                  foreach($getTasks as $val)
                    {
                     $userData[] = [
                                'taskid' => $val->id,
                                'spid' => $val->spid,
                                'userid' => $val->userid,
                                'teamid' => $val->teamid,
                                'teamname' => ucwords($val->teamname),
                                'title' => ucwords($val->title),
                                'task_name' => ($val->task_name==null)?"":ucwords($val->task_name),
                                'taskstatus' => $val->taskstatus ? $val->taskstatus : 'Pending',
                                'description' => ($val->describe)?$val->describe:"",
                                'comments' => $val->comments ? $val->comments : '',
                                'taskdate' => $val->taskdate ? $val->taskdate : '',
                                'start_time' => $val->start_time ? $val->start_time : '',
                                'end_time' => $val->end_time ? $val->end_time : '',
                    
                        ];
                    }
                     $this->response(
                                ['status' => 'success',
                                    'message' => 'Data found successfully',
                                    'data' => $userData,
                                    'responsecode' => REST_Controller::HTTP_OK,
                        ]);
             }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No data found!',
                                
                    ]); 
             }  
            }
    
    }





}
