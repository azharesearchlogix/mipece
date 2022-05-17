<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
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

    public function requestservice_post() {


        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $doctor_id = $this->input->post('doctor_id');
            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'User id  is required',
                        ],
                    ],
                    ['field' => 'doctor_id', 'label' => 'doctor_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Doctor id  is required',
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
                        'user_id' => $user_id,
                        'doctor_id' => $doctor_id,
                         'status' => '1',
                    ];
                    $result = $this->db->insert('tbl_service_request', $formArray);
                    $lid = $this->db->insert_id();
                    if ($lid > 0) {

                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Service Requested Successfully!',
                                    'data' => $lid,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Service Requested Failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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

    public function emergencyservices_get($user_id = NULL, $status_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->uri->segment(3);
            $status_id = $this->uri->segment(4);
            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                if (!empty($user_id) && !empty($status_id)) {

                    $query = $this->db->select('a.created_at as servicerequestdate,a.id as servicerequest_id,b.*,c.name as education')
                            ->from('tbl_service_request as a')
                            ->join('tbl_doctors as b', 'a.doctor_id = b.id', 'left')
                            ->join('tbl_educations as c', 'b.education_id = c.id', 'left')
                            ->where(['a.status' => $status_id, 'user_id' => $user_id])
                            ->get();
                    $result = $query->result();
//                    echo '<pre>';
//                    print_r($result);
//                    die;
                    if (count($result) > 0) {
                        foreach ($result as $val) {
                            $data[] = [
                                'servicerequestdate' => $val->servicerequestdate,
                                'servicerequest_id' => $val->servicerequest_id,
                                'doctor_id' => $val->id,
                                'name' => $val->name,
                                'education' => $val->education,
                                'start_time' => $val->start_time,
                                'end_time' => $val->end_time,
                                'experience' => $val->experience,
                                'fees' => $val->fees,
                                'phone' => $val->phone,
                                'description' => $val->description,
                                'profile_img' => base_url('/') . $val->profile_img,
                                'rating' => $val->rating,
                            ];
                        }
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Service Request Found!',
                                    'data' => $data,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Service Request not Found!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'User id and status id is required',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
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
    
    public function reviewpost_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            
            $user_id = $this->input->post('user_id');
            $provider_id = $this->input->post('provider_id');
            $rating = $this->input->post('rating');
            $comments = $this->security->xss_clean($this->input->post('comments'));
            
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
                    ['field' => 'provider_id', 'label' => 'provider_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Provider id  is required',
                            'numeric' => 'Provider id  should be numeric',
                        ],
                    ],
                    ['field' => 'rating', 'label' => 'rating', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Rating id  is required',
                            'numeric' => 'Rating id  should be numeric',
                        ],
                    ],
                    ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comments id  is required',
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
                        'user_id' => $user_id,
                        'provider_id' => $provider_id,
                        'rating' => $rating,
                        'comments' => $comments,
                    ];
                    $result = $this->db->insert('tbl_user_ratings', $formArray);
                    $lid = $this->db->insert_id();
                    if ($lid > 0) {

                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Rating added Successfully!',
                                    'data' => $lid,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Rating add Failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
    
    public function reviewsget_get($id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $provider_id = $this->uri->segment(4);
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {



                $ratio_res = $this->db->select('rating, SUM(rating) AS total', FALSE)->where('provider_id', $provider_id)->group_by("rating")->get('tbl_user_ratings')->result();
                if(count($ratio_res)>0){
                $devident = 0;
                $devisor = 0;
                foreach ($ratio_res as $r) {
                    $devident += ($r->rating * $r->total);
                    $devisor += $r->total;
                    $ratingcount[] = ['rating' => $r->rating, 'total' => $r->total];
                }

                $ratio = round($devident / $devisor, 2);
                }else{
                    $ratio = 0;
                }

                $provider = $this->db->select('a.*,b.name as education')
                                ->from('tbl_doctors as a')
                                ->join('tbl_educations as b', 'a.education_id = b.id', 'left')
                                ->where('a.id', $provider_id)
                                ->get()->row();
                if (!empty($provider)) {
                    $ptoviderArray = [
                        'provider_name' => $provider->name,
                        'start_time' => $provider->start_time,
                        'end_time' => $provider->end_time,
                        'experience' => $provider->experience,
                        'fees' => $provider->fees,
                        'description' => $provider->description,
                        'education' => $provider->education,
                        'profile_img' => base_url('/upload/users/') . $provider->profile_img,
                        'rating_ratio' => $ratio,
                    ];
                }
                //print_r($ptoviderArray); die;

                $query = $this->db->select('a.comments,a.rating,b.firstname,b.lastname,b.image')
                        ->from('tbl_user_ratings as a')
                        ->join('logincr as b', 'a.provider_id = b.id', 'left')
                        ->where('a.provider_id', $provider_id)
                        ->get();
                $result = $query->result();

                foreach ($result as $val) {
                    $dataArray[] = [
                        'username' => $val->firstname . ' ' . $val->lastname,
                        'comments' => $val->comments,
                        'image' => base_url('upload/users/') . $val->image,
                        'rate' => $val->rating,
                    ];
                }


                if (count($result) > 0) {

                    $f[] = [
                        'data' => $ptoviderArray,
                        'ratingcount' => $ratingcount,
                        'userdata' => $dataArray,
                    ];

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Review found!',
                                'data' => $f,
//                                'ratingcount' => $ratingcount,
//                                'userdata' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Review not found!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'providerdata' => [],
                                'ratingcount' => [],
                                'userdata' => [],
                    ]);
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
    
     public function reviewsget_old_get($id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $provider_id = $this->uri->segment(4);
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                
                

                $ratio_res = $this->db->select('rating, SUM(rating) AS total', FALSE)->where('provider_id', $provider_id)->group_by("rating")->get('tbl_user_ratings')->result();

                $devident = 0;
                $devisor = 0;
                foreach ($ratio_res as $r) {
                    $devident += ($r->rating * $r->total);
                    $devisor += $r->total;
                    $ratingcount[] = ['rating' => $r->rating, 'total' => $r->total];
                }

                $ratio = round($devident / $devisor, 2);
                
                $provider = $this->db->select('a.*,b.name as education')
                        ->from('tbl_doctors as a')
                        ->join('tbl_educations as b', 'a.education_id = b.id', 'left')
                        ->where('a.id', $provider_id)
                        ->get()->row();
                if(!empty($provider)){
                    $ptoviderArray= [
                        'provider_name' => $provider->name,
                        'start_time' => $provider->start_time,
                        'end_time' => $provider->end_time,
                        'experience' => $provider->experience,
                        'fees' => $provider->fees,
                        'description' => $provider->description,
                        'education' => $provider->education,
                        'profile_img' => base_url('/upload/users/').$provider->profile_img,
                        'rating_ratio' => $ratio,
                    ];
                }
               //print_r($ptoviderArray); die;

                $query = $this->db->select('a.comments,a.rating,b.firstname,b.lastname,b.image')
                        ->from('tbl_user_ratings as a')
                        ->join('logincr as b', 'a.provider_id = b.id', 'left')
                        ->where('a.provider_id', $provider_id)
                        ->get();
                $result = $query->result();
                
                foreach ($result as $val) {
                    $dataArray[] = [
                       
                        
                        'username' => $val->firstname.' '.$val->lastname,
                        'comments' => $val->comments,
                        'image' => base_url('upload/users/') . $val->image,
                        'rate' => $val->rating,
                        
                    ];
                }


                if (count($result) > 0) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Review found!',
                                'providerdata' => $ptoviderArray,
                                'ratingcount' => $ratingcount,
                                'userdata' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Review not found!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                               'providerdata' => [],
                                'ratingcount' => [],
                                'userdata' => [],
                    ]);
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
    
    
   public function approvetask_post() {
       $this->load->model('NotificationModel');
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $team_id = $this->input->post('teamid');
        $provider_id = $this->input->post('spid');
        $taskstatus = $this->input->post('taskstatus');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Provider id  is required',
                            'numeric' => 'Provider id  should be numeric',
                        ],
                    ],
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'taskstatus', 'label' => 'taskstatus', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task status   is required',
                        ],
                    ],
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task id   is required',
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
                    $task = $this->db->get_where('assigntask', ['spid' => $provider_id, 'teamid' => $team_id,'id'=>$this->security->xss_clean($this->input->post('taskid'))])->result();
