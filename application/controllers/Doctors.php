<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';

class Doctors extends REST_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index_get() {

        $this->db->select('a.*, b.name as education')->from('tbl_doctors as a');
        $this->db->join('tbl_educations as b', 'b.id = a.education_id', 'left');
        $data = $this->db->get()->result();

        if (count($data) > 0) {
            foreach ($data as $val) {
                $result[] = [
                    'id' => $val->id,
                    'name' => $val->name,
                    'education' => $val->education,
                    'start_time' => $val->start_time,
                    'end_time' => $val->end_time,
                    'experience' => $val->experience,
                    'fees' => $val->fees,
                    'phone' => $val->phone,
                    'description' => $val->description,
                    'profile_img' => base_url('/').$val->profile_img,
                    'rating' => $val->rating,
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

}
