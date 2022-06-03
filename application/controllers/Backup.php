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
                $getSp1 = $this->db->where(['usertype'=>'0' , 'status'=>'1' ,'interested_in_backup'=>'1' ,'id!='=>$userid])
                                  ->order_by('id','DESC')
                                  ->get('logincr')
                                  ->result();
                $getSp2 = $this->db->where(['switch_account'=>'0' , 'status'=>'1' ,'interested_in_backup'=>'1' ,'id!='=>$userid])
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




}
