<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Personality extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'PersonalityModel']);
    }

    public function index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'personality/index',
            'title' => 'Personality List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->PersonalityModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->personality1;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="personality/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->PersonalityModel->count_all(),
            "recordsFiltered" => $this->PersonalityModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'personality/create',
            'title' => 'Create Skill',
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Personality 1', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'personality1' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_personality', $formArray);
                $this->session->set_flashdata('success', 'Personality Added Successfully!');
                redirect('admin/personality');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where('tbl_personality', ['id' => $id])->row();

        $data = [
            'content' => 'personality/create',
            'title' => 'Edit Personality',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Personality 1', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'personality1' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_personality', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Personality Updated Successfully!');
                    redirect('admin/personality');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/personality');
                }
            }
        } else {
            redirect('admin/personality/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_personality');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
