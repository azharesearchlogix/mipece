<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH.'/third_party/vendor/autoload.php';
include APPPATH . 'third_party/stripe/init.php';

class Wallet extends REST_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(['Common_model', 'Mail']);
    }
////////////////// Stripe Generate Client Seceret /////////////////
    public function stripeSeceret_put() {
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

        $charge = \Stripe\PaymentIntent::create([
                    'amount' => $json_obj->items->amount,
                    'currency' => $json_obj->currency,
        ]);
        $this->response(
                [
                    'clientSecret' =>  $charge->client_secret
        ]);
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
        $data = $this->getAmt($switch, $user_id);
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
 public function requestForPayment_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $teamid = $this->input->post('teamid');
        $check_key = $this->authentication($userid , $tokenid);
        $offer_letter = $this->db->select('a.pay_rate , a.id as offer_letter_id , b.title as payment_method , b.days , c.id as teamid , c.agreement_id , c.user_id as client_id')
                                 ->from('tbl_offer_letter as a')
                                 ->join('tbl_payment_mode as b' , 'b.id = a.payment_method' ,'left')
                                 ->join('myteams as c' , 'c.id = a.team_id' ,'left')
                                 ->where(['a.provider_id'=>$userid , 'a.team_id'=>$teamid])
                                 ->get()->row();

                 $getFirstTaskDate = $this->db->select('str_to_date(taskdate, "%d/%m/%Y") as my_date')->order_by('str_to_date(taskdate, "%d/%m/%Y") ASC')->get_where('assigntask',['spid'=>$userid , 'teamid'=>$teamid ,'taskstatus!='=>'' , 'taskstatus'=>'Approved' , 'payment_status'=>'0'])->row('my_date');
            if(!empty($getFirstTaskDate))
            {
            $getFirstTaskDate = $getFirstTaskDate;
            }else{
            $getFirstTaskDate = $this->db->select('payment_date as my_date')->order_by('id DESC')->get_where('assigntask',['spid'=>$userid , 'teamid'=>$teamid ,'taskstatus!='=>'' , 'taskstatus'=>'Approved' , 'payment_status'=>'1'])->row('my_date'); 
            }
            $from_date = ($getFirstTaskDate)?$getFirstTaskDate:'1970-01-01';
            $fdate = strtotime($from_date);
            $to_date = date('Y-m-d',strtotime("+ ".$offer_letter->days."days" , $fdate));
            $getAllTask = $this->db->select('a.id , a.userid , a.sc_id , a.spid , a.teamid , a.title , a.taskstatus , a.taskdate , a.task_name , a.start_time , a.end_time , a.is_expire')
                   ->from('assigntask as a')
                   ->where('str_to_date(a.taskdate, "%d/%m/%Y")>=', $from_date)
                   ->where('str_to_date(a.taskdate, "%d/%m/%Y")<=',$to_date)
                   ->where(['a.spid'=>$userid , 'a.teamid'=>$teamid ,'a.taskstatus!='=>'' , 'a.taskstatus'=>'Approved' , 'a.payment_status'=>'0'])
                   ->get()
                   ->result();  
                    if($getAllTask)
         {
             $sum = 0;
            
            foreach($getAllTask as $tasklist)
            {
                $time1 = strtotime($tasklist->start_time);
                $time2 = strtotime($tasklist->end_time);
                $hours = round(abs($time2 - $time1) / 3600,2);
                $hours = ($hours>0)?$hours:0;
                $sum+= $hours;
                $task_id[] = $tasklist->id;

                $taskWiseData[] = [
                  
                    'taskid' => $tasklist->id,
                    'hours' => $hours,
                    'payrate' => $offer_letter->pay_rate,
                    'amount' => ($hours * $offer_letter->pay_rate),
                    'is_expire'=>'0'
                ];
                $check_already = $this->db->get_where('tbl_taskwise_payment',['taskid'=>$tasklist->id])->num_rows();
                if($check_already > 0)
                {
                    $updateArray[] = [
                    'taskid'=>$tasklist->id,
                    'is_expire' => '1',
                    ];
                }
            }
            $tids = implode(',' , $task_id);
         }else{
            return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No task for payment!',
                                
                    ]); 
         } 

         if($check_key['success']->switch_account == '2')//when sc request for pay to client
         {
            $bulkPayment = [
                'userid' => $userid,
                'scid' => $offer_letter->client_id,
                'spid' => 0,
                'teamid' => $teamid,
                'task_ids' => $tids,
                'total_hours' => $sum,
                'payrate' => $offer_letter->pay_rate,
                'amount_pay' => ($sum * $offer_letter->pay_rate)
            ];
         }else{
         if($offer_letter->agreement_id != 0)//when sp request to sc
         {
            $bulkPayment = [
                'userid' => 0,
                'scid' => $offer_letter->client_id,
                'spid' => $userid,
                'teamid' => $teamid,
                'task_ids' => $tids,
                'total_hours' => $sum,
                'payrate' => $offer_letter->pay_rate,
                'amount_pay' => ($sum * $offer_letter->pay_rate)
            ];    
         }
         else{                    
         
            $bulkPayment = [
                'userid' => $offer_letter->client_id,
                'scid' => 0,
                'spid' => $userid,
                'teamid' => $teamid,
                'task_ids' => $tids,
                'total_hours' => $sum,
                'payrate' => $offer_letter->pay_rate,
                'amount_pay' => ($sum * $offer_letter->pay_rate)
            ];                   
         }
         }

          
            
             $this->db->trans_begin();

             $check_bulk = $this->db->get_where('tbl_bulkpayment_request',['task_ids'=>$tids])->num_rows();
                 if($check_bulk>0)
                 {
                    $this->db->set('is_expire','1')->where(['task_ids'=>$tids , 'spid'=>$userid , 'teamid'=>$teamid])->update('tbl_bulkpayment_request');
                 }
                if(isset($updateArray) && !empty($updateArray))
                {
                $this->db->update_batch('tbl_taskwise_payment',$updateArray, 'taskid');
                } 
                $this->db->insert('tbl_bulkpayment_request' , $bulkPayment);
                $insert_id = $this->db->insert_id();
                $this->db->insert_batch('tbl_taskwise_payment',$taskWiseData);
                 
                $this->db->trans_complete();
            
            if($this->db->trans_status() === TRUE)
            {
                 return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Payment request send successfully',
                                 'data' => "$insert_id"
                    ]); 
            }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Payment request not send successfully',
                                
                    ]);  
            }    
       
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



