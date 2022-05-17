<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Experience extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'ExperienceModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'experience/index',
            'title' => 'Experience List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->ExperienceModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="experience/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->ExperienceModel->count_all(),
            "recordsFiltered" => $this->ExperienceModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'experience/create',
            'title' => 'Create Experience',
            'industry' => $industry,
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
                $this->db->insert('tbl_experience', $formArray);
                $this->session->set_flashdata('success', 'Experience Added Successfully!');
                redirect('admin/experience');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where('tbl_experience', ['id' => $id])->row();
      
        $data = [
            'content' => 'experience/create',
            'title' => 'Edit Experience',
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
                $this->db->update('tbl_experience', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Experience Updated Successfully!');
                    redirect('admin/experience');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/experience');
                }
            }
        } else {
            redirect('admin/experience/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_experience');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
