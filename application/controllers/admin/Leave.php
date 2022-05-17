<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Leave extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['LeaveModel', 'admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function create_leave_type() {
          $this->admin_model->CheckLoginSession();
          $leave_type = $this->db->get_where('tbl_leave_type', ['status' => '1'])->result();
        $data = [
            'content' => 'leave/leave_type_create',
            'title' => 'Leave Type Create',
            'leave_type' => $leave_type,
        ];
         if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'leave_type' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_leave_type', $formArray);
                $this->session->set_flashdata('success', 'Leave Type Added Successfully!');
                redirect('admin/leave/leave_type');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }
    public function leave_type()
    {
        $leave = $this->db->order_by('created_at','DESC')->get_where('tbl_leave_type',[])->result();
        $data = [
            'content' => 'leave/leave_type_index',
            'title' => 'Leave Type List',
            'leave_type' => $leave
        ];
        $this->load->view('admin/template/index', $data);
    }
      public function edit_leave_type($id = NULL) {
        $result = $this->db->get_where('tbl_leave_type', ['id' => $id])->row();

        $data = [
            'content' => 'leave/leave_type_create',
            'title' => 'Edit Leave Type',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }
     public function update_leave_type($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'leave_type' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_leave_type', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Leave Type Updated Successfully!');
                    redirect('admin/leave/leave_type');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/leave/leave_type');
                }
            }
        } else {
            redirect('admin/leave/edit_leave_type/' . $id);
        }
    }


    public function delete_leave_type() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete('tbl_leave_type');
            echo '1';
        } else {
            echo '0';
        }
    }
/*-----------------------------------Leave interval------------------------*/
  public function create_leave_interval() {
          $this->admin_model->CheckLoginSession();
          $leave_interval = $this->db->get_where('tbl_leave_interval', ['status' => '1'])->result();
        $data = [
            'content' => 'leave/leave_interval_create',
            'title' => 'Leave Interval Create',
            'leave_interval' => $leave_interval,
        ];
         if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Interval Name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'interval_name' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_leave_interval', $formArray);
                $this->session->set_flashdata('success', 'Leave Interval Added Successfully!');
                redirect('admin/leave/leave_interval');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }
    public function leave_interval()
    {
        $leave = $this->db->order_by('created_at','DESC')->get_where('tbl_leave_interval',[])->result();
        $data = [
            'content' => 'leave/leave_interval_index',
            'title' => 'Leave Interval List',
            'leave_type' => $leave
        ];
        $this->load->view('admin/template/index', $data);
    }
      public function edit_leave_interval($id = NULL) {
        $result = $this->db->get_where('tbl_leave_interval', ['id' => $id])->row();

        $data = [
            'content' => 'leave/leave_interval_create',
            'title' => 'Edit Leave Interval',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }
     public function update_leave_interval($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Interval Name', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'interval_name' => $this->security->xss_clean($this->input->post('name')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_leave_interval', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Leave Interval Updated Successfully!');
                    redirect('admin/leave/leave_interval');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/leave/leave_interval');
                }
            }
        } else {
            redirect('admin/leave/edit_leave_interval/' . $id);
        }
    }


    public function delete_leave_interval() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete('tbl_leave_interval');
            echo '1';
        } else {
            echo '0';
        }
    }

}
