<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Industry extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['IndustryModel', 'admin_model']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'industry/index',
            'title' => 'Industry List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Industrylist() {
        $list = $this->IndustryModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;
            $row[] = $value->created_at;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="industry/edit/' . base64_encode($value->id) . '">
                          <i class="fa fa-pencil"></i></a>
                            <span class="change text-warning btn btn-warning btn-xs" data-toggle="tooltip" title="Change Status" data-placement="top"  data-change=' . ($value->id) . '><i class="fa fa-check-circle-o" aria-hidden="true"></i></span> 
                            <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '> <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->IndustryModel->count_all(),
            "recordsFiltered" => $this->IndustryModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function create() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'industry/create',
            'title' => 'Create Industry',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->input->post('name'),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert('tbl_industries', $formArray);
                $this->session->set_flashdata('success', 'Industry Added Successfully!');
                redirect('admin/industry');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id){
        $result = $this->db->get_where('tbl_industries',['id'=> base64_decode($id)])->row();
        $data = [
            'content' => 'industry/create',
            'title' => 'Edit Industry',
            'result' => $result,
        ];
         $this->load->view('admin/template/index', $data);
        
    }
    
     public function update($id) {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'industry/create',
            'title' => 'Create Industry',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Name', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->input->post('name'),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update('tbl_industries', $formArray,['id'=> base64_decode($id)]);
                $this->session->set_flashdata('success', 'Industry Updated Successfully!');
                redirect('admin/industry');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function delete() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete('tbl_industries');
            echo '1';
        } else {
            echo '0';
        }
    }
    
     public function change() {
        if ($this->input->post()) {
            $res = $this->db->get_where('tbl_industries',['id'=> $this->input->post('id')])->row();
            if ($res->status == '0') {
                $status = '1';
            } else {
                $status = '0';
            }
            
            $this->db->update('tbl_industries', array('status' => $status),['id'=> $this->input->post('id')]);
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
