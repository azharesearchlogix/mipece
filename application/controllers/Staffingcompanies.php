<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH.'/third_party/vendor/autoload.php';
use HubSpot\Factory;
use HubSpot\Client\Crm\Contacts\ApiException;
use \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;

class Staffingcompanies extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model', 'Mail']);
    }

    public function checktoken($token, $userid) {

        $auth = $this->Common_model->common_getRow('logincr', array('token_security' => $token, 'id' => $userid));

        if (!empty($auth)) {
            $abc['status'] = "true";
            $abc['data'] = $auth;
            return $abc;
        } else {
            $abc['status'] = "false";
            return $abc;
        }
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
    //get all staffing companies for user end (client end)
    public function allStaffingCompanies_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);

        $staffingcompanies = $this->db->order_by('firstname ASC')->where(['status' => '1' , 'usertype'=>'2'])->or_where(['switch_account'=>'2'])->where('id!=',$user_id)->get('logincr')->result();

        if ($staffingcompanies) {
            
            foreach ($staffingcompanies as $check_record) {
                if($user_id == $check_record->id)
                continue;
                $result[] = [
                    'id' => $check_record->id,
                    'usertype' => $check_record->usertype,
                    'firstname' => ucwords($check_record->firstname),
                    'lastname' => ucwords($check_record->lastname),
                     'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'rating' => $check_record->rating,
                    'min_commission' => $check_record->min_commission,
                    'max_commission' => $check_record->max_commission,
                    'aboutus' => $check_record->aboutus,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
            ]);
        }
    }
     public function genreateAgreementLetter_post()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $auth = $this->authentication($user_id, $token);
        $data = $this->input->post();
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Client id  is required',
                    'numeric' => 'Client id  should be numeric',
                ],
            ],
            ['field' => 'sc_id', 'label' => 'sc_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Service company id is required',
                    'numeric' => 'Service company should be numeric',
                ],
            ],
            ['field' => 'no_team_member', 'label' => 'no_team_member', 'rules' => 'required',
                'errors' => [
                    'required' => 'Number of team member is required',
                ],
            ],
            ['field' => 'team_name', 'label' => 'team_name', 'rules' => 'required',
                'errors' => [
                    'required' => 'Lunch break description is required',
                ],
            ],
            ['field' => 'payment_terms', 'label' => 'payment_terms', 'rules' => 'required',
                'errors' => [
                    'required' => 'Payment terms is required',
                ],
            ],
             ['field' => 'job_start_date', 'label' => 'job_start_date', 'rules' => 'required',
                'errors' => [
                    'required' => 'Job start date is required',
                ],
            ],
            ['field' => 'job_end_date', 'label' => 'job_end_date', 'rules' => 'required',
                'errors' => [
                    'required' => 'Job end date is required',
                ],
            ],
            /* ['field' => 'job_start_time', 'label' => 'job_start_time', 'rules' => 'required',
                'errors' => [
                    'required' => 'Job start time is required',
                ],
            ],
            ['field' => 'job_end_time', 'label' => 'job_end_time', 'rules' => 'required',
                'errors' => [
                    'required' => 'Job end time is required',
                ],
            ],*/
            ['field' => 'project_budget', 'label' => 'project_budget', 'rules' => 'required',
                'errors' => [
                    'required' => 'Project budget is required',
                ],
            ],
            ['field' => 'commission', 'label' => 'commission', 'rules' => 'required',
                'errors' => [
                    'required' => 'Commission is required',
                ],
            ],
             
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else
        {
            $team_for = $this->input->post('team_for');
            if($team_for=='0')
            {
               $check_xai = $this->db->get_where('tbl_xai_matching',['for_self_user'=>'1' , 'type'=>'4' , 'user_id'=>$user_id])->row(); 
            }else if($team_for!='0')
            {
                $check_xai = $this->db->get_where('tbl_xai_matching',['for_self_user'=>'0' , 'type'=>'2' , 'user_id'=>$user_id , 'member_id'=>$team_for])->row(); 
            }
            if($check_xai)
            {
            $formArray = [
                'user_id' => $this->input->post('user_id'),
                'sc_id' => $this->input->post('sc_id'),
                'team_name' => $this->input->post('team_name'),
                'team_for' => $this->input->post('team_for'),
                'team_desc' => $this->input->post('team_desc'),
                'no_team_member' => $this->input->post('no_team_member'),
                'language' => $this->input->post('language'),
                'payment_terms' => $this->input->post('payment_terms'),
                'job_start_date' => $this->input->post('job_start_date'),
                'job_end_date' => $this->input->post('job_end_date'),
                'job_start_time' => '00:00:00',
                'job_end_time' => '00:00:00',
                'project_budget' => $this->input->post('project_budget'),
                'commission' => $this->input->post('commission'),
                'encrypt_key' => gerandomstring(30),
                
            ];
            $fileArray = [];
            if (!empty($_FILES['client_signature']['name'])) {
                $file = $_FILES['client_signature']['name'];
                $name = 'client_signature';
                $path = 'client_signature';
                $type = 'jpeg|jpg|png';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $formArray['client_signature'] = $file_data['file'];
                }
            }
            //upload team image
             if (!empty($_FILES['teamimage']['name'])) {
                 $configF['upload_path'] = './upload/images/';
                $configF['allowed_types'] = 'jpeg|jpg|png';
                $configF['max_size'] = 50600;
                $this->upload->initialize($configF);
                if (!$this->upload->do_upload('teamimage')) {
                            $response = ['status' => 'false',
                                 'responsecode' => '403',
                                 'message' => strip_tags($this->upload->display_errors()),
                            ];
                           
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $formArray['teamimage'] = 'upload/images/' . $this->upload->data('file_name');
                        }
            }
             /*$exist = $this->db->get_where('tbl_client_agreement', ['user_id' => $this->input->post('user_id') , 'sc_id' => $this->input->post('sc_id')])->row();
              if ($exist) {
                $this->db->update('tbl_client_agreement', $formArray, ['id' => $exist->id]);
                 $res = $this->db->get_where('tbl_client_agreement', ['id' => $exist->id])->row();
                $effected = $this->db->affected_rows();
            } else {
                $this->db->insert('tbl_client_agreement', $formArray);
                $effected = $this->db->insert_id();
                 $res = $this->db->get_where('tbl_client_agreement', ['id' => $effected])->row();
            }*/
             $this->db->insert('tbl_client_agreement', $formArray);
                $effected = $this->db->insert_id();
                 $res = $this->db->get_where('tbl_client_agreement', ['id' => $effected])->row();

            $data = [
                'agreement_id' => $res->id,
                'user_id' => $res->user_id,
                'sc_id' => $res->sc_id,
                'team_name' => $res->team_name,
                'no_team_member' => $res->no_team_member,
                'team_desc' => $res->team_desc,
                'team_for' => $res->team_for,
                'language' => $res->language,
                'payment_terms' => $res->payment_terms,
                'job_start_date' => $res->job_start_date,
                'job_end_date' => $res->job_end_date,
                'job_start_time' => $res->job_start_time,
                'job_end_time' => $res->job_end_time,
                'project_budget' => $res->project_budget,
                'commission' => $res->commission,
                
                'status' => $res->status,
                'client_signature' => $res->client_signature ? base_url($res->client_signature) : '',
                 'teamimage' => $res->teamimage!=NULL ? base_url($res->teamimage) : base_url('upload/user/phpto.png'),
                'created_at' => date('Y-m-d H:i:s'),
                'preview_url' => base_url('myteam/previewAgreementLetter/' . $res->encrypt_key),
                'pdf_url' => base_url('myteam/downloadAgreementLetter/' . $res->encrypt_key),
            ];

            if ($effected > 0) {

                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Agreement genarated successfully!',
                            'data' => $data,
                ]);
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Some thing went wrong please try after some time!',
                ]);
            }
        }else{
            $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Please fill xai data!',
                ]); 
        }
        }

    }

    //send agreement 
    public function sentAgreement_post() {
        
           $token = $this->input->get_request_header('Secret-Key');
           $agreement_id = $this->input->post('agreement_id');
           $user_id = $this->input->post('user_id');
           $auth = $this->authentication($user_id, $token);
           
           $result = $this->db->update('tbl_client_agreement',['send_status'=>'1'], ['id' => $agreement_id]);
           $effected = $this->db->affected_rows();
           if ($effected > 0) {
        
             $user = $this->db->select('a.*,b.email,CONCAT(b.firstname, " ", b.lastname) AS user_name')
             ->from('tbl_client_agreement as a')
             ->join('logincr as b', 'b.id = a.sc_id', 'left')
             ->where(['a.id' => $agreement_id])->get()->row();
        
             $mailArray = [
                'url' => base_url('myteam/previewAgreementLetter/'.  $user->encrypt_key),
                'name' => $user->user_name,
            ];
           
            $html = $this->load->view('email/send_client_agreement', $mailArray, TRUE);
            $res = $this->Mail->sendmail($user->email, 'Agreement Letter Generation!', $html);
        
            if ($res) {
        
               $this->response(
                ['status' => 'success',
                'responsecode' => REST_Controller::HTTP_OK,
                'message' => 'Agreement sent successfully!',
        
            ]);
        
           } else {
            $this->response(
                [
                    'status' => 'success',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'message' => 'Agreement sent successfully, But mail not sent due to The email server not working at this moment!',             
                ]);
        }
        
        
        } else {
            $this->response(
                ['status' => 'false',
                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                'message' => 'Agreement already sent!',
            ]);
        }
    }
     //reject agreement letter
    public function rejectAgreement_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($this->input->post('user_id'), $token);

        $data = $this->input->post();

        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id should be numeric',
                ],
            ],
            ['field' => 'agreement_id', 'label' => 'agreement_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Agreement is required',
                    'numeric' => 'Agreement id should be numeric',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $result = $this->db->get_where('tbl_client_agreement', ['id' => $this->input->post('agreement_id')])->row();
            if(!empty($this->input->post('message')))
            {
             $myData = [

                'feedback_type' =>'4',
                'main_id' => $this->input->post('agreement_id'), 
                'user_type' => '0',
                'user_id' => $this->input->post('user_id'),
                'message' => $this->input->post('message'),
            ];
            $this->db->insert('tbl_all_feedback',$myData);
            }
            if ($result->status == '1') {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'You have already accept your agreement letter!',
                ]);
            } elseif ($result->status == '2') {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'You have already reject your agreement letter!',
                ]);
            } else {

                $this->db->trans_begin();

                $this->db->update('tbl_client_agreement', ['status' => '2'], ['id' => $this->input->post('agreement_id')]);

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Something went wrong please try aftre some time!',
                    ]);
                } else {
                    $this->db->trans_commit();
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Agreement rejected successfully!',
                    ]);
                }
            }
        }
    }
    //accept offer letter
    public function acceptAgreement_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($this->input->post('user_id'), $token);

        $data = $this->input->post();

        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should be numeric',
                ],
            ],
            ['field' => 'agreement_id', 'label' => 'offer_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Agreement id is required',
                    'numeric' => 'Agreement id should be numeric',
                ],
            ],
             ['field' => 'agreement_sendby_id', 'label' => 'agreement_sendby_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Agreement send by id is required',
                    'numeric' => 'Agreement send by id should be numeric',
                ],
            ],

        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            if (!empty($_FILES['sc_signature']['name'])) {
                $file = $_FILES['sc_signature']['name'];
                $name = 'sc_signature';
                $path = 'sc_signature';
                $type = 'jpeg|jpg|png';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $result = $this->db->get_where('tbl_client_agreement', ['id' => $this->input->post('agreement_id')])->row();
                    if(!$result)
                    {
                        $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Agreement not found!',
                ]);
                    }else{
                   // print_r($result); die;
                    if ($result->status == '1') {
                        $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => 'You have already accept your offer letter!',
                        ]);
                    } elseif ($result->status == '2') {
                         
                        $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => 'You have already reject your offer letter!',
                        ]);
                    } else {
                        $signa = $file_data['file'];
                        $myTeamData = [
                            'agreement_id' => $result->id,
                            'agreement_sendby_id' => $this->input->post('agreement_sendby_id'),
                            'user_id' => $this->input->post('user_id'),
                            'members' => $result->no_team_member,
                            'zipcode' => '',
                            'language' => $result->language,
                            'teamname' => $result->team_name,
                            'teamimage' => ($result->teamimage!=NULL)?$result->teamimage:'',
                            'industry' => '',
                            'skills' =>'',
                            'experience' => '',
                            'description' => $result->team_desc,
                            'budget' => $result->project_budget,
                            'member_id' => $result->team_for,
                        ];

                        $this->db->trans_begin();
                        $this->db->update('tbl_client_agreement', ['sc_signature' => $signa, 'status' => '1'], ['id' => $this->input->post('agreement_id')]);
                        $this->db->insert('myteams',$myTeamData);
                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->response(
                                    ['status' => 'false',
                                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                        'message' => 'Something went wrong please try aftre some time!',
                            ]);
                        } else {
                            $this->db->trans_commit();
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Agreement Letter Accepted!',
                            ]);
                        }
                    }
                }
                }
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Signature is required!',
                ]);
            }
        }
    }
    //get staffing details
    public function staffingCompDetails_get($user_id = NULL , $sc_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);

        $check_record = $this->db->get_where('logincr', ['status' => '1' , 'id'=>$sc_id])->row();

        if ($check_record) {
            $feedback = $this->db->select('a.* , CONCAT(b.firstname," ",b.lastname) as name , b.profile_pic as image')->from('tbl_feedback_sc as a')->join('logincr as b','b.id = a.user_id','left')->where(['a.sc_id'=>$sc_id])->order_by('a.id DESC')->get()->result();
            $feedbackData = [];
            if($feedback)
            {
                foreach($feedback as $fe)
                {
                    $feedbackData[] = [
                        'name' => $fe->name,
                        'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                        'feedback' => $fe->feedback
                    ];
                }
            }
             $result = [
                    'id' => $check_record->id,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                     'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'rating' => $check_record->rating,
                    'min_commission'          => $check_record->min_commission,
                    'max_commission'          => $check_record->max_commission,
                    'abouts'          => $check_record->aboutus,
                    'feedback' => $feedbackData
                ];
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
            ]);
        }
    }
   public function updateProfile_post()
{
    
    $userid = $this->input->post('id');  
    $created = date('Y-m-d h:i');
    $modified = date('Y-m-d h:i');
    $status=1;
    $token = $this->input->get_request_header('Secret-Key');
    if($token !='') 
    { 

    $check_key = $this->checktoken($token,$userid);
    if($check_key['status'] == 'true')
        { 
    

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
                                 $min_commission = $this->input->post('min_commission');
                                 $max_commission = $this->input->post('max_commission');
                                 $abouts = $this->input->post('abouts');
                                
                                
                                    $formdata = array(                                             
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
                                                'update_date' => $created,
                                                'min_commission'  => $min_commission,
                                                'max_commission'  => $max_commission,
                                                'aboutus'  => $abouts,
                                                                
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
                                                        'min_commission'          => $check_record->min_commission,
                                                        'max_commission'          => $check_record->max_commission,
                                                        'abouts'          => $check_record->aboutus,                                                     
                                                        
                                                    );
                                  
                            $this->response(
                              ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Your profile has been updated successfully.',
                            'data' => $dataa_array,

                            ]);
                        
                   }
    
             else
                {
                     $this->response(
                              ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'Please send username!',

                            ]);
                    
                }
            }
          else
            {
                 $this->response(
                              ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                            'message' => 'Invalid token!',

                            ]);     
            } 
         } 
          else
            {
                $this->response(
                              ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
                            'message' => 'Unauthorised Access!',

                            ]);    
            }  
    
}
  //add about us and min commission and max commision
    public function addAboutAndCommission_post() {
        
           $token = $this->input->get_request_header('Secret-Key');
           $user_id = $this->input->post('user_id');
            $min_commission = $this->input->post('min_commission');
            $max_commission = $this->input->post('max_commission');
            $abouts = $this->input->post('abouts');
           $auth = $this->authentication($user_id, $token);
           
           $result = $this->db->update('logincr',['min_commission'=>$min_commission , 'max_commission'=>$max_commission , 'aboutus'=>$abouts], ['id' => $user_id]);
           $effected = $this->db->affected_rows();
           if ($effected > 0) {
        
        
               $this->response(
                ['status' => 'success',
                'responsecode' => REST_Controller::HTTP_OK,
                'message' => 'Data added successfully',
        
            ]);
        
           } else {
            $this->response(
                [
                    'status' => 'success',
                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    'message' => 'Data already added!',             
                ]);
        }
        
    }
    
    /*----------------------- All xai for self client---------------*/
