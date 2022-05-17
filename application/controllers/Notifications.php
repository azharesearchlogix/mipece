<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Notifications extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model', 'NotificationModel']);
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

    public function index_post() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');

        if ($token != '' && $user_id != '') {
            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'device_token', 'label' => 'device_token', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Device token  is required',
                        ],
                    ],
                    ['field' => 'device_tpye', 'label' => 'device_tpye', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Device type  is required',
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
                    
                    $message = [
                        'title' => 'This is the title',
                        'body' => 'This the body',
                        'icon' => base_url('upload/images/notification.png')
                    ];
                    
                    $response = $this->NotificationModel->index($data, $message);
                    
                    $res = json_decode($response);
                    
                    if ($res->success == 1) {
                        $message['user_id'] = $user_id;
                        $this->db->insert('tbl_notification', $message);
                    }


                    $this->response(
                            [
                                'status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'data' => json_decode($response),
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
    
    public function allnotification_get() {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->get_request_header('userid');

        if ($token != '' && $user_id != '') {
            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $data = [];
                $result = $this->db->order_by('id','desc')->limit(20)->get_where('tbl_notification', ['user_id' => $user_id, 'status' => '0'])->result();
                 $receiver = $this->db->get_where('logincr',['id'=>$user_id])->row();
                if (!empty($result)) {
                    foreach ($result as $val) {
                          $sender = $this->db->get_where('logincr',['id'=>$val->sender_id])->row();
                        if($val->type=='0')
                        {
                        $type = 'chat';
                        }else if($val->type=='1')
                        {
                        $type = 'video';
                        }else{
                        $type = 'simple';
                        }
                        $data[] = [
                            'id' => $val->id,
                            'type' => $type,
                            'title' => $val->title,
                            'body' => $val->body,
                            'icon' => $val->icon,
                            'date' => date('F j, Y', strtotime($val->created_at)),
                             'sender_id' => (isset($sender->id))?$sender->id:'0',
                            'sender_name' => (isset($sender->id))?ucwords($sender->firstname.' '.$sender->lastname):'',
                             'receiver_id' => (isset($receiver->id))?$receiver->id:'0',
                            'receiver_name' => (isset($receiver->id))?ucwords($receiver->firstname.' '.$receiver->lastname):'',
                            'last_msg' => ($val->last_message!=NULL)?$val->last_message:'',
                            'room_name' => ($val->room_name!=NULL)?$val->room_name:'',
                        ];
                    }
                    $this->response(
                            [
                                'status' => 'success',
                                'message' => 'Record found!',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'data' => $data,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'failed',
                                'message' => 'Record not found!',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'data' => $data,
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

}
