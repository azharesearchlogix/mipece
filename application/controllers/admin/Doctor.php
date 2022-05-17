<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Doctor extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['DoctorModel', 'admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {

        $data = [
            'content' => 'doctor/index',
            'title' => 'Doctor List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Doctorlist() {
        $list = $this->DoctorModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->name;
            $row[] = $value->education;
            $row[] = $value->start_time . ' - ' . $value->end_time;
            $row[] = $value->experience;
            $row[] = $value->fees;
            $row[] = $value->created_at;
            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="doctor/edit/' . base64_encode($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->DoctorModel->count_all(),
            "recordsFiltered" => $this->DoctorModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {
        $education = $this->db->get_where('tbl_educations', ['status' => '0'])->result();

        $data = [
            'content' => 'doctor/create',
            'title' => 'Doctor List',
            'education' => $education,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('education_id', 'Education', 'required');
            $this->form_validation->set_rules('experience', 'Experience', 'required');
            $this->form_validation->set_rules('start_time', 'Start Time', 'required');
            $this->form_validation->set_rules('end_time', 'End Time', 'required');
            $this->form_validation->set_rules('fees', 'Fees', 'required|numeric');
            $this->form_validation->set_rules('phone', 'Phone', 'required');
            $this->form_validation->set_rules('description', 'Description', 'required');
            $this->form_validation->set_rules('status', 'status', 'required');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'education_id' => $this->input->post('education_id'),
                    'experience' => $this->security->xss_clean($this->input->post('experience')),
                    'start_time' => $this->input->post('start_time'),
                    'end_time' => $this->input->post('end_time'),
                    'fees' => $this->input->post('fees'),
                    'phone' => $this->input->post('phone'),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status'),
                );
                $config['upload_path'] = './upload/users/';
                $config['allowed_types'] = 'gif|jpg|png|png';
                $config['max_width'] = 5000;
                $config['max_height'] = 5000;

                $this->load->library('upload', $config);

                if (!empty($_FILES['profile_img']['name'])) {
                    if (!$this->upload->do_upload('profile_img')) {
                        $this->session->set_flashdata('error', $this->upload->display_errors());
                        redirect('admin/doctor/create');
                    } else {
                        $formArray['profile_img'] = 'upload/users/' . ($this->upload->data('file_name'));
                    }
                }

                $res = $this->db->Insert('tbl_doctors', $formArray);
                if ($res) {
                    $this->session->set_flashdata('success', 'Doctor added successfully!');
                    redirect('admin/doctor');
                } else {
                    $this->session->set_flashdata('error', 'Something went wrong please try after sometime!');
                    redirect('admin/doctor/create');
                }


                // $this->db->insert('tbl_doctors',$this->input->post());
            }
        }
        $this->load->view('admin/template/index', $data);
    }

    public function edit($id) {
        $result = $this->db->get_where('tbl_doctors', ['id' => base64_decode($id)])->row();
        $education = $this->db->get_where('tbl_educations', ['status' => '0'])->result();
        $data = [
            'content' => 'doctor/create',
            'title' => 'Edit Doctor',
            'education' => $education,
            'result' => $result,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Update($id) {
        $education = $this->db->get_where('tbl_educations', ['status' => '0'])->result();
        $result = $this->db->get_where('tbl_doctors', ['id' => base64_decode($id)])->row();

        $data = [
            'content' => 'doctor/create',
            'title' => 'Doctor List',
            'education' => $education,
            'result' => $result,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('education_id', 'Education', 'required');
            $this->form_validation->set_rules('experience', 'Experience', 'required');
            $this->form_validation->set_rules('start_time', 'Start Time', 'required');
            $this->form_validation->set_rules('end_time', 'End Time', 'required');
            $this->form_validation->set_rules('fees', 'Fees', 'required|numeric');
            $this->form_validation->set_rules('phone', 'Phone', 'required');
            $this->form_validation->set_rules('description', 'Description', 'required');
            $this->form_validation->set_rules('status', 'status', 'required');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $formArray = array(
                    'name' => $this->security->xss_clean($this->input->post('name')),
                    'education_id' => $this->input->post('education_id'),
                    'experience' => $this->security->xss_clean($this->input->post('experience')),
                    'start_time' => $this->input->post('start_time'),
                    'end_time' => $this->input->post('end_time'),
                    'fees' => $this->input->post('fees'),
                    'phone' => $this->input->post('phone'),
                    'description' => $this->input->post('description'),
                    'status' => $this->input->post('status'),
                );
                $config['upload_path'] = './upload/users/';
                $config['allowed_types'] = 'gif|jpg|png|png';
                $config['max_width'] = 5000;
                $config['max_height'] = 5000;

                $this->load->library('upload', $config);

                if (!empty($_FILES['profile_img']['name'])) {
                    if (!$this->upload->do_upload('profile_img')) {
                        $this->session->set_flashdata('error', $this->upload->display_errors());
                        redirect('admin/doctor/create');
                    } else {
                        $formArray['profile_img'] = 'upload/users/' . ($this->upload->data('file_name'));
                    }
                }

                $res = $this->db->update('tbl_doctors', $formArray, ['id' => base64_decode($id)]);
                if ($res) {
                    $this->session->set_flashdata('success', 'Doctor updated successfully!');
                    redirect('admin/doctor');
                } else {
                    $this->session->set_flashdata('error', 'Something went wrong please try after sometime!');
                    redirect('admin/doctor/create/' . base64_decode($id));
                }
            }
        }
        $this->load->view('admin/template/index', $data);
    }

    public function Ajaxdelete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $this->db->delete('tbl_doctors');
//            echo $this->db->last_query();
            if ($this->db->affected_rows() > 0) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}
