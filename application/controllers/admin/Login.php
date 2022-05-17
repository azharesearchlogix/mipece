<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('admin_model');
        $this->load->helper('form');
    }

    public function index() {

        $admin_id = $this->session->userdata('admin_id');
        if (!empty($admin_id)) {
            redirect('admin/dashboard', 'refresh');
        } else {

            if ($this->input->post()) {
                $result = $this->admin_model->AdminLogin($this->input->post('useremail'));
                if ($result) {
                    if (password_verify($this->input->post('userpassword'), $result->password)) {
                        $userdata = array(
                            'admin_id' => $result->id,
                            'user_id' => $result->id,
                            'admin_name' => $result->name,
                            'admin_email' => $result->email,
                            'login' => TRUE,
                            'role' => $result->role,
                        );
                        $this->session->set_userdata($userdata);
                        $this->session->set_flashdata('success', 'You are logged in successfully!');
                        redirect('admin/dashboard', 'refresh');
                    } else {
                        $this->session->set_flashdata('error', 'Your provide password is invalid!');
                        redirect('admin/login', 'refresh');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Your provide email id is invalid!');
                    redirect('admin/login', 'refresh');
                }
            }
        }

        $this->load->view('admin/index');
    }

}
