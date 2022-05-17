<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Community extends CI_Controller {

    protected $table = 'tbl_question_post';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/CommunityModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
        $data = [
            'content' => 'community/index',
            'title' => 'Community List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->CommunityModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->question;
            $row[] = strlen($value->description) > 50 ? substr($value->description, 0, 50) . "..." : $value->description;
            $row[] = $value->created_at;
            $row[] = ($value->status == '1' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="View" data-placement="top"  href="community/view/' . base64_encode($value->id) . '">
                          <i class="fa fa-eye"></i></a> 
                          <span class="del  btn btn-warning btn-xs" data-toggle="tooltip" title="Change Status" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-check-circle-o"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->CommunityModel->count_all(),
            "recordsFiltered" => $this->CommunityModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function view($id) {
       $result =  $this->db->select(['a.id', 'a.question', 'a.description', 'a.user_id', 'DATE_FORMAT(a.created_at, "%d-%m-%Y") as created_at','CONCAT(b.firstname, " "  , b.lastname) AS name'])->from($this->table . ' as a')
                ->join('logincr as b', 'b.id = a.user_id', 'left')
                ->where('b.id IS NOT NULL')
                ->where('a.id', base64_decode($id))->get()->row();
//        echo '<pre>';
//        print_r($result);
//        die;
        $data = [
            'content' => 'community/view',
            'title' => 'Community View',
            'result' => $result
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function delete() {
        if ($this->input->post('id')) {

            $res = $this->db->get_where($this->table, ['id' => $this->input->post('id')])->row();
            if ($res->status == '0') {
                $status = '1';
            } else {
                $status = '0';
            }
            $res = $this->db->update($this->table, ['status' => $status], ['id' => $this->input->post('id')]);
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
