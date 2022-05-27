<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {


	public function index()
	{
         $res = $this->Mail->sendmail('azharmohd1996@gmail.com', 'My piece registration successfully!', '<p>My name is azhar</p>');
         print_r($res);
         exit;
		//$this->load->view('common/header');
		$this->load->view('home');
		//$this->load->view('common/footer');
	}
	 public function index_old() {
        ini_set('memory_limit', '-1');
        $data = $this->db->get_where('tbl_zipcode',['status'=>'0'])->result();
        //  echo '<pre>';
        // echo count($data);
        // die;
        $ind = $this->db->get_where('tbl_industries', ['status' => '0'])->result();
        foreach ($data as $val) {
            $formArray = [];
            $ass = [];
            foreach ($ind as $in) {
                $ass[] = [
                    'industry' => $in->id,
                    'min' => rand(10, 50),
                    'max' => rand(55, 100),
                ];
            }
//            echo '<pre>';
//            print_r(json_encode($ass));
//            die;
            $formArray = [
                'location_id' => $val->id,
                'assesment' => json_encode($ass),
                'created_by' => 1,
            ];
//            echo '<pre>';
//            print_r($formArray);
//            die;
            if($formArray){
                       // $this->db->insert('tbl_rate_assesment', $formArray);
                     //  $this->db->update('tbl_rate_assesment', $formArray,['location_id'=>$val->id]);
            }
        }

        echo 'inserted';
        //print_r($formArray);
        // $this->load->view('home');
        //$this->load->view('common/footer');
    }
    public function chat()
    {
        $this->load->view('chat');
    }
    

}
