<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Managerelationship extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'ManagerelationshipModel']);
    }

    public function index() {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'managerelationship/index',
            'title' => 'Manage Relationship List',
        ];
        $this->load->view('admin/template/index', $data);
    }
    public function manage_issues($id) {
        $this->admin_model->CheckLoginSession();

        $data = [
            'content' => 'managerelationship/index_issues',
            'title' => 'Manage Relationship Issues',
            'id' => $id
        ];
        $this->load->view('admin/template/index', $data);
    }
     public function DatalistIssues($id) {
        $list = $this->ManagerelationshipModel->get_datatables_issues($id);
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->relationship;
            $row[] = ($value->status == '1' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="'.site_url('admin/managerelationship/edit_issue/'.($value->id).'/'.$id).'">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->ManagerelationshipModel->count_all_issues($id),
            "recordsFiltered" => $this->ManagerelationshipModel->count_filtered_issues($id),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Datalist() {
        $list = $this->ManagerelationshipModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->relationship;
            $row[] = ($value->status == '1' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-primary btn-xs" data-toggle="tooltip" title="Manage Issues" data-placement="top"  href="managerelationship/manage_issues/' . ($value->id) . '">
                          Manage Issues</a>&nbsp;&nbsp;<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="managerelationship/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->ManagerelationshipModel->count_all(),
            "recordsFiltered" => $this->ManagerelationshipModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function create() {

        $this->admin_model->CheckLoginSession();
        $data = [
            'content' => 'managerelationship/create',
            'title' => 'Create Relationship',
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('relationship', 'Relationship', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'relationship' => $this->security->xss_clean($this->input->post('relationship')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_relationship', $formArray);
                $this->session->set_flashdata('success', 'Relationship Added Successfully!');
                redirect('admin/managerelationship');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }
    public function create_issues($id) {

        $this->admin_model->CheckLoginSession();
        $data = [
            'content' => 'managerelationship/create_issues',
            'title' => 'Create Relationship Issues',
            'id' => $id
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('relationship', 'Relationship', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'relationship' => $this->security->xss_clean($this->input->post('relationship')),
                    'parent_id' => $id,
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->insert('tbl_relationship', $formArray);
                $this->session->set_flashdata('success', 'Relationship Added Successfully!');
                redirect('admin/managerelationship/manage_issues/'.$id);
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where('tbl_relationship', ['id' => $id])->row();
        $data = [
            'content' => 'managerelationship/create',
            'title' => 'Edit Relationship',
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }
    public function edit_issue($id1 = NULL , $id = NULL) {
        $result = $this->db->get_where('tbl_relationship', ['id' => $id1])->row();
        $data = [
            'content' => 'managerelationship/create_issues',
            'title' => 'Edit Relationship Issues',
            'result' => $result,
            'id' => $id,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('relationship', 'Relationship', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'relationship' => $this->security->xss_clean($this->input->post('relationship')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_relationship', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Relationship Updated Successfully!');
                    redirect('admin/managerelationship');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/managerelationship');
                }
            }
        } else {
            redirect('admin/managerelationship/edit/' . $id);
        }
    }
    public function update_issues($id = NULL , $id1 = NULL) {
        if ($this->input->post()) {

            $this->form_validation->set_rules('relationship', 'Relationship', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'relationship' => $this->security->xss_clean($this->input->post('relationship')),
                    'status' => $this->security->xss_clean($this->input->post('status')),
                );
                $this->db->update('tbl_relationship', $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Relationship Issues Updated Successfully!');
                    redirect('admin/managerelationship/manage_issues/'.$id1);
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/managerelationship/manage_issues/'.$id1);
                }
            }
        } else {
            redirect('admin/managerelationship/edit_issue/'. $id.'/'.$id1);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete('tbl_relationship');
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
