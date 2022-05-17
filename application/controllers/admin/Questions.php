<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Questions extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'QuestionModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'question/index',
            'title' => 'Question List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function questionslist() {
        $list = $this->QuestionModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;
            $row[] = $value->question;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="questions/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> |
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->QuestionModel->count_all(),
            "recordsFiltered" => $this->QuestionModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'question/create',
            'title' => 'Create Question',
            'industry' => $industry,
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('industry_id', 'Industry', 'required|trim');
            $this->form_validation->set_rules('question', 'Question', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'industry_id' => $this->input->post('industry_id'),
                    'question' => $this->security->xss_clean($this->input->post('question')),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert('tbl_questions', $formArray);
                $this->session->set_flashdata('success', 'Question Added Successfully!');
                redirect('admin/questions');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where('tbl_questions', ['id' => $id])->row();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'question/create',
            'title' => 'Edit Question',
            'industry' => $industry,
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {
            $this->form_validation->set_rules('industry_id', 'Industry', 'required|trim');
            $this->form_validation->set_rules('question', 'Question', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'industry_id' => $this->input->post('industry_id'),
                    'question' => $this->security->xss_clean($this->input->post('question')),
                    'updated_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update('tbl_questions', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Question Updated Successfully!');
                    redirect('admin/questions');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/questions');
                }
            }
        } else {
            redirect('admin/question/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_questions');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
