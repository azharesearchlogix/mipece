<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['BlogModel', 'admin_model']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'blog/index',
            'title' => 'Blog List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function bloglist() {
        $list = $this->BlogModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = ($value->image!='')? '<a href="' . base_url($value->image) . '" target="_blank"><img class="tbl-img" src="' . base_url($value->image) . '"></a>' : '<img class="tbl-img" src="' . base_url('design/images/noimg.png') . '">';
            $row[] = $value->title;
            $row[] = strlen($value->description) > 100 ? substr($value->description, 0, 100) . "..." : $value->description;
            $row[] = $value->created_at;
            $row[] = ($value->status == '1' ? '<span class="label label-success">Publish</span>' : '<span class="label label-danger">Un-Publish</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="View" data-placement="top"  href="blog/view/' . base64_encode($value->id) . '">
                          <i class="fa fa-eye"></i></a> 
                            <span class="change text-warning btn btn-warning btn-xs" data-toggle="tooltip" title="Change Status" data-placement="top"  data-change=' . ($value->id) . '><i class="fa fa-check-circle-o" aria-hidden="true"></i></span> 
                            <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '> <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->BlogModel->count_all(),
            "recordsFiltered" => $this->BlogModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function view($id) {

        $this->admin_model->CheckLoginSession();

        $query = $this->db->select('a.id, a.title, a.image, a.description, DATE_FORMAT(a.created_at, "%d-%m-%Y") as created_at, a.status,CONCAT(b.firstname, " ", b.lastname) AS user_name')
                        ->from('tbl_blog as a')
                        ->join('logincr  as b', 'b.id = a.created_by', 'left')
                        ->where('a.id', base64_decode($id))->get();
//        echo $this->db->last_query();
        $result = $query->row();

        $data = [
            'content' => 'blog/view',
            'title' => 'Blog View',
            'result' => $result
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function delete() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete('tbl_blog');
            echo '1';
        } else {
            echo '0';
        }
    }

    public function change() {
        if ($this->input->post()) {
            $res = $this->db->get_where('tbl_blog',['id'=> $this->input->post('id')])->row();
            if ($res->status == '0') {
                $status = '1';
            } else {
                $status = '0';
            }
            
            $this->db->update('tbl_blog', array('status' => $status),['id'=> $this->input->post('id')]);
//            echo $this->db->last_query(); die;
            $result = $this->db->affected_rows();
            if ($result > 0) {
                echo '1';
            } else {
                 echo '0';
            }
        }
    }

}
