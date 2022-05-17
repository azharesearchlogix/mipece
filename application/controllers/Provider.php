<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Provider extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model','NotificationModel']);
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


    public function checktoken($token, $userid) {

        $auth = $this->Common_model->common_getRow('logincr', array('token_security' => $token, 'id' => $userid));
        //echo $this->db->last_query();
        // print_r($auth); die;

        if (!empty($auth)) {
            $abc['status'] = "true";
            $abc['data'] = $auth;
            return $abc;
        } else {
            $abc['status'] = "false";
            return $abc;
        }
    }

    public function postquestion_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                        'user_id' => $user_id,
                        'question' => $this->security->xss_clean($this->input->post('question')),
                        'description' => $this->security->xss_clean($this->input->post('description')),
                        'created_by' => $user_id,
                    ];
                    $result = $this->db->insert('tbl_question_post', $formArray);
                    $lid = $this->db->insert_id();
                    if ($lid > 0) {

                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Question added Successfully!',
                                    'data' => $lid,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Something went wrong!',
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
    
    public function myquestions_get($id = NULL) {
        $user_id = $id;
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                //$result = $this->db->get_where('tbl_question_post', ['user_id' => $user_id, 'status' => '0'])->result();
                $query = $this->db->select('a.*,CONCAT(b.firstname, " "  , b.lastname) AS name,b.image , b.profile_pic')
                        ->from('tbl_question_post as a')
                        ->join('logincr as b', 'b.id = a.user_id', 'left')
                        ->where(['a.user_id' => $user_id, 'a.status' => '1'])
                        ->order_by('a.id DESC')
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
                    $dataArray[] = [
                        'id' => $val->id,
                        'username' => $val->name,
                        'userimage' => (!empty($val->profile_pic) && $val!=NULL)?$val->profile_pic:base_url($val->image),
                        'question' => $val->question,
                        'description' => $val->description,
                       'likes' => $this->db->get_where('tbl_like_question_post',['question_id'=>$val->id])->num_rows(),
                        'comments' => $this->db->get_where('tbl_comments_question_post',['question_id'=>$val->id])->num_rows(),
                        'date' => date('F j, Y', strtotime($val->updated_at)),
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Questions found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Questions not found!',
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

    public function postquestions_get($id = NULL) {
        $user_id = $id;
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                //$result = $this->db->get_where('tbl_question_post', ['status' => '0'])->result();
                 $query = $this->db->select('a.*,CONCAT(b.firstname, " "  , b.lastname) AS name,b.image')
                        ->from('tbl_question_post as a')
                        ->join('logincr as b', 'b.id = a.user_id', 'left')
                        ->where('b.id IS NOT NULL')
                        ->order_by('a.id DESC')
                        ->where(['a.status' => '1'])
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
                    $dataArray[] = [
                         'id' => $val->id,
                        'username' => $val->name,
                        'userimage' => base_url($val->image),
                        'question' => $val->question,
                        'description' => $val->description,
                       'likes' => $this->db->get_where('tbl_like_question_post',['question_id'=>$val->id])->num_rows(),
                        'comments' => $this->db->get_where('tbl_comments_question_post',['question_id'=>$val->id])->num_rows(),
                        'date' => date('F j, Y', strtotime($val->updated_at)),
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Questions found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Questions not found!',
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

 public function updatepostquestion_put() {
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
                    ['field' => 'question_id', 'label' => 'question_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question id is required',
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
                        'updated_by' => $user_id,
                    ];
                    $this->db->update('tbl_question_post', $formArray, ['id' => $this->put('question_id'), 'user_id' => $user_id]);
//                    echo $this->db->last_query();
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your question updated succesfully!',
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

    public function deletequestion_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"));
      //  $this->form_validation->set_data(file_get_contents("php://input"));
         $user_id = $data->user_id;

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                
               
                    $this->db->where(['id' =>  $data->question_id, 'user_id' => $user_id]);
                    $this->db->delete('tbl_question_post');
//                    echo $this->db->last_query();
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your has been deleted succesfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Something went wrong!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        ]);
                    }
                }
            
        } else {
            $this->response(
                    ['status' => 'Failed',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    
    public function likepostquestion_post() {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();

            if ($check_key['status'] == 'true') {
                $data = $this->put();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],
                    ['field' => 'question_id', 'label' => 'question_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question id is required',
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
                        'user_id' => $this->input->post('user_id'),
                        'question_id' => $this->input->post('question_id'),
                    ];
                     $likedata = $this->db->get_where('tbl_like_question_post', ['question_id' => $this->input->post('question_id')])->num_rows();
                    $result = $this->db->get_where('tbl_like_question_post', $formArray)->row();
                    //echo $this->db->last_query();
                    if ($result) {
                        $this->db->where($formArray);
                        $this->db->delete('tbl_like_question_post');
                        $affected = $this->db->affected_rows();
                        $msg = 'unlike';
                         $like = $likedata - 1;
                    } else {
                        $this->db->insert('tbl_like_question_post', $formArray);
                        $affected = $this->db->insert_id();
                        $msg = 'like';
                        $like = $likedata + 1;
                    }

                    if ($affected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'like' => $like,
                                    'message' => 'Your have  succesfully ' . $msg . '!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Something went wrong!',
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

public function commentspostquestion_post() {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();

            if ($check_key['status'] == 'true') {
                $data = $this->put();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],
                    ['field' => 'question_id', 'label' => 'question_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Question id is required',
                        ],
                    ],
                    ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comments id is required',
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
                        'user_id' => $this->input->post('user_id'),
                        'question_id' => $this->input->post('question_id'),
                        'comments' => $this->security->xss_clean($this->input->post('comments')),
                    ];

                    $this->db->insert('tbl_comments_question_post', $formArray);

                    if ($this->db->insert_id() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your comments has succesfully added!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Something went wrong!',
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
    
    public function commentslist_get($id = NULL, $qid = NULL) {
        $user_id = $id;
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '' && $qid != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                $query = $this->db->select('a.*,CONCAT(b.firstname, " "  , b.lastname) AS name,b.image')
                        ->from('tbl_comments_question_post as a')
                        ->join('logincr as b', 'b.id = a.user_id', 'left')
                        ->where('a.question_id', $qid)
                        ->where('b.id IS NOT NULL')
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
                    $dataArray[] = [
                        'id' => $val->id,
                        'question_id' => $val->question_id,
                        'username' => $val->name,
                        'userimage' => $val->image != '' ? base_url('upload/users/' . $val->image) : base_url('upload/users/photo.png'),
                        'comments' => $val->comments,
                        'date' => date('F j, Y', strtotime($val->created_at)),
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Comments found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Comments not found!',
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
    
     public function myteam_get() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.id,a.teamid ,a.interviewDate , a.interviewTime,a.spid ,a.status,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.profile_pic as image , c.teamimage,c.teamname")
                        ->from('scheduleinterview as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                        ->join('myteams as c', 'c.id = a.teamid', 'left')
                        ->where('b.id IS NOT NULL')
                        ->where('c.teamName IS NOT NULL')
                        ->where(['a.status' => 'pending'])
                        ->where(['a.spid' => $user_id])
                        ->where(['a.is_soft_status' => '0'])
                        ->order_by('a.id','DESC')
                        ->group_by('a.teamid')
                        ->get();
//                echo $this->db->last_query(); die;
                $result = $query->result();
                foreach ($result as $val) {
                    $dataArray[] = [
                        'spid' => $val->spid,
                        'id' => $val->id,
                        'interviewid' => $val->id,
                        'teamid' => $val->teamid,
                        'username' => $val->name,
                        'userimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        'teamname' => $val->teamname,
                        'interviewDate' => $val->interviewDate,
                        'interviewTime' => $val->interviewTime,
                        'status' => $val->status,
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Team  found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team not found!',
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


public function interviewdetails_get() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');
        $interview_id = $this->input->get_request_header('interviewid');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                $query = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS name , b.image,c.teamimage,c.teamName,c.requiredCondition,c.notes , c.description")
                        ->from('scheduleinterview as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                        ->join('myteams as c', 'c.id = a.teamid', 'left')
                        //->join('myteamnotes as d', 'd.teamid = a.teamid', 'left')
                        ->where('b.id IS NOT NULL')
                        ->where('c.teamName IS NOT NULL')
                        ->where(['a.status' => 'pending'])
                       // ->where(['d.spid' => $user_id])
                        ->where(['a.id' => $interview_id])
                        ->group_by('a.teamid')
                        ->get();


                //echo $this->db->last_query(); die;
                $result = $query->row();
                // echo '<pre>';
                // print_r($result);
                // die;
                

                if (!empty($result)) {
                $dataArray = [
                    'id' => $result->id,
                    'teamid' => $result->teamid,
                    'username' => $result->name,
                    'userimage' => $result->teamimage ? base_url($result->teamimage) : base_url('upload/users/photo.png'),
                    'teamName' => $result->teamName,
                    'interviewTime' => $result->interviewTime,
                    'interviewDate' => $result->interviewDate,
                    'requiredCondition' => $result->description? $result->description:'',
                    'description' => $result->notes?$result->notes:'',
                    'status' => $result->status,
                ];
               
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Team  found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team not found!',
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
    
    public function cancelinterview_put() {
        
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
                    ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Interview id is required',
                        ],
                    ],
                     ['field' => 'device_tpye', 'label' => 'device_tpye', 'rules' => 'required',
                        'errors' => [
                            'required' => 'device_tpye is required',
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
                    
                    $this->db->update('scheduleinterview', ['status'=>'Cancel','cancel_date'=>date('Y-m-d H:i:s'),'cancel_by'=>$user_id], ['id' => $this->put('interview_id')]);
//                    echo $this->db->last_query();
                    $udata = $this->db->get_where('scheduleinterview', ['id' => $this->put('interview_id')])->row();
                    if ($this->db->affected_rows() > 0) {
                        
                        $provider_data = $this->db->get_where('logincr', ['id' => $udata->userid])->row();
                        $message = [
                            'title' => 'Interview canceled',
                            'body' => 'Your interview has been canceled',
                            'icon' => base_url('upload/images/notification.png')
                        ];
                        $notification_data = [
                            'device_tpye' => $this->input->post('device_tpye'),
                            'device_token' => $provider_data->tokenid,
                        ];
                        $response = $this->NotificationModel->index($notification_data, $message);
                        $message['user_id'] = $udata->userid;
                        $this->db->insert('tbl_notification', $message);
                        
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your interview has been canceled!',
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
    
     public function rescheduleinterview_put() {
         
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
                    ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Interview id is required',
                        ],
                    ],
                    ['field' => 'interviewDate', 'label' => 'interviewDate', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Interview Date  is required',
                        ],
                    ],
                    ['field' => 'interviewTime', 'label' => 'interviewTime', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Interview Time  is required',
                        ],
                    ],
                     ['field' => 'device_tpye', 'label' => 'device_tpye', 'rules' => 'required',
                        'errors' => [
                            'required' => 'device_tpye is required',
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
                        'interviewDate' => $this->put('interviewDate'),
                        'interviewTime' => $this->put('interviewTime'),
                        're_schedule_by' => $user_id,
                        're_schedule_date' => date('Y-m-d H:i:s'),
                    ];

                    $this->db->update('scheduleinterview', $formArray, ['id' => $this->put('interview_id')]);
//                    echo $this->db->last_query();
                    $udata = $this->db->get_where('scheduleinterview', ['id' => $this->put('interview_id')])->row();
                    
                    if ($this->db->affected_rows() > 0) {
                        
                        $provider_data = $this->db->get_where('logincr', ['id' => $udata->userid])->row();
                        $message = [
                            'title' => 'Interview re-scheduled',
                            'body' => 'Your interview re-scheduled has been done',
                            'icon' => base_url('upload/images/notification.png')
                        ];
                        $notification_data = [
                            'device_tpye' => $this->put('device_tpye'),
                            'device_token' => $provider_data->tokenid,
                        ];
                        $response = $this->NotificationModel->index($notification_data, $message);
                       
                       
                        $message['user_id'] = $udata->userid;
                        $this->db->insert('tbl_notification', $message);
                        
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your interview re-scheduled has been done!',
                                    
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

public function myselectedteam_get() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.id,a.teamid,a.status,CONCAT(b.firstname, ' '  , b.lastname) AS name,c.teamname,c.teamimage")
                        ->from('scheduleinterview as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                        ->join('myteams as c', 'c.id = a.teamid', 'left')
                        ->where('b.id IS NOT NULL')
                        ->where('c.teamname IS NOT NULL')
                        ->where(['a.status' => 'Approved'])
                        ->where(['a.spid' => $user_id])
                        ->order_by('a.create_at','DESC')
                        ->group_by('a.teamid')
                        ->get();
//                echo $this->db->last_query(); die;
                $result = $query->result();
                $team_id = [];
                $dataArray =[];
                foreach ($result as $val) {
                    array_push($team_id, $val->teamid);
                }
                if (!empty($team_id)) {
                    $teamdata = $this->db->group_by('teamid')->select('*')->from('scheduleinterview')->where_in('teamid', $team_id)->get()->result();

                   $query = $this->db->select("a.*,c.teamimage,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,c.teamname,d.notes as notes , c.description")
                            ->from('scheduleinterview as a')
                            ->join('logincr as b', 'b.id = a.userid', 'left')
                            ->join('myteams as c', 'c.id = a.teamid', 'left')
                            ->where('b.id IS NOT NULL')
                            ->where('c.teamName IS NOT NULL')
                            ->join('myteamnotes as d', 'd.teamid = a.teamid', 'left')
                            ->where(['a.status' => 'Approved'])
                            ->order_by('a.create_at','DESC')
                            ->group_by('a.teamid')
                            ->where_in('a.teamid', $team_id)
                            ->get();
//                echo $this->db->last_query(); die;
                    $data = $query->result();
                    foreach ($data as $k => $v) {
                        $pdata = [];
                         
                        $users = $this->db->select('GROUP_CONCAT(spid SEPARATOR ",") as sp_id')->from('scheduleinterview')->where(['teamid' => $v->teamid, 'status' => 'Approved'])->get()->row();
                        
                        $providers = $this->db->select('*')->from('logincr')->where_in('id', explode(",", $users->sp_id))->get()->result();
                        foreach ($providers as $p) {
                            $pdata[] = [
                                'name' => $p->firstname . ' ' . $p->lastname,
                                'image' => $p->image  ? base_url($p->image) : base_url('upload/users/photo.png'),
                            ];
                        }
                        $dataArray[] = [
                            'interview_id' => $v->id,
                            'teamid' => $v->teamid,
                            'client_id' => $v->client_id,
                            'teamName' => $v->teamname,
                            'image' => $v->teamimage ? base_url($v->teamimage) : base_url('upload/users/photo.png'),
                            'clientname' => $v->name,
                            'description' => $v->description ? $v->description : '',
                            'notes' => $v->notes ? $v->notes : '',
                            'members' => $this->db->get_where('scheduleinterview', ['teamid' => $v->teamid, 'status' => 'Approved'])->num_rows(),
                            'users' => $this->db->select('GROUP_CONCAT(CONCAT(firstname, " "  , lastname)  SEPARATOR ",") as name  ')->from('logincr')->where_in('id', explode(",", $users->sp_id))->get()->row()->name,
                            'members_info' => $pdata,
                            
                        ];
                    }
                }

                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Team  found successfully!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team not found!',
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
    
     public function selectedteamdetails_get() {
         
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');
        $team_id = $this->input->get_request_header('teamid');

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $dataArray = [];
                $team = $this->db->select("a.teamname,a.teamimage,a.description,CONCAT(b.firstname, ' '  , b.lastname) AS clientname,b.contact,b.id,b.image as user_img")
                                ->from('myteams as a')
                                ->join('logincr as b', 'b.id = a.user_id', 'left')
                                ->where(['a.id' => $team_id])
                                ->get()->row();
                if (!empty($team)) {
                    $teamData = [
                        'client_id' => $team->id,
                        'teamname' => $team->teamname,
                        'image' => $team->teamimage ? base_url() . $team->user_img : base_url('upload/users/phpto.png'),
                        'requiredCondition' => ($team->description)?$team->description:'',
                        'clientname' => $team->clientname,
                        'contact' => $team->contact,
                    ];

                    $query = $this->db->select("a.id,a.teamid,a.status,CONCAT(b.firstname, ' '  , b.lastname) AS providername,b.image as userimage,b.id as user_id")
                            ->from('scheduleinterview as a')
                            ->join('logincr as b', 'b.id = a.spid', 'left')
                            ->where('b.id IS NOT NULL')
                            ->where(['a.status' => 'Approved'])
                            ->where(['a.teamid' => $team_id])
                            ->get();

                    $result = $query->result();
                    // echo '<pre>';
                    // print_r($result); die;
                    $userData = [];
                    $noteData = [];

                    foreach ($result as $val) {
                        $userData[] = [
                            'user_id' => $val->user_id,
                            'providername' => $val->providername,
                            'userimage' => $val->userimage ? base_url() . $val->userimage : base_url('upload/users/phpto.png'),
                        ];
                    }

                    $query1 = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS providername,b.image as userimage,b.id")
                            ->from('myteamnotes as a')
                            ->join('logincr as b', 'b.id = a.userid', 'left')
                            ->where('b.id IS NOT NULL')
//                         ->where(['a.status' => 'Approved'])
                            ->where(['a.teamid' => $team_id])
                            ->get();

                    $notes = $query1->result();

                    foreach ($notes as $n) {
                        $noteData[] = [
                            'providername' => $n->providername,
                            'image' => $n->userimage ? base_url() . $n->userimage : base_url('upload/users/phpto.png'),
                            'notes' => $n->notes,
                            'date' => date('F j, Y, H:i A', strtotime($n->create_at)),
                        ];
                    }


                    $dataArray = [
                        'teamData' => $teamData,
                        'userData' => $userData,
                        'noteData' => $noteData,
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

public function teammaker_get() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');
        $team_id = $this->input->get_request_header('teamid');

        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $dataArray = [];
                /*$team = $this->db->select("a.teamName,a.image,a.requiredCondition,CONCAT(b.firstname, ' '  , b.lastname) AS clientname,b.contact,b.id,b.email,b.ssnnum,b.address,b.country,b.postalcode,b.city,b.image as user_img  ")
                                ->from('myteams as a')
                                ->join('logincr as b', 'b.id = a.userid', 'left')
                                ->where(['a.id' => $team_id])
                                ->get()->row();*/
                    $team = $this->db->select("a.teamname ,a.teamimage as image,a.requiredCondition,CONCAT(b.firstname, ' '  , b.lastname) AS clientname,b.contact,b.id,b.email,b.ssnnum,b.address,b.country,b.postalcode,b.city,b.image as user_img")
                    ->from('myteams as a')
                    ->join('logincr as b', 'b.id = a.user_id', 'left')
                    ->where(['a.id' => $team_id])
                    ->get()->row();
                if (!empty($team)) {
                    $dataArray = [
                        'client_id' => $team->id,
                        'teamName' => $team->teamname,
                        'email' => $team->email,
                        'image' => $team->image ? base_url() . $team->user_img : base_url('upload/users/phpto.png'),
                        'requiredCondition' => ($team->requiredCondition)?$team->requiredCondition:'',
                        'clientname' => $team->clientname,
                        'contact' => $team->contact,
                        'ssnnum' => $team->ssnnum,
                        'country' => $team->country,
                        'city' => $team->city,
                        'address' => $team->address,
                        'postalcode' => $team->postalcode,
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

public function xaione_get() {

        $industryArray = [];
        $skillArray = [];
        $experienceArray = [];

        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        if (!empty($industry)) {
            foreach ($industry as $ind) {
                $industryArray[] = ['id' => $ind->id, 'name' => $ind->name];
            }
        }

        $skills = $this->db->get_where('tbl_skill', ['status' => '0'])->result();
        if (!empty($skills)) {
            foreach ($skills as $skill) {
                $skillArray[] = ['id' => $skill->id, 'name' => $skill->name];
            }
        }
        
        $experience = $this->db->get_where('tbl_experience', ['status' => '0'])->result();
        if (!empty($experience)) {
            foreach ($experience as $exp) {
                $experienceArray[] = ['id' => $exp->id, 'name' => $exp->name];
            }
        }

        $dataArray = [
            'industry' => $industryArray,
            'skills' => $skillArray,
            'experiences' => $experienceArray,
        ];

        if (!empty($dataArray)) {

            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found!',
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
    }

public function xaione_put() {
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
                    // ['field' => 'personality', 'label' => 'personality', 'rules' => 'required',
                    //     'errors' => [
                    //         'required' => 'Personality is required',
                    //     ],
                    // ],
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
                        if(!empty($this->put('team_id')))
                       {
                        $team_id = $this->put('team_id');
                       }else{
                        $team_id = 0;
                       }
                    $formArray = [
                        'user_id' => $this->put('user_id'),
                        'industry_id' => $this->put('industry_id'),
                        'skill_id' => $skill_id,
                        'experience_id' => $this->put('experience_id'),
                        'team_id' => $team_id,
                        //'personality' => $this->security->xss_clean($this->put('personality')),
                    ];
                    
                    if ($this->put('personality') != '') {
                        $formArray['personality'] = $this->security->xss_clean($this->put('personality'));
                    }
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'type'=>'0'])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') ,'type'=>'0']);
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
    
    public function factors_get() {

        $factorsArray = [];
        

        $factors = $this->db->get_where('tbl_factors', ['status' => '0'])->result();
        if (!empty($factors)) {
            foreach ($factors as $val) {
                $factorsArray[] = ['id' => $val->id, 'name' => html_entity_decode($val->name)];
            }
        }
       

        if (!empty($factorsArray)) {

            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found!',
                        'data' => $factorsArray,
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
    
    public function xaitwo_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                    // ['field' => 'members', 'label' => 'members', 'rules' => 'required|numeric',
                    //     'errors' => [
                    //         'required' => 'Members is required',
                    //         'numeric' => 'Members should be numeric',
                    //     ],
                    // ],
                    ['field' => 'factors', 'label' => 'factors', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Factors is required',
                        ],
                    ],
//                    ['field' => 'available_days', 'label' => 'available_days', 'rules' => 'required',
//                        'errors' => [
//                            'required' => 'Available Days is required',
//                        ],
//                    ],
//                    ['field' => 'available_time', 'label' => 'available_time', 'rules' => 'required',
//                        'errors' => [
//                            'required' => 'Available time is required',
//                        ],
//                    ],
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

//                    print_r($formArray);   die;
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'type'=>'0'])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'type'=>'0']);
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
    
    public function xaithree_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                        'rate' => $this->security->xss_clean($this->input->post('rate')),
                        'accessment' => $this->security->xss_clean($this->input->post('accessment')),
                        'communication_id' => $this->security->xss_clean($this->input->post('communication_id')),
                        //'softskills_id' => $this->security->xss_clean($this->input->post('softskills_id')),
                    ];
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id]);
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
    //xaithreeNew
    public function xaithreeNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

            $check_key = $this->checktoken($token, $user_id);
            
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
                    /*['field' => 'frequency_id', 'label' => 'frequency_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Frequency is required',
                        ],
                    ],*/
                    
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
                        'rate' => $this->security->xss_clean($this->input->post('rate')),
                        'accessment' => $this->security->xss_clean($this->input->post('accessment')),
                        'communication_id' => $this->security->xss_clean($this->input->post('communication_id')),
                        'softskills_id' => (!empty($this->input->post('softskills_id')))?$this->security->xss_clean($this->input->post('softskills_id')):$this->security->xss_clean($this->input->post('other')),
                        'frequency_id' =>0,// $this->security->xss_clean($this->input->post('frequency_id')),
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
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'type'=>'0'])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'type'=>'0']);
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

 public function communication_get() {

        $factorsArray = [];


        $factors = $this->db->get_where('tbl_communications', ['status' => '0'])->result();
         $frequency = $this->db->get_where('tbl_frequency', ['status' => '0'])->result();
        if (!empty($factors)) {
            foreach ($factors as $val) {
                $factorsArray[] = ['id' => $val->id, 'name' => html_entity_decode($val->name)];
            }
        }
         $data = $this->db->get_where('tbl_softskill',['status'=>'0'])->result();
        $result = [];
        if ($data) {
            foreach($data as $val)
            {
                $result[] = [
                    'id' => $val->id,
                    'softskill' => $val->name,
                ]; 
            }
        }
        if($frequency) {
            foreach($frequency as $fr)
            {
                $frequencyData[] = [
                    'id' => $fr->id,
                    'frequency' => ucwords($fr->type),
                ]; 
            }
        }


        if (!empty($factorsArray)) {

            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found!',
                        'data' => $factorsArray,
                        'softskills' => $result,
                        'frequencyData' => $frequencyData,
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
    
    public function xaifour_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'type'=>'0'])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'type'=>'0']);
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
    
    public function xaifinish_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                    ['field' => 'dream_job_description1', 'label' => 'dream_job_description1', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Dream job description1 is required',
                        ],
                    ],
                    ['field' => 'dream_job_description2', 'label' => 'dream_job_description2', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Dream job description2 is required',
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
                        'motivation' => $this->security->xss_clean($this->input->post('motivation')),
                        'language' => $this->security->xss_clean($this->input->post('language')),
                        'dream_job_description1' => $this->security->xss_clean($this->input->post('dream_job_description1')),
                        'dream_job_description2' => $this->security->xss_clean($this->input->post('dream_job_description2')),
                    ];
                    
                     $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'type'=>'0'])->row();

                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                        $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = $data->industry_id;
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'type'=>'0']);
                        $effected = $this->db->affected_rows();
                        $industry = $exist->industry_id;
                    }

                    $result = [
                        'industry' => $industry,
                        'xaistatus' => '1',
                    ];

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                                     'data' => $result,
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                     'data' => $result,
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

public function verificationstatus_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');

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
                    ['field' => 'verificationstatus', 'label' => 'verificationstatus', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Verification status code is required',
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
                    
                    $this->db->update('logincr', ['verificationstatus' => $this->security->xss_clean($this->input->post('verificationstatus'))], ['id' => $user_id]);
                   
                    if ($this->db->affected_rows() > 0) {

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
    
    public function calender_post() {
        
        $token = $this->input->get_request_header('Secret-Key');
        $id = $this->input->post('user_id');
        $teamid = $this->input->post('team_id');
        
        if ($token != '' && $id != '' && $teamid != '') {
            
            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {

                $task = $this->db->get_where('assigntask', ['spid' => $id, 'teamid' => $teamid])->result();
//                echo $this->db->last_query(); die;

                if (!empty($task)) {
                    foreach ($task as $val) {
                        $taskData[] = [
                            'id' => $val->id,
                            'teamid' => $val->teamid,
                            'userid' => $val->userid,
                            'title' => $val->title,
                            'taskstatus' => $val->taskstatus ? $val->taskstatus : '',
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

/*public function taskcomplete_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {

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
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                    ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comments is required',
                        ],
                    ],
                    ['field' => 'task_status', 'label' => 'task_status', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task status is required',
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
                    $data = $this->db->get_where('assigntask', ['id' => $this->input->post('task_id')])->row();

                    if (!empty($data)) {
                        $formArray = [
                            'task_id' => $data->id,
                            'user_id' => $data->userid,
                            'sp_id' => $data->spid,
                            'team_id' => $data->teamid,
                            'comments' => $this->input->post('comments'),
                            'task_status' => $this->input->post('task_status'),
                            'created_by' => $user_id,
                        ];


                        $this->db->trans_begin();

                        $this->db->insert('tbl_task_status', $formArray);
                        $this->db->update('assigntask', ['taskstatus' => $this->input->post('task_status')], ['id' => $data->id]);

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Something went wrong!',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        } else {
                            $this->db->trans_commit();
                            $this->response(
                                    [
                                        'status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Your comments added successfully!',
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Task not exist!',
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
    }*/
  public function taskcomplete_post() {
        $errorUploadType = $statusMsg = $fileData = $fileData1 = ''; 
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {

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
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                    ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comments is required',
                        ],
                    ],
                    ['field' => 'task_status', 'label' => 'task_status', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task status is required',
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
                    $data = $this->db->get_where('assigntask', ['id' => $this->input->post('task_id')])->row();

                    if (!empty($data)) {
                        /*---------------------- Image Upload-----------------*/
                    if(!empty($_FILES['files']['name']) && count(array_filter($_FILES['files']['name'])) > 0){ 
                $filesCount = count($_FILES['files']['name']); 
                for($i = 0; $i < $filesCount; $i++){ 
                    $_FILES['file']['name']     = $_FILES['files']['name'][$i]; 
                    $_FILES['file']['type']     = $_FILES['files']['type'][$i]; 
                    $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$i]; 
                    $_FILES['file']['error']     = $_FILES['files']['error'][$i]; 
                    $_FILES['file']['size']     = $_FILES['files']['size'][$i]; 
                     
                    // File upload configuration 
                    $uploadPath = './upload/tasks/image/'; 
                    $config['file_name'] = uniqid().'_'.$_FILES['file']['name'];
                    $config['upload_path'] = $uploadPath; 
                    $config['allowed_types'] = 'jpg|jpeg|png|gif'; 
                    $this->load->library('upload', $config); 
                    $this->upload->initialize($config); 
                     
                    // Upload file to server 
                    if($this->upload->do_upload('file')){ 
                        // Uploaded file data 
                        $fileData = $this->upload->data(); 
                        $uploadData[$i]['file_name'] = $fileData['file_name']; 
                    }else{  
                        $errorUploadType .= $this->upload->display_errors(); 
                    } 
                }

                 $errorUploadType = !empty($errorUploadType)?'<br/>File Type Error: '.trim($errorUploadType, ' | '):''; 
                if(!empty($uploadData)){ 
                   $fileData = $uploadData;
                }else{ 
                   
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => "Sorry, there was an error uploading your file.".$errorUploadType,
                    ]);
                    
                } 
                } 
                

                /*---------------------- Image Upload-----------------*/
                if(!empty($_FILES['files1']['name']) && count(array_filter($_FILES['files1']['name'])) > 0){ 
                $filesCount1 = count($_FILES['files1']['name']); 
                for($i = 0; $i < $filesCount1; $i++){ 
                    $_FILES['file1']['name']     = $_FILES['files1']['name'][$i]; 
                    $_FILES['file1']['type']     = $_FILES['files1']['type'][$i]; 
                    $_FILES['file1']['tmp_name'] = $_FILES['files1']['tmp_name'][$i]; 
                    $_FILES['file1']['error']     = $_FILES['files1']['error'][$i]; 
                    $_FILES['file1']['size']     = $_FILES['files1']['size'][$i]; 
                     
                    // File upload configuration 
                    $uploadPath1 = './upload/tasks/image1/'; 
                    $config2['file_name'] = uniqid().'_'.$_FILES['file1']['name'];
                    $config2['upload_path'] = $uploadPath1; 
                    $config2['allowed_types'] = 'jpg|jpeg|png|gif'; 
                    $this->load->library('upload', $config2); 
                    $this->upload->initialize($config2); 
                     
                    // Upload file to server 
                    if($this->upload->do_upload('file1')){ 
                        // Uploaded file data 
                        $fileData1 = $this->upload->data(); 
                        $uploadData1[$i]['file_name'] = $fileData1['file_name']; 
                    }else{  
                        $errorUploadType1 .= $this->upload->display_errors(); 
                    } 
                }

                 $errorUploadType1 = !empty($errorUploadType1)?'<br/>File Type Error: '.trim($errorUploadType1, ' | '):''; 
                if(!empty($uploadData1)){ 
                   $fileData1 = $uploadData1;
                }else{ 
                   
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => "Sorry, there was an error uploading your file.".$errorUploadType1,
                    ]);
                    
                } 
                } 
               



                        /*--------------------- Video Upload---------------*/
                        if(!empty($_FILES['video']['name']))
                        {
                            $mm = explode('.', $_FILES['video']['name']);
                            $ext = strtolower(end($mm));
                            $config1['file_name'] = md5(uniqid()).'.'.$ext;
                            $config1['upload_path'] = './upload/tasks/video/';
                            $config1['allowed_types'] = 'mp4|mov|mpeg4|avi|wmv|flv|3gpp|flv|webm|';
                          //  $config1['max_size']  = '100';
                         
                            
                            $this->load->library('upload', $config1);
                             $this->upload->initialize($config1);
                            
                            if ( !$this->upload->do_upload('video')){
                                $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->upload->display_errors(),
                              ]);
                            }
                            else{
                                $video_name = $this->upload->data('file_name');
                                
                            }
                        }

                         /*--------------------- Video Upload second---------------*/
                        if(!empty($_FILES['video1']['name']))
                        {
                            $mm = explode('.', $_FILES['video1']['name']);
                            $ext = strtolower(end($mm));
                            $config3['file_name'] = md5(uniqid()).'.'.$ext;
                            $config3['upload_path'] = './upload/tasks/video1/';
                            $config3['allowed_types'] = 'mp4|mov|mpeg4|avi|wmv|flv|3gpp|flv|webm|';
                          //  $config1['max_size']  = '100';
                         
                            
                            $this->load->library('upload', $config3);
                             $this->upload->initialize($config3);
                            
                            if ( !$this->upload->do_upload('video1')){
                                $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->upload->display_errors(),
                              ]);
                            }
                            else{
                                $video_name1 = $this->upload->data('file_name');
                                
                            }
                        }

                        

                        $formArray = [
                            'task_id' => $data->id,
                            'user_id' => $data->userid,
                            'sp_id' => $data->spid,
                            'team_id' => $data->teamid,
                            'comments' => $this->input->post('comments'),
                            'task_status' => $this->input->post('task_status'),
                            'created_by' => $user_id,
                            'video_path' => (!empty($video_name))?$video_name:NULL,
                            'video_path1' => (!empty($video_name1))?$video_name1:NULL,
                        ];


                        $this->db->trans_begin();

                        $this->db->insert('tbl_task_status', $formArray);
                        $insert_id = $this->db->insert_id();
                        $my_new_data = $my_new_data1 = array();
                        //before image
                        if($fileData)
                        {
                            foreach($fileData as $fn)
                            {
                                $my_new_data = [
                                    'task_id' => $data->id,
                                    'status_id' => $insert_id,
                                    'image_path' => $fn['file_name']
                                ];
                                $this->db->insert('tbl_task_status_image',$my_new_data);
                            }
                            
                        }
                        //after image
                        if($fileData1)
                        {
                            foreach($fileData1 as $fn1)
                            {
                                $my_new_data1 = [
                                    'task_id' => $data->id,
                                    'status_id' => $insert_id,
                                    'image_path' => $fn1['file_name'],
                                    'type' => '1'

                                ];
                                $this->db->insert('tbl_task_status_image',$my_new_data1);
                            }
                            
                        }
                        $this->db->update('assigntask', ['taskstatus' => $this->input->post('task_status') , 'is_reassign'=>'0' , 'comments' => $this->input->post('comments')], ['id' => $data->id]);

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Something went wrong!',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        } else {
                            $this->db->trans_commit();
                            $this->response(
                                    [
                                        'status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Your comments added successfully!',
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Task not exist!',
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
    
    public function taskdetails_get($user_id = NULL, $taskid = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $result = $this->db->get_where('assigntask', ['id' => $taskid])->result();
//                echo '<pre>';
//                print_r($result);
//                die;
                $dataArray = [];
                foreach ($result as $val) {
                    $dataArray[] = [
                        'id' => $val->id,
                        'title' => $val->title,
                        'describe' => $val->describe,
                        'taskstatus' => $val->taskstatus ? $val->taskstatus : 'Pending',
                        'comments' => $val->comments ? $val->comments : '',
                        'taskdate' => $val->taskdate,
                        'task_name' => $val->task_name,
                        'member_type' => $val->member_type,
                        'start_time' => $val->start_time,
                        'end_time' => $val->end_time,
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data found successfully!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Data not found successfully!',
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
    
    public function basicinfo_put() {
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
                    ['field' => 'usertype', 'label' => 'usertype', 'rules' => 'required',
                'errors' => [
                   'required' => 'User type is required',
            ],
            ],
                    ['field' => 'contact', 'label' => 'contact', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Contact number is required',
                        ],
                    ],
                    ['field' => 'ssnnum', 'label' => 'ssnnum', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Ssnnum is required',
                        ],
                    ],
                    ['field' => 'address', 'label' => 'address', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Address is required',
                        ],
                    ],
                    ['field' => 'country', 'label' => 'country', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Country is required',
                        ],
                    ],
                    ['field' => 'city', 'label' => 'city', 'rules' => 'required',
                        'errors' => [
                            'required' => 'City is required',
                        ],
                    ],
                    ['field' => 'postalcode', 'label' => 'postalcode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Postalcode is required',
                        ],
                    ],
                    ['field' => 'terms', 'label' => 'terms', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Terms is required',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
//                                'message' => $this->form_validation->error_array(),
                                'message' => strip_tags(validation_errors()),
                    ]);
                } else {
                    
                    $formArray = [
                        'contact' => $this->put('contact'),
                        'ssnnum' => $this->put('ssnnum'),
                        'address' => $this->put('address'),
                        'country' => $this->put('country'),
                        'city' => $this->put('city'),
                        'postalcode' => $this->put('postalcode'),
                        'terms' => $this->put('terms'),
                        'usertype' => $this->put('usertype'),
                        'switch_account' => $this->put('usertype'),
                    ];
                    $this->db->update('logincr', $formArray, ['id' => $user_id]);
//                    echo $this->db->last_query();
                     $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
                    //  echo '<pre>';
                    //  print_r($user);
                    //  die;
                     $success = [
                          'id' => $user->id,
                        'token_security' => $user->token_security,
                        'photo' => $user->image != '' ? base_url('upload/users/' . $user->profile_pic) : $user->profile_pic,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'contact' => $user->contact,
                        'ssnnum' => $user->ssnnum,
                        'address' => $user->address,
                        'country' => $user->country,
                        'city' => $user->city,
                        'postalcode' => $user->postalcode,
                        'questionstatus' => '0',
                        'accountstatus' => '0',
                        'usertype' => $user->usertype,
                        'user_service' => '',
                        'xaistatus' => '0',
                        'industry' => '0',
                        'social_login' => $user->terms != 'Yes' ? false : true,
                        'is_fb' => $user->social_media_type=='1' ? true : false,
                        'is_google' => $user->social_media_type=='0' ? true : false,
                        'password_change' => $user->password ? true : false,
                        'switch_account' => $user->switch_account,
                    ];
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your basic information updated succesfully!',
                                    'data' => $success,
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already added!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                    'data' => $success,
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
    public function backupinterestponoff_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {

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
                    ['field' => 'interested', 'label' => 'interested', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Interested is required',
                            'numeric' => 'Interested should be numeric',
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
                    $this->db->update('logincr', ['interested_in_backup' => $this->input->post('interested')], ['id' => $this->input->post('user_id')]);

                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                [
                                    'status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your interested updated successfully!',
                                    'interested' => $this->input->post('interested'),
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your interested already updated!',
                                    'interested' => $this->input->post('interested'),
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
    
    public function mybackup_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $result = $this->db->select('a.*,CONCAT(b.firstname, " "  , b.lastname) AS name,b.image,b.address,b.country,b.city,b.postalcode')
                                ->from('tbl_backup as a')
                                ->join('logincr as b', 'a.backup_id = b.id', 'left')->where('a.user_id',$user_id)
                                ->get()->result();
                $dataArray = [];
                foreach ($result as $val) {
                    $dataArray[] = [
                        'id' => $val->id,
                        'name' => $val->name,
                        'image' => base_url($val->image),
                        'address' => $val->address,
                        'country' => $val->country,
                        'city' => $val->city,
                        'postalcode' => $val->postalcode,
                        'rating' => $this->Common_model->getrating($val->backup_id),
                    ];
                }
                if ($dataArray) {

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
    
    public function myofferletter_get($user_id = NULL) {
        
        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($user_id, $token);

        $result = $this->db->select('a.*,b.teamname,b.teamimage,CONCAT(c.firstname, " "  ,c.lastname) AS user_name')
                        ->from('tbl_offer_letter as a')
                        ->join('myteams as b', 'a.team_id = b.id', 'left')
                        ->join('logincr as c', 'a.user_id = c.id', 'left')
                        ->where(['a.provider_id' => $user_id, 'a.status!=' => '3'])
                        ->where('c.id IS NOT NULL')
                        ->order_by('a.created_at DESC')
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
    
    public function addsignature_post() {

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
            ['field' => 'offer_id', 'label' => 'offer_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Interested is required',
                    'numeric' => 'Offer id should be numeric',
                ],
            ],
            ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Interview id is required',
                    'numeric' => 'Interview id should be numeric',
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
            if (!empty($_FILES['provider_signature']['name'])) {
                $file = $_FILES['provider_signature']['name'];
                $name = 'provider_signature';
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
                    $result = $this->db->get_where('tbl_offer_letter', ['id' => $this->input->post('offer_id')])->row();
                   // print_r($result); die;
                    if ($result->status == '2') {
                        $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => 'You have already accept your offer letter!',
                        ]);
                    } elseif ($result->status == '3') {
                         
                        $this->response(
                                ['status' => 'false',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                    'message' => 'You have already reject your offer letter!',
                        ]);
                    } else {
                        $signa = $file_data['file'];

                        $this->db->trans_begin();
                        $this->db->update('tbl_offer_letter', ['provider_signature' => $signa, 'status' => '2'], ['id' => $this->input->post('offer_id')]);
                        if($this->input->post('interview_id')!=0)
                        {
                            $this->db->update('scheduleinterview', ['i_status' => '2', 'status' => 'Approved'], ['id' => $this->input->post('interview_id')]);
                        $my_new_data = $this->db->get_where('scheduleinterview',['id'=>$this->input->post('interview_id')])->row();
                        $this->db->insert('tbl_leave_history',['leave_by'=>$my_new_data->userid , 'team_id' => $my_new_data->teamid , 'user_id'=>$my_new_data->spid , 'leave_type'=>1 ,'is_in'=>5]);
                        $this->db->insert('tbl_leave_history',['leave_by'=>$my_new_data->userid , 'team_id' => $my_new_data->teamid , 'user_id'=>$my_new_data->spid , 'leave_type'=>2 ,'is_in'=>5]);
                        }
                        
                        
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
                                        'message' => 'Signature add successfully!',
                            ]);
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

    public function rejectoffer_post() {

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
            ['field' => 'offer_id', 'label' => 'offer_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Interested is required',
                    'numeric' => 'Offer id should be numeric',
                ],
            ],
            ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Interview id is required',
                    'numeric' => 'Interview id should be numeric',
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
            $result = $this->db->get_where('tbl_offer_letter', ['id' => $this->input->post('offer_id')])->row();
            if(!empty($this->input->post('message')))
            {
             $myData = [

                'feedback_type' =>'1',
                'main_id' => $this->input->post('offer_id'), 
                'user_type' => '0',
                'user_id' => $this->input->post('user_id'),
                'message' => $this->input->post('message'),
            ];
            $this->db->insert('tbl_all_feedback',$myData);
            }
            if ($result->status == '2') {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'You have already accept your offer letter!',
                ]);
            } elseif ($result->status == '3') {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            'message' => 'You have already relect your offer letter!',
                ]);
            } else {

                $this->db->trans_begin();

                $this->db->update('tbl_offer_letter', ['status' => '3'], ['id' => $this->input->post('offer_id')]);
                if($this->input->post('interview_id')!=0)
                {
                $this->db->update('scheduleinterview', ['i_status' => '4', 'status1' => 'Reject' , 'status' => 'pending'], ['id' => $this->input->post('interview_id')]);
                }

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
                                'message' => 'Offer rejected successfully!',
                    ]);
                }
            }
        }
    }
    
     public function addcertification_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $this->input->post('user_id');
        $auth = $this->authentication($this->input->post('user_id'), $token);

        $data = $this->input->post();

        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should be numeric',
                ],
            ],
            ['field' => 'certification_id', 'label' => 'certification_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Certification is required',
                    'numeric' => 'Certification should be numeric',
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
                'user_id' => $this->input->post('user_id'),
                'certification_id' => $this->input->post('certification_id'),
            ];

            if (!empty($_FILES['certificate']['name'])) {
                $file = $_FILES['certificate']['name'];
                $name = 'certificate';
                $path = 'certificate';
                $type = 'jpeg|jpg|png|pdf';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $formArray['certificate'] = $file_data['file'];
                }
            }

            $this->db->insert('tbl_user_certificate', $formArray);
            if ($this->db->insert_id() > 0) {
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Certificate add successfully!',
                ]);
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Something went wrong please try aftre some time!',
                ]);
            }
        }
    }
