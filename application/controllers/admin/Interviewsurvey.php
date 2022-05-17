<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Interviewsurvey extends CI_Controller {

    protected $table = 'tbl_interview_survey_question';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/InterviewsurveyModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {

        $data = [
            'content' => 'interviewsurvey/index',
            'title' => 'Question List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->InterviewsurveyModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->user_type == '0' ? 'User' : 'Provider';
            $row[] = $value->question;
            $row[] = $value->options;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="interviewsurvey/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->InterviewsurveyModel->count_all(),
            "recordsFiltered" => $this->InterviewsurveyModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'interviewsurvey/create',
            'title' => 'Create Question',
            'industry' => $industry,
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('user_type', 'Industry', 'required|trim');
            $this->form_validation->set_rules('question', 'Question', 'required|trim');
            $this->form_validation->set_rules('options[]', 'Options', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {

                $formArray = [
                    'user_type' => $this->input->post('user_type'),
                    'question' => $this->input->post('question'),
                    'options' => json_encode($this->input->post('options')),
                    'status' => $this->input->post('status'),
                ];

                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Question Added Successfully!');
                redirect('admin/interviewsurvey');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'interviewsurvey/create',
            'title' => 'Edit Question',
            'result' => $result,
            'industry' => $industry,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'interviewsurvey/create',
            'title' => 'Edit Question',
            'result' => $result,
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('user_type', 'Industry', 'required|trim');
            $this->form_validation->set_rules('question', 'Question', 'required|trim');
            $this->form_validation->set_rules('options[]', 'Options', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = [
                    'user_type' => $this->input->post('user_type'),
                    'question' => $this->input->post('question'),
                    'options' => json_encode($this->input->post('options')),
                    'status' => $this->input->post('status'),
                ];
//                echo '<pre>';
//                print_r($formArray);
//                die;
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Question Updated Successfully!');
                    redirect('admin/interviewsurvey');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/interviewsurvey');
                }
            }
        } else {
            redirect('admin/interviewsurvey/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete($this->table);
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>