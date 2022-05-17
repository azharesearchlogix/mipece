<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Unsubscribe extends CI_Controller {

    protected $table = 'tbl_unsubscribe_user';

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model', 'admin/UnsubscribeModel']);
        $this->admin_model->CheckLoginSession();
    }

    public function export() {
        $fileName = 'report_sheet.xlsx';
        $employeeData = $this->UnsubscribeModel->report();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'User');
        $sheet->setCellValue('B1', 'Package');
        $sheet->setCellValue('C1', 'Reason');
        $sheet->setCellValue('D1', 'Comments');
        $sheet->setCellValue('E1', 'Date');
        $rows = 2;
        foreach ($employeeData as $val) {
            $sheet->setCellValue('A' . $rows, $val->user_name);
            $sheet->setCellValue('B' . $rows, $val->package_name);
            $sheet->setCellValue('C' . $rows, $val->question);
            $sheet->setCellValue('D' . $rows, $val->comments);
            $sheet->setCellValue('E' . $rows, $val->created_at);
            $rows++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save("reports/" . $fileName);
        redirect(base_url() . "/reports/" . $fileName);
    }

    public function Index() {

        $data = [
            'content' => 'unsubscribe/index',
            'title' => 'Unsubscribe User List',
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function Datalist() {
        $list = $this->UnsubscribeModel->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $value) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = $value->user_name;
            $row[] = $value->package_name;
            $row[] = $value->question;
            $row[] = strlen($value->comments) > 50 ? substr($value->comments, 0, 50) . "..." : $value->comments;
            $row[] = $value->created_at;
            $row[] = '<a class="text-primary btn btn-success btn-xs" data-toggle="tooltip" title="View" data-placement="top"  href="unsubscribe/view/' . base64_encode($value->id) . '"> <i class="fa fa-eye"></i></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->UnsubscribeModel->count_all(),
            "recordsFiltered" => $this->UnsubscribeModel->count_filtered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function view($id) {
        $query = $this->db->select('a.id,a.comments,CONCAT(b.firstname, " ", b.lastname) AS user_name,c.name as package_name,d.question,DATE_FORMAT(a.created_at, "%d-%m-%Y") as created_at')
                        ->from($this->table . ' as a')
                        ->join('logincr as b', 'a.user_id = b.id', 'left')
                        ->join('tbl_subscription_package as c', 'a.package_id = c.id', 'left')
                        ->join('tbl_unsubscribe_question as d', 'a.question_id = d.id', 'left')
                        ->where('b.id is not null')->where('a.id', base64_decode($id))->get();
//        echo $this->db->last_query(); die;
        $result = $query->row();

        $data = [
            'content' => 'unsubscribe/view',
            'title' => 'Unsubscribe View',
            'result' => $result
        ];
        $this->load->view('admin/template/index', $data);
    }

}

?>