public function xaioneNew_put() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');
        $type = '4';
        $for_self_user = '1';
        

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                $data = $this->put();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],
                    ['field' => 'industry_id', 'label' => 'industry_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Industry id is required',
                            'numeric' => 'Industry id  should  numeric value',
                        ],
                    ],
                    ['field' => 'skill_id', 'label' => 'skill_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Skill id is required',
                            
                        ],
                    ],
                    ['field' => 'experience_id', 'label' => 'experience_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Experience id is required',
                            'numeric' => 'Experience id  should  numeric value',
                        ],
                    ],
                    
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                // 'message' => $this->form_validation->error_array(),
                                'message' => strip_tags(validation_errors()),
                    ]);
                } else {
                    if(!is_numeric($this->put('skill_id')))//when skill not found according to industry id
                       {
                        $this->db->insert('tbl_skill',['industry_id'=>$this->put('industry_id') , 'name'=> $this->put('skill_id') ]);
                        $insert_id = $this->db->insert_id();
                        $skill_id = $insert_id;
                       }else{
                        $skill_id = $this->put('skill_id');
                       }
                    $formArray = [
                        'type' => $type,
                        'for_self_user' => $for_self_user,
                        'team_id' => 0,
                        'member_id' => 0,
                        'user_id' => $this->put('user_id'),
                        'industry_id' => $this->put('industry_id'),
                        'skill_id' => $skill_id,
                        'experience_id' => $this->put('experience_id'),
                        'team_id' => $this->put('team_id'),
                    ];
                    
                    if ($this->put('personality') != '') {
                        $formArray['personality'] = $this->security->xss_clean($this->put('personality'));
                    }
                     $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already added!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            } else {

                $this->response(
                        ['status' => 'Failed',
                            'message' => 'Invalid Token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    ['status' => 'Failed',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //second step xai
    public function xaitwoNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = 0;
            $member_id = 0;
            $type = '4';
            $for_self_user = '1';
           

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'factors', 'label' => 'factors', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Factors is required',
                        ],
                    ],
                    ['field' => 'interest', 'label' => 'interest', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Interest  is required',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(validation_errors()),
                    ]);
                } else {

                    $formArray = [
                        'member_id' => $member_id,
                        'team_id' => $team_id,
                        'type' => $type,
                        'for_self_user' => $for_self_user,
                        'members' => $this->security->xss_clean($this->input->post('members')),
                        'factors' => $this->security->xss_clean($this->input->post('factors')),
                        'interest' => $this->security->xss_clean($this->input->post('interest')),
                    ];
                    if ($this->input->post('seven_24') == '') {
                        $formArray['available_days'] = $this->security->xss_clean($this->input->post('available_days'));
                        $formArray['start_time'] = $this->security->xss_clean($this->input->post('start_time'));
                        $formArray['end_time'] = $this->security->xss_clean($this->input->post('end_time'));
                    } else {
                        $formArray['seven_24'] = $this->security->xss_clean($this->input->post('seven_24'));
                    }
                    
                    if ($this->input->post('backup') == '1') {
                        if ($this->input->post('backup_email') != '') {
                            $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
                            $mailArray = [
                                'name' => $user->firstname . ' ' . $user->lastname,
                                'refralcode' => $user->refralcode,
                            ];
                            $html = $this->load->view('email/backuprequest', $mailArray, TRUE);
                            $res = $this->Mail->sendmail($this->input->post('backup_email'), 'Mipece.com referral request!', $html);
                            if ($res) {
                                $formArray['backup'] = $this->security->xss_clean($this->input->post('backup'));
                            } else {
                                $this->response(
                                        [
                                            'status' => 'false',
                                            'message' => 'The email server not working at this moment please try after some time!',
                                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                ]);
                            }
                        } else {
                            $this->response(
                                    ['status' => 'failed',
                                        'message' => 'Please provide back up email id!',
                                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                            ]);
                        }
                    }

           
                   $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }


                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //xai step 3
    public function xaithreeNewUserr_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $member_id = $this->input->post('member_id');
           

            $check_key = $this->checktoken($token, $user_id);
            $team_id = $this->input->post('team_id');
              $email = $this->input->post('email');
            $email_type = $this->input->post('email_type');
            $email_pref = $this->input->post('email_pref');
            //
            $video  = $this->input->post('video');
            $video_type = $this->input->post('video_type');
            $video_pref = $this->input->post('video_pref');
             //
            $chat   = $this->input->post('chat');
            $chat_type = $this->input->post('chat_type');
            $chat_pref = $this->input->post('chat_pref');
             //
            $phone_call   = $this->input->post('phone_call');
            $phone_call_type = $this->input->post('phone_call_type');
            $phone_call_pref = $this->input->post('phone_call_pref');
            $type = '4';
            $for_self_user = '1';
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'rate', 'label' => 'rate', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Rate is required',
                        ],
                    ],
                    ['field' => 'accessment', 'label' => 'accessment', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Accessment is required',
                        ],
                    ],
                   
                    ['field' => 'communication_id', 'label' => 'communication_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Communication  is required',
                        ],
                    ],
                    ['field' => 'softskills_id', 'label' => 'softskills_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'SoftSkills  is required',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->form_validation->error_array(),
                    ]);
                } else {
                    $formArray = [
                         'member_id' => 0,
                         'for_self_user' => $for_self_user,
                        'rate' => $this->security->xss_clean($this->input->post('rate')),
                        'accessment' => $this->security->xss_clean($this->input->post('accessment')),
                        'communication_id' => $this->security->xss_clean($this->input->post('communication_id')),
                        'softskills_id' => $this->security->xss_clean($this->input->post('softskills_id')),
                        'frequency_id' => 0,//$this->security->xss_clean($this->input->post('frequency_id')),
                          'is_email' => $email,
                          'email_type' => $email_type,
                          'email_pref' => $email_pref,
                           'is_chat' => $chat,
                          'chat_type' => $chat_type,
                          'chat_pref' => $chat_pref,
                           'is_video' => $video,
                          'video_type' => $video_type,
                          'video_pref' => $video_pref,
                           'is_other' => $phone_call,
                          'other_type' => $phone_call_type,
                          'other_pref' => $phone_call_pref,
                    ];
                    
                     $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }
                        
                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
