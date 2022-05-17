<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include APPPATH . 'third_party/stripe/init.php';
require_once APPPATH . '/libraries/REST_Controller.php';

class Stripe extends REST_Controller {

    public function __construct() {
        parent::__construct();
    }
    
     public function checktoken($token, $userid) {
        $this->load->model('Common_model');

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

    public function index_put() {
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str);
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));

        $charge = \Stripe\PaymentIntent::create([
                    'amount' => $json_obj->items->amount,
                    'currency' => $json_obj->currency,
        ]);
        $this->response(
                [
                    'clientSecret' => $charge->client_secret
        ]);
        
    }
    
    public function Paymentsuccess_post() {

        // $token = $this->input->get_request_header('Secret-Key');
        // $user_id = $this->input->post('user_id');

        // if ($token != '') {

        //     $check_key = $this->checktoken($token, $user_id);
        //     if ($check_key['status'] == 'true') {
                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should  numeric value',
                        ],
                    ],
                    ['field' => 'clientSecret', 'label' => 'clientSecret', 'rules' => 'required|is_unique[tbl_payment.clientSecret]',
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
                    ['field' => 'transaction_id', 'label' => 'transaction_id', 'rules' => 'required|is_unique[tbl_payment.transaction_id]',
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
                    $order_id = strtoupper(uniqid('TEAM'));
                    $data['created'] = date('Y-m-d H:i:s', strtotime($this->input->post('created')));
                    $data['order_id'] = $order_id;
                    $data['payment_through'] = '0';
                    $promocode_id = $this->input->post('promocode_id');
                     if(!empty($promocode_id))
                    {
                        $promo = $this->db->get_where('tbl_promocode',['id'=>$promocode_id])->row();
                         $data['promocode'] = $promo->promocode;
                         $data['discount'] = $promo->discount;
                         $this->db->where('id',$promocode_id)->set('is_expire','1')->update('tbl_promocode');
                    }
//                    echo '<pre>';
//                    print_r($data);
//                    die;

                    $this->db->insert('tbl_payment', $data);
                      $my_insert = $this->db->insert_id();
                    if ($this->db->insert_id() > 0) {
                         $invoice_id = $this->input->post('invoice_id');
                        if(!empty($invoice_id))
                        {
                            $myInv = $this->db->get_where('tbl_invoice',['id'=>$invoice_id])->row();
                            $invData = [
                                'type' => '1',
                                'invoice_id' => $invoice_id,
                                'task_id' => $myInv->task_id,
                                'team_id' => $myInv->team_id,
                                'user_id' => $myInv->user_id,
                                'spid' => $myInv->spid,
                                'order_id' => $order_id,
                                'hours' => $myInv->total_work_hours,
                                'amount' => $myInv->amount,
                                'in_or_out' => '0'

                            ];
                            $this->db->insert('tbl_ewallet',$invData);
                            $this->db->set('paid_status','1')->where('id',$invoice_id)->update('tbl_invoice');
                        }else{
                            $user_id = $this->input->post('user_id');
                            $check = $this->db->get_where('tbl_subscription' ,['user_id'=>$user_id])->num_rows();
                            if($check>0)
                            {
                                $this->db->set('status','0')->where('user_id',$user_id)->update('tbl_subscription');
                            }else{
                                $this->db->insert('tbl_subscription',['user_id'=>$user_id , 'package_id'=>1 , 'status'=>'0']);
                            }
                        }
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'id' => $my_insert,
                                    'order_id' => $order_id,
                                    'message' => 'Payment data added successfully!',
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'Failed',
                                    'message' => 'Payment data added failed!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                }
        //     } else {

        //         $this->response(
        //                 ['status' => 'Failed',
        //                     'message' => 'Invalid Token',
        //                     'responsecode' => REST_Controller::HTTP_FORBIDDEN,
        //         ]);
        //     }
        // } else {

        //     $this->response(
        //             ['status' => 'Failed',
        //                 'message' => 'Unauthorised Access',
        //                 'responsecode' => REST_Controller::HTTP_BAD_GATEWAY,
        //     ]);
        // }
    }
}