//                    print_r($task);
//                    die;

                    if (count($task) > 0) {
                        $this->db->update('assigntask', ['taskstatus' => $this->security->xss_clean($this->input->post('taskstatus'))], ['spid' => $provider_id, 'teamid' => $team_id,'id'=>$this->security->xss_clean($this->input->post('taskid'))]);
                        $effected = $this->db->affected_rows();
                        if ($effected > 0) {
                            $msg = $this->input->post('message');
                        if(!empty($msg))
                        {
                        $provider_details = $this->db->get_where('logincr', ['id' => $provider_id])->row();
                        $message = [
                            'title' => 'Task Done',
                            'body' => 'Your task approved successfully',
                            'icon' => base_url('upload/images/notification.png')
                        ];
                        $notification_data = [
                            'device_tpye' => $this->input->post('device_tpye'),
                            'device_token' => $provider_details->tokenid,
                        ];
                        $response = $this->NotificationModel->index($notification_data, $message);
                        $message['user_id'] = $provider_details->id;
                        $this->db->insert('tbl_notification', $message);
                        $this->db->insert('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$this->input->post('taskid') ,'user_type'=>'1' ,'user_id'=>$user_id ,'message'=>$msg]);
                        }
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Status updated',
                                        'task_status' => $this->security->xss_clean($this->input->post('taskstatus')),
                            ]);
                        } else {
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Internal Server Error',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                        
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Review not found!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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

    public function rejecttask_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $team_id = $this->input->post('teamid');
        $provider_id = $this->input->post('spid');
        $taskstatus = $this->input->post('taskstatus');
        $comments = $this->input->post('comments');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Provider id  is required',
                            'numeric' => 'Provider id  should be numeric',
                        ],
                    ],
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'taskstatus', 'label' => 'taskstatus', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task status   is required',
                        ],
                    ],
                    ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comments  is required',
                        ],
                    ],
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task id  is required',
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
                    $task = $this->db->get_where('assigntask', ['spid' => $provider_id, 'teamid' => $team_id,'id'=>$this->security->xss_clean($this->input->post('taskid'))])->row();
                    // print_r($task);
                    // die;

                    if (!empty($task)) {
                        $formArray = [
                            'task_id' => $task->id,
                            'user_id' => $task->userid,
                            'sp_id' => $provider_id,
                            'team_id' => $team_id,
                            'comments' => $this->security->xss_clean($this->input->post('comments')),
                            'task_status' => $this->security->xss_clean($this->input->post('taskstatus')),
                            'created_by' => $user_id,
                        ];
//                        print_r($formArray);
//                        die; 
                        $this->db->trans_begin();
                        
                        $this->db->insert('tbl_task_status', $formArray);
                        $this->db->update('assigntask', ['taskstatus' => $this->security->xss_clean($this->input->post('taskstatus')),
                            'comments' => $this->security->xss_clean($this->input->post('comments'))], ['spid' => $provider_id, 'teamid' => $team_id,
                            'id' => $this->security->xss_clean($this->input->post('taskid'))
                        ]);
                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Internal Server Error',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        } else {
                            $this->db->trans_commit();
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Status updated',
                                        'task_status' => $this->security->xss_clean($this->input->post('taskstatus')),
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Review not found!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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

 public function cancelrequest_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $provider_id = $this->input->post('spid');
        $service_request_id = $this->input->post('service_request_id');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Provider id  is required',
                            'numeric' => 'Provider id  should be numeric',
                        ],
                    ],
                    ['field' => 'service_request_id', 'label' => 'service_request_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service request id   is required',
                            'numeric' => 'Service request id  should be numeric',
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
                    $this->db->update('tbl_service_request', ['status' => '3'], ['id' => $service_request_id]);
                    $effected = $this->db->affected_rows();
                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Request cancel succesfully',
                                    //'task_status' => $this->security->xss_clean($this->input->post('taskstatus')),
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Request allready canceled',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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

public function submitanswer_post() {

        $user_id = $this->input->post('user_id');
        $question_id = $this->input->post('question_id');
        $answer = $this->input->post('answer');
        
        $data = $this->input->post();
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should be numeric',
                ],
            ],
            ['field' => 'question_id', 'label' => 'question_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Question id  is required',
                    'numeric' => 'Question id  should be numeric',
                ],
            ],
            ['field' => 'answer', 'label' => 'answer', 'rules' => 'required',
                'errors' => [
                    'required' => 'Service request id   is required',
                ],
            ],
            ['field' => 'industry_id', 'label' => 'industry_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Industry id   is required',
                    'numeric' => 'Industry id  should be numeric',
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

            $exist_data = $this->db->get_where('tbl_answer', ['question_id' => $this->input->post('question_id'), 'industry_id' => $this->input->post('industry_id'), 'user_id' => $user_id])->row();
            $questions = $this->db->get_where('tbl_questions', ['industry_id' => $this->input->post('industry_id'), 'status' => '0'])->num_rows();
            $answers = $this->db->get_where('tbl_answer', ['industry_id' => $this->input->post('industry_id'), 'user_id' => $user_id])->num_rows();
            $percent = round((100 * $answers) / $questions);

            if ($answers < $questions) {

                $formArray = [
                    'user_id' => $this->input->post('user_id'),
                    'industry_id' => $this->input->post('industry_id'),
                    'question_id' => $this->input->post('question_id'),
                    'answer' => $this->security->xss_clean($this->input->post('answer')),
                ];
                if ($exist_data) {
                    $this->db->update('tbl_answer', $formArray, ['id' => $exist_data->id]);
                    $affected = $this->db->affected_rows();
                     $msg = 'Answer updated successfully!';
                    
                } else {
                    $this->db->insert('tbl_answer', $formArray);
                    $affected = $this->db->insert_id();
                     $msg = 'Answer submitted successfully!';
                   
                }
                $questions = $this->db->get_where('tbl_questions', ['industry_id' => $this->input->post('industry_id'), 'status' => '0'])->num_rows();
                $answers = $this->db->get_where('tbl_answer', ['industry_id' => $this->input->post('industry_id'), 'user_id' => $user_id])->num_rows();

                $percent = round((100 * $answers) / $questions);
                if ($affected > 0) {
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => $msg,
                                'percent' => $percent,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' =>  'You answer is already updated!',
                                'percent' => $percent,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'Your all question has finished!',
                            'percent' => $percent,
                ]);
            }
        }
    }
    
    
     public function addblog_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $title = $this->input->post('title');

        $config['upload_path'] = './upload/users/';
        $config['allowed_types'] = 'jpeg|jpg|png';
        $config['max_size'] = 50600;
        // $config['max_width'] = 3075;
        // $config['max_height'] = 3075;

        $this->load->library('upload', $config);
        $description = $this->input->post('description');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'title', 'label' => 'title', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Ttitle  is required',
                        ],
                    ],
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description   is required',
                        ],
                    ],
                    ['field' => 'image', 'label' => 'image', 'rules' => 'callback_file_check',
//                        'errors' => [
//                            'required' => 'image  is required',
//                        ],
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


                    $file = '';
                    if (!empty($_FILES['image']['name'])) {
                        if (!$this->upload->do_upload('image')) {
                            $this->response(
                                    ['status' => 'false',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                        'message' => strip_tags($this->upload->display_errors()),
                            ]);
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $file = 'upload/users/' . $this->upload->data('file_name');
                        }
                    }
                    $formArray = [
                        'title' => $this->security->xss_clean($this->input->post('title')),
                        'description' => $this->security->xss_clean($this->input->post('description')),
                        'image' => $file,
                        'created_by' => $this->input->post('user_id'),
                    ];
//                    echo '<pre>';
//                    print_r($formArray);
//                    die;

                    $this->db->insert('tbl_blog', $formArray);
                    if ($this->db->insert_id() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Blog added succesfully',
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Server error',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
    
    public function file_check($str) {
        $allowed_mime_type_arr = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png');
        if (!empty($_FILES['image']['name'])) {
            $mime = get_mime_by_extension($_FILES['image']['name']);
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
                if (in_array($mime, $allowed_mime_type_arr)) {
                    return true;
                } else {
                    $this->form_validation->set_message('file_check', 'Please select only jpg/jpeg/png file.');
                    return false;
                }
            } else {
                $this->form_validation->set_message('file_check', 'Please choose a file to upload.');
                return false;
            }
        } else {
            $this->form_validation->set_message('file_check', 'Image is not a file.');
            return false;
        }
    }
    
    public function blogs_get($id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {

            $check_key = $this->db->get_where('logincr', ['token_security'=>$token])->result();

            if (count($check_key)>0) {

                // $result = $this->db->get_where('tbl_blog', ['status' => '1'])->result();
                $query = $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('tbl_blog as a')
                        ->join('logincr as b', 'a.created_by = b.id', 'left')
                         ->where('b.id IS NOT NULL')
                         ->order_by('a.updated_at','DESC')
                        ->where('a.status', '1');
                if ($id) {
                    $query = $this->db->where('a.created_by', $id);
                }
                $query = $this->db->get();
                $result = $query->result();
                if (count($result) > 0) {
                    foreach ($result as $val) {
                        $dataArray[] = [
                            'id' => $val->id,
                            'user_id' => $val->created_by,
                            'profile_image' => ($val->profile_image!='') ? base_url().$val->profile_image :base_url('upload/users/photo.png') ,
                            'user_name' => $val->user_name,
                            'title' => $val->title,
                            'description' => $val->description,
                            'image' =>  base_url().$val->image,
                            'date' => date('F d Y h:i A', strtotime($val->updated_at)),
                        ];
                    }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Blogs Found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Blogs not Found!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
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

public function updateblog_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $blog_id = $this->input->post('blog_id');
        $user_id = $this->input->post('user_id');
        $title = $this->input->post('title');

        $config['upload_path'] = './upload/users/';
        $config['allowed_types'] = 'jpeg|jpg|png';
        $config['max_size'] = 50600;
        // $config['max_width'] = 3075;
        // $config['max_height'] = 3075;

        $this->load->library('upload', $config);
        $description = $this->input->post('description');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'title', 'label' => 'title', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Ttitle  is required',
                        ],
                    ],
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description   is required',
                        ],
                    ],
                    ['field' => 'image', 'label' => 'image', 'rules' => 'callback_file_update',
//                        'errors' => [
//                            'required' => 'image  is required',
//                        ],
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


                    //$file = '';
                    $formArray = [
                        'title' => $this->security->xss_clean($this->input->post('title')),
                        'description' => $this->security->xss_clean($this->input->post('description')),                      
                        'created_by' => $this->input->post('user_id'),
                    ];
                    if (!empty($_FILES['image']['name'])) {
                        if (!$this->upload->do_upload('image')) {
                           $this->response(
                                    ['status' => 'false',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                        'message' => strip_tags($this->upload->display_errors()),
                            ]);
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $file = 'upload/users/' . $this->upload->data('file_name');
                            $formArray['image'] = $file;
                        }
                    }
                    
                   

                    $this->db->update('tbl_blog', $formArray, ['id' => $blog_id]);
                    $effected = $this->db->affected_rows();
                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Blog updated successfully',
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
    
    public function file_update($str) {
        $allowed_mime_type_arr = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png');
        if (!empty($_FILES['image']['name'])) {
            $mime = get_mime_by_extension($_FILES['image']['name']);
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
                if (in_array($mime, $allowed_mime_type_arr)) {
                    return true;
                } else {
                    $this->form_validation->set_message('file_check', 'Please select only jpg/jpeg/png file.');
                    return false;
                }
            } else {
                $this->form_validation->set_message('file_check', 'Please choose a file to upload.');
                return false;
            }
        } 
    }
    
    public function deleteblog_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();
            if (count($check_key) > 0) {
                $data = json_decode(file_get_contents("php://input"));

                if (isset($data->id)) {
                    $this->db->delete('tbl_blog', ['id' => $data->id]);
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                [
                            'status' => 'success',
                            'message' => 'Blog has been deleted'
                                ], REST_Controller::HTTP_OK);
                    } else {
                        $this->response(
                                ['status' => 'false',
                            'message' => 'internal server error',
                                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                    }
                } else {
                    $this->response(
                            [
                        'status' => 'false',
                        'message' => "Blog Id  is required"
                            ], REST_Controller::HTTP_NOT_FOUND);
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
    
    public function skilleducation_get($id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $id != '') {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {

               // $skill = $this->db->get_where('userskill', ['userid' => $id])->row();

                $skillData = $this->Common_model->get_SkillIxperienceIndustry($id);
                
                $education = $this->db->get_where('usereducation', ['userid' => $id])->result();
                $educationData = [];
                if($education){

                foreach ($education as $val) {
                    $educationData[] = [
                        'id' => $val->id,
                        'userid' => $val->userid,
                        'education' => $val->education,
                        'passingyear' => $val->passingyear,
                        'certificate' => $val->certificate ? base_url('upload/users/').$val->certificate : '',
                        'collegename' => $val->collegename,
                    ];
                }
                }else{
                    
                }
                $data =[
                    'skill' =>$skillData,
                    'education' =>$educationData,
                ];
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Record Found!',
                            'data' => $data,
                ]);
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
    
    public function updateskill_put($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');

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
                    ['field' => 'skill_id', 'label' => 'skill_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Skill id is required',
                        ],
                    ],                    
                    ['field' => 'experience', 'label' => 'experience', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Experience is required',
                        ],
                    ],                    
                    ['field' => 'industry', 'label' => 'industry', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Industry is required',
                        ],
                    ],                    
                    ['field' => 'skills', 'label' => 'skills', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Skills is required',
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
                        'experience' => $this->put('experience'),                       
                        'industry' => $this->put('industry'),                       
                        'skills' => $this->put('skills'),                       
                    ];
                    $this->db->update('userskill', $formArray, ['id' => $this->put('skill_id'),'userid' => $user_id]);
//                    echo $this->db->last_query();
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your skill updated succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
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
    
   public function eduactionupdate_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $education_id = $this->input->post('education_id');
        $user_id = $this->input->post('user_id');


        $config['upload_path'] = './upload/users/';
        $config['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
        $config['max_size'] = 50600;
        $config['max_width'] = 3075;
        $config['max_height'] = 3075;

        $this->load->library('upload', $config);
        $description = $this->input->post('description');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
//                    ['field' => 'education_id', 'label' => 'education_id', 'rules' => 'required|numeric',
//                        'errors' => [
//                            'required' => 'Education id  is required',
//                            'numeric' => 'Education id  should be numeric',
//                        ],
//                    ],
                    ['field' => 'education', 'label' => 'education', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Education  is required',
                        ],
                    ],
                    ['field' => 'collegename', 'label' => 'collegename', 'rules' => 'required',
                        'errors' => [
                            'required' => 'College name  is required',
                        ],
                    ],
                    ['field' => 'passingyear', 'label' => 'passingyear', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Passing Year   is required',
                        ],
                    ],
                    ['field' => 'certificate', 'label' => 'certificate', 'rules' => 'callback_certificate_update',
//                        'errors' => [
//                            'required' => 'image  is required',
//                        ],
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
                        'education' => $this->security->xss_clean($this->input->post('education')),
                        'collegename' => $this->security->xss_clean($this->input->post('collegename')),
                        'passingyear' => $this->security->xss_clean($this->input->post('passingyear')),
                    ];
                    if (!empty($_FILES['certificate']['name'])) {
                        if (!$this->upload->do_upload('certificate')) {

                            $this->response(
                                    [
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                        'status' => 'false',
                                        'message' => strip_tags($this->upload->display_errors()),
                            ]);

                            //die;
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $file = 'upload/users/' . $this->upload->data('file_name');
                            $formArray['certificate'] = $file;
                        }
                    }
//                    echo '<pre>';
//                    print_r($formArray);
//                    die;
                    if(key_exists('certificate', $formArray)){
                        $file_data = 'also uplaod fie';
                    } else {
                         $file_data = '';
                    }

                    if (!empty($education_id)) {
                        $this->db->update('usereducation', $formArray, ['id' => $education_id]);
                        $effected = $this->db->affected_rows();
                    } else {
                        $formArray['userid'] = $user_id;
                        $this->db->insert('usereducation', $formArray);
                        $effected = $this->db->insert_id();
                    }
                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                     'message' => 'Education Updated succesfully.'.$file_data,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Data already added!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
    
    public function certificate_update($str) {
        $allowed_mime_type_arr = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png','pdf','doc');
        if (!empty($_FILES['image']['name'])) {
            $mime = get_mime_by_extension($_FILES['image']['name']);
            if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
                if (in_array($mime, $allowed_mime_type_arr)) {
                    return true;
                } else {
                    $this->form_validation->set_message('file_check', 'Please select only jpg/jpeg/png file.');
                    return false;
                }
            } else {
                $this->form_validation->set_message('file_check', 'Please choose a file to upload.');
                return false;
            }
        }
    }

public function updatefeeservice_put() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');

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
                    ['field' => 'servicetype', 'label' => 'servicetype', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service type id is required',                            
                            'numeric' => 'Service type id  should  numeric value',
                        ],
                    ],
                    ['field' => 'fromtime', 'label' => 'fromtime', 'rules' => 'required',
                        'errors' => [
                            'required' => 'From time is required',
                        ],
                    ],
                    ['field' => 'totime', 'label' => 'totime', 'rules' => 'required',
                        'errors' => [
                            'required' => 'To time is required',
                        ],
                    ],
                    ['field' => 'fees', 'label' => 'fees', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Fees is required',
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
                        'servicetype' => $this->put('servicetype'),
                        'fromtime' => $this->put('fromtime'),
                        'totime' => $this->put('totime'),
                        'fees' => $this->put('fees'),
                    ];
                   $data = $this->db->get_where('userservice', ['userid' => $user_id, 'servicetype' => $this->put('servicetype')])->row();
                    if ($data) {

                        $this->db->update('userservice', $formArray, ['userid' => $user_id, 'servicetype' => $this->put('servicetype')]);
                        $affected = $this->db->affected_rows();
                    } else {
                        $formArray['userid'] = $user_id;
                        $this->db->insert('userservice', $formArray);
                        $affected = $this->db->insert_id();
                    }