//xai step4
    public function xaifourNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = 0;
            $member_id = 0;
            $for_self_user = '1';
            $type = '4';
             

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'expectation', 'label' => 'expectation', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Expectation is required',
                        ],
                    ],
                    ['field' => 'driving_distance', 'label' => 'driving_distance', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Driving distance is required',
                        ],
                    ],
                   
                    ['field' => 'xai_personality', 'label' => 'xai_personality', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Xai personality  is required',
                        ],
                    ],
                    ['field' => 'additional_skills', 'label' => 'additional_skills', 'rules' => 'required',
                'errors' => [
                    'required' => 'Additional skills is required',
                ],
            ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->form_validation->error_array(),
                    ]);
                } else {
                    $formArray = [
                        'expectation' => $this->security->xss_clean($this->input->post('expectation')),
                        'driving_distance' => $this->security->xss_clean($this->input->post('driving_distance')),
                        'xai_personality' => $this->security->xss_clean($this->input->post('xai_personality')),
                         'additional_skills' => $this->security->xss_clean($this->input->post('additional_skills')),
                    ];
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //xai 5th step
    public function xaifiveNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = 0;
            $member_id = 0;
            $type = '4';
            $for_self_user = '1';
            

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                   
                    ['field' => 'category', 'label' => 'category', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Category is required',
                        ],
                    ],
                    ['field' => 'options_preferences', 'label' => 'options_preferences', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Options preferences is required',
                        ],
                    ],
                    
                   
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => rtrim(str_replace("\n",',',strip_tags(validation_errors())),","),
                    ]);
                } else {
                    $provide = $this->security->xss_clean($this->input->post('provide'));
                    $provide_input = $this->security->xss_clean($this->input->post('provide_input'));
                    $required = $this->security->xss_clean($this->input->post('required'));
                    $required_input = $this->security->xss_clean($this->input->post('required_input'));
                    $category = $this->security->xss_clean($this->input->post('category'));
                    $categorytype = $this->security->xss_clean($this->input->post('categorytype'));
                    $options_preferences = $this->security->xss_clean($this->input->post('options_preferences'));
                    $covid_vaccination_proof = $this->security->xss_clean($this->input->post('covid_vaccination_proof'));
                     $negative_tests_proof = $this->security->xss_clean($this->input->post('negative_tests_proof'));
                      $none = $this->security->xss_clean($this->input->post('none'));
                      $allergy  = $this->security->xss_clean($this->input->post('allergy'));
                      $allergy_text = $this->security->xss_clean($this->input->post('allergy_text'));
                    $formArray = [

                        'provide' => $provide,
                        'provide_input' => $provide_input,
                        'required' => $required,
                        'required_input' => $required_input,
                        'category' => $category,
                        'categorytype' => $categorytype,
                        'options_preferences' => $options_preferences,
                        'covid_vaccination_proof' => $covid_vaccination_proof,
                        'negative_tests_proof' => $negative_tests_proof,
                        'none' => $none,
                         'allergy' => $allergy,
                        'allergy_text' => $allergy_text,
                    ];
                    
                   $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //last step xai
     public function xaifinishNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = 0;
            $member_id = 0;
            $type = '4';
            $for_self_user = '1';
            

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'motivation', 'label' => 'motivation', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Motivation is required',
                        ],
                    ],
                    ['field' => 'language', 'label' => 'language', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Language distance is required',
                        ],
                    ],
                   
                   
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->form_validation->error_array(),
                    ]);
                } else {
                    

                    $formArray = [
                        'member_id' => $member_id,
                        'motivation' => $this->security->xss_clean($this->input->post('motivation')),
                        'language' => $this->security->xss_clean($this->input->post('language')),
                         'dream_job_description1' => $this->security->xss_clean($this->input->post('dream_job_description1')),
                         'dream_job_description2' => $this->security->xss_clean($this->input->post('dream_job_description2')),
                    ];
                    
                  $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->input->post('user_id') ,'type' => '4' , 'for_self_user'=>$for_self_user]);
                        $effected = $this->db->affected_rows();
                    }

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                                    
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                    
                        ]);
                    }
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //get xai data
       public function getXaiDataNew_get($user_id = NULL) {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $check_key = $this->authentication($user_id , $tokenid);  
            $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.type'=>'4' , 'for_self_user'=>'1'])->get()->row(); 
        if ($data) {
            $factorsData = [];
            $factors = $this->db->where_in('id',explode(',',$data->factors))->get('tbl_factors')->result();
            if($factors)
            {
                foreach($factors as $f)
                {
                    $factorsData[] =['id'=>$f->id , 'name'=>$f->name];
                }
            }
             $communicationData = [];
            $communication = $this->db->where_in('id',explode(',',$data->communication_id))->get('tbl_communications')->result();
            if($communication)
            {
                foreach($communication as $cm)
                {
                    $communicationData[] =['id'=>$cm->id , 'name'=>$cm->name];
                }
            }
            $addskiilsData = [];
            $addskills = $this->db->where_in('id',explode(',',$data->additional_skills))->get('tbl_additional_skills')->result();
            if($addskills)
            {
                foreach($addskills as $sk)
                {
                    $addskiilsData[] =['id'=>$sk->id , 'skills'=>$sk->skills];
                }
            }
             $softskiilsData = [];
            $softskills = $this->db->where_in('id',explode(',',$data->softskills_id))->get('tbl_softskill')->result();
            if($softskills)
            {
                foreach($softskills as $sk1)
                {
                    $softskiilsData[] =['id'=>$sk1->id , 'skills'=>$sk1->name];
                }
            }
             $frequencyData = [];
            $frequency = $this->db->where_in('id',explode(',',$data->frequency_id))->get('tbl_frequency')->result();
            if($frequency)
            {
                foreach($frequency as $fr)
                {
                    $frequencyData[] =['id'=>$fr->id , 'type'=>$fr->type];
                }
            }
            $personalityData = [];
            $mypersonality = $this->db->where_in('id',explode(',',$data->new_personality))->get('tbl_personality')->result();
            if($mypersonality)
            {
                foreach($mypersonality as $pk)
                {
                    $personalityData[] =['id'=>$pk->id , 'personality'=>$pk->personality1];
                }
            }
            $cattypeData = [];
            $mycattype = $this->db->where_in('id',explode(',',$data->categorytype))->get('tbl_seniorcare_type')->result();
            if($mycattype)
            {
                foreach($mycattype as $dk)
                {
                    $cattypeData[] =['id'=>$dk->id , 'name'=>$dk->name];
                }
            }

            $myData[] = [
                'user_id' => $data->user_id,
                'team_id' => $data->team_id,
                'member_id' => $data->member_id,
                'industry_id' => $data->industry_id,
                'industry_name' => $data->industry_name,
                'skill_id' => $data->skill_id,
                'skill_name' => ($data->skill_name!=NULL)?$data->skill_name:"",
                'experience_id' => $data->experience_id,
                'experience_name' => $data->experience_name,
                'personality' => $data->personality,
                'members' => $data->members,
                'factors' => $factorsData,
                'available_days' => $data->available_days,
                'start_time' => $data->start_time,
                'end_time' => $data->end_time,
                'seven_24' => ($data->seven_24!=NULL)?$data->seven_24:"",
                'interest' => $data->interest,
                'backup' => $data->backup,
                'rate' => ($data->rate!=NULL)?$data->rate:"",
                'accessment' => ($data->accessment!=NULL)?$data->accessment:"",
                'communication_data' => $communicationData,
                'expectation' => ($data->expectation!=NULL)?$data->expectation:"",
                'driving_distance' => ($data->driving_distance!=NULL)?$data->driving_distance:"",
                'xai_personality' => ($data->xai_personality!=NULL)?$data->xai_personality:"",
                'motivation' => ($data->motivation!=NULL)?$data->motivation:"",
                'language' => ($data->language!=NULL)?$data->language:"",
                'frequency' => $frequencyData,
                'softskill' => $softskiilsData,
                'additional_skills' => $addskiilsData,
                'new_personality' => $personalityData,
                'percentage' => ($data->percentage!=NULL)?$data->percentage:"",
                 'provide' => ($data->provide!=NULL)?$data->provide:"",
                'provide_input' => ($data->provide_input!=NULL)?$data->provide_input:"",
                'required' => ($data->required!=NULL)?$data->required:"",
                'required_input' => ($data->required_input!=NULL)?$data->required_input:"",
                'category' => ($data->category!=NULL)?$data->category:"",
                'categorytype' => $cattypeData,
                'options_preferences_id' => ($data->options_preferences_id!=NULL)?$data->options_preferences_id:"",
                'options_preferences' => ($data->options_preferences!=NULL)?$data->options_preferences:"",
                'covid_vaccination_proof' => ($data->covid_vaccination_proof!=NULL)?$data->covid_vaccination_proof:"",
                'negative_tests_proof' => ($data->negative_tests_proof!=NULL)?$data->negative_tests_proof:"",
                'none' => ($data->none!=NULL)?$data->none:"",
                'dream_job_description1' => ($data->dream_job_description1!=NULL)?$data->dream_job_description1:"",
                'dream_job_description2' => ($data->dream_job_description2!=NULL)?$data->dream_job_description2:"",
                'allergy' => $data->allergy,
                 'allergy_text' => ($data->allergy_text!=NULL)?$data->allergy_text:"",
                 'is_email' => ($data->is_email!=NULL)?$data->is_email:"",
                    'email_type' => ($data->email_type!=NULL)?$data->email_type:"",
                    'email_pref' => ($data->email_pref!=NULL)?$data->email_pref:"",
                    'is_chat' => ($data->is_chat!=NULL)?$data->is_chat:"",
                    'chat_type' => ($data->chat_type!=NULL)?$data->chat_type:"",
                    'chat_pref' => ($data->chat_pref!=NULL)?$data->chat_pref:"",
                    'is_video' => ($data->is_video!=NULL)?$data->is_video:"",
                    'video_type' => ($data->video_type!=NULL)?$data->video_type:"",
                    'video_pref' => ($data->video_pref!=NULL)?$data->video_pref:"",
                    'is_phone_call' => ($data->is_other!=NULL)?$data->is_other:"",
                    'phone_call_type' => ($data->other_type!=NULL)?$data->other_type:"",
                    'phone_call_pref' => ($data->other_pref!=NULL)?$data->other_pref:"",


            ];

            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found!',
                        'data' => $myData,           
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Data not found!',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'data' => [],
            ]);
        }
    }
     //get all agreement at sc end
    public function allScAgreementList_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);

        $all_agreements = $this->db->select('a.* , CONCAT(b.firstname , " " , b.lastname) as name , b.image')
                                   ->from('tbl_client_agreement as a')
                                   ->join('logincr as b','b.id = a.user_id' , 'left')

                                   ->where(['a.sc_id'=>$user_id])
                                   ->order_by('a.created_at DESC')
                                   ->get()->result();

        if ($all_agreements) {
            $result = [];
            $pending = [];
            foreach ($all_agreements as $check_record) {
                if($check_record->status=='0')
                {
                     $pending[] = [
                    'id' => $check_record->id,
                    'client_id' => $check_record->user_id,
                    'client_name' => $check_record->name,
                    'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                    'teamname' => $check_record->team_name,
                    'job_start_date' => $check_record->job_start_date,
                    'job_end_date' => $check_record->job_end_date,
                    'project_budget' => $check_record->project_budget,
                    'commission' => $check_record->commission,
                     'preview_url' => base_url('myteam/previewAgreementLetter/' . $check_record->encrypt_key),
                    'pdf_url' => base_url('myteam/downloadAgreementLetter/' . $check_record->encrypt_key),
                    'status' => $check_record->status,
                ];
                }else{
                   $result[] = [
                    'id' => $check_record->id,
                    'client_id' => $check_record->user_id,
                    'client_name' => $check_record->name,
                    'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                    'teamname' => $check_record->team_name,
                    'job_start_date' => $check_record->job_start_date,
                    'job_end_date' => $check_record->job_end_date,
                    'project_budget' => $check_record->project_budget,
                    'commission' => $check_record->commission,
                     'preview_url' => base_url('myteam/previewAgreementLetter/' . $check_record->encrypt_key),
                    'pdf_url' => base_url('myteam/downloadAgreementLetter/' . $check_record->encrypt_key),
                    'status' => $check_record->status,
                ];  
                }
               
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'pending' => $pending,
                        'completed' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
            ]);
        }
    }
    //all agreement list show at client end
    public function allClientAgreementList_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);

        $all_agreements = $this->db->select('a.* , CONCAT(b.firstname , " " , b.lastname) as name , b.image')
                                   ->from('tbl_client_agreement as a')
                                   ->join('logincr as b','b.id = a.sc_id' , 'left')

                                   ->where(['a.user_id'=>$user_id])
                                   ->order_by('a.updated_at DESC , a.created_at DESC')
                                   ->get()->result();

        if ($all_agreements) {
            foreach ($all_agreements as $check_record) {
                $myData[] = [
                    'id' => $check_record->id,
                    'sc_name' => $check_record->name,
                    'profile_image' => $check_record->image ? base_url($check_record->image) : base_url('upload/users/photo.png'),
                    'teamname' => $check_record->team_name,
                    'job_start_date' => $check_record->job_start_date,
                    'job_end_date' => $check_record->job_end_date,
                    'project_budget' => $check_record->project_budget,
                    'commission' => $check_record->commission,
                    'status' => $check_record->status,
                    'preview_url' => base_url('myteam/previewAgreementLetter/' . $check_record->encrypt_key),
                    'pdf_url' => base_url('myteam/downloadAgreementLetter/' . $check_record->encrypt_key),
                ];
               
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $myData,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
            ]);
        }
    }
   


   


}
