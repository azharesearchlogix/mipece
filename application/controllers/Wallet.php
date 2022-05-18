<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH.'/third_party/vendor/autoload.php';

class Wallet extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model', 'Mail']);
    }

    public function checktoken($token, $userid) {

        $auth = $this->Common_model->common_getRow('logincr', array('token_security' => $token, 'id' => $userid));

        if (!empty($auth)) {
            $abc['status'] = "true";
            $abc['data'] = $auth;
            return $abc;
        } else {
            $abc['status'] = "false";
            return $abc;
        }
    }

    public function authentication($user_id = NULL, $token = NULL) {
        $result = $this->Common_model->Access($user_id, $token);
        if (!key_exists('error', $result)) {
            return $result;
        } else {
            $this->response(
                    ['status' => 'Failed',
                        'message' => $result['error'],
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
public function getWalletAmount_get($user_id = '')
{
    $token = $this->input->get_request_header('Secret-Key');
    $user_id = $user_id;
    $check = $this->authentication($user_id , $token);
    if($check)
    {
        $switch = $check['success']->switch_account;
        if($switch == '1')//for user
        {
          $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'0' , 'user_type'=>'1' , 'receiver_id'=>0])->group_by('user_id')->get()->row('amount');//all amount add by self
           $total_amount_out = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'1' , 'user_type'=>'1'])->group_by('user_id')->get()->row('amount');
           $wallet_left_amount = ($total_amount_in - $total_amount_out);
           $used_amount = $total_amount_out;  
            $data = [
            'total_amount_in' => ($total_amount_in>0)?"$total_amount_in":"0",
            'total_amount_out' => ($total_amount_out>0)?"$total_amount_out":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
            'used_amount' => ($used_amount>0)?"$used_amount":"0",
           ];
        }else if($switch == '2')//for sc (staffing company)
        {
            $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'0' , 'user_type'=>'2' , 'receiver_id'=>0])->group_by('user_id')->get()->row('amount');//all amount add by self
            $total_amount_from_client = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['receiver_id'=>$user_id , 'in_or_out'=>'0'])->group_by('receiver_id')->get()->row('amount');//all amount add by any sender
            $total_in = ($total_amount_in + $total_amount_from_client);
           $total_amount_out = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'1'])->group_by('user_id')->get()->row('amount');
           $wallet_left_amount = ($total_in - $total_amount_out);
           $used_amount = $total_amount_out;  
           $data = [
            'total_amount_in' => ($total_in>0)?"$total_in":"0",
            'total_amount_out' => ($total_amount_out>0)?"$total_amount_out":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
            'used_amount' => ($used_amount>0)?"$used_amount":"0",
           ];
         }else{
            $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['in_or_out'=>'0' , 'receiver_id'=>$user_id])->group_by('user_id')->get()->row('amount');//all amount add by self
            $total_in = ($total_amount_in);
           $total_amount_out = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'1' , 'user_type'=>'0' ,'is_request'=>'1'])->group_by('user_id')->get()->row('amount');
           $wallet_left_amount = ($total_in - $total_amount_out);
           $used_amount = $total_amount_out; 

            $data = [
            'total_amount_in' => ($total_in>0)?"$total_in":"0",
            'total_amount_out' => ($total_amount_out>0)?"$total_amount_out":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
            'used_amount' => ($used_amount>0)?"$used_amount":"0",
           ];
         }
     return $this->response([
            'status' => 'success',
            'responsecode' => REST_Controller::HTTP_OK,
            'message' => 'Data found successfully',
            'data' => $data
         ]);
         
    }else{
        return $this->response(
            [
          'status' => 'failed',
          'message' => 'User data not found!',
          'responsecode' => REST_Controller::HTTP_BAD_GATEWAY  
        ]);
    }
    

}



}
