<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tasktime extends CI_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->model(['TasktimeModel', 'admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {
        $data = [
            'content' => 'tasktime/index',
            'title' => 'Task List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->TasktimeModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;
            $start = date('h:i:s', strtotime($value->start_time));
            $end = date('h:i:s', strtotime($value->end_time));
            $hours = round(abs(strtotime($end) - strtotime($start)) / 3600, 2);

            $row = array();
            $row[] = $no;
            $row[] = $value->title;
            $row[] = strlen($value->describe) > 100 ? substr($value->describe, 0, 100) . "..." : $value->describe;
            $row[] = strlen($value->comments) > 100 ? substr($value->comments, 0, 100) . "..." : $value->comments;
            $row[] = '<span class="label label-success">' . $value->taskstatus . '</span>';
            $row[] = $value->taskdate;
            $row[] = $value->start_time;
            $row[] = $value->end_time;
            $row[] = $hours . ' Hours';
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="View" data-placement="top"  href="tasktime/view/' . base64_encode($value->id) . '">
                          <i class="fa fa-eye"></i></a> ';
          
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->TasktimeModel->count_all(),
            "recordsFiltered" => $this->TasktimeModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function view($id) {

        $this->admin_model->CheckLoginSession();

        $result = $this->db->get_where('assigntask',['id'=>base64_decode($id)])->row();
        $data = [
            'content' => 'tasktime/view',
            'title' => 'Task View',
            'result' => $result
        ];
        $this->load->view('admin/template/index', $data);
    }

}
