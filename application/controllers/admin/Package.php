<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Package extends CI_Controller {

    protected $table = 'tbl_subscription_package';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'PackageModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'package/index',
            'title' => 'Package List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->PackageModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;
            $row[] = '$' . $value->rate;
            $row[] = '$' . $value->discount_rate;
            $row[] = $value->days . ' Days';

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="package/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->PackageModel->count_all(),
            "recordsFiltered" => $this->PackageModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();
        $industry = $this->db->get_where($this->table, ['status' => '0'])->result();

        $data = [
            'content' => 'package/create',
            'title' => 'Create Package',
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'name', 'required|trim');
            $this->form_validation->set_rules('rate', 'Rate', 'required|trim');
            $this->form_validation->set_rules('discount_rate', 'Discount Rate', 'required|trim');
            $this->form_validation->set_rules('days', 'Days', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'rate' => $this->security->xss_clean($this->input->post('rate')),
                    'discount_rate' => $this->security->xss_clean($this->input->post('discount_rate')),
                    'days' => $this->security->xss_clean($this->input->post('days')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Package Added Successfully!');
                redirect('admin/package');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();

        $data = [
            'content' => 'package/create',
            'title' => 'Edit Package',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('rate', 'Rate', 'required|trim');
            $this->form_validation->set_rules('discount_rate', 'Discount Rate', 'required|trim');
            $this->form_validation->set_rules('days', 'Days', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'rate' => $this->security->xss_clean($this->input->post('rate')),
                    'discount_rate' => $this->security->xss_clean($this->input->post('discount_rate')),
                    'days' => $this->security->xss_clean($this->input->post('days')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Package Updated Successfully!');
                    redirect('admin/package');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/package');
                }
            }
        } else {
            redirect('admin/package/edit/' . $id);
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