//new
 public function addcertificationNew_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $this->input->post('user_id');
        $auth = $this->authentication($this->input->post('user_id'), $token);

        $data = $this->input->post();

        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should be numeric',
                ],
            ],
            ['field' => 'certification_id', 'label' => 'certification_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Certification is required',
                    'numeric' => 'Certification should be numeric',
                ],
            ],
            ['field' => 'passing_date', 'label' => 'passing_date', 'rules' => 'required',
                'errors' => [
                    'required' => 'Passing date is required',
                ],
            ],
            ['field' => 'renewal_date', 'label' => 'renewal_date', 'rules' => 'required',
                'errors' => [
                    'required' => 'Renewal date is required',
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
                'user_id' => $this->input->post('user_id'),
                'certification_id' => $this->input->post('certification_id'),
                'passing_date' => $this->input->post('passing_date'),
                'renewal_date' => $this->input->post('renewal_date'),
                'continue_passing_date' => $this->input->post('continue_passing_date'),
                'continue_renewal_date' => $this->input->post('continue_renewal_date'),
               // 'additional_skills' => $this->input->post('additional_skills'),
            ];

            if (!empty($_FILES['certificate']['name'])) {
                $file = $_FILES['certificate']['name'];
                $name = 'certificate';
                $path = 'certificate';
                $type = 'jpeg|jpg|png|pdf';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $formArray['certificate'] = $file_data['file'];
                }
            }
            //working license
             if (!empty($_FILES['license']['name'])) {
                 $path1 = 'upload/license/';
                $config2['upload_path'] = './upload/license/';
                $config2['allowed_types'] = 'jpeg|jpg|png|pdf';
                $config2['max_size']  = '5000';
                
                $this->upload->initialize($config2);
                
                if ( ! $this->upload->do_upload('license')){
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $this->upload->display_errors(),
                    ]);
                 
                }
                else{
                    $formArray['license'] = $path1.$this->upload->data('file_name');
                }
            }
            //certificate continue education
             if (!empty($_FILES['certification_education']['name'])) {
                 $path = 'upload/certification_education/';
                $config1['upload_path'] = './upload/certification_education/';
                $config1['allowed_types'] = 'jpeg|jpg|png|pdf';
                $config1['max_size']  = '5000';
                
                $this->upload->initialize($config1);
                
                if ( ! $this->upload->do_upload('certification_education')){
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $this->upload->display_errors(),
                    ]);
                 
                }
                else{
                    $formArray['certification_education'] = $path.$this->upload->data('file_name');
                }
            }

            $this->db->insert('tbl_user_certificate', $formArray);
            if ($this->db->insert_id() > 0) {
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Certificate add successfully!',
                ]);
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Something went wrong please try aftre some time!',
                ]);
            }
        }
    }
    public function mycertificates_get($user_id = NULL, $provider_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $provider_id = $this->uri->segment(4);

        $auth = $this->authentication($user_id, $token);
        if ($provider_id) {
            $user_id = $provider_id;
        }
        $res = [];       
        $res = $this->db->select('a.*,b.title')
                        ->from('tbl_user_certificate as a')
                        ->join('tbl_certification as b', 'b.id = a.certification_id', 'left')
                        ->where(['a.user_id' => $user_id])
                        ->order_by('a.id DESC')
                        ->get()->result();

        if ($res) {
            foreach ($res as $val) {
                $result[] = [
                    'id' => $val->id,
                    'title' => $val->title,
                    'mime_type' => pathinfo($val->certificate, PATHINFO_EXTENSION),
                    'certificate' => base_url($val->certificate),
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
                        'data' => [],
            ]);
        }
    }
    
     public function deletecertificats_delete() {
         
        $token = $this->input->get_request_header('Secret-Key');        
        $data = json_decode(file_get_contents("php://input"), true);
       
        $this->form_validation->set_data($this->delete());
        $user_id = $data['user_id'];
        $auth = $this->authentication($user_id, $token);        
        $config = [            
            ['field' => 'certificate_id', 'label' => 'certificate_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Certificate is required',
                    'numeric' => 'Certificate should be numeric',
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
             $this->db->where(['id' => $data['certificate_id'], 'user_id' => $user_id]);
            $this->db->delete('tbl_user_certificate');
            if ($this->db->affected_rows() > 0) {
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'message' => 'Certificate delete successfully!',
                ]);
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'Something went wrong please try aftre some time!',
                ]);
            }
        }
    }
    
     // team details and leave details , this is for service provider
     public function teamLeaveList_post($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $teams = $this->db->select('a.*,b.name as language')->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')->where(['a.user_id' => $user_id, 'a.status' => '0'])->get()->result();
//                echo '<pre>';
//                print_r($teams);
//                die;
                if ($teams) {
                    foreach ($teams as $t) {
                 $leave_data = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$t->id , 'is_out'=>0.0])->row('total_leave');
                  $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$t->id  , 'is_in'=>0.0])->row('taken_leave');
                        $result[] = [
                            'teamid' => $t->id,
                            'user_id' => $t->user_id,
                            'reqiured_members' => $t->members,
                            'language' => $t->language,
                            'teamname' => $t->teamname,
                            'teamimage' => base_url($t->teamimage),
                            'description' => $t->description,
                            'totalmember' => '',
                            'leave_data' => [ 
                                'total_leave' => ($leave_data!=null)?$leave_data:0,
                                'used_leave' => ($taken_leave!=null)?$taken_leave:0
                              ]
                        ];
               
                       
                    }

                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Record found successfully!',
                        'data' => $result,
                    ];
                } else {
                    $response = [
                        'responsecode' => '404',
                        'status' => 'false',
                        'message' => 'Record not found!',
                    ];
                }
            } else {
                $response = [
                    'responsecode' => '403',
                    'status' => 'false',
                    'message' => 'Invalid Token!',
                ];
            }
        } else {
            $response = [
                'responsecode' => '502',
                'status' => 'false',
                'message' => 'Unauthorised Access!',
            ];
        }
        echo json_encode($response);
    }
    //Apply leave
    public function applyLeave_post()
    {
        $team_id = $this->input->post('team_id');
        $leave_type = $this->input->post('leave_type');//employee id
        $from_date = $this->input->post('from_date');//employee id
        $to_date = $this->input->post('to_date');//employee id
        $interval = $this->input->post('interval');//employee id
        $leave_reason = $this->input->post('leave_reason');//employee id
        $userid = $this->input->post('userid');//employee id
        $tokenid = $this->input->get_request_header('Secret-Key');//employee id
        $check_key = $this->authentication($userid , $tokenid);
        $now = time(); // or your date as well
        $your_date = strtotime($from_date);
        $datediff = $now - $your_date;
        $days = abs(round($datediff / (60 * 60 * 24)));
        $date1_ts = strtotime($from_date);
        $date2_ts = strtotime($to_date);
        $diff = $date2_ts - $date1_ts;
        $days_between =  round($diff / 86400);
           $config = [
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                   
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => $this->form_validation->error_array(),
                    ]);
            } else {
                if($leave_type!=1 && $days<15)
                {
                     return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'You can only apply leave before 15 days to take leave',
                    ]);   
                }else if($leave_type==1 && $days<1){
                     return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'You can only apply sick leave before 1 day!',
                    ]);
                }else{
                  $in_leave = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_out'=>0.0 , 'user_id'=>$userid , 'leave_type'=>$leave_type])->row('total_leave');//all in leave
                   $out_leave = $this->db->select('sum(is_out) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_in'=>0.0 , 'user_id'=>$userid , 'leave_type'=>$leave_type])->row('total_leave');//all out leave
                   $balance = ($in_leave-$out_leave);
                   $leave_day = ($interval==1)?0.5:1.0;
                   $total_leave = ($leave_day*$days_between);
                  if($balance>0)
                  {
                   $leave_data_insert = [
                    'team_id' => $team_id,
                    'user_id' => $userid,
                    'leave_type' => $leave_type,
                    'is_in' => 0.0,
                    'is_out' => $total_leave,
                    'leave_apply' => $total_leave,
                    'leave_reason' => $leave_reason

                   ];
                   $this->db->insert('tbl_leave_history',$leave_data_insert);
                   if($this->db->affected_rows()>0)
                   {
                    $this->db->set('out_leave', 'out_leave+'.$total_leave, FALSE);
                    $this->db->where(['team_id'=>$team_id ,'user_id'=>$userid]);
                    $this->db->update('tbl_my_leave');
                    return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave applied successfully...',
                                'data' => $leave_data_insert
                              
                    ]);
                   }else{
                    return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Leave can\'t apply',
                              
                    ]);
                   }
                  
                  }else{
                     return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No leave in your account',
                                'leave_balance' => $balance
                    ]);
                  }
              }
            }

    }
     public function approveLeave_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $lid = $this->input->post('lid');
        $userid = $this->input->post('userid');
        $teamid = $this->input->post('teamid');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'lid', 'label' => 'lid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Leave id  is required',
                            'numeric' => 'Leave id  should be numeric',
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
            $this->db->set('is_approved','1')->where(['id'=>$lid , 'team_id'=>$teamid , 'user_id'=>$userid])->update('tbl_leave_history');
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave approved successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave already approved successfully...',
                            ]);
            }
           }
       }
    public function rejectLeave_post()
    {
         $tokenid = $this->input->get_request_header('Secret-Key');
        $lid = $this->input->post('lid');
        $userid = $this->input->post('userid');
        $teamid = $this->input->post('teamid');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'lid', 'label' => 'lid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Leave id  is required',
                            'numeric' => 'Leave id  should be numeric',
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
            $this->db->set('is_approved','2')->where(['id'=>$lid , 'team_id'=>$teamid , 'user_id'=>$userid])->update('tbl_leave_history');
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave rejected successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave already rejected successfully...',
                            ]);
            }
           }
    }
    //find all sp and team scheduled interview details by date
     public function myteamByDate_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');
        $interviewDate = $this->input->get_request_header('interviewDate');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.id,a.teamid ,a.interviewDate , a.interviewTime,a.spid ,a.status,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.profile_pic as image , c.teamimage,c.teamname")
                        ->from('scheduleinterview as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                        ->join('myteams as c', 'c.id = a.teamid', 'left')
                        ->where('b.id IS NOT NULL')
                        ->where('c.teamname IS NOT NULL')
                        ->where(['a.status' => 'pending'])
                        ->where(['a.spid' => $user_id])
                        ->where(['a.interviewDate' => $interviewDate])
                        ->order_by('a.id','DESC')
                        ->group_by('a.teamid')
                        ->get();
//                echo $this->db->last_query(); die;
                $result = $query->result();
                foreach ($result as $val) {
                    $dataArray[] = [
                        'spid' => $val->spid,
                        'id' => $val->id,
                        'interviewid' => $val->id,
                        'teamid' => $val->teamid,
                        'username' => $val->name,
                        'userimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        'teamname' => $val->teamname,
                        'interviewDate' => $val->interviewDate,
                        'interviewTime' => $val->interviewTime,
                        'status' => $val->status,
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Team  found!',
                                'data' => $dataArray,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Team not found!',
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
    
    /*--------------------- Invoice Genrate Part----------------------*/
    public function allWorkDetails_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $sp_id = $this->input->get_request_header('spid');
        if ($token != '' && $sp_id != '') {

            $check_key = $this->checktoken($token, $sp_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $query = $this->db->select("a.id as taskid ,a.userid as userid ,a.title as tasktitle , a.create_at as date , a.taskstatus ,a.start_time , a.end_time , CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid")
                        ->from('assigntask as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                         ->join('myteams as c', 'c.id = a.teamid', 'left')
                        ->where(['a.taskstatus'=>'Approved' , 'a.spid'=>$sp_id])
                        ->get();
                $result = $query->result();
                foreach ($result as $val) {
                $datetime1 = new DateTime($val->start_time);
                $datetime2 = new DateTime($val->end_time);
                $interval = $datetime1->diff($datetime2);
            
                    $dataArray[] = [
                        'spid' => $sp_id,
                        'user_id' => $val->userid,
                        'teamid' => $val->teamid,
                         'taskid' => $val->taskid,
                        'customer_name' => $val->name,
                        'teamimage' => $val->teamimage ? base_url($val->teamimage) : base_url('upload/users/photo.png'),
                        'teamname' => $val->teamname,
                        'taskstatus' => $val->taskstatus,
                        'taskhours' =>$interval->format('%h Hours'),
                        'date' => date('d-m-Y', strtotime($val->date)),
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
    public function getInvoiceData_post($value='')
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');
        $taskid = $this->input->post('taskid');
        $total_work_hours = $this->input->post('total_work_hours');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
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
            
             $all_leave = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$teamid , 'is_out'=>0.0 , 'user_id'=>$spid])->row('total_leave');
            $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$teamid , 'is_in'=>0.0 , 'user_id'=>$spid])->row('taken_leave');
            $rate = $this->db->select('pay_rate')->get_where('tbl_offer_letter',['team_id'=>$teamid , 'user_id'=>$userid , 'provider_id'=>$spid , 'status'=>'2'])->row('pay_rate');
            $task_details = $this->db->get_where('assigntask',['id'=>$taskid])->row();
            $pay_rate = ($rate)?$rate:"0";
            $user =  $this->db->select('*')->get_where('logincr',['id'=>$userid])->row();
            $total_l = ($all_leave)?$all_leave:0.0;
            $taken_l = ($taken_leave)?$taken_leave:0.0;
            $datetime1 = new DateTime($task_details->start_time);
            $datetime2 = new DateTime($task_details->end_time);
            $interval = $datetime1->diff($datetime2);
            $amount = ($pay_rate*$total_work_hours);
            $dataArray = [
            'customer_name' => $user->firstname.' '.$user->lastname,
            'userid' => $user->id,
            'spid' => $spid,
            'teamid' => $teamid,
            'leave_balance' => number_format($total_l-$taken_l,2), 
            'leave_taken' => "$taken_l",
            'total_work_hours' =>   $total_work_hours,
            'rate' => $pay_rate,
            'amount' =>"$amount",

            ];
           
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
           }
    
    }
     //for submit invoice
    public function submitInvoice_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');
        $taskid = $this->input->post('taskid');
        $leave_balance = $this->input->post('leave_balance');
        $leave_taken = $this->input->post('leave_taken');
        $total_work_hours = $this->input->post('total_work_hours');
        $rate = $this->input->post('rate');
        $amount = $this->input->post('amount');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
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
           
            $check_data = $this->db->get_where('tbl_invoice',['task_id'=>$taskid])->num_rows();
            if($check_data<1)
            {
                $insertData = [
                    'invoice_id' => 'INV'.rand(1111111111,9999999999),
                    'user_id' => $userid,
                    'spid' => $spid,
                    'task_id' => $taskid,
                    'team_id' => $teamid,
                    'leave_balance' => $leave_balance,
                    'leave_taken' => $leave_taken,
                    'total_work_hours' => $total_work_hours,
                    'rate' => $rate,
                    'amount' => $amount
                ];
                $this->db->insert('tbl_invoice',$insertData);
                if($this->db->affected_rows()>0)
                {
                    $insertData['id'] = $this->db->insert_id();
                      $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Invoice generated successfully!',
                                'data' => $insertData,
                    ]);
                }else{
                 $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Invoice not generated',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);    
                }
            }else{
             $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Invoice already generated for this task!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);   
            }
           }
    }
     // get task according to team id and spid
    public function timesheetTask_get($user_id = NULL, $team_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {
                $result = $this->db->get_where('assigntask', ['teamid' => $team_id])->result();
//                echo '<pre>';
//                print_r($result);
//                die;
 $user_id = $this->db->select('user_id')->get_where('myteams',['id'=>$team_id])->row('user_id');        
            $check = $this->db->order_by('priority','ASC')->get_where('tbl_customer_policy_duration',['team_id'=>$team_id , 'user_id'=>$user_id])->result();
            $myData = [];
                if($check)
                {
                foreach($check as $cc)
                {
                $myData[] = [
                'id' => $cc->id,
                'title' => $cc->title,
                ];   
                }
                }
                $dataArray = [];
                foreach ($result as $val) {
                    $dataArray[] = [
                        'id' => $val->id,
                        'task_title' => $val->title,
                        'task_name' => $val->task_name,
                    ];
                }
                if (!empty($dataArray)) {

                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data found successfully!',
                                'data' => $dataArray,
                                'policy' => $myData,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Data not found successfully!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'data' => [],
                                'policy' => $myData,
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
    //for generate timesheet
    /*public function generateTimesheet_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $spid = $this->input->post('spid');
        $team_id = $this->input->post('team_id');
        $task_id = $this->input->post('task_id');
        $date = $this->input->post('date');
        $time = $this->input->post('time');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $break_time = $this->input->post('break_time');
        $meal_time = $this->input->post('meal_time');
        $ot = $this->input->post('ot');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                     ['field' => 'date', 'label' => 'date', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Date is required',
                        ],

                    ],
                     ['field' => 'time', 'label' => 'time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Time is required',
                        ],

                    ],
                     ['field' => 'start_time', 'label' => 'start_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Start time is required',
                        ],

                    ],
                    ['field' => 'end_time', 'label' => 'end_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'End time is required',
                        ],

                    ],
                    ['field' => 'break_time', 'label' => 'break_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Break time is required',
                        ],

                    ],
                     ['field' => 'meal_time', 'label' => 'meal_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Meal time is required',
                        ],

                    ],
                    ['field' => 'ot', 'label' => 'ot', 'rules' => 'required',
                        'errors' => [
                            'required' => 'OT is required',
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
            $check = $this->db->get_where('tbl_sp_timesheet',['team_id'=>$team_id ,'task_id'=>$task_id , 'date'=>$date])->num_rows();
            if($check<1)
            {
                $insertData = [
                    'spid' => $spid,
                    'team_id' => $team_id,
                    'task_id' => $task_id,
                    'date' => $date,
                    'time' => $time,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'break_time' => $break_time,
                    'meal_time' => $meal_time,
                    'ot' => $ot,
                ];
                $this->db->insert('tbl_sp_timesheet',$insertData);
                if($this->db->affected_rows()>0)
                {
                    $mm = $this->db->insert_id();
                    $insertData['id'] = "$mm";
                      $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet generated successfully!',
                                'data' => $insertData,
                    ]);
                }else{
                 $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet generated not generated!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);    
                }
           }else{
             $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet already added for date '.$date,
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]); 
           }
       }
    }*/
    //for generate timesheet
    public function generateTimesheet_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $spid = $this->input->post('spid');
        $team_id = $this->input->post('team_id');
        $task_id = $this->input->post('task_id');
        $date = $this->input->post('date');
        $time = $this->input->post('time');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $policy = $this->input->post('policy');
        $meal_time = $this->input->post('meal_time');
        $ot = $this->input->post('ot');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                     ['field' => 'date', 'label' => 'date', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Date is required',
                        ],

                    ],
                     ['field' => 'time', 'label' => 'time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Time is required',
                        ],

                    ],
                     ['field' => 'start_time', 'label' => 'start_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Start time is required',
                        ],

                    ],
                    ['field' => 'end_time', 'label' => 'end_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'End time is required',
                        ],

                    ],
                    ['field' => 'ot', 'label' => 'ot', 'rules' => 'required',
                        'errors' => [
                            'required' => 'OT is required',
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
            $check = $this->db->get_where('tbl_sp_timesheet',['team_id'=>$team_id ,'task_id'=>$task_id , 'date'=>$date])->num_rows();
            if($check<1)
            {
                $insertData = [
                    'spid' => $spid,
                    'team_id' => $team_id,
                    'task_id' => $task_id,
                    'date' => $date,
                    'time' => $time,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'ot' => $ot,
                ];
                $this->db->insert('tbl_sp_timesheet',$insertData);
                if($this->db->affected_rows()>0)
                {
                    $mm = $this->db->insert_id();
                    $insertData['id'] = "$mm"; 
                    $expo = explode('|',$policy);
                    if(count($expo)>0)
                    {
                       for($i=0; $i<count($expo); $i++)
                        {
                            $n = (isset($expo[$i]))?$expo[$i]:',';
                            $nnn = (isset($aa[1]))?$aa[1]:',';
                            $aa = explode(',',$n);
                            $tt = explode(':',$nnn);
                            $durData[] = [
                                'team_id' => $team_id,
                                'task_id' => $task_id,
                                'spid' => $spid,
                                'generate_id' => $mm,
                                'policy_id' => (isset($aa[0]))?$aa[0]:'',
                                'hours' => (isset($tt[0]))?$tt[0]:'',
                                'minutes' => (isset($tt[1]))?$tt[1]:''
                            ]; 
                        }
                        $this->db->insert_batch('tbl_dynamic_timesheet_relation',$durData);
                    }

                      $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet generated successfully!',
                                'data' => $insertData,
                   
                    ]);
                }else{
                 $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet generated not generated!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);    
                }
           }else{
             $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet already added for this date',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]); 
           }
       }
    }
    //get data for send timesheet
            public function getSendTimesheet_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $team_id = $this->uri->segment(4);
            $task_id = $this->uri->segment(5);
            $check_key = $this->authentication($spid , $tokenid);
            $check = $this->db->get_where('tbl_sp_timesheet',['team_id'=>$team_id ,'task_id'=>$task_id , 'spid'=>$spid ,'status'=>'0'])->result();
            if($check)
            {
            $total_hours = $total_min = $break_time_hours = $break_time_min = $meal_time_hours = $meal_time_min = $ot_time_hours = $ot_time_min = 0;
            foreach($check as $cc)
            {
            /* for find total hours----------*/
            $time1 = strtotime($cc->start_time);
            $time2 = strtotime($cc->end_time);
            $diff = abs($time2 - $time1);
            // Convert $diff to minutes
            $tmins = $diff/60;
            $hours = floor($tmins/60);
            $mins = $tmins%60;
            $total_hours+=$hours;
            $total_min+=$mins;
            
            /* for find meal time */
            $ee =explode(':',$cc->ot);
            $ot_time_hours+=$ee[0];
            $ot_time_min+= $ee[1];




            }
            if($total_min>=60)//for calculate total hours done
            {
            $mm = $total_min%60;
            $hh = floor($total_min/60);
            $total_hours+=$hh;
            $total_min=$mm;

            }
            if($ot_time_min>=60)//for calculate ot time
            {
            $mm3 = $ot_time_min%60;
            $hh3 = floor($ot_time_min/60);
            $ot_time_hours+=$hh3;
            $ot_time_min=$mm3;

            }
            $myData = [
            'spid' => $spid,
            'teamid' => $team_id,
            'task_id' => $task_id,
            'hours_done' => sprintf("%02d", $total_hours).':'.sprintf("%02d", $total_min),
            'ot' => sprintf("%02d", $ot_time_hours).':'.sprintf("%02d", $ot_time_min),
            'policy' => $this->myPolicy($spid , $team_id , $task_id),


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

             //for final timesheet send
    public function sendTimesheet_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $spid = $this->input->post('spid');
        $team_id = $this->input->post('team_id');
        $task_id = $this->input->post('task_id');
        $hours_done = $this->input->post('hours_done');
        $policy = $this->input->post('policy');
        $ot = $this->input->post('ot');
        $goal_complete = $this->input->post('goal_complete');
        $task_done = $this->input->post('task_done');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                     ['field' => 'hours_done', 'label' => 'hours_done', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Hours done is required',
                        ],

                    ],
                     ['field' => 'goal_complete', 'label' => 'goal_complete', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Goal complete is required',
                        ],

                    ],
                     ['field' => 'task_done', 'label' => 'task_done', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Task done is required',
                        ],

                    ],
                    ['field' => 'ot', 'label' => 'ot', 'rules' => 'required',
                        'errors' => [
                            'required' => 'OT is required',
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
                $insertData = [
                    'spid' => $spid,
                    'team_id' => $team_id,
                    'task_id' => $task_id,
                    'hours_done' => $hours_done,
                    'goal_complete' => $goal_complete,
                    'task_done' => $task_done,
                    'policy' => $policy,
                    'ot' => $ot,
                ];
                $this->db->insert('tbl_final_timesheet',$insertData);
                if($this->db->affected_rows()>0)
                {
                    $mm= $this->db->insert_id();
                    $insertData['id'] = "$mm" ;
                    $this->db->set(['status'=>'1' , 'timesheet_id'=>$mm])->where(['spid'=>$spid , 'team_id'=>$team_id , 'task_id'=>$task_id , 'status'=>'0'])->update('tbl_sp_timesheet');
                    $this->db->set(['status'=>'1' , 'final_timesheet_id'=>$mm])->where(['spid'=>$spid , 'team_id'=>$team_id , 'task_id'=>$task_id , 'status'=>'0'])->update('tbl_dynamic_timesheet_relation');
                      $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet send successfully!',
                                'data' => $insertData,
                    ]);
                }else{
                 $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet not send!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);    
                }
           
       }
    }
     // get timesheet history
            public function getTimesheetHistory_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $check_key = $this->authentication($spid , $tokenid);
            $check = $this->db->select('a.id as timesheet_id , b.teamimage , b.teamname,b.id as teamid , CONCAT(c.firstname, " "  , c.lastname) as spname , d.task_name , d.title , d.id as taskid')
                              ->from('tbl_sp_timesheet as a')
                              ->join('myteams as b' , 'b.id = a.team_id','left')
                              ->join('logincr as c' , 'c.id = a.spid','left')
                              ->join('assigntask as d','d.id = a.task_id','left')
                              ->where(['a.spid'=>$spid])
                              ->order_by('a.created_at','DESC')
                              ->get()
                              ->result();                          
            if($check)
            {
           
           foreach($check as $cc)
           {    
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
              // get timesheet history
            public function getTimesheetDetails_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $id = $this->uri->segment(4);
            $check_key = $this->authentication($spid , $tokenid);
            $cc = $this->db->select('a.* , b.teamimage , b.teamname,b.id as teamid , CONCAT(c.firstname, " "  , c.lastname) as spname , d.task_name , d.title , d.id as taskid')
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
            $sunday = $monday = $tuesday = $wednesday = $thursday = $friday = $saturday = '';
            $get_all_time = $this->db->select('created_at as date , task_done')->from('tbl_final_timesheet')->where(['spid'=>$cc->spid , 'team_id'=>$cc->team_id , 'task_id'=>$cc->task_id])->get()->result();
            if($get_all_time)
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
            }
           
            $myData[] = [
                'id' => $cc->id,
                'timesheet_id' => '000000'.$cc->id,
                'heading' => $cc->teamname.'('.$cc->title.')',
                'teamid' => $cc->teamid,
                'teamname' => $cc->teamname,
                'teamimage' => $cc->teamimage ? base_url($cc->teamimage) : base_url('upload/users/photo.png'),
                'taskid' => $cc->taskid,
                'task_title' => $cc->title,
                'spid' => '000000'.$spid,
                'spname' => $cc->spname,
                'date' => date('d/m/Y',strtotime($cc->created_at)),
                'time' => date('H:i',strtotime($cc->created_at)),
                'hours_done' => $cc->hours_done,
                'over_time' => $cc->ot,
                'policy' => $this->myPolicy1($cc->teamid , $id),
                'goal_completed' => (is_numeric($cc->goal_complete))?$cc->goal_complete.'%':$cc->goal_complete,
                'status' => $status,
                'monday' => rtrim($monday,"|"),
                'tuesday' => rtrim($tuesday,"|"),
                'wednesday' => rtrim($wednesday,"|"),
                'thursday' => rtrim($thursday,"|"),
                'friday' => rtrim($friday,"|"),
                'saturday' => rtrim($saturday,"|"),

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
            // get policy condition
            public function getPolicyCond_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $teamid = $this->uri->segment(4);
            $task_id = ($this->uri->segment(5))?$this->uri->segment(5):'';
            $check_key = $this->authentication($spid , $tokenid);
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
                $hours+=1;
                $minutes-=60;
             }
            if(!empty($task_id))
            {
            $myData[] = [
                'id' => $cc->id,
                'title' => $cc->title,
                'hours' => sprintf('%02d:%02d', $hours, $minutes),
            ];
        }else{
         $myData[] = [
                'id' => $cc->id,
                'title' => $cc->title,
            ];   
        }
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
        //get data for edit timesheet 
         public function getEditTimesheet_get()
            {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $id = $this->uri->segment(4);
            $check_key = $this->authentication($spid , $tokenid);    
            $row = $this->db->select('a.* , b.teamimage , b.teamname,b.id as teamid , d.task_name , d.title , d.id as taskid')
                            ->from('tbl_sp_timesheet as a')
                              ->join('myteams as b' , 'b.id = a.team_id','left')
                              ->join('logincr as c' , 'c.id = a.spid','left')
                              ->join('assigntask as d','d.id = a.task_id','left')
                              ->where(['a.id'=>$id])->get()->row();
            if($row)
            {
                $al = [];
             $my = $this->db->select('a.* , b.title')->from('tbl_dynamic_timesheet_relation as a')->join('tbl_customer_policy_duration as b','a.policy_id=b.id' ,'left')->where(['a.generate_id'=>$id])->get()->result();
             if($my)
             {
                 foreach($my as $mm)
                 {
                     $al[] = [
                        'mid' => $mm->id,
                      'id' => $mm->policy_id,
                'title' => $mm->title,
                'hours' => sprintf('%02d:%02d', $mm->hours, $mm->minutes),
                ];
                 }
             }
           $myData= [
                'id' => $row->id,
                'spid' => $spid,
                'teamid' => $row->teamid,
                'teamname' => $row->teamname,
                'task_id' => $row->taskid,
                 'task_title' => $row->title,
                'date' => $row->date,
                'time' => $row->time,
                'start_time' => date('H:i',strtotime($row->start_time)),
                'end_time' => date('H:i',strtotime($row->end_time)),
                'ot' => $row->ot,
                'policy' => $al,

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
            //update timesheet data
            public function updateTimesheet_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $id = $this->input->post('id');
        $spid = $this->input->post('spid');
        $team_id = $this->input->post('team_id');
        $task_id = $this->input->post('task_id');
        $date = $this->input->post('date');
        $time = $this->input->post('time');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
        $policy = $this->input->post('policy');
        $meal_time = $this->input->post('meal_time');
        $ot = $this->input->post('ot');
        $check_key = $this->authentication($spid , $tokenid);
         $config = [
             ['field' => 'id', 'label' => 'id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Timesheet id  is required',
                            'numeric' => 'Timesheet id  should be numeric',
                        ],
                    ],
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Service provider id  is required',
                            'numeric' => 'Service provider id  should be numeric',
                        ],

                    ],
                     ['field' => 'date', 'label' => 'date', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Date is required',
                        ],

                    ],
                     ['field' => 'time', 'label' => 'time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Time is required',
                        ],

                    ],
                     ['field' => 'start_time', 'label' => 'start_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Start time is required',
                        ],

                    ],
                    ['field' => 'end_time', 'label' => 'end_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'End time is required',
                        ],

                    ],
                    ['field' => 'ot', 'label' => 'ot', 'rules' => 'required',
                        'errors' => [
                            'required' => 'OT is required',
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
            $check = $this->db->get_where('tbl_sp_timesheet',['team_id'=>$team_id ,'task_id'=>$task_id , 'date'=>$date])->num_rows();
                $updateData = [
                    'spid' => $spid,
                    'team_id' => $team_id,
                    'task_id' => $task_id,
                    'date' => $date,
                    'time' => $time,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'ot' => $ot,
                ];
                
                if($this->db->set($updateData)->where('id',$id)->update('tbl_sp_timesheet'))
                {
                    $mm = $this->db->insert_id();
                    $insertData['id'] = "$mm"; 
                    $expo = explode('|',$policy);
                    if(count($expo)>0)
                    {
                        for($i=0; $i<count($expo); $i++)
                        {
                            $aa = explode(',',$expo[$i]);
                            $tt = explode(':',$aa[1]);
                            $this->db->set(['hours'=>$tt[0] , 'minutes'=>$tt[1]])->where(['policy_id'=>$aa[0] , 'generate_id'=>$id])->update('tbl_dynamic_timesheet_relation');
                        }
                       
                    }

                      $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Timesheet updated successfully!',
                                'data' => $updateData,
                   
                    ]);
                }else{
                 $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Timesheet already updated!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);    
                }
           
       }
    }
     //offer letter reject feedback
    public function offerrejectFeedback_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'offer_id', 'label' => 'offer_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Offer id is required',
                    'numeric' => 'Offer id numeric value',
                ],
            ],
            ['field' => 'message', 'label' => 'message', 'rules' => 'required',
                'errors' => [
                    'required' => 'Message is required',
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
            $myData = [

                'feedback_type' =>'1',
                'main_id' => $this->input->post('offer_id'), 
                'user_type' => '0',
                'user_id' => $this->input->post('user_id'),
                'message' => $this->input->post('message'),
            ];
            $this->db->insert('tbl_all_feedback',$myData);
            if($this->db->affected_rows()>0)
            {
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data added successfully...',
            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Data not added successfully...',
            ]);
            }
           
        }
    }
     //check in checkout
    public function checkinCheckout_post() {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->input->post('user_id');
            $check_key = $this->authentication($spid , $tokenid);    
        $data = $this->input->post();
        $config = [
            ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Task id is required',
                    'numeric' => 'Task id numeric value',
                ],
            ],
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id is required',
                    'numeric' => 'User id numeric value',
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
            $my_status = $this->input->post('my_status');//if status 0 means checkin , or if 1 means checkout
            if($my_status==0)//checkin
            {
                $check = $this->db->get_where('tbl_task_checkin_checkout',['type'=>'0' ,'user_id'=>$this->input->post('user_id') , 'checkin_status'=>'1' ,'checkout_status'=>'0' ,'task_id'=>$this->input->post('task_id')])->num_rows();
                if($check)
                {
                $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'You already checkin with another task first checkout task!',

                 ]);
                }else{
                $myData = [

                'user_id' =>$this->input->post('user_id'),
                'task_id' => $this->input->post('task_id'), 
                'check_in' => $this->input->post('check_in'),
                'date_checkin' => date('Y-m-d'),
                'checkin_status' => '1',
            ];
            $this->db->insert('tbl_task_checkin_checkout',$myData);
            if($this->db->affected_rows()>0)
            {
                $myData['id'] = $this->db->insert_id();
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Checkin succesfully....',
                        'data' => $myData,

            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Checkin not succesfully....',
            ]);
            }
        }
            }else//checkout
            {
                $id = $this->input->post('id');
                 $myData = [
                'id' => "$id",
                'check_out' => $this->input->post('check_out'),
                'date_checkout' => date('Y-m-d'),
                'checkin_status' => '0',
                'checkout_status' => '1'
            ];
            $this->db->set($myData)->where('id',$id)->update('tbl_task_checkin_checkout');
            if($this->db->affected_rows()>0)
            {
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Checkout succesfully....',
                        'data' => $myData,

            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Checkout not succesfully....',
            ]);
            }
            }
            
            
           
        }
    }
    //break time checkin checkout
    //check in checkout
    public function breakcinCout_post() {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->input->post('user_id');
            $check_key = $this->authentication($spid , $tokenid);    
        $data = $this->input->post();
        $config = [
            ['field' => 'task_id', 'label' => 'task_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Task id is required',
                    'numeric' => 'Task id numeric value',
                ],
            ],
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id is required',
                    'numeric' => 'User id numeric value',
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
            $my_status = $this->input->post('my_status');//if status 0 means checkin , or if 1 means checkout
            if($my_status==0)//checkin
            {
                $check = $this->db->get_where('tbl_task_checkin_checkout',['type'=>'1','user_id'=>$this->input->post('user_id') , 'checkin_status'=>'1' ,'checkout_status'=>'0' ,'task_id'=>$this->input->post('task_id')])->num_rows();
                if($check)
                {
                $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'You already checkin with another task first checkout task!',

                 ]);
                }else{
                
                $myData = [
                'type' => '1',   
                'user_id' =>$this->input->post('user_id'),
                'task_id' => $this->input->post('task_id'), 
                'check_in' => $this->input->post('check_in'),
                'date_checkin' => date('Y-m-d'),
                'checkin_status' => '1',
            ];
            $this->db->insert('tbl_task_checkin_checkout',$myData);
            if($this->db->affected_rows()>0)
            {
                $myData['id'] = $this->db->insert_id();
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Break Time Checkin succesfully....',
                        'data' => $myData,

            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Break Time Checkin not succesfully....',
            ]);
            }
        }
            }else//checkout
            {
                $id = $this->input->post('id');
                 $myData = [
                'type' => '1', 
                'id' => $id,
                'check_out' => $this->input->post('check_out'),
                'date_checkout' => date('Y-m-d'),
                'checkin_status' => '0',
                'checkout_status' => '1'
            ];
            $this->db->set($myData)->where('id',$id)->update('tbl_task_checkin_checkout');
            if($this->db->affected_rows()>0)
            {
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Break Time Checkout succesfully....',
                        'data' => $myData,

            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Break Time Checkout not succesfully....',
            ]);
            }
            }
            
            
           
        }
    }
    //get checkin checkout status
         public function getCheckStatus_get()
            {
                $myData = array();
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $task_id = $this->uri->segment(4);
            $check_key = $this->authentication($spid , $tokenid);    
            $row = $this->db->select('*')
                            ->from('tbl_task_checkin_checkout')
                              ->where(['type'=>'0' , 'task_id'=>$task_id])->limit(1,0)->order_by('id','DESC')->get()->row();
            if($row)
            {
           $myData= [
            'id' => $row->id,
            'spid' => $row->user_id,
            'task_id' => $row->task_id,
            'check_in' => $row->check_in,
            'check_out' => $row->check_out,
            'date_checkin' => $row->date_checkin,
            'date_checkout' => ($row->date_checkout)?$row->date_checkout:'',
            'checkin_status' => $row->checkin_status,
            'checkout_status' => $row->checkout_status,

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
            'status' => 'success',
            'message' => 'No data found!',
            'responsecode' => REST_Controller::HTTP_OK,
            'data' => NULL,
            ]); 
            }

            }
            //break time status
            //get checkin checkout status
         public function getBreakCheckStatus_get()
            {
                $myData = array();
            $tokenid = $this->input->get_request_header('Secret-Key');
            $spid = $this->uri->segment(3);
            $task_id = $this->uri->segment(4);
            $check_key = $this->authentication($spid , $tokenid);    
            $row = $this->db->select('*')
                            ->from('tbl_task_checkin_checkout')
                              ->where(['type'=>'1' , 'task_id'=>$task_id])->limit(1,0)->order_by('id','DESC')->get()->row();
            if($row)
            {
           $myData= [
            'id' => $row->id,
            'spid' => $row->user_id,
            'task_id' => $row->task_id,
            'check_in' => $row->check_in,
            'check_out' => $row->check_out,
            'date_checkin' => $row->date_checkin,
            'date_checkout' => ($row->date_checkout)?$row->date_checkout:'',
            'checkin_status' => $row->checkin_status,
            'checkout_status' => $row->checkout_status,

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
            'status' => 'success',
            'message' => 'No data found!',
            'responsecode' => REST_Controller::HTTP_OK,
            'data' => NULL,
            ]);
            }

            }
             //get xai data
       public function getXaiData_get($user_id = NULL) {
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
                 ->where(['a.user_id'=>$user_id])->get()->row(); 
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

            $myData = [
                'user_id' => $data->user_id,
                'team_id' => $data->team_id,
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
                'rate' => $data->rate,
                'accessment' => $data->accessment,
                'communication_data' => $communicationData,
                'expectation' => $data->expectation,
                'driving_distance' => $data->driving_distance,
                'xai_personality' => $data->xai_personality,
                'motivation' => $data->motivation,
                'language' => $data->language,
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
    
    
            //get xai data
       public function getXaiDataN_get($user_id = NULL) {
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
                 ->where(['a.user_id'=>$user_id])->get()->row(); 
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

            $myData = [
                'user_id' => $data->user_id,
                'team_id' => $data->team_id,
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
                'rate' => $data->rate,
                'accessment' => $data->accessment,
                'communication_data' => $communicationData,
                'expectation' => $data->expectation,
                'driving_distance' => $data->driving_distance,
                'xai_personality' => $data->xai_personality,
                'motivation' => $data->motivation,
                'language' => $data->language,
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
    
    
     /*--------------------- For User end-----------------------*/
    public function xaioneNew_get() {

        $industryArray = [];
        $skillArray = [];
        $experienceArray = [];

        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        if (!empty($industry)) {
            foreach ($industry as $ind) {
                $industryArray[] = ['id' => $ind->id, 'name' => $ind->name];
            }
        }

        $skills = $this->db->get_where('tbl_skill', ['status' => '0'])->result();
        if (!empty($skills)) {
            foreach ($skills as $skill) {
                $skillArray[] = ['id' => $skill->id, 'name' => $skill->name];
            }
        }
        
        $experience = $this->db->get_where('tbl_experience', ['status' => '0'])->result();
        if (!empty($experience)) {
            foreach ($experience as $exp) {
                $experienceArray[] = ['id' => $exp->id, 'name' => $exp->name];
            }
        }

        $dataArray = [
            'industry' => $industryArray,
            'skills' => $skillArray,
            'experiences' => $experienceArray,
        ];

        if (!empty($dataArray)) {

            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found!',
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
    }

public function xaioneNew_put() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($this->put());
        $user_id = $this->put('user_id');
        $member_id = $this->put('member_id');
        $type = $this->put('type');
        

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
                    ['field' => 'type', 'label' => 'type', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Type is required',
                            
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
                       
                     if(!empty($member_id) && $this->put('team_id')=="0")
                        {
                            $member_id = $this->put('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->put('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->put('team_id')!="0")
                        {
                            $member_id = 0;
                        }
                    $formArray = [
                        'type' => $type,
                        'member_id' => $member_id,
                        'user_id' => $this->put('user_id'),
                        'industry_id' => $this->put('industry_id'),
                        'skill_id' => $skill_id,
                        'experience_id' => $this->put('experience_id'),
                        'team_id' => $this->put('team_id'),
                        //'personality' => $this->security->xss_clean($this->put('personality')),
                    ];
                    
                    if ($this->put('personality') != '') {
                        $formArray['personality'] = $this->security->xss_clean($this->put('personality'));
                    }
                    
                   /*
                     $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'member_id' => $this->put('member_id')])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'team_id' => $this->put('team_id')])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                      //  $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') ,'team_id' => $this->put('team_id')]);
                      (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') , 'member_id'=>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id')]);
                        $effected = $this->db->affected_rows();
                    }*/
                    
                    if(!empty($member_id) && $this->put('team_id')=="0")//when xai data set on member create
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'member_id' => $this->put('member_id') , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->put('team_id')!="0")//when at the time of create team we also select team
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'member_id' => $this->put('member_id') , 'team_id'=>$this->put('team_id')])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') , 'member_id'=>$member_id , 'team_id'=>$this->put('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->put('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $this->put('user_id') ,'team_id' => $this->put('team_id') , 'member_id'=>'0'])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $this->put('user_id') ,'team_id' => $this->put('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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
    
    
    public function xaitwoNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $member_id = $this->input->post('member_id');
           

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
                    // ['field' => 'members', 'label' => 'members', 'rules' => 'required|numeric',
                    //     'errors' => [
                    //         'required' => 'Members is required',
                    //         'numeric' => 'Members should be numeric',
                    //     ],
                    // ],
                    ['field' => 'factors', 'label' => 'factors', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Factors is required',
                        ],
                    ],
//                    ['field' => 'available_days', 'label' => 'available_days', 'rules' => 'required',
//                        'errors' => [
//                            'required' => 'Available Days is required',
//                        ],
//                    ],
//                    ['field' => 'available_time', 'label' => 'available_time', 'rules' => 'required',
//                        'errors' => [
//                            'required' => 'Available time is required',
//                        ],
//                    ],
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
                     if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }

                    $formArray = [
                        'member_id' => $member_id,
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

/*               
                     $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' =>$member_id])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' =>$team_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        //$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' =>$team_id]);
                        (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'member_id' =>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' =>$team_id]);
                        $effected = $this->db->affected_rows();
                    }*/
                    if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0'])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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
    
    public function xaithreeNewUser_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $member_id = $this->input->post('member_id');
           

            $check_key = $this->checktoken($token, $user_id);
            $team_id = $this->input->post('team_id');
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
                     if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }

                    $formArray = [
                         'member_id' => $member_id,
                        'rate' => $this->security->xss_clean($this->input->post('rate')),
                        'accessment' => $this->security->xss_clean($this->input->post('accessment')),
                        'communication_id' => $this->security->xss_clean($this->input->post('communication_id')),
                        'softskills_id' => $this->security->xss_clean($this->input->post('softskills_id')),
                        'frequency_id' => $this->security->xss_clean($this->input->post('frequency_id')),
                    ];
                    
                   /*
                    $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'member_id'=>$member_id])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'team_id'=>$team_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        //$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id, 'team_id'=>$team_id]);
                        (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'team_id'=>$team_id]);
                        $effected = $this->db->affected_rows();
                    }*/
                        if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0'])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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
    ///
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
                     if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }

                    $formArray = [
                         'member_id' => $member_id,
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
                    
                   /*
                    $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'member_id'=>$member_id])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'team_id'=>$team_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        //$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id, 'team_id'=>$team_id]);
                        (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'team_id'=>$team_id]);
                        $effected = $this->db->affected_rows();
                    }*/
                        if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0'])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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

    public function xaifourNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $member_id = $this->input->post('member_id');
            

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
                     if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }

                    $formArray = [
                        'member_id' => $member_id,
                        'expectation' => $this->security->xss_clean($this->input->post('expectation')),
                        'driving_distance' => $this->security->xss_clean($this->input->post('driving_distance')),
                        'xai_personality' => $this->security->xss_clean($this->input->post('xai_personality')),
                         'additional_skills' => $this->security->xss_clean($this->input->post('additional_skills')),
                    ];
                    
                   /*
                     $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'member_id'=>$member_id])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'team_id'=>$team_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       // $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,  'team_id'=>$team_id]);
                       (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,  'member_id'=>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,  'team_id'=>$team_id]);
                        $effected = $this->db->affected_rows();
                    }*/
                    if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {

                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                       
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                       
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>0])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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
    
    public function xaifinishNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $member_id = $this->input->post('member_id');
            

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
                    if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }

                    $formArray = [
                        'member_id' => $member_id,
                        'motivation' => $this->security->xss_clean($this->input->post('motivation')),
                        'language' => $this->security->xss_clean($this->input->post('language')),
                    ];
                    
                   /*
                     $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'member_id'=>$member_id])->row():$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'team_id'=>$team_id])->row();

                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                        $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = $data->industry_id;
                    } else {
                       // $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id'=>$team_id]);
                       (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'member_id'=>$member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id'=>$team_id]);
                        $effected = $this->db->affected_rows();
                        $industry = $exist->industry_id;
                    }*/
                     if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {

                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                       
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                       
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>0])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                         $data = $this->db->get_where('tbl_xai_matching', ['id' => $effected])->row();
                        $industry = (isset($data->industry_id))?$data->industry_id:"0";
                    }
                    }

                    $result = [
                        'industry' => $industry,
                        'xaistatus' => '1',
                    ];

                    if ($effected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your data has been saved!',
                                     'data' => $result,
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'failed',
                                    'message' => 'Data already updated!',
                                    'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                     'data' => $result,
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
       public function getXaiDataNew_get($user_id = NULL , $team_id = NULL , $member_id = NULL) {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $check_key = $this->authentication($user_id , $tokenid);  
            $get_team = $this->db->get_where('myteams',['id'=>$team_id])->row();
            $agreement_id = ($get_team)?$get_team->agreement_id:'';
           
            if(!empty($agreement_id) && $agreement_id != 0 && $member_id == 0)//this call when we send agreement and client accept agreement and at the time of agreement create client select own member
            {
                $agreement_by_id = $get_team->agreement_sendby_id;
                 $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$agreement_by_id , 'a.type'=>'4' , 'a.for_self_user'=>'1'])->get()->row(); 

            }else if(!empty($agreement_id) && $agreement_id != 0 && $member_id!=0)//this call when we send agreement and client accept agreement and at the time of agreement create client select any member
            {
                 $agreement_by_id = $get_team->agreement_sendby_id;
                  $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$agreement_by_id , 'a.type'=>'2' , 'a.for_self_user'=>'0' , 'member_id'=>$member_id])->get()->row(); 
            }else{//below all code execute when no seen of agreement
            if($member_id=='0' && $team_id!=0)
            {
                
            $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.team_id'=>$team_id , 'a.member_id'=>0])->get()->row(); 
            }else if($member_id!=0 && $team_id==0)
            {
                  $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.member_id'=>$member_id , 'a.team_id'=>$team_id])->get()->row(); 
            }else if($member_id!=0 && $team_id!=0)
            {
                 $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.member_id'=>$member_id , 'a.team_id'=>$team_id])->get()->row(); 
            }
            }
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
                'rate' => $data->rate,
                'accessment' => $data->accessment,
                'communication_data' => $communicationData,
                'expectation' => $data->expectation,
                'driving_distance' => $data->driving_distance,
                'xai_personality' => $data->xai_personality,
                'motivation' => $data->motivation,
                'language' => $data->language,
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
    
    //get xai data
       public function getXaiDataNeww_get($user_id = NULL , $team_id = NULL , $member_id = NULL) {
            $tokenid = $this->input->get_request_header('Secret-Key');
            $check_key = $this->authentication($user_id , $tokenid);  
            if($member_id=='0' && $team_id!=0)
            {
                
            $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.team_id'=>$team_id , 'a.member_id'=>0])->get()->row(); 
            }else if($member_id!=0 && $team_id==0)
            {
                  $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.member_id'=>$member_id , 'a.team_id'=>$team_id])->get()->row(); 
            }else if($member_id!=0 && $team_id!=0)
            {
                 $data = $this->db->select('a.* , b.name as industry_name , c.name as experience_name , d.name as skill_name , e.name as softskill_name , f.title as options_preferences , f.id as options_preferences_id , g.name as category')
                 ->from('tbl_xai_matching as a')
                 ->join('tbl_industries as b' ,'b.id = a.industry_id','left')
                 ->join('tbl_experience as c' ,'c.id = a.experience_id','left')
                 ->join('tbl_skill as d' ,'d.id = a.skill_id','left')
                 ->join('tbl_softskill as e' ,'e.id = a.softskills_id','left')
                 ->join('tbl_working_condition as f' ,'f.id = a.options_preferences','left')
                 ->join('tbl_industries as g' ,'g.id = a.category','left')
                 ->where(['a.user_id'=>$user_id ,'a.member_id'=>$member_id , 'a.team_id'=>$team_id])->get()->row(); 
            }
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
            /* $frequencyData = [];
            $frequency = $this->db->where_in('id',explode(',',$data->frequency_id))->get('tbl_frequency')->result();
            if($frequency)
            {
                foreach($frequency as $fr)
                {
                    $frequencyData[] =['id'=>$fr->id , 'type'=>$fr->type];
                }
            }*/
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
                'rate' => $data->rate,
                'accessment' => $data->accessment,
                'communication_data' => $communicationData,
                'expectation' => $data->expectation,
                'driving_distance' => $data->driving_distance,
                'xai_personality' => $data->xai_personality,
                'motivation' => $data->motivation,
                'language' => $data->language,
                //'frequency' => $frequencyData,
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
    
   //xai fifth step for provider end
    public function xaifive_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
          

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
                    
                    $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'type'=>'0'])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'type'=>'0']);
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

    //xai fifth step for user end
    public function xaifiveNew_post() {

        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '') {
            $user_id = $this->input->post('user_id');
            $team_id = $this->input->post('team_id');
            $member_id = $this->input->post('member_id');
            

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
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
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
                       if(!empty($member_id) && $this->input->post('team_id')=="0")
                        {
                            $member_id = $this->input->post('member_id');
                        }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = $member_id;
                        }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")
                        {
                            $member_id = 0;
                        }
                    $formArray = [
                        'member_id' => $member_id,
                         'member_id' => (!empty($member_id))?$member_id:0,
                        'team_id' => $team_id,
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
                    
                   /*
                     $exist = (!empty($member_id))?$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'member_id'=>$member_id]):$this->db->get_where('tbl_xai_matching', ['user_id' => $user_id , 'team_id'=>$team_id])->row();
                    if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       // $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,' team_id'=> $team_id]);
                        (!empty($member_id))?$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,' member_id'=> $member_id]):$this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,' team_id'=> $team_id]);
                        $effected = $this->db->affected_rows();
                    }*/
                    if(!empty($member_id) && $this->input->post('team_id')=="0")//when xai data set on member create
                    {

                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>'0'])->row();
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id!="0" && $this->input->post('team_id')!="0")//when at the time of create team we also select team
                    {
                       
                        $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'member_id' => $member_id , 'team_id'=>$this->input->post('team_id')])->row();
                       
                         if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                        $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id , 'member_id'=>$member_id , 'team_id'=>$this->input->post('team_id')]);
                        $effected = $this->db->affected_rows();
                    }
                    }else if(isset($member_id) && $member_id=="0" && $this->input->post('team_id')!="0")//when at the time of team creation own member select not select any member
                    {
                       
                          $exist = $this->db->get_where('tbl_xai_matching', ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>0])->row();
                           if (empty($exist)) {
                        $this->db->insert('tbl_xai_matching', $formArray);
                        $effected = $this->db->insert_id();
                    } else {
                       $this->db->update('tbl_xai_matching', $formArray, ['user_id' => $user_id ,'team_id' => $this->input->post('team_id') , 'member_id'=>'0']);
                        $effected = $this->db->affected_rows();
                    }
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
    
     //get all client list hired a particular sp
    public function getClientList_get($id = NULL) {
        $user_id = $id;
        $token = $this->input->get_request_header('Secret-Key');
        if ($token != '' && $user_id != '') {

            $check_key = $this->checktoken($token, $user_id);
            $data = $this->input->post();
            if ($check_key['status'] == 'true') {

                $getData = $this->db->select('b.*')
                                    ->from('tbl_offer_letter as a')
                                    ->join('logincr as b' ,'b.id = a.user_id','left')
                                    ->where(['a.provider_id'=>$id , 'a.status'=>'2' ,'b.id!='=>$id])
                                    ->group_by('b.id')
                                    ->get()->result();
                
                if($getData)
                {
                    foreach($getData as $check_record)
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
    
    public function getspDetails_get($id = '') {

        $this->load->model('Common_model');
        $token = $this->input->get_request_header('Secret-Key');

        //$userid  = $this->input->post('id');
        $spid = $id;
         $check_key = $this->checktoken($token, $spid);

        if ($spid != '') {
            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'status' => '1'));
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

                $edu_record = $this->db->get_where('usereducation', ['userid' => $spid])->result();
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
                                ->where('a.user_id', $spid)->get()->row();

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
                                ->where('a.user_id', $spid)
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
                    'rating' => $this->Common_model->getrating($spid),
                   // 'interviewdatetime' => $data_interview,
                    'educationdata' => $data_edu,
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
