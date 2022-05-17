<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Unsubscribequestion extends CI_Controller {
    
    protected $table = 'tbl_unsubscribe_question';
    function __construct() {
        parent::__construct();
        $this->load->model(['admin/UsubscribequestionModel', 'admin_model']);
    }

    public function Index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'unsubscribequestion/index',
            'title' => 'Question List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function datalist() {
        $list = $this->UsubscribequestionModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->question;
            $row[] = $value->created_at;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="unsubscribequestion/edit/' . base64_encode($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                            <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '> <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->UsubscribequestionModel->count_all(),
            "recordsFiltered" => $this->UsubscribequestionModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function create() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'unsubscribequestion/create',
            'title' => 'Create Question',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('question', 'Question', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'question' => $this->input->post('question'),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Question Added Successfully!');
                redirect('admin/unsubscribequestion');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id){
        $result = $this->db->get_where($this->table,['id'=> base64_decode($id)])->row();
        $data = [
            'content' => 'unsubscribequestion/create',
            'title' => 'Edit Question',
            'result' => $result,
        ];
         $this->load->view('admin/template/index', $data);
        
    }
    
     public function update($id) {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'unsubscribequestion/create',
            'title' => 'Create Question',
        ];
        if ($this->input->post()) {
            $this->form_validation->set_rules('question', 'Question', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'question' => $this->input->post('question'),
                    'created_by' => $this->session->userdata('admin_id'),
                );
                $this->db->update($this->table, $formArray,['id'=> $id]);
               // echo $this->db->last_query(); die;
                $this->session->set_flashdata('success', 'Question Updated Successfully!');
                redirect('admin/unsubscribequestion');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function delete() {
        if ($this->input->post()) {

            $this->db->where('id', $this->input->post('id'));
            $this->db->delete($this->table);
            echo '1';
        } else {
            echo '0';
        }
    }
    

}
?>