//                    echo $this->db->last_query();
                    if ($affected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your fee for services updated succesfully!',
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
    
    public function Backgroundverification_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'FirstName', 'label' => 'FirstName', 'rules' => 'required',
                        'errors' => [
                            'required' => 'First Name  is required',
                        ],
                    ],
                    ['field' => 'LastName', 'label' => 'LastName', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Last Name  is required',
                        ],
                    ],
                    ['field' => 'Addr1', 'label' => 'Addr1', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Addr1 is required',
                        ],
                    ],
                    ['field' => 'City', 'label' => 'City', 'rules' => 'required',
                        'errors' => [
                            'required' => 'City is required',
                        ],
                    ],
                    ['field' => 'PostalCode', 'label' => 'PostalCode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Postal Code is required',
                        ],
                    ],
                    ['field' => 'TaxId', 'label' => 'TaxId', 'rules' => 'required',
                        'errors' => [
                            'required' => 'SSN Number Code is required',
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
                    $token = $this->Common_model->Crimknaltoken();
                    if (property_exists(($token), 'access_token')) {
//            echo $token->access_token;

                        $auth_data = [
                            "PersonInfo" =>
                            [
                                "PersonName" => [
                                    "LastName" => $this->input->post('LastName'),
                                    "FirstName" => $this->input->post('FirstName'),
                                    "MiddleName" => $this->input->post('MiddleName'),
                                ],
                                "ContactInfo" => [
                                    "PostAddr" => [
                                        "Addr1" => $this->input->post('Addr1'),
                                        "City" => $this->input->post('City'),
                                        "StateProv" => $this->input->post('StateProv'),
                                        "PostalCode" => $this->input->post('PostalCode'),
                                    ]
                                ],
                                "TINInfo" => [
                                    "TINType" => "SSN",
                                    "TaxId" => $this->input->post('TaxId'),
                                ],
                            ]
                        ];
//            echo json_encode($auth_data,JSON_PRETTY_PRINT); die;
                         $result = $this->Common_model->verification($token, $auth_data);
                        if (json_decode($result)->MsgRsHdr->Status->StatusCode == 0) {
                            $this->db->update('logincr', ['background_verification' => '1'], ['id' => $this->input->post('userid')]);
                        }
                        $this->db->update('logincr', ['background_verification_status' => '1'], ['id' => $this->input->post('userid')]);

                        $this->response(
                                [
                                    'status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Record found!',
                                    'data' => json_decode($result)->MsgRsHdr->Status,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => 'token mismatch!',
                                    'data' => ($token),
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
    
     public function calender_get($id = NULL, $team_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $id != '') {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {
                $user_id = $id;
                if($check_key[0]->switch_account == '1')
                {
                 $team_data = $this->db->get_where('myteams', ['id' => $team_id])->row();
                 $task = $this->db->where(['userid' => $user_id, 'teamid' => $team_id])
                 ->or_where(['userid' => $team_data->user_id])
                 ->get('assigntask')->result();   
                }else if($check_key[0]->switch_account == '2')
                {
                 $team_data = $this->db->get_where('myteams', ['id' => $team_id])->row();
                 $task = $this->db->where(['userid' => $user_id, 'teamid' => $team_id])
                 ->or_where(['userid' => $team_data->agreement_sendby_id])
                 ->get('assigntask')->result();  
                    
                }else{
                $task = $this->db->get_where('assigntask', ['userid' => $id, 'teamid' => $team_id])->result();
                }

                if (!empty($task)) {
                    foreach ($task as $val) {
                        $taskData[] = [
                            'id' => $val->id,
                            'teamid' => $val->teamid,
                            'userid' => $val->userid,
                            'title' => $val->task_name,
                            'taskstatus' => $val->taskstatus ? $val->taskstatus : 'Pending',
                            'comments' => $val->comments ? $val->comments : '',
                            'taskdate' => $val->taskdate ? $val->taskdate : '',
                        ];
                    }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Record Found!',
                                'data' => $taskData,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Record not found!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
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
    
     public function taskdetailsbycalender_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $id = $this->input->post('user_id');
         $teamid = $this->input->post('teamid');
         if ($token != '' && $id != '' && $teamid != '' ) {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {
                $user = $this->db->get_where('logincr', ['id' => $id])->row();
                 //print_r($user); die;
                if (empty($user)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'User not exist!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                   
                } else {
                    if ($user->usertype == '1' || $user->usertype == '1') {
                         $task =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.spid = b.id', 'left')
                         ->where(['userid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')])
                        ->where('b.id IS NOT NULL')->get()->result();
                       // $task = $this->db->get_where('assigntask', ['userid' => $id, 'taskdate' => $this->input->post('taskdate')])->result();
                    } else {
                        $task =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.userid = b.id', 'left')
                        ->where(['spid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')])
                        ->where('b.id IS NOT NULL')->get()->result();
                        //$task = $this->db->get_where('assigntask', ['spid' => $id, 'taskdate' => $this->input->post('taskdate')])->result();
                    }
                    // echo $this->db->last_query(); die;
                    if (!empty($task)) {
                        foreach ($task as $val) {
                             $rel_data = $this->db->get_where('tbl_user_relatives',['id'=>$val->relative_member])->row();
                            $taskData[] = [
                                'id' => $val->id,
                                'title' => $val->title,
                                'task_name' => $val->task_name,
                                'member_name' => $val->member_type,
                                'taskstatus' => $val->taskstatus ? $val->taskstatus : 'Pending',
                                'comments' => $val->comments ? $val->comments : '',
                                'taskdate' => $val->taskdate ? $val->taskdate : '',
                                'start_time' => $val->start_time ? $val->start_time : '',
                                'end_time' => $val->end_time ? $val->end_time : '',
                                'assignto' => $val->user_name ? $val->user_name : '',
                                 'relative' => ($val->relative_member==0)?'Self':$rel_data->relative
                            ];
                        }
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Record Found!',
                                    'data' => $taskData,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Record not found!',
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

   public function socialmedialogin_post() {
       $check_qusans = '';
        $data = $this->input->post();
        $config = [
            ['field' => 'sourcemedia', 'label' => 'sourcemedia', 'rules' => 'required',
                'errors' => [
                    'required' => 'Source media is required',
                ],
            ],
            ['field' => 'tokenid', 'label' => 'tokenid', 'rules' => 'required',
                'errors' => [
                    'required' => 'Fire base token is required',
                ],
            ],
          //  ['field' => 'usertype', 'label' => 'usertype', 'rules' => 'required',
                //'errors' => [
                   // 'required' => 'User type is required',
                //],
            //],
            // ['field' => 'profile_pic', 'label' => 'profile_pic', 'rules' => 'required',
            //     'errors' => [
            //         'required' => 'Profile image is required',
            //     ],
            // ],
            ['field' => 'firstname', 'label' => 'firstname', 'rules' => 'required',
                'errors' => [
                    'required' => 'First name is required',
                ],
            ],
            // ['field' => 'lastname', 'label' => 'lastname', 'rules' => 'required',
            //     'errors' => [
            //         'required' => 'Last name is required',
            //     ],
            // ],
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email name is required',
                    'email' => 'Please enter valid email ID',
                ],
            ],
            ['field' => 'social_id', 'label' => 'social_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Social id is required',
                ],
            ],
            ['field' => 'social_media_type', 'label' => 'social_media_type', 'rules' => 'required',
                'errors' => [
                    'required' => 'Social media type id is required',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'errors' => strip_tags(validation_errors()),
            ]);
        } else {


            $user = $this->db->get_where('logincr', ['email' => $this->input->post('email')])->row();
            if ($user) {
                if ($this->input->post('social_media_type') != $user->social_media_type) {
                    if ($this->input->post('social_media_type') == '0') {
                        $Array = [
                            'google_id' => $this->input->post('social_id'),
                            'social_media_type' => $this->input->post('social_media_type'),
                        ];
                    } else {
                        $Array = [
                            'facebook_id' => $this->input->post('social_id'),
                            'social_media_type' => $this->input->post('social_media_type'),
                        ];
                    }
                    $this->db->update('logincr', $Array, ['id' => $user->id]);
                }
                $user_service = '';
                $xaistatus = '0';
                $industry = '0';

                /*if ($user->usertype != $this->input->post('usertype')) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'You are already registered in diffrent role!',
                    ]);
                } else {*/
                    if ($user->switch_account == '1') {
                        $check_qusans = ($user->usertype=='1')?$this->Common_model->common_getRow('userans', array('userid' => $user->id)):$this->Common_model->common_getRow('tbl_answer', array('user_id' => $user->id));
                         $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $user->id, 'language IS NOT NULL' ,'type'=>'0'])->row();
                        if (!empty($xai)) {
                            $xaistatus = '1';
                            $industry = $xai->industry_id;
                        }
                    } else if ($user->switch_account == '0') {
                        $user_services = $this->db->get_where('userservice', ['userid' => $user->id])->row();
                        if ($user_services) {
                            $user_service = $user_services->servicetype;
                        }
                       // $check_qusans = $this->Common_model->common_getRow('tbl_answer', array('user_id' => $user->id));
                         $check_qusans = ($user->usertype=='1')?$this->Common_model->common_getRow('userans', array('userid' => $user->id)):$this->Common_model->common_getRow('tbl_answer', array('user_id' => $user->id));
                        $xai = $this->db->get_where('tbl_xai_matching', ['user_id' => $user->id, 'language IS NOT NULL' ,'type'=>'0'])->row();
                        if (!empty($xai)) {
                            $xaistatus = '1';
                            $industry = $xai->industry_id;
                        }
                    }
                    if ($check_qusans) {
                        $questionStatus = '1';
                    } else {
                        $questionStatus = '0';
                    }

                    $check_account = $this->Common_model->common_getRow('accountdetails', array('userid' => $user->id));
                    if ($check_account) {
                        if ($check_account->userid) {
                            $accountStatus = '1';
                        } else {
                            $accountStatus = '0';
                        }
                    } else {
                        $accountStatus = '0';
                    }
                    $DataArray = [
                        'id' => $user->id,
                        'token_security' => $user->token_security,
                        'photo' => $user->image != '' ? base_url('upload/users/' . $user->image) : $user->profile_pic,
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
                        'usertype' => $user->usertype,
                        'social_media_type' => $this->input->post('social_media_type'),
                        'user_service' => $user_service,
                        'xaistatus' => $xaistatus,
                        'industry' => $industry,
                        'social_login' => $user->terms != 'Yes' ? false : true,
                        'is_fb' => $this->input->post('social_media_type') == '1' ? true : false,
                        'is_google' => $this->input->post('social_media_type') == '0' ? true : false,
                        'password_change' => $user->password ? true : false,
                        'switch_account' => $user->switch_account,
                    ];
                    if ($this->input->post('social_media_type') == '0') {
                        $DataArray['google_id'] = $this->input->post('social_id');
                    } elseif ($this->input->post('social_media_type') == '1') {
                        $DataArray['facebook_id'] = $this->input->post('social_id');
                    } elseif ($this->input->post('social_media_type') == '2') {
                        $DataArray['twitter_id'] = $this->input->post('social_id');
                    } else {
                        $DataArray['linkedin_id'] = $this->input->post('social_id');
                    }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'You have logged-in successfully!',
                                'data' => $DataArray,
                    ]);
                //}
            } else {
                $fname = '';
                $lname = '';
                $name = explode(' ', $this->input->post('firstname'));
                if (key_exists(1, $name)) {
                    $fname = $name[0];
                    $lname = $name[1];
                }
                if (key_exists(2, $name)) {
                    $fname = $name[0];
                    $lname = $name[2];
                }
                
                $FormArray = [
                    'token_security' => hash('ripemd160', $this->input->post('email')),
                    'sourcemedia' => $this->input->post('sourcemedia'),
                    'tokenid' => $this->input->post('tokenid'),
                    'usertype' => '',
                    'social_media_type' => $this->input->post('social_media_type'),
                    'profile_pic' => $this->input->post('profile_pic'),
                    'firstname' => $lname ? $fname : $this->input->post('firstname'),
                    'lastname' => $lname ? $lname : $this->input->post('lastname'),
                    'email' => $this->input->post('email'),
                    'status' => '1',
                ];
                if ($this->input->post('social_media_type') == '0') {
                    $FormArray['google_id'] = $this->input->post('social_id');
                } elseif ($this->input->post('social_media_type') == '1') {
                    $FormArray['facebook_id'] = $this->input->post('social_id');
                } elseif ($this->input->post('social_media_type') == '2') {
                    $FormArray['twitter_id'] = $this->input->post('social_id');
                } else {
                    $FormArray['linkedin_id'] = $this->input->post('social_id');
                }

                $data = $this->db->insert('logincr', $FormArray);
                $lsat_id = $this->db->insert_id();
                if ($data) {

                    $FormArray['id'] = "$lsat_id";
                    $FormArray['social_media_type'] = $this->input->post('social_media_type');
                    $FormArray['social_login'] = false;
                    $FormArray['social_id'] = $this->input->post('social_id');
                    $FormArray['photo'] = $this->input->post('profile_pic');
                    $FormArray['contact'] = '';
                    $FormArray['ssnnum'] = '';
                    $FormArray['address'] = '';
                    $FormArray['country'] = '';
                    $FormArray['city'] = '';
                    $FormArray['postalcode'] = '';
                    $FormArray['accountstatus'] = '1';
                    $FormArray['questionstatus'] = '0';
                    $FormArray['user_service'] = '';
                    $FormArray['xaistatus'] = '0';
                    $FormArray['industry'] = '0';
                    $FormArray['is_fb'] = $this->input->post('social_media_type') == '1' ? true : false;
                    $FormArray['is_google'] = $this->input->post('social_media_type') == '0' ? true : false;
                    $FormArray['industry'] = '0';
                    $FormArray['password_change'] = false;

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'You have logged-in successfully!',
                                'data' => $FormArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Some thing went wrong please try again!',
                    ]);
                }
            }
        }
    }
    
    public function language_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $data = $this->db->get_where('tbl_language', ['status' => '0'])->result();
                $member = $this->db->select('a.* , b.id as relationship_id , b.relationship')->from('tbl_members as a')->join('tbl_relationship as b','b.id=a.relationship','left')->where(['a.status' => '0', 'a.user_id' => $user_id])->get()->result();
                 $result1 = [];
                    foreach ($member as $val1) {
                        $result1[] = [
                            'id' => $val1->id,
                            'name' => $val1->name,
                            'relationship_id' => $val1->relationship_id,
                            'relationship' => $val1->relationship,
                        ];
                    }
                if ($data) {
                    foreach ($data as $val) {
                        $result[] = [
                            'id' => $val->id,
                            'language' => $val->name,
                        ];
                    }
                    $result3 = [];
                    $paymentmode = $this->db->get_where('tbl_payment_mode', ['status' => '0'])->result();
        if ($paymentmode) {
            foreach ($paymentmode as $val) {
                $result3[] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }
        }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Record found successfully!',
                                'data' => $result,
                                'member' => $result1,
                                'paymentmode' => $result3,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Record not found!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
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

    public function searchprovider_get($user_id = NULL, $team_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $requirement = $this->db->get_where('tbl_team_requirement', ['team_id' => $team_id, 'user_id' => $user_id])->result();

                if ($requirement) {
                    foreach ($requirement as $k => $re) {
                        $industries[] = $re->industry;
                        $all[$re->industry] = [
                            'budget' => $re->budget,
                            'skills' => $re->skills,
                            'experience' => $re->experience,
                        ];
                    }
                    $provider = $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name,b.address,c.name as skillname,d.name as expe,e.name as industry')
                                    ->from('tbl_xai_matching as a')
                                    ->where_in('a.industry_id', $industries)
                                    ->join('logincr as b', 'a.user_id = b.id', 'left')
                                    ->join('tbl_skill as c', 'a.skill_id = c.id', 'left')
                                    ->join('tbl_experience as d', 'a.experience_id = d.id', 'left')
                                    ->join('tbl_industries as e', 'a.industry_id = e.id', 'left')
                                    ->where('b.id is not null')
                                    ->where('a.language is not null')
                                    ->order_by('a.created_at','DESC')
                                    ->get()->result();
                    if($provider){
                        $total = 0;
                        $result = [];
                        foreach ($provider as $val) {
                            $exist = $this->db->order_by('id','DESC')->limit(1)->get_where('scheduleinterview', ['teamid' => $team_id, 'spid' => $val->user_id])->row();
                            $cdata = [];
                            $certificates = $this->db->select('a.*,b.title')->from('tbl_user_certificate as a')
                                    ->join('tbl_certification as b', 'a.certification_id = b.id','left')
                                    ->where(['a.user_id' => $val->user_id])->get()->result();
                            if ($certificates) {
                                foreach ($certificates as $cert) {
                                    $cdata[] = [
                                        'certification_id' => $cert->certification_id,
                                        'title' => $cert->title,
                                        'mime_type' => pathinfo($cert->certificate, PATHINFO_EXTENSION),
                                        'document' => base_url($cert->certificate),
                                    ];
                                }
                            }
                            if ($exist) {
                                //print_r($exist); die;
                                if($exist->status!='Pending'){
                                    $per = 0;
                                    $per += ($this->percent($all[$val->industry_id]['skills'], $val->skill_id) + $this->percent($all[$val->industry_id]['experience'], $val->experience_id) + $this->percent($all[$val->industry_id]['budget'], $val->rate)) / 3;
                                    $total += $per;
                                    $result[] = [
                                        'provider_id' => $val->user_id,
                                        'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                        'name' => $val->user_name,
                                        'fees' => '$' . $val->rate,
                                        'percent' => round($per) . '%',
                                        'industry_id' => $val->industry_id,
                                        'industry' => $val->industry,
                                        'skillname' => $val->skillname ? $val->skillname : '',
                                        'experience' => $val->expe,
                                        'address' => $val->address,
                                        'rating' => $this->Common_model->getrating($val->user_id),
                                        'certificates' =>$cdata
                                    ];
                                
                                    $total = $total / count($provider);
                                    if ($total > 50) {
                                        $ready = '1';
                                    } else {
                                        $ready = '0';
                                    }
                               }
                            }else{
                                $per = 0;
                                $per += ($this->percent($all[$val->industry_id]['skills'], $val->skill_id) + $this->percent($all[$val->industry_id]['experience'], $val->experience_id) + $this->percent($all[$val->industry_id]['budget'], $val->rate)) / 3;
                                $total += $per;
                                $result[] = [
                                    'provider_id' => $val->user_id,
                                    'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                    'name' => $val->user_name,
                                    'fees' => '$' . $val->rate,
                                    'percent' => round($per) . '%',
                                    'industry_id' => $val->industry_id,
                                    'industry' => $val->industry,
                                    'skillname' => $val->skillname ? $val->skillname : '',
                                    'experience' => $val->expe,
                                    'address' => $val->address,
                                    'rating' => $this->Common_model->getrating($val->user_id),
                                    'certificates' =>$cdata
                                ];
                            
                                $total = $total / count($provider);
                                if ($total > 50) {
                                    $ready = '1';
                                } else {
                                    $ready = '0';
                                }
                            }
                        }
                        
                        if ($result) {
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Record found successfully!',
                                        'requirement_status' => $ready,
                                        'data' => $result,
                            ]);
                        } else {
                            $this->response(
                                    ['status' => 'false',
                                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                        'message' => 'Record not found!',
                                        'data' => [],
                            ]);
                        }
                    }else{
                    $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                    'message' => 'Record not found!',
                                    'data' => [],
                        ]); 
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team requirement not found!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token!',
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

    public function percent($first = NULL, $second = NULL) {
        $oldFigure = $first == 0 ? 1 : $first;
        $newFigure = $first == 0 ? 1 :$second;
        if ($oldFigure < $newFigure) {
            $percentChange = (1 - $oldFigure / $newFigure) * 100;
        } else {
            $percentChange = (1 - $newFigure / $oldFigure) * 100;
        }

        return (100 - round($percentChange));
    }
    public function rateassessment_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = $this->input->post();
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'User id is required',
                ],
            ],
            ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Team id is required',
                ],
            ],
            ['field' => 'industry', 'label' => 'industry', 'rules' => 'required',
                'errors' => [
                    'required' => 'Industry id is required',
                ],
            ],
            ['field' => 'experience', 'label' => 'experience', 'rules' => 'required',
                'errors' => [
                    'required' => 'Experience id is required',
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
            $auth = $this->authentication($this->input->post('user_id'), $token);
            $industry = $this->input->post('industry');
            $experience = $this->input->post('experience');

            $team = $this->db->select('a.*,b.id as zipid,c.assesment')->from('myteams as a')
                            ->join('tbl_zipcode as b', 'a.zipcode = b.zip', 'left')
                            ->join('tbl_rate_assesment as c', 'c.location_id = b.id', 'left')
                            ->where('a.id', $this->input->post('team_id'))->get()->row();
            if ($team) {
                $assesment = (json_decode($team->assesment));
                if ($assesment) {
                    $min = 0;
                    $max = 0;
                    $response = [];
                    foreach ($assesment as $val) {
                        if ($val->industry == $industry) {
                            if ($experience != 1) {
                                $min = ($val->min * ($experience * $experience) / 100);
                                $max = ($val->max * ($experience * $experience) / 100);
                            }

                            $response = [
                                'min' => '$' . (number_format((float) $val->min + $min, 2, '.', '')),
                                'max' => '$' . (number_format((float) $val->max + $max, 2, '.', '')),
                            ];
                        }
                    }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Record found successfully!',
                                'data' => $response,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Assessment rate not found on this zipcode',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Team does not exist',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        }
    }
    
    public function matchingsurvey_get($user_id = NULL, $provider_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $provider_id = $this->uri->segment(4);

        $auth = $this->authentication($user_id, $token);
        if ($provider_id) {
            $industry = $this->db->get_where('tbl_xai_matching', ['user_id' => $provider_id])->row()->industry_id;
            $question = $this->db->get_where('tbl_serve_question', ['industry_id' => $industry])->result();
            if ($question) {
                foreach ($question as $val) {
                    $result[] = [
                        'id' => $val->id,
                        'question' => $val->question,
                        'options' => json_decode($val->options),
                    ];
                }
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Record found successfully!',
                            'data' => $result,
                ]);
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Record not found!',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Provider id is required!',
                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
            ]);
        }
    }

    public function matchingsurveyansewr_put($user_id = NULL, $provider_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->put('user_id');
        $auth = $this->authentication($user_id, $token);
        $data = $this->put();

        $this->form_validation->set_data($this->put());
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should  numeric value',
                ],
            ],
            ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Team id is required',
                ],
            ],
            ['field' => 'survey_for', 'label' => 'survey_for', 'rules' => 'required',
                'errors' => [
                    'required' => 'Survey id is required',
                ],
            ],
            ['field' => 'industry_id', 'label' => 'industry_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Industry is required',
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
            $question_dta = $this->put('question_dta');
            if (!empty($question_dta)) {

                $formArray = [
                    'user_id' => $this->put('user_id'),
                    'team_id' => $this->put('team_id'),
                    'industry_id' => $this->put('industry_id'),
                    'survey_for' => $this->put('survey_for'),
                ];
                foreach ($question_dta as $k => $q) {
                    $finalArray[] = array_merge($formArray, $q);
                }

                $this->db->trans_begin();
                $this->db->where(['user_id' => $this->put('user_id'), 'survey_type' => '1' ,'team_id' => $this->put('team_id'), 'industry_id' => $this->put('industry_id')])->delete('tbl_survey_answer');

                $this->db->insert_batch('tbl_survey_answer', $finalArray);

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Some thing went wrong please try after some time!',
                    ]);
                } else {
                    $this->db->trans_commit();
                    $total_survey = $this->db->group_by('team_id')->get_where('tbl_survey_answer', ['user_id' => $this->put('user_id')])->num_rows();
                    $total_points = $this->db->get_where('tbl_survey_answer', ['user_id' => $this->put('user_id')])->num_rows();
                    $points =  count($question_dta);
                    $data = [
                        'total_survey' => "$total_survey",
                        'total_points' => "$total_points",
                        'current_points' => "$points",
                    ];
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Your survey has been done successfully!',
                                'data' => $data,
                    ]);
                }
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'question and answer field are required!',
                ]);
            }
        }
    }
    
    public function sharedoc_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $auth = $this->authentication($user_id, $token);
        $data = $this->input->post();
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'User id is required',
                ],
            ],
            ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Interview id is required',
                ],
            ],
            ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                'errors' => [
                    'required' => 'Comments is required',
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
                'interview_id' => $this->input->post('interview_id'),
                'comments' => $this->input->post('comments'),
            ];
            $fileArray = [];
            if (!empty($_FILES['images']['name'][0])) {
                $images = [];
                $error = [];
                $files = $_FILES['images'];
                foreach ($files['name'] as $key => $image) {
                    $_FILES['images[]']['name'] = $files['name'][$key];
                    $_FILES['images[]']['type'] = $files['type'][$key];
                    $_FILES['images[]']['tmp_name'] = $files['tmp_name'][$key];
                    $_FILES['images[]']['error'] = $files['error'][$key];
                    $_FILES['images[]']['size'] = $files['size'][$key];

                    $file = $files['name'][$key];
                    $name = 'images[]';
                    $path = 'sharedoc';
                    $type = 'jpeg|jpg|png|pdf|zip|doc|docx';
                    $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                    if (key_exists('error', $file_data)) {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => $file_data['error'],
                        ]);
                    } else {
                        $fileArray[] = $file_data['file'];
                    }
                }
            }

            $this->db->trans_begin();
            $this->db->insert('tbl_shared_details', $formArray);
            $lst = $this->db->insert_id();
            if ($fileArray) {
                foreach ($fileArray as $k => $v) {
                    $fileFinal[] = ['shared_id' => $lst, 'files' => $v];
                }
                $this->db->insert_batch('tbl_shared_doc', $fileFinal);
            }
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Some thing went wrong please try after some time!',
                ]);
            } else {
                $this->db->trans_commit();
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Documents shared has been done successfully!',
                ]);
            }
        }
    }

    public function getshareddoc_get($user_id = NULL, $interview_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $interview_id = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);
        
        $query = $this->db->select('a.*,c.image as profile_image,CONCAT(c.firstname, " ", c.lastname) AS user_name')
                            ->from('tbl_shared_details as a')
                            ->join('scheduleinterview as b', 'a.interview_id = b.id', 'left')
                            ->join('logincr as c', 'c.id = b.userid', 'left')
                            ->where(['a.interview_id' => $interview_id])
                            ->get();
                    $result = $query->result();
        if ($result) {
            foreach ($result as $val) {
                $ids[] = $val->id;
                $data['comments'][] = [
                    'id' => $val->id,
                    'user_name' => $val->user_name,
                    'profile_image' => $val->profile_image ?  base_url($val->profile_image) : base_url('upload/users/photo.png'),
                    'comments' => $val->comments,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }
            $file = $this->db->where_in('shared_id', $ids)->get('tbl_shared_doc')->result();
            foreach ($file as $f) {
                $data['files'][] = [
                    'link' => base_url($f->files)
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found successfully!',
                        'data' => $data,
            ]);
        } else {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);
        }
    }

     public function paymentmode_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $auth = $this->authentication($user_id, $token);

        $result = $this->db->get_where('tbl_payment_mode', ['status' => '0'])->result();
        if ($result) {
            foreach ($result as $val) {               
                $data[] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found successfully!',
                        'data' => $data,
            ]);
        } else {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);
        }
    }
    
    public function requiredoc_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $auth = $this->authentication($user_id, $token);

        $result = $this->db->get_where('tbl_required_doc', ['status' => '0'])->result();
        if ($result) {
            foreach ($result as $val) {               
                $data[] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found successfully!',
                        'data' => $data,
            ]);
        } else {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);
        }
    }
    
    public function requiredocandpaymentmode_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $auth = $this->authentication($user_id, $token);

        $data['requireddoc'] = [];
        $data['paymentmode'] = [];
        $data['benifits'] = [];

        $requireddoc = $this->db->get_where('tbl_required_doc', ['status' => '0'])->result();
        if ($requireddoc) {
            foreach ($requireddoc as $val) {
                $data['requireddoc'][] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }
        }

        $paymentmode = $this->db->get_where('tbl_payment_mode', ['status' => '0'])->result();
        if ($paymentmode) {
            foreach ($paymentmode as $val) {
                $data['paymentmode'][] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }
        }
        $benifits = $this->db->get_where('tbl_benefits', ['status' => '0'])->result();
        if ($benifits) {
            foreach ($benifits as $val) {
                $data['benifits'][] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'created_at' => date('d-m-Y', strtotime($val->created_at)),
                ];
            }
        }

        if ($data) {
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found successfully!',
                        'data' => $data,
            ]);
        } else {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
                        'data' => $data,
            ]);
        }
    }

    public function generateofferletter_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $auth = $this->authentication($user_id, $token);
        $data = $this->input->post();
        $config = [
            ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'Interview id  should be numeric',
                ],
            ],
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id is required',
                    'numeric' => 'User id  should be numeric',
                ],
            ],
            ['field' => 'provider_id', 'label' => 'provider_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Provider id is required',
                    'numeric' => 'Provider id  should be numeric',
                ],
            ],
            ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Team id is required',
                    'numeric' => 'Team id  should be numeric',
                ],
            ],
            ['field' => 'pay_rate', 'label' => 'pay_rate', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Pay rate is required',
                    'numeric' => 'Pay rate should be numeric',
                ],
            ],
            ['field' => 'joining_date', 'label' => 'joining_date', 'rules' => 'required',
                'errors' => [
                    'required' => 'Joining date is required',
                ],
            ],
            ['field' => 'payment_method', 'label' => 'payment_method', 'rules' => 'required',
                'errors' => [
                    'required' => 'Payment Method is required',
                ],
            ],
            ['field' => 'required_doc', 'label' => 'required_doc', 'rules' => 'required',
                'errors' => [
                    'required' => 'Required doc is required',
                ],
            ],
             ['field' => 'emp_type', 'label' => 'emp_type', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Employment type is required',
                    'numeric' => 'Employment type should be numeric',
                ],
            ],
            // ['field' => 'benifits', 'label' => 'benifits', 'rules' => 'required',
            //     'errors' => [
            //         'required' => 'benifits is required',
            //     ],
            // ],
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
                'interview_id' => $this->input->post('interview_id'),
                'user_id' => $this->input->post('user_id'),
                'provider_id' => $this->input->post('provider_id'),
                'team_id' => $this->input->post('team_id'),
                'pay_rate' => $this->input->post('pay_rate'),
                'joining_date' => $this->input->post('joining_date'),
                'payment_method' => $this->input->post('payment_method'),
                'required_doc' => $this->input->post('required_doc'),
                'benifits' => $this->input->post('benifits'),
                'encrypt_key' => gerandomstring(30),
                'emp_type' => $this->input->post('emp_type'),
            ];
            $fileArray = [];
            if (!empty($_FILES['user_signature']['name'])) {
                $file = $_FILES['user_signature']['name'];
                $name = 'user_signature';
                $path = 'offers';
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
                    $formArray['user_signature'] = $file_data['file'];
                }
            }
             //offer letter logo
             $this->load->library('upload');
            if (!empty($_FILES['offer_logo']['name'])) {
                $config1['upload_path'] = './upload/offer_logo/';
                $config1['allowed_types'] = 'gif|jpg|png|jpeg';
                
                $this->upload->initialize($config1);
			
			if (!$this->upload->do_upload('offer_logo')){
			    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $this->upload->display_errors(),
                    ]);
			}
			else{
				$formArray['offer_logo'] =  'upload/offer_logo/'.$this->upload->data('file_name');
			}
            }

            $exist = $this->db->get_where('tbl_offer_letter', ['interview_id' => $this->input->post('interview_id')])->row();
            if($this->input->post('interview_id')==0)
            {
                 $exist_new = $this->db->get_where('tbl_offer_letter', ['interview_id' => $this->input->post('interview_id') , 'team_id'=>$this->input->post('team_id') , 'provider_id'=>$this->input->post('provider_id')])->row();
                 if($exist_new)
                 {
                      $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Offer letter already send!',
                ]);
                 }else{
                 $this->db->insert('tbl_offer_letter', $formArray);
                $effected = $this->db->insert_id();
                $res = $this->db->get_where('tbl_offer_letter', ['id' => $effected])->row();
                 }
            }else{
            if ($exist) {
                $this->db->update('tbl_offer_letter', $formArray, ['id' => $exist->id]);
                $res = $this->db->get_where('tbl_offer_letter', ['id' => $exist->id])->row();
                $effected = $this->db->affected_rows();
            } else {
                $this->db->insert('tbl_offer_letter', $formArray);
                $effected = $this->db->insert_id();
                $res = $this->db->get_where('tbl_offer_letter', ['id' => $effected])->row();
            }
            }
            
           $payment_mode = [];
            $payment_method = $this->db->get_where('tbl_payment_mode', ['id' => $res->payment_method])->row();
            if ($payment_method) {
                $payment_mode = [
                    'id' => $payment_method->id,
                    'title' => $payment_method->title,
                ];
            }

            $required_docs = [];
            $required_doc = $this->db->select('*')->from('tbl_required_doc')->where_in('id', explode(',', $res->required_doc))->get()->result();
            if ($required_doc) {
                foreach ($required_doc as $val) {
                    $required_docs[] = [
                        'id' => $val->id,
                        'title' => $val->title,
                    ];
                }
            }

            $benefits = [];
            if ($res->benifits) {
                $benefit = $this->db->select('*')->from('tbl_benefits')->where_in('id', explode(',', $res->benifits))->get()->result();
                if ($benefit) {
                    foreach ($benefit as $val) {
                        $benefits[] = [
                            'id' => $val->id,
                            'title' => $val->title,
                        ];
                    }
                }
            }
            
                if($res->emp_type==1)
                {
                    $tt = 'Full Time';
                }else if($res->emp_type==2)
                {
                    $tt = 'Contractor';
                }else{
                    $tt=  '';
                }
            $data = [
                'offer_id' => $res->id,
                'interview_id' => $res->interview_id,
                'user_id' => $res->user_id,
                'provider_id' => $res->provider_id,
                'team_id' => $res->team_id,
                'pay_rate' => $res->pay_rate,
                'joining_date' => $res->joining_date,
                'payment_method' => $res->payment_method,
                'user_signature' => $res->user_signature ? base_url($res->user_signature) : '',
                'preview_url' => base_url('myteam/previewofferletter/' . $res->encrypt_key),
                'pdf_url' => base_url('myteam/downloadofferletter/' . $res->encrypt_key),
                'offer_logo' => base_url($res->offer_logo),
                'payment_mode' => $payment_mode,
                'required_docs' => $required_docs,
                'benefit' => $benefits,
                'emp_type' => $res->emp_type,
                'emp_title' => $tt,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if ($effected > 0) {

                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Offer letter genarate successfully!',
                            'data' => $data,
                ]);
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Some thing went wrong please try after some time!',
                ]);
            }
        }
    }
    
    public function sentofferletter_post() {
        
           $token = $this->input->get_request_header('Secret-Key');
           $offer_id = $this->input->post('offer_id');
           $user_id = $this->input->post('user_id');
           $auth = $this->authentication($user_id, $token);
           
           $result = $this->db->update('tbl_offer_letter',['status'=>'1'], ['id' => $offer_id]);
           $effected = $this->db->affected_rows();
           if ($effected > 0) {
        
             $user = $this->db->select('a.*,b.email,CONCAT(b.firstname, " ", b.lastname) AS user_name')
             ->from('tbl_offer_letter as a')
             ->join('logincr as b', 'b.id = a.provider_id', 'left')
             ->where(['a.id' => $offer_id])->get()->row();
        
             $mailArray = [
                'url' => base_url('myteam/previewofferletter/'.  $user->encrypt_key),
                'name' => $user->user_name,
            ];
           
            $html = $this->load->view('email/sendofferletter', $mailArray, TRUE);
            $res = $this->Mail->sendmail($user->email, 'Offer Letter Generation!', $html);
        
            if ($res) {
        
               $this->response(
                ['status' => 'success',
                'responsecode' => REST_Controller::HTTP_OK,
                'message' => 'Offer letter genarate successfully!',
        
            ]);
        
           } else {
            $this->response(
                [
                    'status' => 'success',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'message' => 'Offer letter genarate successfully, But mail not sent due to The email server not working at this moment!',             
                ]);
        }
        
        
        } else {
            $this->response(
                ['status' => 'false',
                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                'message' => 'Offer letter already sended!',
            ]);
        }
    }
    //get before after image data
    //get task image and videos
    public function taskImageVideo_post() {
          $result = [];
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $team_id = $this->input->post('teamid');
        $provider_id = $this->input->post('spid');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            


            if ($check_key['status'] == 'true') {

                $data = $this->input->post();
                $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Provider id  is required',
                            'numeric' => 'Provider id  should be numeric',
                        ],
                    ],
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                   
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task id  is required',
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
                     $teamnameexist = $this->db->get_where('myteams', ['id' => $team_id])->row();
                     $switch = $check_key['data']->switch_account;
                     if($switch == '1')
                     {
                         $sc_id = $teamnameexist->user_id;
                          $task = $this->db->select('a.*')
                                     ->from('tbl_task_status as a')
                                     ->where(['a.sp_id' => $provider_id, 'a.team_id' => $team_id, 'task_id' => $this->security->xss_clean($this->input->post('taskid')) , 'user_id' => $user_id])
                                     ->or_where(['a.user_id'=>$sc_id])
                                     ->order_by('a.created_at','DESC')
                                     ->get()
                                     ->result();
                     }else if($switch == '2')
                     {
                          $uid = $teamnameexist->agreement_sendby_id;
                          $task = $this->db->select('a.*')
                                     ->from('tbl_task_status as a')
                                     ->where(['a.sp_id' => $provider_id, 'a.team_id' => $team_id, 'task_id' => $this->security->xss_clean($this->input->post('taskid')) , 'user_id' => $user_id])
                                     ->or_where(['a.user_id'=>$uid])
                                     ->order_by('a.created_at','DESC')
                                     ->get()
                                     ->result();
                     }else{
                    $task = $this->db->select('a.*')
                                     ->from('tbl_task_status as a')
                                     ->where(['a.sp_id' => $provider_id, 'a.team_id' => $team_id, 'task_id' => $this->security->xss_clean($this->input->post('taskid')) , 'user_id' => $user_id])
                                     ->order_by('a.created_at','DESC')
                                     ->get()
                                     ->result();
                     }
                                   
                                   

                    if (!empty($task)) {
                        foreach($task as $tt)
                        {
                            

                           $image_data = $this->db->select('image_path , type')->get_where('tbl_task_status_image',['task_id'=>$this->input->post('taskid')])->result();
              $r = [];
              $r1 = [];
                if (!empty($image_data)) {

                    foreach ($image_data as $val) {
                        if ($val->type == '0') {
                            $r[] = [
                                'images' => base_url('upload/tasks/image/' . $val->image_path),
                            ];
                        } else {
                            $r1[] = [
                                'images' => base_url('upload/tasks/image1/' . $val->image_path),
                            ];
                        }
                    }
                     
                              

                           /* $result[] = [
                                'id' => $tt->id,
                                'task_id' => $tt->task_id,
                                'user_id' => $tt->user_id,
                                'team_id' => $tt->team_id,
                                'before_video' => ($tt->video_path!=NULL)?base_url('upload/tasks/video/'.$tt->video_path):'',
                                'after_video' => ($tt->video_path1!=NULL)?base_url('upload/tasks/video1/'.$tt->video_path1):'',
                                'before_image' => $r,
                                'after_image' => $r1,
                              


                            ];*/
                            
                }
                            $result[] = [
                                'id' => $tt->id,
                                'task_id' => $tt->task_id,
                                'user_id' => $tt->user_id,
                                'team_id' => $tt->team_id,
                                'before_video' => ($tt->video_path!=NULL)?base_url('upload/tasks/video/'.$tt->video_path):'',
                                'after_video' => ($tt->video_path1!=NULL)?base_url('upload/tasks/video1/'.$tt->video_path1):'',
                                'before_image' => ($r)?$r:[],
                                'after_image' => ($r1)?$r1:[],
                              


                            ];

                        }

                            $this->response(
                            [
                            'status' => 'ok',
                            'message' => 'Data found!',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'data' => $result
                            ]);
                        } else {
                         $this->response(
                            [
                            'status' => 'false',
                            'message' => 'No Data found!',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                            'data' => []
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
     /*------------------------ For invoice------------------------*/
    //get pending invoice data
    public function pendingInvoice_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->get_request_header('userid');//user id
        if ($token != '' && $userid != '') {

            $check_key = $this->checktoken($token, $userid);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.*, CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid ,  b.image , b.profile_pic , b.id as spid")
                        ->from('tbl_invoice as a')
                        ->join('logincr as b', 'b.id = a.spid', 'left')
                        ->join('myteams as c', 'c.id = a.team_id', 'left')
                        ->where(['a.paid_status'=>'0' , 'a.user_id'=>$userid])
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
            
                    $dataArray[] = [
                        'id' => $val->id,
                        'spid' => $val->spid,
                        'user_id' => $userid,
                        'teamid' => $val->teamid,
                        'taskid' => $val->task_id,
                        'username' => $val->name,
                         'userimage' => $val->image ? base_url($val->image) : $val->profile_pic,
                        'teamimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        'teamname' => $val->teamname,
                        'status' => ($val->paid_status=='0')?'Pending':'',
                        'total_work_hours' =>$val->total_work_hours,
                        'rate' =>$val->rate,
                        'amount' =>$val->amount,
                        'create_date' => date('d-m-Y', strtotime($val->created_at)),
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data  found!',
                                'data' => $dataArray,
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
     //get invoice details by inv id
    public function invoiceDetails_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->get_request_header('userid');//user id
        $inv_id = $this->uri->segment(3);//user id
        if ($token != '' && $userid != '') {

            $check_key = $this->checktoken($token, $userid);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
              $val = $this->db->select('a.*, b.task_name')
                               ->from('tbl_invoice as a')
                               ->join('assigntask as b' ,'b.id = a.task_id','left')
                               ->where(['a.id'=>$inv_id])
                               ->get()
                               ->row();
                
                if (!empty($val)) {
                      $job_type = $this->db->select('a.name')->from('tbl_industries as a')->join('tbl_xai_matching as b','b.industry_id=a.id','left')->where('b.user_id',$val->spid)->get()->row();
                    $dataArray = [
                        'id' => $val->id,
                        'inv_no' => $val->invoice_id,
                        'user_id' => $val->user_id,
                        'teamid' => $val->team_id,
                        'taskid' => $val->task_id,
                         'task_name' => $val->task_name,
                         'job_type' => $job_type->name,
                        'leave_balance' => $val->leave_balance,
                        'leave_taken' => $val->leave_taken,
                        'total_work_hours' =>$val->total_work_hours,
                        'rate' =>$val->rate,
                        'amount' =>$val->amount,
                        'create_date' => date('d-m-Y', strtotime($val->created_at)),
                    ];
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data  found!',
                                'data' => $dataArray,
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
 //get paid invoice data
    public function paidInvoice_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->get_request_header('userid');//user id
        if ($token != '' && $userid != '') {

            $check_key = $this->checktoken($token, $userid);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.*, CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid ,  b.image , b.profile_pic , b.id as spid")
                        ->from('tbl_invoice as a')
                        ->join('logincr as b', 'b.id = a.spid', 'left')
                        ->join('myteams as c', 'c.id = a.team_id', 'left')
                        ->where(['a.paid_status'=>'1' , 'a.user_id'=>$userid])
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
            
                    $dataArray[] = [
                        'id' => $val->id,
                        'spid' => $val->spid,
                        'user_id' => $userid,
                        'teamid' => $val->teamid,
                        'taskid' => $val->task_id,
                        'username' => $val->name,
                         'userimage' => $val->image ? base_url($val->image) : $val->profile_pic,
                        'teamimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        'teamname' => $val->teamname,
                        'status' => ($val->paid_status=='1')?'Paid':'',
                        'total_work_hours' =>$val->total_work_hours,
                        'rate' =>$val->rate,
                        'amount' =>$val->amount,
                        'create_date' => date('d-m-Y', strtotime($val->created_at)),
                         'pdf_view' => base_url('myteam/downloadInvoice/'.$val->id),
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data  found!',
                                'data' => $dataArray,
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
    
    public function previewInvoice_get($invoice_id = NULL) {     

        $result = $this->db->select("a.*, CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid ,  b.image , b.profile_pic")
        ->from('tbl_invoice as a')
        ->join('logincr as b', 'b.id = a.user_id', 'left')
        ->join('myteams as c', 'c.id = a.team_id', 'left')
        ->where(['a.id' => $this->uri->segment(3)])->get()->row();
        if( $result){
          $sp_detail = $this->db->get_where('logincr',['id'=>$result->spid])->row();
         $data['invoice'] = $result;
         $data['sp'] = $sp_detail;
        $this->load->view('invoice/invoice',$data); 
    }else{
        echo "<h2>Data not found!</h2>";
    }
}
//get all earning data
    public function earningData_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $spid = $this->input->get_request_header('userid');//user id
        if ($token != '' && $spid != '') {

            $check_key = $this->checktoken($token, $spid);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $total_work_hours = $this->db->select('sum(hours) as total_work_hours')->group_by('spid')->get_where('tbl_ewallet',['spid'=>$spid])->row('total_work_hours');
                  $total_amount = $this->db->select('sum(amount) as total_amount')->group_by('spid')->get_where('tbl_ewallet',['spid'=>$spid])->row('total_amount');
                $query = $this->db->select("a.*, CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid ,  b.image , b.profile_pic , b.id as spid")
                        ->from('tbl_invoice as a')
                        ->join('logincr as b', 'b.id = a.spid', 'left')
                        ->join('myteams as c', 'c.id = a.team_id', 'left')
                        ->where(['a.paid_status'=>'1' , 'a.spid'=>$spid])
                        ->get();
                $result = $query->result();
                 $myData = [
                    'total_work_hours'=>($total_work_hours)?$total_work_hours:'0',
                    'total_amount'=>($total_amount)?$total_amount:'0',
                ];
                foreach ($result as $val) {
            
                    $dataArray[] = [
                        'id' => $val->id,
                        'spid' => $spid,
                        'user_id' => $val->user_id,
                        'teamid' => $val->teamid,
                        //'taskid' => $val->task_id,
                        //'username' => $val->name,
                        // 'userimage' => $val->image ? base_url($val->image) : $val->profile_pic,
                        'teamname' => $val->teamname,
                        'teamimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        
                       // 'status' => ($val->paid_status=='1')?'Paid':'',
                        'spent_hours' =>$val->total_work_hours,
                        //'rate' =>$val->rate,
                        'earning' =>$val->amount,
                        'create_date' => date('d-m-Y', strtotime($val->created_at)),
                        //'pdf_view' => base_url('myteam/downloadInvoice'),
                    ];
                }

                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data  found!',
                                'data' => $dataArray,
                                'totalData' => $myData
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
    //add customer policies
    public function addPolicies_post() {


        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $question = $this->input->post('question');
            $description = $this->input->post('description');

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'User id  is required',
                        ],
                    ],
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Doctor id  is required',
                        ],
                    ],
                    ['field' => 'question', 'label' => 'question', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question is required',
                        ],
                    ],
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description  is required',
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
                        'team_id' => $team_id,
                        'user_id' => $user_id,
                        'question' => $question,
                        'description' => $description,
                    ];
                    $result = $this->db->insert('tbl_customer_policy', $formArray);
                    $lid = $this->db->insert_id();
                    if ($lid > 0) {
                        $data = [
                            'policy_id' => $lid
                        ];

                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy Added Successfully!',
                                    'data' => $data,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Service Requested Failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
     //update policy
     public function myPolicyUpdate_put()
     {
          $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');
       
       
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
                    ['field' => 'id', 'label' => 'id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Policy id is required',
                        ],
                    ],                    
                    ['field' => 'question', 'label' => 'question', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question is required',
                        ],
                    ],                    
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description is required',
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
                        'question' => $this->put('question'),                       
                        'description' => $this->put('description'),                       
                      
                    ];
                   // $this->db->update('tbl_customer_policy', $formArray, ['id' => $this->put('id')]);
//                    echo $this->db->last_query();
                    if ($this->db->update('tbl_customer_policy', $formArray, ['id' => $this->put('id')])) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy updated succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Policy already added!',
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
     public function updatePolicy1_put() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');
       
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
                    ['field' => 'id', 'label' => 'id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Policy id is required',
                        ],
                    ],                    
                    ['field' => 'question', 'label' => 'question', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question is required',
                        ],
                    ],                    
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description is required',
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
                        'question' => $this->put('question'),                       
                        'description' => $this->put('description'),                       
                      
                    ];
                    $this->db->update('tbl_customer_policy', $formArray, ['id' => $this->put('id')]);
//                    echo $this->db->last_query();
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy updated succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Policy already added!',
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
     //delete ploicy
     public function deletePolicy_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"));
        $user_id = $data->user_id;

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                    if ( $this->db->delete('tbl_customer_policy', ['id' => $data->id])) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy deleted succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Policy already deleted!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
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
    //add policies second step
    public function addPoliciesDuration_post() {


        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $title = $this->input->post('title');
            $hours = $this->input->post('hours');
            $priority = $this->input->post('priority');


            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'User id  is required',
                        ],
                    ],
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Team id  is required',
                        ],
                    ],
                    ['field' => 'title', 'label' => 'title', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Title is required',
                        ],
                    ],
                    ['field' => 'hours', 'label' => 'hours', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Hours  is required',
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
                        'team_id' => $team_id,
                        'user_id' => $user_id,
                        'title' => $title,
                        'hours' => $hours,
                        'priority' => $priority
                    ];
                    $result = $this->db->insert('tbl_customer_policy_duration', $formArray);
                    $lid = $this->db->insert_id();
                    if ($lid > 0) {
                        $data = [
                            'duration_id' => $lid
                        ];

                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy Duration Added Successfully!',
                                    'data' => $data,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Service Requested Failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
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
     //update policy duration
     public function updatePolicyDuration_put() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');

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
                    ['field' => 'id', 'label' => 'id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Policy id is required',
                        ],
                    ],                    
                    ['field' => 'title', 'label' => 'title', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question is required',
                        ],
                    ],                    
                    ['field' => 'hours', 'label' => 'hours', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Hours is required',
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
                        'title' => $this->put('title'),                       
                        'hours' => $this->put('hours'),                       
                      
                    ];
                 //   $this->db->update('tbl_customer_policy_duration', $formArray, ['id' => $this->put('id')]);
