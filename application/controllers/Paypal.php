<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include APPPATH . 'third_party/stripe/init.php';
require_once APPPATH . '/libraries/REST_Controller.php';

class Paypal extends REST_Controller {

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

   

    public function Paymentsuccess_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $promocode_id = $this->input->post('promocode_id');

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
                    
                    
                    ['field' => 'isLiveMode', 'label' => 'isLiveMode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'isLiveMode is required',
                        ],
                    ],
                    
                    ['field' => 'created', 'label' => 'created', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Created is required',
                        ],
                    ],
                    
                    ['field' => 'transaction_id', 'label' => 'transaction_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Transaction id is required',
                        ],
                    ],
                    
                    ['field' => 'status', 'label' => 'status', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Status is required',
                        ],
                    ],
                    ['field' => 'amount', 'label' => 'amount', 'rules' => 'required',
                        'errors' => [
                            'required' => 'amount is required',
                        ],
                    ],
                    
                    ['field' => 'currency', 'label' => 'currency', 'rules' => 'required',
                        'errors' => [
                            'required' => 'currency is required',
                        ],
                    ],
                    ['field' => 'paymentMethodTypes', 'label' => 'paymentMethodTypes', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Payment Method Types is required',
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
                    $data['payment_through'] = '1';
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
    
     public function Ordersuccess_post() {

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
                    ['field' => 'order_id', 'label' => 'order_id', 'rules' => 'required',
                        'errors' => [
                            'required' => 'order id  is required',
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
                    $order_id = strtoupper(uniqid('CARE'));
                    $data['created'] = date('Y-m-d H:i:s', $this->input->post('created'));
                    $data['order_id'] = $order_id;
//                    echo '<pre>';
//                    print_r($data);
//                    die;

                    $this->db->insert('tbl_payment', $data);
                    if ($this->db->insert_id() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'id' => $this->db->insert_id(),
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
    
    

}
