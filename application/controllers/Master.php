<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Master extends REST_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function industries_get() {

       $data= $this->db->get_where('tbl_industries',['status'=>'0'])->result();
//        $data = $this->db->get()->result();

        if (count($data) > 0) {
            foreach ($data as $val) {
                $result[] = [
                    'id' => $val->id,
                    'name' => $val->name,
                    
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found',
                        'data' => $result,
            ]);
        } else {
            $this->response(
                    ['status' => 'Failed',
                        'message' => 'Record not found',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
            ]);
        }
    }
    
    public function appfees_get() {
        $token = $this->input->get_request_header('Secret-Key');
        $id = $this->input->get_request_header('userid');
        if ($token != '' && $id != '') {

            $check_key = $this->db->get_where('logincr', ['token_security' => $token])->result();

            if (count($check_key) > 0) {
                $data = $this->db->get_where('tbl_app_fee', ['id' => '1'])->row();
                if (!empty($data)) {


                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Record found',
                                'fees' => $data->fees,
                    ]);
                } else {
                    $this->response(
                            ['status' => 'Failed',
                                'message' => 'Record not found',
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
    
    public function skill_list_get($industry_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
         $user_id = $this->input->get_request_header('userid'); 
        if ($token != '' && $user_id != '') {

            $user = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();

            if ($user) {
                $query = $this->db->select('*')->from('tbl_skill')->where('status', '0');
                if ($industry_id) {
                    $query = $this->db->where('industry_id', $industry_id);
                }
                $result = $this->db->get()->result();
                if (!empty($result)) {

                    foreach ($result as $val) {
                        $data[] = [
                            'id' => $val->id,
                            'industry_id' => $val->industry_id,
                            'skill_language' => $val->name,
                        ];
                    }
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Record found',
                                'skill' => $data,
                    ]);
                } else {
                    $this->response(
                            ['status' => 'Failed',
                                'message' => 'Record not found',
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

}
