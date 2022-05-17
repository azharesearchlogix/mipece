<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Assesment extends CI_Controller {

    protected $table = 'tbl_rate_assesment';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/AssesmentModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function Index() {

        $data = [
            'content' => 'assesment/index',
            'title' => 'Assessment List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->AssesmentModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->zip;
            $row[] = $value->assesment;

            $row[] = ($value->status == '0' ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-Active</span>');
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="Edit" data-placement="top"  href="assesment/edit/' . ($value->id) . '">
                          <i class="fa fa-pencil"></i></a> 
                          <span class="del text-danger btn btn-danger btn-xs" data-toggle="tooltip" title="Delete" data-placement="top"  data-delete=' . ($value->id) . '>
                          <i class="fa fa-trash"></i></span>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->AssesmentModel->count_all(),
            "recordsFiltered" => $this->AssesmentModel->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function Create() {


        $location = $this->db->get_where('tbl_zipcode', ['status' => '0'])->result();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();

        $location = $this->db->select('a.*')->from('tbl_zipcode as a')->join($this->table . ' as b', 'a.id = b.location_id', 'left')
                        ->where('b.id IS NULL')
                        ->where(['a.status' => '0'])
                        ->get()->result();

        $data = [
            'content' => 'assesment/create',
            'title' => 'Create Assessment',
            'location' => $location,
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('location_id', 'location_id', 'required|trim');
            $this->form_validation->set_rules('industry[]', 'Industry', 'required|trim');
            $this->form_validation->set_rules('min[]', 'Min Rate', 'required|trim');
            $this->form_validation->set_rules('max[]', 'Max Rate', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');


            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index', $data);
            } else {
                $assesment = [];
                foreach ($this->input->post('industry_id') as $key => $val) {
                    $assesment[] = [
                        'industry' => $this->input->post('industry_id')[$key],
                        'min' => $this->input->post('min')[$key],
                        'max' => $this->input->post('max')[$key],
                    ];
                }
                $formArray = [
                    'location_id' => $this->input->post('location_id'),
                    'assesment' => json_encode($assesment),
                    'status' => $this->input->post('status'),
                ];
//                echo '<pre>';
//                print_r($formArray);
//                die;

                $this->db->insert($this->table, $formArray);
                $this->session->set_flashdata('success', 'Assesment Added Successfully!');
                redirect('admin/assesment');
            }
        } else {
            $this->load->view('admin/template/index', $data);
        }
    }

    public function edit($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $location = $this->db->select('a.*')->from('tbl_zipcode as a')->join($this->table . ' as b', 'a.id = b.location_id')->where(['a.status' => '0'])->get()->result();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'assesment/create',
            'title' => 'Edit Assessment',
            'result' => $result,
            'location' => $location,
            'industry' => $industry,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function update($id = NULL) {
        $result = $this->db->get_where($this->table, ['id' => $id])->row();
        $location = $this->db->select('a.*')->from('tbl_zipcode as a')->join($this->table . ' as b', 'a.id = b.location_id')->where(['a.status' => '0'])->get()->result();
        $industry = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        $data = [
            'content' => 'assesment/create',
            'title' => 'Edit Assessment',
            'result' => $result,
            'location' => $location,
            'industry' => $industry,
        ];
        if ($this->input->post()) {

            $this->form_validation->set_rules('location_id', 'location_id', 'required|trim');
            $this->form_validation->set_rules('industry[]', 'Industry', 'required|trim');
            $this->form_validation->set_rules('min[]', 'Min Rate', 'required|trim');
            $this->form_validation->set_rules('max[]', 'Max Rate', 'required|trim');
            $this->form_validation->set_rules('status', 'status', 'required|trim');

            $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
            if ($this->form_validation->run() == FALSE) {
                $this->load->view('admin/template/index',$data);
               
            } else {
                $assesment = [];
                foreach ($this->input->post('industry_id') as $key => $val) {
                    $assesment[] = [
                        'industry' => $this->input->post('industry_id')[$key],
                        'min' => $this->input->post('min')[$key],
                        'max' => $this->input->post('max')[$key],
                    ];
                }
                $formArray = [
                    'location_id' => $this->input->post('location_id'),
                    'assesment' => json_encode($assesment),
                    'status' => $this->input->post('status'),
                ];
//                echo '<pre>';
//                print_r($formArray);
//                die;
                $this->db->update($this->table, $formArray, ['id' => $id]);
                $effected = $this->db->affected_rows();
                if ($effected > 0) {
                    $this->session->set_flashdata('success', 'Assesment Updated Successfully!');
                    redirect('admin/assesment');
                } else {
                    $this->session->set_flashdata('error', 'No Any Changes Found!');
                    redirect('admin/assesment');
                }
            }
        } else {
            redirect('admin/assesment/edit/' . $id);
        }
    }

    public function delete() {
        if ($this->input->post('id')) {
            $this->db->where('id', $this->input->post('id'));
            $res = $this->db->delete($this->table);
            if ($res) {
                echo '1';
            } else {
                echo '0';
            }
        }
    }

}

?>
