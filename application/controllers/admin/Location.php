<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Location extends CI_Controller {

    protected $table = 'tbl_zipcode';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/LocationModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'location/index',
            'title' => 'Location List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->LocationModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->zip;
            $row[] = $value->state_name;
            $row[] = $value->city;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="location/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->LocationModel->count_all(),
            "recordsFiltered" => $this->LocationModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();
        $industry = $this->db->get_where($this->table, ['status' => '0'])->result();

        $data = [
            'content' => 'location/create',
            'title' => 'Create Location',
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('zip', 'zipcode', 'required|trim');
            $this->form_validation->set_rules('city', 'city', 'required|trim');
            $this->form_validation->set_rules('state_name', 'state_name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'zip' => $this->security->xss_clean($this->input->post('zip')),
                    'city' => $this->security->xss_clean($this->input->post('city')),
                    'state_name' => $this->security->xss_clean($this->input->post('state_name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Location Added Successfully!');
                redirect('admin/location');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();

        $data = [
            'content' => 'location/create',
            'title' => 'Edit Location',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('zip', 'zipcode', 'required|trim');
            $this->form_validation->set_rules('city', 'city', 'required|trim');
            $this->form_validation->set_rules('state_name', 'state_name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'zip' => $this->security->xss_clean($this->input->post('zip')),
                    'city' => $this->security->xss_clean($this->input->post('city')),
                    'state_name' => $this->security->xss_clean($this->input->post('state_name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Location Updated Successfully!');
                    redirect('admin/location');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/location');
                }
            }
        } else {
            redirect('admin/location/edit/' . $id);
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