//                    echo $this->db->last_query();
                    if ( $this->db->update('tbl_customer_policy_duration', $formArray, ['id' => $this->put('id')])) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy duration updated succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Policy duration already added!',
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
    //delete ploicy duration
     public function deletePolicyDuration_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"));
        $user_id = $data->user_id;

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                    if ( $this->db->delete('tbl_customer_policy_duration', ['id' => $data->id])) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Policy duration deleted succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Policy duration already deleted!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
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
    // get timesheet history
            public function getTimesheetHistory_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $user_id = $this->uri->segment(3);
            $check_key = $this->authentication($user_id , $tokenid);
            $check = $this->db->select('a.id as timesheet_id , a.user_status , b.teamimage , b.teamname,b.id as teamid , CONCAT(c.firstname, " "  , c.lastname) as spname , d.task_name , d.title , d.id as taskid')
                              ->from('tbl_final_timesheet as a')
                              ->join('myteams as b' , 'b.id = a.team_id','left')
                              ->join('logincr as c' , 'c.id = a.spid','left')
                              ->join('assigntask as d','d.id = a.task_id','left')
                              ->where(['b.user_id'=>$user_id])
                              ->order_by('a.created_at','DESC')
                              ->get()
                              ->result();                          
            if($check)
            {
           
           foreach($check as $cc)
           {
            if($cc->user_status=='0')
            {
                $status = 'Pending';
            }else if($cc->user_status=='1')
            {
                $status = 'Accept';
            }else{
                $status  = 'Reject';
            }
            $myData[] = [
                'id' => $cc->timesheet_id,
                'timesheet_id' => '000000'.$cc->timesheet_id,
                'heading' => $cc->teamname.'('.$cc->title.')',
                'teamid' => $cc->teamid,
                'teamname' => $cc->teamname,
                'teamimage' => $cc->teamimage ? base_url($cc->teamimage) : base_url('upload/users/photo.png'),
                'taskid' => $cc->taskid,
                'task_title' => $cc->title,
                'spname' => $cc->spname,
                'status' => $status,

            ];
           }
            
            $this->response(
            ['status' => 'success',
            'responsecode' => REST_Controller::HTTP_OK,
            'message' => 'Data found successfully',
            'data' => $myData,
            ]);  
            }else{
            $this->response(
            [
            'status' => 'false',
            'message' => 'No data found!',
            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
            ]); 
            }

            }
            // get timesheet history details
            public function getTimesheetDetails_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $user_id = $this->uri->segment(3);
            $id = $this->uri->segment(4);
            $check_key = $this->authentication($user_id , $tokenid);
            $cc = $this->db->select('a.* , b.teamimage , b.teamname,b.id as teamid , CONCAT(c.firstname, " "  , c.lastname) as spname , c.contact , d.task_name , d.title , d.id as taskid')
                              ->from('tbl_final_timesheet as a')
                              ->join('myteams as b' , 'b.id = a.team_id','left')
                              ->join('logincr as c' , 'c.id = a.spid','left')
                              ->join('assigntask as d','d.id = a.task_id','left')
                              ->where(['a.id'=>$id])
                              ->get()
                              ->row();                          
            if($cc)
            {
           
          
            if($cc->status=='0')
            {
                $status = 'Pending';
            }else if($cc->status=='1')
            {
                $status = 'Accept';
            }else{
                $status  = 'Reject';
            }
            //$sunday = $monday = $tuesday = $wednesday = $thursday = $friday = $saturday = '';
           // $get_all_time = $this->db->select('created_at as date , task_done')->from('tbl_final_timesheet')->where(['spid'=>$cc->spid , 'team_id'=>$cc->team_id , 'task_id'=>$cc->task_id])->get()->result();
           /* if($get_all_time)
            {
                foreach($get_all_time as $gg)
                {
                    $day = strtolower(date('l',strtotime($gg->date)));
                    if($day=='sunday')
                    {
                        $sunday.=$gg->task_done.',';
                    }else if($day=='monday')
                    {
                      $monday.=$gg->task_done.'|';  
                  }else if($day=='tuesday')
                    {
                      $tuesday.=$gg->task_done.'|';  
                    }else if($day=='wednesday')
                    {
                      $wednesday.=$gg->task_done.'|';  
                    }
                    else if($day=='thursday')
                    {
                      $thursday.=$gg->task_done.'|';  
                    }else if($day=='friday')
                    {
                      $friday.=$gg->task_done.'|';  
                    }
                    else if($day=='saturday')
                    {
                      $saturday.=$gg->task_done.'|';  
                    }
                    
                }
            }*/
           
            $myData[] = [
                'id' => $cc->id,
                'timesheet_id' => '000000'.$cc->id,
                'heading' => $cc->teamname.'('.$cc->title.')',
                'teamid' => $cc->teamid,
                'teamname' => $cc->teamname,
                'teamimage' => $cc->teamimage ? base_url($cc->teamimage) : base_url('upload/users/photo.png'),
                'taskid' => $cc->taskid,
                'task_title' => $cc->title,
                'spid' => '000000'.$cc->spid,
                'spname' => $cc->spname,
                'spcontact' => $cc->contact,
                'date' => date('d/m/Y',strtotime($cc->created_at)),
                'time' => date('H:i',strtotime($cc->created_at)),
                'hours_done' => $cc->hours_done,
                'over_time' => $cc->ot,
                'policy' => $this->myPolicy1($cc->teamid , $id),
                'goal_completed' => (is_numeric($cc->goal_complete))?$cc->goal_complete.'%':$cc->goal_complete,
                'status' => $status,
                'task_done' => $cc->task_done,
                //'monday' => rtrim($monday,"|"),
                //'tuesday' => rtrim($tuesday,"|"),
               // 'wednesday' => rtrim($wednesday,"|"),
               // 'thursday' => rtrim($thursday,"|"),
                //'friday' => rtrim($friday,"|"),
               // 'saturday' => rtrim($saturday,"|"),

            ];
            
            $this->response(
            ['status' => 'success',
            'responsecode' => REST_Controller::HTTP_OK,
            'message' => 'Data found successfully',
            'data' => $myData,
            ]);  
            }else{
            $this->response(
            [
            'status' => 'false',
            'message' => 'No data found!',
            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
            ]); 
            }

            }
             public function myPolicy($spid , $teamid , $task_id)
            {
                $myData = [];
            $user_id = $this->db->select('user_id')->get_where('myteams',['id'=>$teamid])->row('user_id');        
            $check = $this->db->order_by('priority','ASC')->get_where('tbl_customer_policy_duration',['team_id'=>$teamid , 'user_id'=>$user_id])->result();               
            if($check)
            {
           
           foreach($check as $cc)
           {
            $hours = $this->db->select('SUM(hours) as hours')->get_where('tbl_dynamic_timesheet_relation',['spid'=>$spid , 'team_id'=>$teamid , 'task_id'=>$task_id ,'policy_id'=>$cc->id ,'status'=>'0'])->row('hours');
             $minutes = $this->db->select('SUM(minutes) as minutes')->get_where('tbl_dynamic_timesheet_relation',['spid'=>$spid , 'team_id'=>$teamid , 'task_id'=>$task_id ,'policy_id'=>$cc->id ,'status'=>'0'])->row('minutes');
             if($minutes>=60)
             {
               $mm = $minutes%60;
            $hh = floor($minutes/60);
            $hours+=$hh;
            $minutes=$mm;
             }
            $myData[] = [
                'id' => $cc->id,
                'title' => $cc->title,
                'hours' => sprintf('%02d:%02d', $hours, $minutes),
            ];
           }

            }
             return $myData;
        }
        public function myPolicy1($teamid , $id)
            {
                $myData = [];
            $user_id = $this->db->select('user_id')->get_where('myteams',['id'=>$teamid])->row('user_id');        
            $check = $this->db->order_by('priority','ASC')->get_where('tbl_customer_policy_duration',['team_id'=>$teamid , 'user_id'=>$user_id])->result();               
            if($check)
            {
           
           foreach($check as $cc)
           {
            $hours = $this->db->select('SUM(hours) as hours')->get_where('tbl_dynamic_timesheet_relation',['final_timesheet_id'=>$id ,'policy_id'=>$cc->id ])->row('hours');
             $minutes = $this->db->select('SUM(minutes) as minutes')->get_where('tbl_dynamic_timesheet_relation',['final_timesheet_id'=>$id ,'policy_id'=>$cc->id ])->row('minutes');
             if($minutes>=60)
             {
               $mm = $minutes%60;
            $hh = floor($minutes/60);
            $hours+=$hh;
            $minutes=$mm;
             }
            $myData[] = [
                'id' => $cc->id,
                'title' => $cc->title,
                'hours' => sprintf('%02d:%02d', $hours, $minutes),
            ];
           }

            }
             return $myData;
        }
        public function approveTimesheet_post()
    {
         $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $id = $this->input->post('id');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'id', 'label' => 'id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Timesheet id  is required',
                            'numeric' => 'Timesheet id  should be numeric',
                        ],
                    ],
                    
                   
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $this->db->set('user_status','1')->where(['id'=>$id])->update('tbl_final_timesheet');
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet approved successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet already approved...',
                            ]);
            }
           }
    }
    //timesheet reject
    public function rejectTimesheet_post()
    {
         $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $id = $this->input->post('id');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'id', 'label' => 'id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Timesheet id  is required',
                            'numeric' => 'Timesheet id  should be numeric',
                        ],
                    ],
                    
                   
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $this->db->set('user_status','2')->where(['id'=>$id])->update('tbl_final_timesheet');
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet reject successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet already rejected...',
                            ]);
            }
           }
    }
     //all sp offer letter
    public function allofferletter_get($user_id = NULL , $spid = NULL , $teamid = NULL) {
        
        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($user_id, $token);

        $result = $this->db->select('a.*,b.teamname,b.teamimage,CONCAT(c.firstname, " "  ,c.lastname) AS user_name')
                        ->from('tbl_offer_letter as a')
                        ->join('myteams as b', 'a.team_id = b.id', 'left')
                        ->join('logincr as c', 'a.provider_id = c.id', 'left')
                        ->where(['a.provider_id' => $spid, 'a.team_id'=>$teamid ,'a.status' => '2'])
                        ->where('c.id IS NOT NULL')
                        ->get()->result();
        $dataArray = [];
        if ($result) {
            foreach ($result as $val) {
                if($val->status=='2')
                {
                    $my_status = '1';
                }else if($val->status=='3')
                {
                     $my_status = '0';
                }else{
                   $my_status = '2';  
                }
               $dataArray[] = [
                    'offer_id' => $val->id,
                    'interview_id' => $val->interview_id,
                    'user_name' => $val->user_name,
                    'teamimage' => base_url($val->teamimage),
                    'teamname' => $val->teamname,
                    'preview_url' => base_url('myteam/previewofferletter/' . $val->encrypt_key),
                    'pdf_url' => base_url('myteam/downloadofferletter/' . $val->encrypt_key),
                    'status' => $my_status,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $dataArray,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'data' => $dataArray,
            ]);
        }
    }
    
    /*----------------------- family Member------------------*/
     public function addRelative_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $relative = $this->input->post('relative');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'relative', 'label' => 'relative', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Relative  is required',
                        ],
                    ],
                    
                   
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
           $this->db->insert('tbl_user_relatives',['user_id'=>$userid , 'relative'=>$relative]);
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Member added Successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Member not added Successfully...',
                            ]);
            }
           }
    }
    //get family member
     public function getRelative_get($user_id = NULL) {
        
        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($user_id, $token);

        $result = $this->db->get_where('tbl_user_relatives',['user_id'=>$user_id])->result();
        $dataArray = [];
        if ($result) {
            foreach ($result as $val) {
               $dataArray[] = [
                    'id' => $val->id,
                    'relative' => $val->relative,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $dataArray,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'data' => $dataArray,
            ]);
        }
    }
    //delete relative
     public function deleteRelative_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"));
        $user_id = $data->user_id;

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                    if ( $this->db->delete('tbl_user_relatives', ['id' => $data->id])) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Member deleted succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Member already deleted!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
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
    //new
    public function taskdetailsbycalenderNew_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $id = $this->input->post('user_id');
         $teamid = $this->input->post('teamid');
         if ($token != '' && $id != '' && $teamid != '' ) {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {
                $user = $this->db->get_where('logincr', ['id' => $id])->row();
                if (empty($user)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'User not exist!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                   
                } else {
                     $team = $this->db->select('myteams.* ,  tbl_members.name as mem_name')->from("myteams")
                                                   ->join("tbl_members" , 'tbl_members.id = myteams.member_id' ,'left')
                                                   ->where(['myteams.id'=>$this->input->post('teamid')])
                                                   ->get()
                                                   ->row();    
                    if ($user->switch_account == '1') {
                          
                         $task =  $this->db->select('a.relative_member')
                        ->from('assigntask as a')
                         ->where(['userid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')])
                         ->or_where(['userid'=>$team->user_id])
                       ->get()->result();
                    }else if ($user->switch_account == '2') {
                         $task =  $this->db->select('a.relative_member')
                        ->from('assigntask as a')
                         ->where(['userid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')])
                         ->or_where(['userid'=>$team->agreement_sendby_id])
                       ->get()->result();
                    } else {
                        $task =  $this->db->select('a.relative_member')
                        ->from('assigntask as a')
                         ->where(['spid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')]);
                       
                    }
                    if (!empty($task)) {
                        /*
                        $arr = [];

                        foreach ($task as $val) {
                           $arr[] = [$val->relative_member];
                        }
                      $input = array_map("unserialize", array_unique(array_map("serialize", $arr)));
                      foreach ($input as $in)
                      {
                        $rel = $this->db->get_where('tbl_user_relatives',['id'=>$in[0]])->row();

                        $myData[] = [
                            'id' => (isset($rel->relative))?$rel->id:"0",
                            'relative' => (isset($rel->relative))?$rel->relative:"Self",
                            'taskdate' => $this->input->post('taskdate'),
                            'userid' => $id,
                            'teamid' => $this->input->post('teamid'),
                        ];
                      }*/
                       $myData[] = [
                            'id' => ($team->member_id!=0)?$team->member_id:0,
                            'relative' => ($team->member_id!=0)?$team->mem_name:"Self",
                            'taskdate' => $this->input->post('taskdate'),
                            'userid' => $id,
                            'teamid' => $this->input->post('teamid'),
                        ];
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Record Found!',
                                    'data' => $myData,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Record not found!',
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
    public function taskNewDetails_post() {
        $token = $this->input->get_request_header('Secret-Key');
         $id = $this->input->post('user_id');
         $teamid = $this->input->post('teamid');
         $relative_id = $this->input->post('relative_id');
        
         if ($token != '' && $id != '' && $teamid != '' ) {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {
                $user = $this->db->get_where('logincr', ['id' => $id])->row();
                 //print_r($user); die;
                if (empty($user)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'User not exist!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                   
                } else {
                    $team = $this->db->select('*')->get_where('myteams',['id'=>$this->input->post('teamid')])->row(); 
                    if($team->agreement_id != 0)
                    {
                         if ($user->switch_account == '1' ) {
                           $user_id = $team->agreement_sendby_id;
                           $sc_id = $team->user_id ;
                          $task1 =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.spid = b.id')
                         ->where(['a.userid' => $user_id , 'a.sc_id' =>$sc_id , 'a.taskdate' => $this->input->post('taskdate'),'a.teamid'=>$this->input->post('teamid') ])
                          //->or_where(['a.userid'=>$team->agreement_sendby_id])
                        ->get()->result();
                         $task2 =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.spid = b.id')
                         ->where(['a.userid' => $sc_id , 'a.sc_id' =>$user_id , 'a.taskdate' => $this->input->post('taskdate'),'a.teamid'=>$this->input->post('teamid')])
                          //->or_where(['a.userid'=>$team->agreement_sendby_id])
                        ->get()->result();
                        $task = array_merge($task1 , $task2);
                    }else  if ($user->switch_account == '2' ) {
                         $user_id = $team->user_id;
                         $sc_id = $team->agreement_sendby_id ;
                         $team = $this->db->select('*')->get_where('myteams',['id'=>$this->input->post('teamid')])->row();    
                         $task1 =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.spid = b.id')
                         ->where(['a.userid' => $user_id , 'a.sc_id' =>$sc_id , 'a.taskdate' => $this->input->post('taskdate'),'a.teamid'=>$this->input->post('teamid') ])
                          //->or_where(['a.userid'=>$team->agreement_sendby_id])
                        ->get()->result();
                         $task2 =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.spid = b.id')
                         ->where(['a.userid' => $sc_id , 'a.sc_id' =>$user_id , 'a.taskdate' => $this->input->post('taskdate'),'a.teamid'=>$this->input->post('teamid') ])
                          //->or_where(['a.userid'=>$team->agreement_sendby_id])
                        ->get()->result();
                        $task = array_merge($task1 , $task2);
                    }
                    }else{
                         $task =  $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('assigntask as a')
                        ->join('logincr as b', 'a.userid = b.id', 'left')
                        ->where(['spid' => $id, 'taskdate' => $this->input->post('taskdate'),'teamid'=>$this->input->post('teamid')])
                        ->get()->result();
                    }
                    
                    // echo $this->db->last_query(); die;
                    if (!empty($task)) {
                        foreach ($task as $val) {
                            $rel_data = $this->db->get_where('tbl_user_relatives',['id'=>$val->relative_member])->row();
                             $approve_feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$val->id])->row();
                            $taskData[] = [
                                'id' => $val->id,
                                'teamid' => $val->teamid,
                                'spid' => $val->spid,
                                'title' => $val->title,
                                'task_name' => ($val->task_name==null)?"":$val->task_name,
                                'member_name' => ($val->member_type==null)?"":$val->member_type,
                                'taskstatus' => $val->taskstatus ? $val->taskstatus : 'Pending',
                                'description' => ($val->describe)?$val->describe:"",
                                'comments' => $val->comments ? $val->comments : '',
                                'taskdate' => $val->taskdate ? $val->taskdate : '',
                                'start_time' => $val->start_time ? $val->start_time : '',
                                'end_time' => $val->end_time ? $val->end_time : '',
                                'assignto' => $val->user_name ? $val->user_name : '',
                                'relative_id' => ($val->relative_member==0)?"0":$rel_data->id,
                                'relative' => ($val->relative_member==0)?'Self':$rel_data->relative,
                                'approve_feedback' => (isset($approve_feedback->message) && $approve_feedback->message!=null)?$approve_feedback->message:"",
                            ];
                        }
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Record Found!',
                                    'data' => $taskData,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Record not found!',
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
    
    //search new
     public function searchproviderNew_get($user_id = NULL, $team_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
              //  $xaidata = $this->db->get_where('tbl_xai_matching', ['team_id' => $team_id, 'user_id' => $user_id])->result();
              $teamdata = $this->db->get_where('myteams',['id'=>$team_id])->row();
                if($teamdata->agreement_id!=0 && $teamdata->member_id == 0)
                {
                $xaidata = $this->db->get_where('tbl_xai_matching', ['type' => '4', 'user_id' => $teamdata->agreement_sendby_id , 'for_self_user'=>'1'])->result();
                }else if($teamdata->agreement_id!=0 && $teamdata->member_id != 0){
                 $xaidata = $this->db->get_where('tbl_xai_matching', ['type' => '2', 'user_id' => $teamdata->agreement_sendby_id , 'team_id'=>0 , 'member_id'=>$teamdata->member_id])->result();
                }else{
                $xaidata = $this->db->get_where('tbl_xai_matching', ['team_id' => $team_id, 'user_id' => $user_id])->result();
                }

                if ($xaidata) {
                    foreach ($xaidata as $k => $re) {
                        $industries[] = $re->industry_id;
                        $all[$re->industry_id] = [
                            'budget' => $re->rate,
                            'skills' => $re->skill_id,
                            'experience' => $re->experience_id,
                            'personality' => $re->personality,
                            'members' => $re->members,
                            'factors' => $re->factors,
                            'available_days' => $re->available_days,
                            'start_time' => $re->start_time,
                            'end_time' => $re->end_time,
                            'seven_24' => $re->seven_24,
                            'interest' => $re->interest,
                            'backup' => $re->backup,
                            'accessment' => $re->accessment,
                            'communication_id' => $re->communication_id,
                            'expectation' => $re->expectation,
                            'driving_distance' => $re->driving_distance,
                            'xai_personality' => $re->xai_personality,
                            'motivation' => $re->motivation,
                            'language' => $re->language,
                            'softskills_id' => $re->softskills_id,
                            'additional_skills' => $re->additional_skills,
                            'frequency_id' => $re->frequency_id,
                            'options_preferences' => $re->options_preferences,
                            'covid_vaccination_proof' => $re->covid_vaccination_proof,
                            'negative_tests_proof' => $re->negative_tests_proof,
                            'none' => $re->none,
                        ];
                    }
                    $provider = $this->db->select('a.*,b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name,b.address , b.usertype,c.name as skillname,d.name as expe,e.name as industry')
                                    ->from('tbl_xai_matching as a')
                                    ->where_in('a.industry_id', $industries)
                                    ->join('logincr as b', 'a.user_id = b.id', 'left')
                                    ->join('tbl_skill as c', 'a.skill_id = c.id', 'left')
                                    ->join('tbl_experience as d', 'a.experience_id = d.id', 'left')
                                    ->join('tbl_industries as e', 'a.industry_id = e.id', 'left')
                                    ->where('b.id is not null')
                                    ->where('b.id!=',$user_id)
                                    ->where('a.language is not null')
                                    ->group_by('a.user_id')
                                    ->get()->result();
                    if($provider){
                        $total = 0;
                        $result = [];
                        foreach ($provider as $val) {
                            if($val->usertype=='1')
                            {
                                continue;
                            }else
                            $exist = $this->db->order_by('id','DESC')->limit(1)->get_where('scheduleinterview', ['teamid' => $team_id, 'spid' => $val->user_id])->row();
                            $cdata = [];
                            $certificates = $this->db->select('a.*,b.title')->from('tbl_user_certificate as a')
                                    ->join('tbl_certification as b', 'a.certification_id = b.id','left')
                                    ->where(['a.user_id' => $val->user_id])->get()->result();
                            if ($certificates) {
                                foreach ($certificates as $cert) {
                                    $cdata[] = [
                                        'certification_id' => $cert->certification_id,
                                        'title' => $cert->title,
                                        'mime_type' => pathinfo($cert->certificate, PATHINFO_EXTENSION),
                                        'document' => base_url($cert->certificate),
                                    ];
                                }
                            }
                            if ($exist) {
                                //print_r($exist); die;
                                if($exist->status!='Pending'){
                                    
                                    $per = 0;
                               $com = count(explode(',',$all[$val->industry_id]['communication_id']));
                               $com1 = count(explode(',',$val->communication_id));
                                $soft = count(explode(',',$all[$val->industry_id]['softskills_id']));
                               $soft1 = count(explode(',',$val->softskills_id));
                                $add = count(explode(',',$all[$val->industry_id]['additional_skills']));
                               $add1 = count(explode(',',$val->additional_skills));
                                $fr = count(explode(',',$all[$val->industry_id]['frequency_id']));
                               $fr1 = count(explode(',',$val->frequency_id));
                                
                                $per += (
                                         $this->percent($all[$val->industry_id]['skills'], $val->skill_id)+ 
                                         $this->percent($all[$val->industry_id]['experience'], $val->experience_id)+ 
                                         $this->percent($all[$val->industry_id]['budget'], $val->rate)+
                                          $this->percent(is_numeric($all[$val->industry_id]['personality'])?$all[$val->industry_id]['personality']:1,is_numeric($val->personality)?$val->personality:2)+
                                            $this->percent($all[$val->industry_id]['members'], $val->members)+
                                            $this->percent(is_numeric($all[$val->industry_id]['factors']), is_numeric($val->factors))+
                                            $this->percent(is_numeric($all[$val->industry_id]['available_days'])?$all[$val->industry_id]['available_days']:1,is_numeric($val->available_days)?$val->available_days:2)+
                                             $this->percent(is_numeric($all[$val->industry_id]['start_time'])?$all[$val->industry_id]['start_time']:1,is_numeric($val->start_time)?$val->start_time:2)+
                                            $this->percent(is_numeric($all[$val->industry_id]['end_time'])?$all[$val->industry_id]['end_time']:1,is_numeric($val->end_time)?$val->end_time:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['interest'])?$all[$val->industry_id]['interest']:1,is_numeric($val->interest)?$val->interest:2)+
                                              $this->percent(($all[$val->industry_id]['backup']=='1')?is_numeric($all[$val->industry_id]['backup']):1,($val->interest=='1')?is_numeric($val->interest):0)+
                                              $this->percent(is_numeric($all[$val->industry_id]['accessment'])?$all[$val->industry_id]['accessment']:1,is_numeric($val->accessment)?$val->accessment:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['expectation'])?$all[$val->industry_id]['expectation']:1,is_numeric($val->expectation)?$val->expectation:2)+
                                                $this->percent(is_numeric($all[$val->industry_id]['xai_personality'])?$all[$val->industry_id]['xai_personality']:1,is_numeric($val->xai_personality)?$val->xai_personality:2)+
                                                 $this->percent(is_numeric($all[$val->industry_id]['motivation'])?$all[$val->industry_id]['motivation']:1,is_numeric($val->motivation)?$val->motivation:2)+
                                                   $this->percent(is_numeric($all[$val->industry_id]['language'])?$all[$val->industry_id]['language']:1,is_numeric($val->language)?$val->language:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['options_preferences'])?$all[$val->industry_id]['options_preferences']:1,is_numeric($val->options_preferences)?$val->options_preferences:2)+
                                              $this->percent(($all[$val->industry_id]['covid_vaccination_proof']=='0')?1:2,($val->covid_vaccination_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['negative_tests_proof']=='0')?1:2,($val->negative_tests_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['none']=='0')?1:2,($val->none=='1')?1:2)+
                                                   $this->percent($com?$com:0,$com1?$com1:0)+
                                                   $this->percent($soft?$soft:0,$soft1?$soft1:0)+
                                                   $this->percent($add?$add:0,$add1?$add1:0)+
                                                    $this->percent($fr?$fr:0,$fr1?$fr1:0)+
                                                    $this->percent(is_numeric($all[$val->industry_id]['driving_distance']), is_numeric($val->driving_distance))



                                         )
                                     /21;
                                $total += $per;
                                    $result[] = [
                                        'provider_id' => $val->user_id,
                                        'usertype' => $val->usertype,
                                        'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                        'name' => $val->user_name,
                                        'fees' => '$' . $val->rate,
                                        'percent' => round($per) . '%',
                                        'industry_id' => $val->industry_id,
                                        'industry' => $val->industry,
                                        'skillname' => $val->skillname ? $val->skillname : '',
                                        'experience' => $val->expe,
                                        'address' => $val->address,
                                        'rating' => $this->Common_model->getrating($val->user_id),
                                        'certificates' =>$cdata
                                    ];
                                
                                    $total = $total / count($provider);
                                    if ($total > 50) {
                                        $ready = '1';
                                    } else {
                                        $ready = '0';
                                    }
                               }
                            }else{
                                $per = 0;
                               $com = count(explode(',',$all[$val->industry_id]['communication_id']));
                               $com1 = count(explode(',',$val->communication_id));
                                $soft = count(explode(',',$all[$val->industry_id]['softskills_id']));
                               $soft1 = count(explode(',',$val->softskills_id));
                                $add = count(explode(',',$all[$val->industry_id]['additional_skills']));
                               $add1 = count(explode(',',$val->additional_skills));
                                $fr = count(explode(',',$all[$val->industry_id]['frequency_id']));
                               $fr1 = count(explode(',',$val->frequency_id));
                                
                                $per += (
                                         $this->percent($all[$val->industry_id]['skills'], $val->skill_id)+ 
                                         $this->percent($all[$val->industry_id]['experience'], $val->experience_id)+ 
                                         $this->percent($all[$val->industry_id]['budget'], $val->rate)+
                                          $this->percent(is_numeric($all[$val->industry_id]['personality'])?$all[$val->industry_id]['personality']:1,is_numeric($val->personality)?$val->personality:2)+
                                            $this->percent($all[$val->industry_id]['members'], $val->members)+
                                            $this->percent(is_numeric($all[$val->industry_id]['factors']), is_numeric($val->factors))+
                                            $this->percent(is_numeric($all[$val->industry_id]['available_days'])?$all[$val->industry_id]['available_days']:1,is_numeric($val->available_days)?$val->available_days:2)+
                                             $this->percent(is_numeric($all[$val->industry_id]['start_time'])?$all[$val->industry_id]['start_time']:1,is_numeric($val->start_time)?$val->start_time:2)+
                                            $this->percent(is_numeric($all[$val->industry_id]['end_time'])?$all[$val->industry_id]['end_time']:1,is_numeric($val->end_time)?$val->end_time:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['interest'])?$all[$val->industry_id]['interest']:1,is_numeric($val->interest)?$val->interest:2)+
                                              $this->percent(($all[$val->industry_id]['backup']=='1')?is_numeric($all[$val->industry_id]['backup']):1,($val->interest=='1')?is_numeric($val->interest):0)+
                                              $this->percent(is_numeric($all[$val->industry_id]['accessment'])?$all[$val->industry_id]['accessment']:1,is_numeric($val->accessment)?$val->accessment:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['expectation'])?$all[$val->industry_id]['expectation']:1,is_numeric($val->expectation)?$val->expectation:2)+
                                                $this->percent(is_numeric($all[$val->industry_id]['xai_personality'])?$all[$val->industry_id]['xai_personality']:1,is_numeric($val->xai_personality)?$val->xai_personality:2)+
                                                 $this->percent(is_numeric($all[$val->industry_id]['motivation'])?$all[$val->industry_id]['motivation']:1,is_numeric($val->motivation)?$val->motivation:2)+
                                                   $this->percent(is_numeric($all[$val->industry_id]['language'])?$all[$val->industry_id]['language']:1,is_numeric($val->language)?$val->language:2)+
                                                   $this->percent(is_numeric($all[$val->industry_id]['options_preferences'])?$all[$val->industry_id]['options_preferences']:1,is_numeric($val->options_preferences)?$val->options_preferences:2)+
                                              $this->percent(($all[$val->industry_id]['covid_vaccination_proof']=='0')?1:2,($val->covid_vaccination_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['negative_tests_proof']=='0')?1:2,($val->negative_tests_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['none']=='0')?1:2,($val->none=='1')?1:2)+
                                                   $this->percent($com?$com:0,$com1?$com1:0)+
                                                   $this->percent($soft?$soft:0,$soft1?$soft1:0)+
                                                   $this->percent($add?$add:0,$add1?$add1:0)+
                                                    $this->percent($fr?$fr:0,$fr1?$fr1:0)+
                                                    $this->percent(is_numeric($all[$val->industry_id]['driving_distance']), is_numeric($val->driving_distance))



                                         )
                                     /21;
                                $total += $per;
                                $result[] = [
                                    'provider_id' => $val->user_id,
                                    'usertype' => $val->usertype,
                                    'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                    'name' => $val->user_name,
                                    'fees' => '$' . $val->rate,
                                    'percent' => round($per) . '%',
                                    'industry_id' => $val->industry_id,
                                    'industry' => $val->industry,
                                    'skillname' => $val->skillname ? $val->skillname : '',
                                    'experience' => $val->expe,
                                    'address' => $val->address,
                                    'rating' => $this->Common_model->getrating($val->user_id),
                                    'certificates' =>$cdata
                                ];
                            
                                $total = $total / count($provider);
                                if ($total > 50) {
                                    $ready = '1';
                                } else {
                                    $ready = '0';
                                }
                            }
                        }
                        
                        if ($result) {
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Record found successfully!',
                                        'requirement_status' => $ready,
                                        'data' => $result,
                            ]);
                        } else {
                            $this->response(
                                    ['status' => 'false',
                                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                        'message' => 'Record not found!',
                                        'data' => [],
                            ]);
                        }
                    }else{
                    $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                    'message' => 'Record not found!',
                                    'data' => [],
                        ]); 
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team requirement not found!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Invalid token!',
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
    
     //get all ap list hired by client
 public function getSpList_get($id = NULL) {
        $user_id = $id;
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                $getData = $this->db->select('b.*')
                                    ->from('tbl_offer_letter as a')
                                    ->join('logincr as b' ,'b.id = a.provider_id','left')
                                    ->where(['a.user_id'=>$id , 'a.status'=>'2' ,'b.id!='=>$id])
                                    ->group_by('b.id')
                                    ->get()->result();
                
                if($getData)
                {
                    foreach($getData as $check_record)
                    {
                     $userData[] = [
                    'user_id' => $check_record->id,
                    'profile_img' => $check_record->image ? base_url().$check_record->image : base_url('upload/users/phpto.png'),
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
                    $this->response(
                                ['status' => 'failed',
                                    'message' => 'No data found!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
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
    //get user details
    public function getUserDetails_get($id = '') {

        $this->load->model('Common_model');
        $token = $this->input->get_request_header('Secret-Key');

        //$userid  = $this->input->post('id');
        $user_id = $id;
         $check_key = $this->checktoken($token, $user_id);

        if ($user_id != '') {
            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $user_id, 'status' => '1'));
            if ($check_record != '') {

                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;

                if ($photo != '') {
                    $uphoto = $basepath . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }



                // $edu_record = $this->Common_model->common_getRow('usereducation', array('userid'=>$spid));

                $edu_record = $this->db->get_where('usereducation', ['userid' => $user_id])->result();
                if ($edu_record != '') {
                    foreach ($edu_record as $val) {
                        $data_edu[] = [
                            'id' => $val->id,
                            'userid' => $val->userid,
                            'education' => $val->education,
                            'passingyear' => $val->passingyear,
                            'certificate' => $val->certificate ? base_url('upload/users/') . $val->certificate : '',
                            'collegename' => $val->collegename,
                        ];
                    }
                } else {
                    $data_edu = [];
                }

                $exp_record = $this->db->select('b.name as experience, c.name as industry, d.name as skills')->from('tbl_xai_matching as a')
                                ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                                ->join('tbl_industries as c', 'a.industry_id = c.id ', 'left')
                                ->join('tbl_skill as d', 'a.skill_id = d.id ', 'left')
                                ->where('a.user_id', $user_id)->get()->row();

                $data_exp = [];
                if ($exp_record) {

                    $data_exp[] = array(
                        'experience' => $exp_record->experience,
                        'industry' => $exp_record->industry,
                        'skills' => $exp_record->skills,
                    );
                }

               /* $interviewdata = $this->Common_model->common_getRow('scheduleinterview', array('spid' => $spid, 'teamid' => $teamid, 'status' => 'pending'));

                if ($interviewdata != '') {
                    $data_interview[] = array(
                        'interviewdate' => $interviewdata->interviewDate,
                        'interviewtime' => $interviewdata->interviewTime,
                    );
                } else {
                    $data_interview = [];
                }*/
                
                $certificates = [];
                $certificate = $this->db->select('a.*,b.title')->from('tbl_user_certificate as a')
                                ->join('tbl_certification as b', 'a.certification_id=b.id', 'left')
                                ->where('a.user_id', $user_id)
                                ->get()->result();
                if ($certificate) {
                    foreach ($certificate as $cert) {
                        $certificates[] = [
                            'id' => $cert->id,
                            'title' => $cert->title,
                            'mime_type' => pathinfo($cert->certificate, PATHINFO_EXTENSION),
                            'certificate' => base_url($cert->certificate),
                        ];
                    }
                }

                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
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
                    'audio_file' => (!empty($check_record->audio_file) || $check_record->audio_file!=NULL)?base_url($check_record->audio_file):'',
                    'rating' => $this->Common_model->getrating($user_id),
                   // 'interviewdatetime' => $data_interview,
                    'educationdata' => (isset($data_edu))?$data_edu:[],
                    'experiencedata' => $data_exp,
                    'certificates' => $certificates,
                );
                 $this->response(
                                ['status' => 'success',
                                    'message' => 'Data found successfully',
                                    'data' => $data_array,
                                    'responsecode' => REST_Controller::HTTP_OK,
                        ]);
            } else {
                 $this->response(
                                ['status' => 'failed',
                                    'message' => 'Record not found!',
                                    'responsecode' => REST_Controller::HTTP_PAYMENT_REQUIRED,
                        ]);
            }
        } else {
           $this->response(
                                ['status' => 'failed',
                                    'message' => 'Spid not found!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
        }
    }


   
}
