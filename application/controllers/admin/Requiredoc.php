<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Requiredoc extends CI_Controller {

    protected $table = 'tbl_required_doc';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin/RequiredocModel', 'admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
       
        $data = [
            'content' => 'requiredoc/index',
            'title' => 'Requiredoc List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->RequiredocModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->title;
            $row[] = $value->created_at;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="requiredoc/edit/' . base64_encode($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                            <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '> <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->RequiredocModel->count_all(),
            "recordsFiltered" => $this->RequiredocModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function create() {
        
        $data = [
            'content' => 'requiredoc/create',
            'title' => 'Create Requiredoc',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Name', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->input->post('title'),
                    'status' => $this->input->post('status'),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Requiredoc Added Successfully!');
                redirect('admin/requiredoc');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id) {
        $result = $this->db->get_where($this->table, ['id' => base64_decode($id)])->row();
        $data = [
            'content' => 'requiredoc/create',
            'title' => 'Edit Requiredoc',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id) {
       
        $data = [
            'content' => 'requiredoc/create',
            'title' => 'Create Requiredoc',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('title', 'Name', 'required|trim');
            $this->form_validation->set_rules('status', 'Status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->input->post('title'),
                    'status' => $this->input->post('status'),
                    'updated_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $this->session->set_flashdata('success', 'Requiredoc Updated Successfully!');
                redirect('admin/requiredoc');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function delete() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete($this->table);
            echo '1';
        } else {
            echo '0';
        }
    }

    public function change() {
        if ($this->input->post()) {
            $res = $this->db->get_where($this->table, ['id' => $this->input->post('id')])->row();
            if ($res->status == '0') {
                $status = '1';
            } else {
                $status = '0';
            }

            $this->db->update($this->table, array('status' => $status), ['id' => $this->input->post('id')]);
//            echo $this->db->last_query(); die;
            $result = $this->db->affected_rows();
            if ($result > 0) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}
