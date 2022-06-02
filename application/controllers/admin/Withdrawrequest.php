<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Withdrawrequest extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model']);
        $this->admin_model->CheckLoginSession();
    }
    function getAmt($user_type , $user_id)
{
    $switch = $user_type;
    if($switch == '1')//for user
        {
          $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'sc_id'=>0 , 'sp_id'=>0 ,  'in_or_out'=>'0'])->get()->row('amount');//all amount add by self
         $total_sendto_sc = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'1' , 'sc_id!='=>0 , 'sp_id'=>0])->get()->row('amount');
         $total_sendto_sp = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>$user_id , 'in_or_out'=>'1' , 'sc_id'=>0 , 'sp_id!='=>0])->get()->row('amount');
           $wallet_left_amount = ($total_amount_in - ( $total_sendto_sc + $total_sendto_sp) );
           $used_amount = ( $total_sendto_sc + $total_sendto_sp) ;  
            $data = [
            'total_amount_in' => ($total_amount_in>0)?"$total_amount_in":"0",
            'total_amount_out' => ($used_amount>0)?"$used_amount":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
          
           ];
        }else if($switch == '2')//for sc (staffing company)
        {
         $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id!='=>0 , 'sc_id'=>$user_id , 'sp_id'=>0 ,  'in_or_out'=>'0'])->get()->row('amount');//all amount add by self
         $total_sendto_sp = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>0 , 'in_or_out'=>'1' , 'sc_id'=>$user_id , 'sp_id!='=>0])->get()->row('amount');
         $total_request = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['user_id'=>0 , 'in_or_out'=>'1' , 'sc_id'=>$user_id , 'sp_id'=>0])->get()->row('amount');
           $wallet_left_amount = ($total_amount_in - ( $total_request + $total_sendto_sp) );
           $used_amount = ( $total_request + $total_sendto_sp) ;  
            $data = [
            'total_amount_in' => ($total_amount_in>0)?"$total_amount_in":"0",
            'total_amount_out' => ($used_amount>0)?"$used_amount":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
        ];
         }else{//when service provider
             $total_amount_in = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['sp_id'=>$user_id ,  'in_or_out'=>'0'])->get()->row('amount');//all amount in
         $total_request = $this->db->select("SUM(amount) as amount")->from('tbl_ewallet')->where(['sp_id'=>$user_id ,  'in_or_out'=>'1'])->get()->row('amount');
           $wallet_left_amount = ($total_amount_in - ( $total_request) );
           $used_amount = ( $total_request) ;  
            $data = [
            'total_amount_in' => ($total_amount_in>0)?"$total_amount_in":"0",
            'total_amount_out' => ($used_amount>0)?"$used_amount":"0",
            'wallet_left_amount' => ($wallet_left_amount>0)?"$wallet_left_amount":"0",
        ];
         }
         return $data;
}
    public function index()
    {
        $myData = $this->db->select('a.* , CONCAT(b.firstname , " ", b.lastname) as name , b.email , b.contact')
                          ->from('tbl_withdraw_request as a')
                          ->join('logincr as b' , 'b.id = a.userid')
                          ->order_by('a.created_at','DESC')
                          ->get()
                          ->result();
        $data = [
            'content' => 'withdrawrequest/index',
            'title' => 'All Withdrawal Request',
            'alldata' => $myData
        ];
        $this->load->view('admin/template/index', $data);
    }
    public function changestatus()
    {
        $id = $this->security->xss_clean($this->input->post('pid'));
        $status = $this->security->xss_clean($this->input->post('status'));
        $usertype = $this->security->xss_clean($this->input->post('usertype'));
        $comment = $this->security->xss_clean($this->input->post('comment'));
        $this->form_validation->set_rules('pid', 'Id', 'trim|required|is_numeric');
        $this->form_validation->set_rules('status', 'Status', 'trim|required');
        if ($this->form_validation->run() == TRUE ) {
            $data = $this->db->get_where('tbl_withdraw_request',['id'=>$id])->row();
            $userid = $data->userid;
            $this->db->set('comments',$comment)->where('id',$id)->update('tbl_withdraw_request');
           if($status == '1')
           {
            $walletAdd = [
                'is_from_witdraw' => '1',
                'amount' => $data->amount,
                'in_or_out' => '1'
            ];
            if($usertype=='0')
            {
                 $wallet = $this->getAmt('0' , $userid);
                 $wallet_balance = ($wallet['wallet_left_amount']);
                 $walletAdd['sp_id'] = $data->userid;
            }
            if($usertype=='2')
            {    $wallet = $this->getAmt('2' , $userid);
                 $wallet_balance = ($wallet['wallet_left_amount']);
                 $walletAdd['sc_id'] = $data->userid;
            }
            
            if($data->amount>$wallet_balance)
            {
                $this->session->set_flashdata('error', 'No payment in requester wallet!');
            return redirect('admin/withdrawrequest');
            }else{
                if($this->db->insert('tbl_ewallet',$walletAdd))
                {
                    $this->db->set(['payment_status'=>'1' , 'payment_date'=>date('Y-m-d H:i:s')])->where(['id'=>$id])->update('tbl_withdraw_request');
                    $this->session->set_flashdata('success', 'Payment approved successfully');
                    return redirect('admin/withdrawrequest'); 
                }
            }
           }else{//payment declined
             $this->db->set(['payment_status'=>'2'])->where(['id'=>$id])->update('tbl_withdraw_request');
                    $this->session->set_flashdata('success', 'Payment declined successfully');
                    return redirect('admin/withdrawrequest'); 
           }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('admin/withdrawrequest');
        }
    }

}
