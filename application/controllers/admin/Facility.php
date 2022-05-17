<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Facility extends CI_Controller {

    protected $table = 'tbl_facilities';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/FacilityModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
        $data = [
            'content' => 'facility/index',
            'title' => 'Facility List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->FacilityModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->title;
            $row[] = $value->created_at;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="facility/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = [
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->FacilityModel->count_all(),
            "recordsFiltered" => $this->FacilityModel->count_filtered(),
            "data" => $data,
        ];
        echo json_encode($output);
    }

    public function Create() {
        $industries = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'facility/create',
            'title' => 'Create Facility',
            'industries' => $industries,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('title', 'title', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->security->xss_clean($this->input->post('title')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Facility Added Successfully!');
                redirect('admin/facility');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $industries = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $data = [
            'content' => 'facility/create',
            'title' => 'Edit Facility',
            'result' => $result,
            'industries' => $industries,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('title', 'title', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'title' => $this->security->xss_clean($this->input->post('title')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                    'updated_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Facility Updated Successfully!');
                    redirect('admin/facility');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/facility');
                }
            }
        } else {
            redirect('admin/facility/edit/' . $id);
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
