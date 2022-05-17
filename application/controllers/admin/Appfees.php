<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Appfees extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->model(['admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
        $result = $this->db->get_where('tbl_app_fee', ['id' => '1'])->row();

        $data = [
            'content' => 'appfees/create',
            'title' => 'Fees',
            'result' => $result,
        ];
        if ($this->input->post()) {
            $this->db->update('tbl_app_fee', ['fees' => $this->input->post('fees')], ['id' => '1']);
            $this->session->set_flashdata('success', 'Fees updated Successfully!');
            redirect('admin/appfees');
        }
        $this->load->view('admin/template/index', $data);
    }

}
