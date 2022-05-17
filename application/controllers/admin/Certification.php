<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Certification extends CI_Controller {

    protected $table = 'tbl_certification';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/CertificationModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
        $data = [
            'content' => 'certification/index',
            'title' => 'Certification List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->CertificationModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->role_name;
            $row[] = $value->title;
            $row[] = $value->created_at;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="certification/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->CertificationModel->count_all(),
            "recordsFiltered" => $this->CertificationModel->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function Create() {
        $industries = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'certification/create',
            'title' => 'Create Certification',
            'industries' => $industries,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('title', 'title', 'required|trim');
            $this->form_validation->set_rules('role_id', 'role_id', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->security->xss_clean($this->input->post('title')),
                    'role_id' => $this->security->xss_clean($this->input->post('role_id')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Certification Added Successfully!');
                redirect('admin/certification');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $industries = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'certification/create',
            'title' => 'Edit Certification',
            'result' => $result,
            'industries' => $industries,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('title', 'title', 'required|trim');
            $this->form_validation->set_rules('role_id', 'role_id', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->security->xss_clean($this->input->post('title')),
                    'role_id' => $this->security->xss_clean($this->input->post('role_id')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                    'updated_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Certification Updated Successfully!');
                    redirect('admin/certification');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/certification');
                }
            }
        } else {
            redirect('admin/certification/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_certification');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
