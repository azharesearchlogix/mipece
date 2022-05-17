<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Promocode extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'PromocodeModel']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'promocode/index',
            'title' => 'Promocode List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->PromocodeModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->promocode;
            $row[] = $value->discount;
             $row[] = ($value->is_expire == '0' ? '<span class="label label-success">Not Used</span>' : '<span class="label label-danger">Used</span>');
            
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="promocode/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->PromocodeModel->count_all(),
            "recordsFiltered" => $this->PromocodeModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {

        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'promocode/create',
            'title' => 'Create Promocode',
           
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'name', 'required|trim|is_unique[tbl_promocode.promocode]');
             $this->form_validation->set_rules('discount', 'Discount', 'required|trim|is_numeric');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'discount' => $this->security->xss_clean($this->input->post('discount')),
                    'promocode' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_promocode', $formArray);
                $this->session->set_flashdata('success', 'Promocode Added Successfully!');
                redirect('admin/promocode');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where('tbl_promocode', ['id' => $id])->row();

        $data = [
            'content' => 'promocode/create',
            'title' => 'Edit Skill',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'name', 'required|trim');
             $this->form_validation->set_rules('discount', 'Discount', 'required|trim|is_numeric');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'promocode' => $this->security->xss_clean($this->input->post('name')),
                    'discount' => $this->security->xss_clean($this->input->post('discount')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_promocode', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Promocode Updated Successfully!');
                    redirect('admin/promocode');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/promocode');
                }
            }
        } else {
            redirect('admin/promocode/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_promocode');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
