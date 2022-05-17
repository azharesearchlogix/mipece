<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('admin_model');
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
       // print_r(menu()); die;
        $res = $this->db->get_where('tbl_admin_menu', ['id' => '1'])->row();
        $data = [
            'title' => 'Menu List',
            'content' => 'menu/list',
            'result' => $res,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Menu() {
        $this->db->where('id', '1');
        $res = $this->db->update('tbl_admin_menu', ['data' => $_POST['data']]);
        if ($res) {
            echo '1';
        } else {
            echo '0';
        }
    }

}

?>
