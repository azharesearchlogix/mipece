<?php

$data['user'] = $this->session->userdata('admin_name');
$data['userid'] = $this->session->userdata('admin_id');

$this->load->view('admin/common/header', $data);
$this->load->view('admin/common/sidebar', $data);
$this->load->view('admin/'.$content);
$this->load->view('admin/common/footer');
?>