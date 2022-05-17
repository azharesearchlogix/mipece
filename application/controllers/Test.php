<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Test extends REST_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index_get() {
       
        $this->db->select('a.*, b.name as education')->from('tbl_doctors as a');
        $this->db->join('tbl_educations as b', 'b.id = a.education_id', 'left');
        $data = $this->db->get()->result();
        if (count($data) > 0) {
            $final_output['responsecode'] = '200';
            $final_output['status'] = 'success';
            $final_output['message'] = 'Records Found';
            $final_output['data'] = $data;
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }
         header("content-type: application/json");
        echo json_encode($final_output);
    }
    
     public function destroy_user_question_post() {
        if ($this->input->post('user_id')) {
            $this->db->delete('userans', ['userid' => $this->input->post('user_id')]);
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Questionds delete successfully!',
            ]);
        }
    }

    public function destroy_provider_question_post() {
        if ($this->input->post('user_id')) {
            $this->db->delete('tbl_answer', ['user_id' => $this->input->post('user_id')]);
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Questionds delete successfully!',
            ]);
        }
    }
}
