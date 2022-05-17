<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Feedback extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'FeedbackModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'feedback/index',
            'title' => 'Feedback List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->FeedbackModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;
            if($value->feedback_type=='0')
            {
                $my = 'Cancel Interview Feedback';
            }else if($value->feedback_type=='1')
            {
                $my = 'Reject Offer Letter Feedback';
            }else if($value->feedback_type=='2')
            {
                $my = 'User Approved Task Feedback';
            }
            $row = array();
            $row[] = $no;
            $row[] = $my;
            $row[] = $value->message;
             $row[] = '<span class="del_feedback text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->FeedbackModel->count_all(),
            "recordsFiltered" => $this->FeedbackModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_all_feedback');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