/*-------------------------- Bulk payment details------------------*/
 public function paymentDetails_get($user_id = '' , $teamid = '')
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $user_id;
        $teamid = $teamid;
        $check_key = $this->authentication($userid , $tokenid);
        $teamData = 
        $offer_letter = $this->db->select('a.pay_rate , a.id as offer_letter_id , b.title as payment_method , b.days , c.id as teamid , c.agreement_id , c.user_id as client_id , c.teamname  , CONCAT(d.firstname , " ", d.lastname) as name')
                                 ->from('tbl_offer_letter as a')
                                 ->join('tbl_payment_mode as b' , 'b.id = a.payment_method' ,'left')
                                 ->join('myteams as c' , 'c.id = a.team_id' ,'left')
                                 ->join('logincr as d' , 'd.id = c.user_id' , 'left')
                                 ->where(['a.provider_id'=>$userid , 'a.team_id'=>$teamid])
                                 ->get()->row();

         if(!$offer_letter)
         {
            return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'No offer letter found!',
                    ]);
         }else if($offer_letter->agreement_id != 0)
         {
           
           $getFirstTaskDate = $this->db->select('str_to_date(taskdate, "%d/%m/%Y") as my_date')->order_by('str_to_date(taskdate, "%d/%m/%Y") ASC')->get_where('assigntask',['spid'=>$userid , 'teamid'=>$teamid , 'payment_status'=>'0'])->row('my_date');
            $from_date = ($getFirstTaskDate)?$getFirstTaskDate:'1970-01-01';
            $fdate = strtotime($from_date);
            $to_date = date('Y-m-d',strtotime("+ ".$offer_letter->days."days" , $fdate));
            $myData = [];
            $sum = 0;
             $getAllTask = $this->db->select('a.id , a.userid , a.sc_id , a.spid , a.teamid , a.title , a.taskstatus , a.taskdate , a.task_name , a.start_time , a.end_time , b.teamname')->where('str_to_date(taskdate, "%d/%m/%Y")>=', $from_date)
                                ->from('assigntask as a')
                                ->join('myteams as b' , 'b.id = a.teamid' , 'inner')
                                ->where('str_to_date(a.taskdate, "%d/%m/%Y")<=',$to_date)
                                ->where(['a.spid'=>$userid , 'a.teamid'=>$teamid , 'payment_status'=>'0'])
                                   ->get()
                                   ->result();

             $taskWiseData = [];  
             $sum =  0;                   
           if($getAllTask)
           {
            foreach($getAllTask as $tasklist)
            {
                $time1 = strtotime($tasklist->start_time);
                $time2 = strtotime($tasklist->end_time);
                $hours = round(abs($time2 - $time1) / 3600,2);
                $hours = ($hours>0)?$hours:0;
                if($tasklist->taskstatus == 'Approved')
                {
                $sum+= $hours;
                }
                $my_amount = ($hours * $offer_letter->pay_rate);
                $taskWiseData[] = [
                    'taskid' => $tasklist->id,
                    'taskname' => ucwords($tasklist->task_name),
                    'hours' => "$hours",
                    'payrate' => $offer_letter->pay_rate,
                    'amount' => "$my_amount",
                    'taskstatus' => $tasklist->taskstatus
                ];
            }
        }
        $totamount = ($sum * $offer_letter->pay_rate);
         if($check_key['success']->switch_account == '2')
            {
                $agreement_data = $this->db->select('a.commission , b.teamname')
                                           ->from('tbl_client_agreement as a')
                                           ->join('myteams as b' , 'b.agreement_id = a.id')
                                           ->where(['b.id'=>$teamid])
                                           ->get()
                                           ->row();
                $commission = (isset($agreement_data->commission) && $agreement_data->commission)?$agreement_data->commission:0;
                $cal = ($totamount * $commission )/100;                           
            }else{
                $cal = 0;
            }
            $payble_amount = $totamount + $cal;
            $myData = [
                'date' => date('d/m/Y' , strtotime($from_date)).' - '.date('d/m/Y' , strtotime($to_date)),
                'teamname' => ucwords($offer_letter->teamname),
                'clientname' => ucwords($offer_letter->name),
                'payrate' => $offer_letter->pay_rate,
                'commission_amount' => "$cal",
                'payableamount' => "$payble_amount",
                 'tasklist' =>  $taskWiseData,
            ];

         }else{//it request direct to client
           $getFirstTaskDate = $this->db->select('str_to_date(taskdate, "%d/%m/%Y") as my_date')->order_by('str_to_date(taskdate, "%d/%m/%Y") ASC')->get_where('assigntask',['spid'=>$userid , 'teamid'=>$teamid , 'payment_status'=>'0'])->row('my_date');
            $from_date = ($getFirstTaskDate)?$getFirstTaskDate:'1970-01-01';
            $fdate = strtotime($from_date);
            $to_date = date('Y-m-d',strtotime("+ ".$offer_letter->days."days" , $fdate));
            $getAllTask = $this->db->select('a.id , a.userid , a.sc_id , a.spid , a.teamid , a.title , a.taskstatus , a.taskdate , a.task_name , a.start_time , a.end_time , b.teamname')->where('str_to_date(taskdate, "%d/%m/%Y")>=', $from_date)
                                ->from('assigntask as a')
                                ->join('myteams as b' , 'b.id = a.teamid' , 'inner')
                                ->where('str_to_date(a.taskdate, "%d/%m/%Y")<=',$to_date)
                                ->where(['a.spid'=>$userid , 'a.teamid'=>$teamid , 'payment_status'=>'0'])
                                   ->get()
                                   ->result();

             $taskWiseData = [];  
             $sum = 0;                   
           if($getAllTask)
           {
            foreach($getAllTask as $tasklist)
            {
                $time1 = strtotime($tasklist->start_time);
                $time2 = strtotime($tasklist->end_time);
                $hours = round(abs($time2 - $time1) / 3600,2);
                $hours = ($hours>0)?$hours:0;
                if($tasklist->taskstatus == 'Approved')
                {
                $sum+= $hours;
                }
                $my_amount = ($hours * $offer_letter->pay_rate);
                $taskWiseData[] = [
                    'taskid' => $tasklist->id,
                    'taskname' => ucwords($tasklist->task_name),
                    'hours' => "$hours",
                    'payrate' => $offer_letter->pay_rate,
                    'amount' => "$my_amount",
                    'taskstatus' => $tasklist->taskstatus
                ];
            }
        }
            $payble_amount = ($sum * $offer_letter->pay_rate);
            $myData = [
                'date' => date('d/m/Y' , strtotime($from_date)).' - '.date('d/m/Y' , strtotime($to_date)),
                'teamname' => ucwords($offer_letter->teamname),
                'clientname' => ucwords($offer_letter->name),
                'payrate' => $offer_letter->pay_rate,
                'commission' => "0",
                'payableamount' => "$payble_amount",
                 'tasklist' =>  $taskWiseData,
            ];
        }
        if($myData)
            {             return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data found successfully!',
                                 'data' => $myData
                    ]); 
           }
            else{
             return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No data found!',
                                
                    ]); 
           }                   

                                 
         }

         //////////          Stripe Success   ///////////////
         public function Stripepayment_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');

        if ($token != '') {

            $check_key = $this->checktoken($token, $user_id);

            if ($check_key['status'] == 'true') {
                $switch = $check_key['data']->switch_account;
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],
                    ['field' => 'clientSecret', 'label' => 'clientSecret', 'rules' => 'required|is_unique[tbl_ewallet_payment.clientSecret]',
                        'errors' => [
                            'required' => 'ClientSecret  is required',
                            'is_unique' => 'clientSecret  already exists',
                        ],
                    ],
                    ['field' => 'amount', 'label' => 'amount', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Amount  is required',
                        ],
                    ],
                    ['field' => 'confirmationMethod', 'label' => 'confirmationMethod', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Confirmation Method  is required',
                        ],
                    ],
                    ['field' => 'currency', 'label' => 'currency', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Currency  is required',
                        ],
                    ],
                    ['field' => 'transaction_id', 'label' => 'transaction_id', 'rules' => 'required|is_unique[tbl_ewallet_payment.transaction_id]',
                        'errors' => [
                            'required' => 'transaction_id  is required',
                            'is_unique' => 'transaction_id  already exists',
                        ],
                    ],
                    ['field' => 'isLiveMode', 'label' => 'isLiveMode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'IsLive Mode  is required',
                        ],
                    ],
                    ['field' => 'postalCode', 'label' => 'postalCode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Postal Code  is required',
                        ],
                    ],
                    ['field' => 'brand', 'label' => 'brand', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Brand is required',
                        ],
                    ],
                    ['field' => 'country', 'label' => 'country', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Country is required',
                        ],
                    ],
                    ['field' => 'expiryMonth', 'label' => 'expiryMonth', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Eexpiry Month is required',
                        ],
                    ],
                    ['field' => 'expiryYear', 'label' => 'expiryYear', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Eexpiry Year is required',
                        ],
                    ],
                    ['field' => 'funding', 'label' => 'funding', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Funding is required',
                        ],
                    ],
                    ['field' => 'last4', 'label' => 'last4', 'rules' => 'required',
                        'errors' => [
                            'required' => 'last 4 digit is required',
                        ],
                    ],
                    ['field' => 'paymentMethodId', 'label' => 'paymentMethodId', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Payment Method Id is required',
                        ],
                    ],
                    ['field' => 'paymentMethodTypes', 'label' => 'paymentMethodTypes', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Payment Method Types is required',
                        ],
                    ],
                    ['field' => 'status', 'label' => 'status', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Status is required',
                        ],
                    ],
                    ['field' => 'created', 'label' => 'created', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Created is required',
                        ],
                    ],
                    ['field' => 'canceledAt', 'label' => 'canceledAt', 'rules' => 'required',
                        'errors' => [
                            'required' => 'canceledAt is required',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message'=>'Payment data not added',
                                'error' => $this->form_validation->error_array(),
                    ]);
                } else {
                    if($switch == '1')
                    {
                    $order_id = strtoupper(uniqid('MIPECE'));
                    $data['created'] = date('Y-m-d H:i:s', strtotime($this->input->post('created')));
                    $data['order_id'] = $order_id;
                    $data['payment_through'] = '0';

                    $this->db->insert('tbl_ewallet_payment', $data);
                    if ($this->db->insert_id() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'id' => $this->db->insert_id(),
                                    'order_id' => $order_id,
                                    'message' => 'Paymeny data added successfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Paymeny data added failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                  }else{
                    $this->response(
                                ['status' => 'Failed',
                                    'message' => 'As a client you can added money into wallet!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                  }
                }
            } else {

                $this->response(
                        ['status' => 'Failed',
                            'message' => 'Invalid Token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {

            $this->response(
                    ['status' => 'Failed',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }


    ////////////  Add Money into Wallet ////////////////
    public function addmoneytowallet_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');

        if ($token != '') {

            $check_key = $this->checktoken($token, $user_id);
            if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],                    
                    
                    ['field' => 'amount', 'label' => 'amount', 'rules' => 'required',
                        'errors' => [
                            'required' => 'amount is required',
                        ],
                    ],
                    
                    ['field' => 'order_id', 'label' => 'order_id', 'rules' => 'required|is_unique[tbl_ewallet.order_id]',
                        'errors' => [
                            'required' => 'order_id is required',
                             'is_unique' => 'order id  already exists',
                        ],
                    ],
                    
                    
                    
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message'=>'Wallet data added failed!',
                                'error' => $this->form_validation->error_array(),
                    ]);
                } else {
                    
                    $data['in_or_out'] = '0';

                    $this->db->insert('tbl_ewallet', $data);
                    if ($this->db->insert_id() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'id' => $this->db->insert_id(),
                                    'message' => 'Amount added in wallet successfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Wallet data added failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                }
            } else {

                $this->response(
                        ['status' => 'Failed',
                            'message' => 'Invalid Token',
                            'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                ]);
            }
        } else {

            $this->response(
                    ['status' => 'Failed',
                        'message' => 'Unauthorised Access',
                        'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
            ]);
        }
    }
    //get all payments request date
    public function getRequestPayment_get($userid = '')
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $date = $this->input->get_request_header('date');
        $check_key = $this->authentication($userid , $tokenid);
        if($check_key['success']->switch_account == '1')
        {

            $getPayment = $this->db->select('a.*')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->where(['a.userid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->group_by('DATE(a.created_at)')
                                   ->get()
                                   ->result();
                                
        }else if($check_key['success']->switch_account == '2')
        {
             $getPayment = $this->db->select('a.*')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->where(['a.scid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->group_by('DATE(a.created_at)')
                                   ->get()
                                   ->result();
        }else{

            $getPayment = $this->db->select('a.*')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->where(['a.spid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->group_by('DATE(a.created_at)')
                                   ->get()
                                   ->result();
        }

        if($getPayment)
            {
                foreach($getPayment as $payment)
                {
                $myData[] = [
                    'request_date' => date('d/m/Y' , strtotime($payment->created_at)),

                ];
            }
                return $this->response([
                    'status' => 'success',
                    'message' => 'Data found successfully',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'data' => $myData
                ]);
            }else{
                return $this->response([
                    'status' => 'false',
                    'message' => 'No data found!',
                    'responsecode' => REST_Controller::HTTP_NOT_FOUND
                ]);
            }   
    }
    //all payment by date group as team
    public function getRequestPaymentNext_get($userid = '')
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $my_date = $this->input->get_request_header('date');
        $date = str_replace('/', '-', $my_date);
        $date =  date("Y-m-d", strtotime($date));
        $check_key = $this->authentication($userid , $tokenid);
        if($check_key['success']->switch_account == '1')
        {

            $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.userid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->group_by('b.id')
                                   ->get()
                                   ->result();   
                                
        }else if($check_key['success']->switch_account == '2')
        {
             $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.scid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->group_by('b.id')
                                   ->get()
                                   ->result();
        }else{

            $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.spid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0'])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->group_by('b.id')
                                   ->get()
                                   ->result();
        }

        if($getPayment)
            {
                foreach($getPayment as $payment)
                {
                $myData[] = [
                    'userid' => $payment->userid,
                    'teamid' => $payment->id,
                    'teamname' => ucwords($payment->teamname),
                    'request_date' => date('d/m/Y' , strtotime($payment->created_at)),

                ];
            }
                return $this->response([
                    'status' => 'success',
                    'message' => 'Data found successfully',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'data' => $myData
                ]);
            }else{
                return $this->response([
                    'status' => 'false',
                    'message' => 'No data found!',
                    'responsecode' => REST_Controller::HTTP_NOT_FOUND
                ]);
            }   
    }
    public function getRequestPaymentDetails_get($userid = '')
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $my_date = $this->input->get_request_header('date');
        $date = str_replace('/', '-', $my_date);
        $date =  date("Y-m-d", strtotime($date));
        $teamid = $this->input->get_request_header('teamid');
        $check_key = $this->authentication($userid , $tokenid);
        if($check_key['success']->switch_account == '1')
        {
            $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.userid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0' , 'teamid'=>$teamid])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->get()
                                   ->row();
                                
        }else if($check_key['success']->switch_account == '2')
        {
             $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.scid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0' , 'teamid'=>$teamid])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->get()
                                   ->row();
        }else{
            $getPayment = $this->db->select('a.* , b.teamname')
                                   ->from('tbl_bulkpayment_request as a')
                                   ->join('myteams as b' , 'b.id = a.teamid' , 'left')
                                   ->where(['a.spid'=>$userid , 'a.is_expire'=>'0' , 'a.payment_status'=>'0' , 'teamid'=>$teamid])
                                   ->where("DATE(a.created_at)" ,$date)
                                   ->get()
                                   ->row();
        }

        if($getPayment)
            {
                $payment = $getPayment;
                    $tasks = explode(',',$payment->task_ids);
                    foreach($tasks as $t)
                    {
                        $taskData[] = $this->db->select('a.id as taskid , a.title , a.task_name , b.hours , b.payrate , b.amount')
                                             ->from('assigntask as a')
                                             ->join('tbl_taskwise_payment as b' , 'b.taskid = a.id')
                                             ->where(['b.taskid'=>$t , 'b.is_expire'=>'0'])
                                             ->get()
                                             ->row();

                    }
                $myData = [
                    'payment_id' => $payment->id,
                    'userid' => $userid,
                    'teamid' => $payment->teamid,
                    'teamname' => ucwords($payment->teamname),
                    'total_hours' => $payment->total_hours,
                    'payrate' => $payment->payrate,
                    'commission' => $payment->commission,
                    'amount_pay' => $payment->amount_pay,
                    'request_date' => date('Y-m-d' , strtotime($payment->created_at)),
                    'tasklist' => $taskData

                ];
                return $this->response([
                    'status' => 'success',
                    'message' => 'Data found successfully',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'data' => $myData
                ]);
            }else{
                return $this->response([
                    'status' => 'false',
                    'message' => 'No data found!',
                    'responsecode' => REST_Controller::HTTP_NOT_FOUND
                ]);
            }   
    }
public function sendPayment_post()
{
    $tokenid = $this->security->xss_clean($this->input->get_request_header('Secret-Key'));
    $userid = $this->security->xss_clean($this->input->post('userid'));//payment sender id
    $paymentid = $this->security->xss_clean($this->input->post('paymentid'));//payment sender id
    $check_key = $this->authentication($userid , $tokenid);
  
    $config = [

        ['field'=>'userid' , 'label'=>'userid' , 'rules'=>'trim|required|is_numeric' , 
        'errors' => [
            'required' => 'User id is required',
            'is_numeric' => 'User id should be numeric'
        ]
    ],
     ['field'=>'paymentid' , 'label'=>'paymentid' , 'rules'=>'trim|required|is_numeric' , 
        'errors' => [
            'required' => 'Payment id is required',
            'is_numeric' => 'Payment id should be numeric'
        ]
    ],

    ];
    $this->form_validation->set_data($this->security->xss_clean($this->input->post()));
    $this->form_validation->set_rules($config);
    if($this->form_validation->run() == FALSE)
    {
         return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'errors' => $this->form_validation->error_array(),

        ]);
    }else{

      $paymentData = $this->db->select('a.* , b.agreement_id')
                            ->from('tbl_bulkpayment_request as a')
                            ->join('myteams as b' , 'b.id = a.teamid')
                            ->where(['a.id'=>$paymentid , 'a.payment_status'=>'0'])
                            ->get()
                            ->row();                  
        if($paymentData)
        {
            $payment_for_pay = doubleval($paymentData->amount_pay);
            $taskids = $paymentData->task_ids;
        }else{
            return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'No payment data found!',

        ]);
        }
    if($check_key['success']->switch_account == '1')
    {
        /*----------------- User send payment--------------*/
        $wallet = $this->getAmt(1, $userid);
        $wallet_balance = doubleval($wallet['wallet_left_amount']);
        if($payment_for_pay > $wallet_balance)
        {
             return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'No sufficient balance in your wallet please add amount then pay!',

        ]);
        }else{
            foreach(explode(',',$taskids) as $t)
            {
                $updateTaskTable[] = [
                    'id' => $t,
                    'payment_date' => date('Y-m-d'),
                    'payment_status' => '1',
                    'is_expire' => '1'

                ];
                 $updateTaskWiseTable[] = [
                    'taskid' => $t,
                    'is_expire' => '1'

                ];
            }
            $this->db->trans_start();
            $this->db->update_batch('tbl_taskwise_payment',$updateTaskWiseTable , 'taskid');
            $this->db->update_batch('assigntask',$updateTaskTable , 'id');
            $this->db->where('id',$paymentid)->set(['payment_status'=>'1' , 'payment_date'=>date('Y-m-d') , 'is_expire'=>'1'])->update('tbl_bulkpayment_request');
            $this->db->insert('tbl_ewallet' ,['user_id'=>$userid , 'amount'=>$payment_for_pay , 'in_or_out'=>'1']);//minus amount from client wallet
            if($paymentData->agreement_id!=0)
            {
                 $this->db->insert('tbl_ewallet' ,['user_id'=>$userid , 'amount'=>$payment_for_pay , 'in_or_out'=>'0' , 'sc_id'=>$paymentData->scid]);//send payment to sc
            }else{
                $this->db->insert('tbl_ewallet' ,['user_id'=>$userid , 'amount'=>$payment_for_pay , 'in_or_out'=>'0' , 'sp_id'=>$paymentData->spid]);//send payment to direct sp
            } 
            $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE)
                {
                     return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'Payment not send successfully!',

                ]);
                }else{
                      return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_OK,
                'message' => 'Payment send successfully!',

                ]);
                }
                }
                /*--------------- User send payment end------------*/

        
    }else if($check_key['success']->switch_account == '2')
    {
         /*----------------- User send payment--------------*/
        $wallet = $this->getAmt(1, $userid);
        $wallet_balance = doubleval($wallet['wallet_left_amount']);
        if($payment_for_pay > $wallet_balance)
        {
             return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'No sufficient balance in your wallet please add amount then pay!',

        ]);
        }else{
            foreach(explode(',',$taskids) as $t)
            {
                $updateTaskTable[] = [
                    'id' => $t,
                    'payment_date' => date('Y-m-d'),
                    'payment_status' => '1',
                    'is_expire' => '1'

                ];
                 $updateTaskWiseTable[] = [
                    'taskid' => $t,
                    'is_expire' => '1'

                ];
            }
            $this->db->trans_start();
            $this->db->update_batch('tbl_taskwise_payment',$updateTaskWiseTable , 'taskid');
            $this->db->update_batch('assigntask',$updateTaskTable , 'id');
            $this->db->where('id',$paymentid)->set(['payment_status'=>'1' , 'payment_date'=>date('Y-m-d') , 'is_expire'=>'1'])->update('tbl_bulkpayment_request');
            $this->db->insert('tbl_ewallet' ,['sc_id'=>$userid , 'amount'=>$payment_for_pay , 'in_or_out'=>'1']);//minus amount from sc wallet
             $this->db->insert('tbl_ewallet' ,['user_id'=>0 , 'amount'=>$payment_for_pay , 'in_or_out'=>'0' , 'sp_id'=>$paymentData->spid , 'sc_id' => $userid]);//send scpayment to direct sp
            $this->db->trans_complete();
                if ($this->db->trans_status() === FALSE)
                {
                     return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'Payment not send successfully!',

                ]);
                }else{
                      return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_OK,
                'message' => 'Payment send successfully!',

                ]);
                }
                }
                /*--------------- User send payment end------------*/
    }else{
        return $this->response([
                'status' => 'false',
                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                'message' => 'As a sp user you can\'t request for pay',

        ]);
    }
}


}




}
