<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Language extends CI_Controller {

    protected $table = 'tbl_language';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'LanguageModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {

        $data = [
            'content' => 'language/index',
            'title' => 'Language List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
         $list = $this->LanguageModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="language/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->LanguageModel->count_all(),
            "recordsFiltered" => $this->LanguageModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {
        
        $data = [
            'content' => 'language/create',
            'title' => 'Create Language',
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(                   
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Language Added Successfully!');
                redirect('admin/language');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();

        $data = [
            'content' => 'language/create',
            'title' => 'Edit Language',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Language Updated Successfully!');
                    redirect('admin/language');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/language');
                }
            }
        } else {
            redirect('admin/language/edit/' . $id);
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
