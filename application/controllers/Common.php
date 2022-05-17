<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH.'/third_party/vendor/autoload.php';
use HubSpot\Factory;
use HubSpot\Client\Crm\Contacts\ApiException;
use \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;

class Common extends REST_Controller {

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

    public function signup_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'usertype', 'label' => 'usertype', 'rules' => 'required',
                'errors' => [
                    'required' => 'User type is required',
                ],
            ],
            ['field' => 'sourcemedia', 'label' => 'sourcemedia', 'rules' => 'required',
                'errors' => [
                    'required' => 'Source Media is required',
                ],
            ],
            ['field' => 'tokenid', 'label' => 'tokenid', 'rules' => 'required',
                'errors' => [
                    'required' => 'Token is required',
                ],
            ],
            ['field' => 'firstname', 'label' => 'firstname', 'rules' => 'required',
                'errors' => [
                    'required' => 'First name is required',
                ],
            ],
            ['field' => 'lastname', 'label' => 'lastname', 'rules' => 'required',
                'errors' => [
                    'required' => 'Last name is required',
                ],
            ],
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email is required',
                // 'is_unique' => 'The email has been already taken, please try another email!',
                ],
            ],
            ['field' => 'contact', 'label' => 'contact', 'rules' => 'required',
                'errors' => [
                    'required' => 'contact number is required',
                ],
            ],
            ['field' => 'ssnnum', 'label' => 'ssnnum', 'rules' => 'required',
                'errors' => [
                    'required' => 'SSN number is required',
                ],
            ],
            ['field' => 'password', 'label' => 'password', 'rules' => 'required',
                'errors' => [
                    'required' => 'Password is required',
                ],
            ],
            ['field' => 'address', 'label' => 'address', 'rules' => 'required',
                'errors' => [
                    'required' => 'Address is required',
                ],
            ],
            ['field' => 'country', 'label' => 'country', 'rules' => 'required',
                'errors' => [
                    'required' => 'Country is required',
                ],
            ],
            ['field' => 'city', 'label' => 'city', 'rules' => 'required',
                'errors' => [
                    'required' => 'City is required',
                ],
            ],
            ['field' => 'postalcode', 'label' => 'postalcode', 'rules' => 'required',
                'errors' => [
                    'required' => 'Postal code is required',
                ],
            ],
            ['field' => 'terms', 'label' => 'terms', 'rules' => 'required',
                'errors' => [
                    'required' => 'Terms condition is required',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {

            $token = $this->Common_model->generatestring(100);
            $formArray = [
                'token_security' => $token,
                'usertype' => $this->security->xss_clean(strtolower($this->input->post('usertype'))),
                'sourcemedia' => $this->security->xss_clean($this->input->post('sourcemedia')),
                'tokenid' => $this->input->post('tokenid'),
                'firstname' => $this->security->xss_clean($this->input->post('firstname')),
                'lastname' => $this->security->xss_clean($this->input->post('lastname')),
                'email' => $this->security->xss_clean($this->input->post('email')),
                'contact' => $this->security->xss_clean($this->input->post('contact')),
                'otp' => sprintf("%06d", mt_rand(1, 999999)),
                'ssnnum' => $this->security->xss_clean($this->input->post('ssnnum')),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                'address' => $this->security->xss_clean($this->input->post('address')),
                'country' => $this->security->xss_clean($this->input->post('country')),
                'city' => $this->security->xss_clean($this->input->post('city')),
                'postalcode' => $this->security->xss_clean($this->input->post('postalcode')),
                'latitude' => $this->security->xss_clean($this->input->post('latitude')),
                'longitude' => $this->security->xss_clean($this->input->post('longitude')),
                'refralcode' => strtoupper(uniqid()),
                'terms' => $this->security->xss_clean($this->input->post('terms')),
                'status' => '2',
                'switch_account' => $this->security->xss_clean($this->input->post('usertype')),
            ];

            if (!empty($_FILES['image']['name'])) {
                $file = $_FILES['image']['name'];
                $name = 'image';
                $path = 'users';
                $type = 'jpeg|jpg|png';
                $file_data = $this->Common_model->fileupload($path, $type, $file, $name);
                if (key_exists('error', $file_data)) {
                    $this->response(
                            [
                                'status' => 'false',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                                'message' => $file_data['error'],
                    ]);
                } else {
                    $formArray['image'] = $file_data['file'];
                }
            }
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
            if ($user) {
                if ($user->status == '1' || $user->status == '0') {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'The email id already registered!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                } else {
                    $this->db->update('logincr', $formArray, ['id' => $user->id]);
                    $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
                    $mailArray = [
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'otp' => $user->otp,
                    ];
                    $response = [
                        'id' => "$user->id",
                        'token' => $token,
                    ];
                    $html = $this->load->view('email/registration', $mailArray, TRUE);
                    $res = $this->Mail->sendmail($user->email, 'My piece registration successfully!', $html);
                    if ($res) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Thank you for signing up with my team!',
                                    'data' => $response,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'The email server not working at this moment please try after some time!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                }
            } else {
                $this->db->trans_begin();

                $result = $this->db->insert('logincr', $formArray);
                $user_id = $this->db->insert_id();
                if ($this->input->post('refralcode')) {
                    $referral_user = $this->db->get_where('logincr', ['refralcode' => $this->security->xss_clean($this->input->post('refralcode'))])->row();
                    if ($referral_user) {
                        $this->db->insert('tbl_backup', ['user_id' => $referral_user->id, 'backup_id' => $user_id]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Invalid referral  code, you can register without referral code!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                }

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Something went wrong!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                } else {
                    $this->db->trans_commit();
                    $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
                    $mailArray = [
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'otp' => $user->otp,
                    ];
                    $response = [
                        'id' => "$user_id",
                        'token' => $token,
                    ];
                    $html = $this->load->view('email/registration', $mailArray, TRUE);
                    $res = $this->Mail->sendmail($user->email, 'My piece registration successfully!', $html);
                    if ($res) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Thank you for signing up with my team!',
                                    'data' => $response,
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'The email server not working at this moment please try after some time!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                }
//                $result = $this->db->insert('logincr', $formArray);
//                $user_id = $this->db->insert_id();
//                if ($user_id > 0) {
//
//                    if ($this->input->post('refralcode')) {
//                        $referral_user = $this->db->get_where('logincr', ['refralcode' => $this->security->xss_clean($this->input->post('refralcode'))])->row();
//                        if ($referral_user) {
//                            $this->db->insert('tbl_backup', ['user_id' => $referral_user->id, 'backup_id' => $user_id]);
//                        } else {
//                            $this->response(
//                                    [
//                                        'status' => 'false',
//                                        'message' => 'Invalid referral  code, you can register without referral code!',
//                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
//                            ]);
//                        }
//                    }
//
//                    $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
//                    $mailArray = [
//                        'name' => $user->firstname . ' ' . $user->lastname,
//                        'otp' => $user->otp,
//                    ];
//                    $response = [
//                        'id' => "$user_id",
//                        'token' => $token,
//                    ];
//                    $html = $this->load->view('email/registration', $mailArray, TRUE);
//                    $res = $this->Mail->sendmail($user->email, 'My piece registration successfully!', $html);
//                    if ($res) {
//                        $this->response(
//                                ['status' => 'success',
//                                    'responsecode' => REST_Controller::HTTP_OK,
//                                    'message' => 'Thank you for signing up with my team!',
//                                    'data' => $response,
//                        ]);
//                    } else {
//                        $this->response(
//                                [
//                                    'status' => 'false',
//                                    'message' => 'The email server not working at this moment please try after some time!',
//                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
//                        ]);
//                    }
//                } else {
//                    $this->response(
//                            [
//                                'status' => 'false',
//                                'message' => 'Something went wrong!',
//                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
//                    ]);
//                }
            }
        }
    }

    public function verifyotp_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email id is required',
                    'valid_email' => 'kindly provide the valid email id',
                ],
            ],
            ['field' => 'otp', 'label' => 'otp', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'OTP is required',
                    'numeric' => 'OTP should  numeric value',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
            if ($user) {
                if ($user->status == '0') {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Your account is de-activated!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                } elseif ($user->otp === $this->security->xss_clean($this->input->post('otp'))) {
                    $this->db->update('logincr', ['status' => '1'], ['id' => $user->id]);
                    if ($this->db->affected_rows() > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your OTP verification successfully done!',
                                    'data' => $this->Common_model->getuserdata($user->id),
                        ]);
                    } else {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your OTP verification has allready done!',
                        ]);
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Provided OTP is invalid!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Provided email id does not exist in records!',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                ]);
            }
        }
    }

    public function resendotp_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email id is required',
                    'valid_email' => 'kindly provide the valid email id',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();

            if ($user) {
                if ($user->status == '2') {
                    $otp = sprintf("%06d", mt_rand(1, 999999));
                    $this->db->update('logincr', ['otp' => $otp], ['id' => $user->id]);
                    $mailArray = [
                        'name' => $user->firstname . ' ' . $user->lastname,
                        'otp' => $otp,
                    ];
                    $html = $this->load->view('email/resendotp', $mailArray, TRUE);
                    $res = $this->Mail->sendmail($user->email, 'Resend verification OTP!', $html);
                    if ($res) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'OTP send successfully, please check your inbox!',
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'The email server not working at this moment please try after some time!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                } if ($user->status == '0') {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'User account has been de-activated!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Your OTP verification has allready done!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Provided email id does not exist in records!',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                ]);
            }
        }
    }

    public function login_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email id is required',
                    'valid_email' => 'kindly provide the valid email id',
                ],
            ],
            ['field' => 'password', 'label' => 'password', 'rules' => 'required',
                'errors' => [
                    'required' => 'Password is required',
                ],
            ],
            ['field' => 'tokenid', 'label' => 'tokenid', 'rules' => 'required',
                'errors' => [
                    'required' => 'FCM token is required',
                ],
            ],
            ['field' => 'sourcemedia', 'label' => 'sourcemedia', 'rules' => 'required',
                'errors' => [
                    'required' => 'Source media is required',
                ],
            ],
            
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
            if ($user) {
               
                    $pass = $this->security->xss_clean($this->input->post('password'));
                    if (password_verify($pass, $user->password)) {
                        if ($user->status == '2') {
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Your username is not verify!',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        } elseif ($user->status == '0') {
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'Your account is not active!',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        } else {
                            $updateArray = [
                                'token_security' => $this->Common_model->generatestring(100),
                                'sourcemedia' => $this->security->xss_clean($this->input->post('sourcemedia')),
                                'tokenid' => $this->input->post('tokenid'),
                            ];
                            $this->db->update('logincr', $updateArray, ['id' => $user->id]);
                              $member_count = $this->db->get_where('tbl_members',['user_id'=>$user->id])->num_rows();
                           

                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'You have logged-in successfully!',
                                        'data' => $this->Common_model->getuserdata($user->id),
                                        'member_count' => "$member_count",
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Invalid password!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
               
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Email id does not exist',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                ]);
            }
        }
    }

    public function forgotpasswordotp_post() {
        $data = $this->input->post();
        $config = [
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email id is required',
                    'valid_email' => 'kindly provide the valid email id',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
            if ($user) {
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $this->db->update('logincr', ['otp' => $otp], ['id' => $user->id]);
                $mailArray = [
                    'name' => $user->firstname . ' ' . $user->lastname,
                    'otp' => $otp,
                ];
                $html = $this->load->view('email/forgotpass', $mailArray, TRUE);
                $res = $this->Mail->sendmail($user->email, 'Forgot Password OTP!', $html);
                if ($res) {
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'OTP send successfully, please check your inbox!',
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'The email server not working at this moment please try after some time!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Provided email id does not exist in records!',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                ]);
            }
        }
    }

    public function resetpassword_post() {

        $data = $this->input->post();
        $config = [
            ['field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Email id is required',
                    'valid_email' => 'kindly provide the valid email id',
                ],
            ],
            ['field' => 'password', 'label' => 'password', 'rules' => 'required',
                'errors' => [
                    'required' => 'Password is required',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $user = $this->db->get_where('logincr', ['email' => $this->security->xss_clean($this->input->post('email'))])->row();
            if ($user) {
                $pass = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                $this->db->update('logincr', ['password' => $pass], ['id' => $user->id]);
                if ($this->db->affected_rows() > 0) {
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Your password has been reset successfully. Please login with new password!',
                    ]);
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'The email server not working at this moment please try after some time!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            } else {
                $this->response(
                        [
                            'status' => 'false',
                            'message' => 'Provided email id does not exist in records!',
                            'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                ]);
            }
        }
    }

    public function changepassword_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('id');
        $access = $this->Common_model->Access($user_id, $token);

        if (!key_exists('error', $access)) {
            $data = $this->input->post();
            $config = [
                ['field' => 'id', 'label' => 'id', 'rules' => 'required',
                    'errors' => [
                        'required' => 'User id is required',
                    ],
                ],
                ['field' => 'password', 'label' => 'password', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Password is required',
                    ],
                ],
                ['field' => 'oldpassword', 'label' => 'oldpassword', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Old password is required',
                    ],
                ],
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($config);

            if ($this->form_validation->run() == FALSE) {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => strip_tags(validation_errors()),
                ]);
            } else {
                $user = $this->db->get_where('logincr', ['id' => $user_id])->row();
//                echo '<pre>';
//                print_r($this->input->post('oldpassword'));
                if ($user) {
                    $pass = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
                    if (password_verify($this->input->post('oldpassword'), $user->password)) {
                        $this->db->update('logincr', ['password' => $pass], ['id' => $user->id]);
                        if ($this->db->affected_rows() > 0) {
                            $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Your password has been changed successfully. Please login with new password!',
                            ]);
                        } else {
                            $this->response(
                                    [
                                        'status' => 'false',
                                        'message' => 'something went wrong, please try after some time!',
                                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                            ]);
                        }
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'Sorry! your old password do not correct!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'Provided user id does not exist in records!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            }
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => $access['error'],
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
            ]);
        }
    }

    public function subscriptionpackage_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);

        $package = $this->db->get_where('tbl_subscription_package', ['status' => '0'])->result();

        if ($package) {
            foreach ($package as $val) {
                $result[] = [
                    'id' => $val->id,
                    'name' => $val->name,
                    'rate' => '$'.$val->rate,
                    'discount_rate' => '$'.$val->discount_rate,
                    'days' => $val->days,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }

    public function subscription_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');

        $auth = $this->authentication($user_id, $token);
        $data = $this->input->post();
        if ($this->input->post('subscribe') == '1') {
            $config = [
                ['field' => 'package_id', 'label' => 'package_id', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Package id is required',
                    ],
                ],
                ['field' => 'subscribe', 'label' => 'subscribe', 'rules' => 'required',
                    'errors' => [
                        'required' => 'subscribe option is required',
                    ],
                ],
                ['field' => 'subscribe_id', 'label' => 'subscribe_id', 'rules' => 'required',
                    'errors' => [
                        'required' => 'subscribe id is required',
                    ],
                ],
                ['field' => 'question_id', 'label' => 'question_id', 'rules' => 'required',
                    'errors' => [
                        'required' => 'question id is required',
                    ],
                ],
                ['field' => 'comments', 'label' => 'comments', 'rules' => 'required',
                    'errors' => [
                        'required' => 'comments is required',
                    ],
                ],
            ];
        } else {
            $config = [
                ['field' => 'package_id', 'label' => 'package_id', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Package id is required',
                    ],
                ],
                ['field' => 'subscribe', 'label' => 'subscribe', 'rules' => 'required',
                    'errors' => [
                        'required' => 'subscribe option is required',
                    ],
                ],
            ];
        }


        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $formArray = [
                'user_id' => $user_id,
                'package_id' => $this->input->post('package_id'),
                'question_id' => $this->input->post('question_id'),
                'comments' => $this->input->post('comments'),
            ];

            if ($this->input->post('subscribe') == '1') {
                $this->db->trans_begin();

                $this->db->update('tbl_subscription', ['status' => '1'], ['user_id' => $user_id, 'id' => $this->input->post('subscribe_id')]);
                $this->db->insert('tbl_unsubscribe_user', $formArray);
                $this->db->where('userid',$user_id)->delete('bankdetails');

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'something went wrong, please try after some time!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                } else {
                    $this->db->trans_commit();
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Your have been unsubscribe successfully!',
                    ]);
                }
            } else {
                $validity = 0;
                $days = 0;
                $subscription = $this->db->select('a.*,b.days')->from('tbl_subscription as a')
                                ->join('tbl_subscription_package as b', 'b.id = a.package_id', 'left')
                                ->where(['a.user_id' => $user_id, 'a.status' => '0'])
                                ->get()->row();
                if ($subscription) {
                    $validity = $subscription->days;
                    $datediff = time() - strtotime(date('Y-m-d', strtotime($subscription->created_at)));
                    $days = round($datediff / (60 * 60 * 24));
                }

                if ($days >= $validity) {
                    $this->db->insert('tbl_subscription', ['user_id' => $user_id, 'package_id' => $this->input->post('package_id')]);
                    $affected = $this->db->insert_id();

                    if ($affected > 0) {
                        $this->response(
                                ['status' => 'success',
                                    'responsecode' => REST_Controller::HTTP_OK,
                                    'message' => 'Your have been subscribe successfully!',
                        ]);
                    } else {
                        $this->response(
                                [
                                    'status' => 'false',
                                    'message' => 'something went wrong, please try after some time!',
                                    'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        ]);
                    }
                } else {
                    $this->response(
                            [
                                'status' => 'false',
                                'message' => 'You have already subscription!',
                                'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                    ]);
                }
            }
        }
    }
    
    public function mysubscription_get($user_id = NULL) {
         $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);
       // $mypackage = [];
        $packages = [];

        $package = $this->db->get_where('tbl_subscription_package', ['status' => '0'])->result();
        if ($package) {
            foreach ($package as $val) {
                $packages[] = [
                    'id' => $val->id,
                    'name' => $val->name,
                    'rate' => '$' . $val->rate,
                    'discount_rate' => '$' . $val->discount_rate,
                    'days' => $val->days,
                ];
            }
        }
        $result = $this->db->select('a.*,b.name as package_name,b.discount_rate,b.days')->from('tbl_subscription as a')->join('tbl_subscription_package as b', 'b.id = a.package_id', 'left')->where(['user_id'=>$user_id ,'a.status'=>'0'])->get()->row();
        if ($result) {
            $mypackage = [
                'id' => $result->id,
                'user_id' => $result->user_id,
                'package_id' => $result->package_id,
                'package_name' => $result->package_name,
                'rate' => '$' . $result->discount_rate,
                'subscribe_date' => date('d-m-Y', strtotime($result->created_at)),
                'validity' => $result->days . ' Days',
                'expire_at' => date('d-m-Y', strtotime("+$result->days day", strtotime($result->created_at))),
                'status' => ($result->status=='0')?'0':'1',
            ];
        }else{
          $mypackage = null;  
        }
        $data = [
            'subscription' => $mypackage,
            'packages' => $packages,
        ];
        $this->response(
                ['status' => 'success',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'message' => 'Record found successfully!',
                    'data' => $data,
        ]);
    }
    
    public function interviewsurvey_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);
        if (strtolower($auth['success']->usertype) == '1') {
            $usertype = '0';
        } else {
            $usertype = '1';
        }
        $question = $this->db->get_where('tbl_interview_survey_question', ['user_type' => $usertype])->result();
        if ($question) {
            foreach ($question as $val) {
                $result[] = [
                    'id' => $val->id,
                    'user_type' => $val->user_type,
                    'question' => $val->question,
                    'options' => json_decode($val->options),
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
            ]);
        }
    }
    
    public function interviewsurveyansewr_put($user_id = NULL, $provider_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->put('user_id');
        $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'Token' => $token,
                                'userid' => $user_id,
                                //'team_id' => $this->put('team_id'),
                                'data' => $this->put(),
                    ]);
        $auth = $this->authentication($user_id, $token);
        $data = $this->put();

        $this->form_validation->set_data($this->put());
        $config = [
            ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'User id  is required',
                    'numeric' => 'User id  should  numeric value',
                ],
            ],
            ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required',
                'errors' => [
                    'required' => 'Team id is required',
                ],
            ],
            ['field' => 'survey_for', 'label' => 'survey_for', 'rules' => 'required',
                'errors' => [
                    'required' => 'Survey to is required',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $question_dta = $this->put('question_dta');
            if (!empty($question_dta)) {

                $formArray = [
                    'user_id' => $this->put('user_id'),
                    'survey_type' => '2',
                    'team_id' => $this->put('team_id'),
                    'survey_for' => $this->put('survey_for'),
                ];
                foreach ($question_dta as $k => $q) {
                    $finalArray[] = array_merge($formArray, $q);
                }

                $this->db->trans_begin();
                $this->db->where(['user_id' => $this->put('user_id'), 'team_id' => $this->put('team_id'), 'survey_type' => '2' ])->delete('tbl_survey_answer');

                $this->db->insert_batch('tbl_survey_answer', $finalArray);

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->response(
                            ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Some thing went wrong please try after some time!',
                    ]);
                } else {
                    $this->db->trans_commit();
                    $total_survey = $this->db->group_by('team_id')->get_where('tbl_survey_answer', ['user_id' => $this->put('user_id')])->num_rows();
                    $total_points = $this->db->get_where('tbl_survey_answer', ['user_id' => $this->put('user_id')])->num_rows();
                    $points = count($question_dta);
                    $data = [
                        'total_survey' => "$total_survey",
                        'total_points' => "$total_points",
                        'current_points' => "$points",
                    ];
                    $this->response(
                            ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Your survey has been done successfully!',
                                'data' => $data,
                    ]);
                }
            } else {
                $this->response(
                        ['status' => 'false',
                            'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                            'message' => 'question and answer field are required!',
                ]);
            }
        }
    }
    
    public function unsubscribesurvey_get($user_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);

        $auth = $this->authentication($user_id, $token);
        
        $question = $this->db->get_where('tbl_unsubscribe_question', ['status' => '0'])->result();
        if ($question) {
            foreach ($question as $val) {
                $result[] = [
                    'id' => $val->id,
                    'question' => $val->question,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
            ]);
        }
    }
    
    public function certifications_get($user_id = NULL, $role_id = NULL) {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $role_id = $this->uri->segment(4);

        $auth = $this->authentication($user_id, $token);
//         print_r($auth); die;
        $res = [];
        if ($role_id) {
            $res = $this->db->get_where('tbl_certification', ['status' => '0', 'role_id' => $role_id])->result();
        } else {
            $user_role = $this->db->get_where('tbl_xai_matching', ['user_id' => $auth['success']->id])->row();
            if ($user_role) {
                $res = $this->db->get_where('tbl_certification', ['status' => '0', 'role_id' => $user_role->industry_id])->result();
            }
        }
        $add_skills = $this->db->get_where('tbl_additional_skills', ['status' => '0'])->result();
         $result1 = [];
         if ($add_skills) {
            foreach ($add_skills as $val1) {
                $result1[] = [
                    'id' => $val1->id,
                    'skill' => ucwords($val1->skills),
                ];
            }
        }
        if ($res) {
            foreach ($res as $val) {
                $result[] = [
                    'id' => $val->id,
                    'role_id' => $val->role_id,
                    'title' => $val->title,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result,
                         'additional_skills' => $result1,
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                        'data' => [],
                        'additional_skills' => $result1,
            ]);
        }
    }
     // get leave type and interval
    public function leaveType_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->get_request_header('userid');
        $auth = $this->authentication($userid , $token);
        $leave_type_details = $this->db->get_where('tbl_leave_type',['status'=>'1'])->result();
        $leave_interval_details = $this->db->get_where('tbl_leave_interval',['status'=>'1'])->result();
        if($leave_type_details)
        {
            foreach($leave_type_details as $type)
            {
                $data[] = [
                    'id' => $type->id,
                    'leave_type' => $type->leave_type,
                ];
            }
            foreach($leave_interval_details as $type1)
            {
                $data_interval[] = [
                    'id' => $type1->id,
                    'interval_name' => $type1->interval_name,
                ];
            }
             $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'leave_type_data' => $data,
          'leave_interval_data' => $data_interval,
          ]);
        }else{
          $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    //leave by type
     public function allTeamLeaveByType_post()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);
        $auth = $this->authentication($userid , $token);
        $leave_type_details = $this->db->get_where('tbl_leave_type',['status'=>'1'])->result();
        if($leave_type_details)
        {
            foreach($leave_type_details as $type)
            {
                $leave_data = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_out'=>0.0 , 'user_id'=>$userid ,'leave_type'=>$type->id])->row('total_leave');
                  $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_in'=>0.0 ,'leave_type'=>$type->id ])->row('total_leave');
                $data[] = [
                    'team_id' => $team_id,
                    'user_id' => $userid,
                    'leave_type' => $type->leave_type,
                    'total_leave' => ($leave_data!=null)?$leave_data:'0.0',
                    'used_leave' => ($taken_leave!=null)?$taken_leave:'0.0',
                ];
            }
             $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'leave_type_data' => $data,
         
          ]);
        }else{
          $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    
    // team details and leave details
     public function teamLeaveList_post($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $teams = $this->db->select("a.id,a.teamid,a.status,CONCAT(b.firstname, ' '  , b.lastname) AS name,c.teamname,c.teamimage")
                        ->from('scheduleinterview as a')
                        ->join('logincr as b', 'b.id = a.userid', 'left')
                        ->join('myteams as c', 'c.id = a.teamid', 'left')
                        ->where('b.id IS NOT NULL')
                        ->where('c.teamname IS NOT NULL')
                        ->where(['a.status' => 'Approved'])
                        ->where(['a.spid' => $user_id])
                        ->group_by('a.teamid')
                        ->get()->result();
                        
                if ($teams) {
                    foreach ($teams as $t) {
                 $leave_data = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$t->teamid , 'is_out'=>0.0])->row('total_leave');
                  $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$t->teamid  , 'is_in'=>0.0])->row('taken_leave');
                        $result[] = [
                            'teamid' => $t->teamid,
                            'user_id' => $user_id,
                            'teamname' => $t->teamname,
                            'teamimage' => base_url($t->teamimage),
                            'leave_data' => [ 
                                'total_leave' => ($leave_data!=null)?$leave_data:0,
                                'used_leave' => ($taken_leave!=null)?$taken_leave:0
                              ]
                        ];
               
                       
                    }

                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Record found successfully!',
                        'data' => $result,
                    ];
                } else {
                    $response = [
                        'responsecode' => '404',
                        'status' => 'false',
                        'message' => 'Record not found!',
                    ];
                }
            } else {
                $response = [
                    'responsecode' => '403',
                    'status' => 'false',
                    'message' => 'Invalid Token!',
                ];
            }
        } else {
            $response = [
                'responsecode' => '502',
                'status' => 'false',
                'message' => 'Unauthorised Access!',
            ];
        }
        echo json_encode($response);
    }
    //team details
    public function teamMemLeaveDetails_post($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $teams = $this->db->select('a.*,b.name as language')
                                ->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')
                                ->where(['a.user_id' => $user_id, 'a.status' => '0'])->get()->result();
                if ($teams) {
                    foreach($teams as $teams){
                      //  $result['teammember'] = [];

                    $teammember = $this->db->select("a.*,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image")
                                    ->from('scheduleinterview as a')
                                    ->join('logincr as b', 'b.id = a.spid', 'left')
                                    ->join('myteams as c', 'c.id = a.teamid', 'left')
                                    ->where('b.id IS NOT NULL')
                                    ->where('c.teamname IS NOT NULL')
                                    ->where(['a.status' => 'Approved'])
                                    ->group_by('a.teamid')
                                    ->where('a.teamid', $teams->id)->get()->result();
                    /*------------------- Team members get start-------------------*/
                    $team_mem = [];
                    if ($teammember) {
                      
                      //  $result['teamdetails']['selected_members'] = count($teammember);
                        foreach ($teammember as $mem) {
                             $leave_data = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$teams->id, 'is_out'=>0.0 ,'user_id'=>$mem->spid])->row('total_leave');
                  $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$teams->id  , 'is_in'=>0.0  ,'user_id'=>$mem->spid])->row('taken_leave');
                            $team_mem[] = [
                                'member_id' => $mem->spid,
                                'name' => $mem->name,
                                'image' => $mem->image ? base_url($mem->image) : base_url('upload/user.phpto.png'),
                                 'leave_data' => [ 
                                'total_leave' => ($leave_data!=null)?$leave_data:0,
                                'used_leave' => ($taken_leave!=null)?$taken_leave:0
                              ]
                            ];
                        }
                    }
                    /*------------------- Team members get end-------------------*/
                    if($team_mem)
                    {
                    $result[] = [
                        'team_id' => $teams->id,
                        'user_id' => $teams->user_id,
                        'zipcode' => ($teams->zipcode!=null)?$teams->zipcode:'',
                        'required_members' => $teams->members,
                        'language' => $teams->language,
                        'teamname' => $teams->teamname,
                        'teamimage' => base_url($teams->teamimage),
                        'description' => $teams->description,
                        'team_members' => $team_mem

                    ];
                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Record found successfully!',
                        'data' => $result,
                    ];
                }
                    
                }

                  
                } else {
                    $response = [
                        'responsecode' => '404',
                        'status' => 'false',
                        'message' => 'Record not found!',
                    ];
                }
            } else {
                $response = [
                    'responsecode' => '403',
                    'status' => 'false',
                    'message' => 'Invalid Token!',
                ];
            }
        } else {
            $response = [
                'responsecode' => '502',
                'status' => 'false',
                'message' => 'Unauthorised Access!',
            ];
        }
        echo json_encode((isset($response))?$response:[]);
    }
    public function singleUserLeave_post($team_id=NULL , $user_id = NULL)
    {
      
       $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                $teams = $this->db->select('a.*,b.name as language')
                                ->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')
                                ->where(['a.id' => $team_id, 'a.user_id' => $user_id, 'a.status' => '0'])->get()->row();
                if ($teams) {
                    $result['teamdetails'] = [
                        'team_id' => $teams->id,
                        'user_id' => $teams->user_id,
                        'zipcode' => ($teams->zipcode!=null)?$teams->zipcode:'',
                        'required_members' => $teams->members,
                        'language' => $teams->language,
                        'teamname' => $teams->teamname,
                        'teamimage' => base_url($teams->teamimage),
                        'description' => $teams->description,
                    ];
                
                    $result['teammember'] = [];
                  
                   
                    $teammember = $this->db->select("a.*,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image")
                                    ->from('scheduleinterview as a')
                                    ->join('logincr as b', 'b.id = a.spid', 'left')
                                    ->join('myteams as c', 'c.id = a.teamid', 'left')
                                    ->where('b.id IS NOT NULL')
                                    ->where('c.teamname IS NOT NULL')
                                    ->where(['a.status' => 'Approved'])
                                    ->group_by('a.teamid')
                                    ->where('a.teamid', $team_id)->get()->result();

                    if ($teammember) {
                         $leave_type_details = $this->db->get_where('tbl_leave_type',['status'=>'1'])->result();
       
                        $result['teamdetails']['selected_members'] = count($teammember);
                        foreach ($teammember as $mem) {
                             if($leave_type_details)
        {
            foreach($leave_type_details as $type)
            {
                $leave_data = $this->db->select('sum(is_in) as total_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_out'=>0.0 ,'leave_type'=>$type->id , 'user_id'=>$mem->client_id])->row('total_leave');
                  $taken_leave = $this->db->select('sum(is_out) as taken_leave')->group_by('team_id')->get_where('tbl_leave_history',['team_id'=>$team_id , 'is_in'=>0.0 ,'leave_type'=>$type->id  , 'user_id'=>$mem->client_id])->row('taken_leave');
                $leave_data_my[] = [
                  
                    'leave_type' => $type->leave_type,
                    'total_leave' => ($leave_data!=null)?$leave_data:0,
                    'used_leave' => ($taken_leave!=null)?$taken_leave:0,
                ];
            }
        }
                            $result['teammember'][] = [
                                'member_d' => $mem->client_id,
                                'name' => $mem->name,
                                'image' => $mem->image ? base_url($mem->image) : base_url('upload/user.phpto.png'),
                                'leave_details' => $leave_data_my
                            ];
                        }
                    }

                   

                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Record found successfully!',
                        'data' => $result,
                    ];
                } else {
                    $response = [
                        'responsecode' => '404',
                        'status' => 'false',
                        'message' => 'Record not found!',
                    ];
                }
            } else {
                $response = [
                    'responsecode' => '403',
                    'status' => 'false',
                    'message' => 'Invalid Token!',
                ];
            }
        } else {
            $response = [
                'responsecode' => '502',
                'status' => 'false',
                'message' => 'Unauthorised Access!',
            ];
        }
        echo json_encode($response);
    }
//all leave requests show in service provider end
    public function leaveRequest_post()
    {
        $userid = $this->uri->segment(3);//employee id
        $tokenid = $this->input->get_request_header('Secret-Key');//employee id
        $check_key = $this->authentication($userid , $tokenid);
        if(!empty($userid) && isset($userid))
        {
         //$data = $this->db->select('a.*,a.id as myid , b.*')->from('tbl_leave_history as a')->join('myteams as b' , 'b.id = a.team_id','left')->where('a.user_id',$userid)->where('a.is_approved','0')->where('a.is_in',0.0)->order_by('a.created_at','DESC')->get()->result();
         $data = $this->db->select('a.*, a.id as myid , b.* ,c.firstname , c.lastname , c.image')->from('tbl_leave_history as a')->join('myteams as b' , 'b.id = a.team_id','left')->join('logincr as c' , 'c.id = a.user_id','left')->where('a.user_id',$userid)->where('a.is_approved','0')->where('a.is_in',0.0)->order_by('a.created_at','DESC')->get()->result();
        if($data)
        {
           
          foreach($data as $mm)
          {
            $uu[] = [
                'lid' => $mm->myid,
                'team_id' => $mm->team_id,
                'team_name' => $mm->teamname,
                 'username' => $mm->firstname.' '.$mm->lastname,
                'image' => base_url().$mm->image,
                'leave_taken' => $mm->is_out,
                'status' => ($mm->is_approved=='0')?'pending':'approved',
            ];
          } 
           $this->response([
          'status' => 'true',  
          'message' => 'Record found',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $uu
          ]); 
        }else{
            $this->response([
          'status' => 'false',  
          'message' => 'No record found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);   
        }
        }else{
             $this->response([
          'status' => 'false',  
          'message' => 'User id is required',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    //give leave
     public function giveLeave_post()
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $teamid = $this->input->post('teamid');
        $leave_type = $this->input->post('leave_type');
        $leave_count = $this->input->post('leave_count');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'leave_count', 'label' => 'leave_count', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Number of leave  is required',
                            'numeric' => 'Number of leave  should be numeric',
                        ],
                    ],
                    ['field' => 'leave_type', 'label' => 'leave_type', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Leave type  is required',
                            'numeric' => 'Leave type  should be numeric',
                        ],
                    ],
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
              $teammember = $this->db->select("a.*,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image")
                                    ->from('scheduleinterview as a')
                                    ->join('logincr as b', 'b.id = a.userid', 'left')
                                    ->join('myteams as c', 'c.id = a.teamid', 'left')
                                    ->where('b.id IS NOT NULL')
                                    ->where('c.teamname IS NOT NULL')
                                    ->where(['a.status' => 'Approved'])
                                    ->group_by('a.teamid')
                                    ->where('a.teamid', $teamid)->get()->result();
                if($teammember)
                {
                    foreach($teammember as $tt)
                    {
                        $data[] = [
                            'team_id' => $tt->teamid,
                            'leave_by' => $userid,
                            'user_id' => $tt->client_id,
                            'leave_type' => $leave_type,
                            'is_in' => $leave_count,

                        ];
                    }
                    $this->db->insert_batch('tbl_leave_history',$data);
                    if($this->db->affected_rows()>0)
                    {
                        return $this->response( ['status' => 'true',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Leave assign successfully...',
                    ]);  
                    }else{
                        return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Leave not given....',
                    ]);  
                    }
                }else{
                    return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No members in this team',
                    ]); 
                }
           }
    }
    // get personality
    public function getPersonality_get()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->get_request_header('userid');
        $auth = $this->authentication($userid , $token);
        $personality_data = $this->db->get_where('tbl_personality',['status'=>'0'])->result();
       $result = [];
         if($personality_data)
        {
            $i=$k=0;
            foreach($personality_data as $type)
            {

                ++$i;
                if($i%2!=0)
                {
                //$data[] = $type->id; 
                $data[] = ['id'=>$type->id,'personality'=>$type->personality1];
              }else{
               //$data1[] = $type->id; 
                $data1[] = ['id'=>$type->id,'personality'=>$type->personality1];
              }
               
            }
         foreach($data as $key=>$value ){
      $val=$data1[$key];
      $result[]=array($value,$val);
    }
             $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $result,
          ]);
        }else{
          $this->response([
          'status' => 'false',  
          'message' => 'Data not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }//personality percentage
     public function checkPersonalityStatus_post()
    {
         $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $personality = $this->input->post('personality');
        $leave_count = $this->input->post('leave_count');
        $teamid = $this->input->post('teamid');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'personality', 'label' => 'personality', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Team id  is required',
                        ],
                    ],
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $imp = explode(',', $personality);
            $count = count($imp);
            $find = $this->db->select('count(*) as count')->from('tbl_personality')->where('status','0')->get()->row('count');
            $half_val = floor($find/2);
            $single_per = floor(100/$half_val);
            if($count>0)
            {
                $per = 0;
             for($i=0; $i<$count;$i++)
             {
                $per+=$single_per;
             }   
             if(!empty($teamid))
             {
                $this->db->set(['new_personality'=>$personality , 'percentage'=>$per])->where(['user_id'=>$userid , 'team_id'=>$teamid])->update('tbl_xai_matching');
             }else{
                 $this->db->set(['new_personality'=>$personality , 'percentage'=>$per])->where(['user_id'=>$userid , 'team_id'=>0])->update('tbl_xai_matching');
             }
             return $this->response( ['status' => 'ok',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Data found',
                                'percentage' => "$per"
                    ]); 
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'No personality found!',
                    ]); 
            }
           }
    }
     //check user subscribe or not
    public function checkUserSubscribe_post($value='')
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $check = $this->db->get_where('tbl_subscription',['status'=>'0' ,'user_id'=>$userid])->row();
           
           
            if($check)
            {
                 $data = [
                'userid' => $check->user_id,
                'status' => ($check->status=='0')?'subscribe':'unsubscribe'
                
            ];
                 return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'User subscribe',
                                 'data' => $data
                    ]); 
            }else{
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'User not subscribe',
                                
                    ]); 
            }
           }
    }
    //get user list
     public function getUserList_post()
    {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('user_id');
        $user_type = $this->input->post('user_type');
        $auth = $this->authentication($user_id, $token);
        if($user_type=='0')
        {
         $users = $this->db->group_by('a.id')->select('a.* , b.teamid')
                                ->from('logincr as a')
                                ->join('scheduleinterview as b' , 'b.spid = a.id','left')
                                ->where('b.i_status','0')
                                ->or_where('b.i_status','1')
                                ->or_where('b.i_status','2')
                                ->order_by('create_date','DESC')
                                ->get()->result();
        }else{
             $users = $this->db->group_by('a.id')->select('a.* , b.teamid')
                                ->from('logincr as a')
                                ->join('scheduleinterview as b' , 'b.userid = a.id','left')
                                ->where('b.i_status','0')
                                ->or_where('b.i_status','1')
                                ->or_where('b.i_status','2')
                                ->order_by('create_date','DESC')
                                ->get()->result();
        }
                               

        if ($users) {
            foreach ($users as $val) {
                if($val)
                $result[] = [
                    'id' => $val->id,
                    'user_type' => $val->usertype,
                    'name' => $val->firstname.' '.$val->lastname,
                    'profile' => $val->profile_pic != '' ? base_url('upload/users/' . $val->profile_pic) : '',
                    'create_date' => date('d-m-y H:i', strtotime($val->create_date)),
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //get policy
    public function getPolicy_get($user_id = NULL , $team_id = NULL , $spid = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);

        if(!empty($spid) && $spid!=NULL)
        {
        $auth = $this->authentication($spid, $token);   
        }else{
        $auth = $this->authentication($user_id, $token);
        }

        $policy = $this->db->get_where('tbl_customer_policy', ['status' => '0' , 'team_id'=>$team_id , 'user_id'=>$user_id])->result();

        if ($policy) {
            foreach ($policy as $val) {
                $result[] = [
                    'id' => $val->id,
                    'user_id' =>  $val->user_id,
                    'team_id' => $val->team_id,
                    'question' => $val->question,
                    'description' => $val->description,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //get policy duration
    public function getPolicyDuration_get($user_id = NULL , $team_id = NULL , $spid = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);

        if(!empty($spid) && $spid!=NULL)
        {
        $auth = $this->authentication($spid, $token);   
        }else{
        $auth = $this->authentication($user_id, $token);
        }

        $policy = $this->db->order_by('priority','ASC')->get_where('tbl_customer_policy_duration', ['status' => '0' , 'team_id'=>$team_id , 'user_id'=>$user_id])->result();

        if ($policy) {
            foreach ($policy as $val) {
                $result[] = [
                    'id' => $val->id,
                    'user_id' =>  $val->user_id,
                    'team_id' => $val->team_id,
                    'title' => $val->title,
                    'hours' => $val->hours,
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
     //interview feedback
    //interview feedback
    public function interviewFeedback_post() {
        $data = $this->input->post();
         $this->load->model('Common_model');
         $this->load->model('NotificationModel');

        $date = date("Y-m-d h:i");


        $interviewid = $this->input->post('interviewid');
        $config = [
            ['field' => 'interviewid', 'label' => 'interviewid', 'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'Interview id is required',
                    'numeric' => 'Interview id numeric value',
                ],
            ],
            ['field' => 'message', 'label' => 'message', 'rules' => 'required',
                'errors' => [
                    'required' => 'Message is required',
                ],
            ],
        ];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
            $myData = [

                'feedback_type' =>'0',
                'main_id' => $this->input->post('interviewid'), 
                'user_type' => $this->input->post('user_type'),
                'user_id' => $this->input->post('user_id'),
                'message' => $this->input->post('message'),
            ];
            $this->db->insert('tbl_all_feedback',$myData);
            if($this->db->affected_rows()>0)
            {
                if($this->input->post('user_type')=='0')//when sp
                {
                      $this->db->update('scheduleinterview', ['status' => 'Cancel', 'cancel_date' => date('Y-m-d H:i:s'), 'cancel_by' => $this->input->post('user_id')], ['id' => $this->input->post('interviewid')]);
//                    echo $this->db->last_query();
                    $udata = $this->db->get_where('scheduleinterview', ['id' => $this->input->post('interviewid')])->row();
                        if ($this->db->affected_rows() > 0) {

                        $provider_data = $this->db->get_where('logincr', ['id' => $udata->userid])->row();
                        $message = [
                            'title' => 'Interview cancelled',
                            'body' => 'You have cancelled an interview.',
                            'icon' => base_url('upload/images/notification.png')
                        ];
                        $notification_data = [
                            'device_tpye' => $this->input->post('device_tpye'),
                            'device_token' => $provider_data->tokenid,
                        ];
                        $response = $this->NotificationModel->index($notification_data, $message);
                        $message['user_id'] = $udata->userid;
                        $this->db->insert('tbl_notification', $message);
                    }
                }else{//when user means customer
                    
                    if ($interviewid != '') {

            $data = array(
                'status' => 'Cancel',
                'update_at' => $date
            );

            $spid = $this->db->get_where('scheduleinterview', ['id' => $interviewid])->row()->spid;
            $provider_data = $this->db->get_where('logincr', ['id' => $spid])->row();


            $update_value = $this->Common_model->updateData('scheduleinterview', $data, 'id="' . $interviewid . '" ');

            if ($update_value) {

                $message = [
                    'title' => 'Interview canceled',
                    'body' => 'Your Interview canceled',
                    'icon' => base_url('upload/images/notification.png')
                ];
                $notification_data = [
                    'device_tpye' => $this->input->post('device_tpye'),
                    'device_token' => $provider_data->tokenid,
                ];
                $response = $this->NotificationModel->index($notification_data, $message);
                $message['user_id'] = $spid;
                $this->db->insert('tbl_notification', $message);
            }
        }
                }
                 $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Interview canceled successfully....',
            ]);
            }else{
                 $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Interview canceled successfully....',
            ]);
            }
           
        }
    }
    
    //add contACT
     public function saveNewContact_post() {

        $token = $this->input->get_request_header('Secret-Key');
        $data = $this->input->post();
            $config = [
                ['field' => 'company', 'label' => 'company', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Company is required',
                    ],
                ],
                ['field' => 'email', 'label' => 'email', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Email is required',
                    ],
                ],
                ['field' => 'fname', 'label' => 'fname', 'rules' => 'required',
                    'errors' => [
                        'required' => 'First name is required',
                    ],
                ],
                ['field' => 'lname', 'label' => 'lname', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Last name is required',
                    ],
                ],
                ['field' => 'mobile', 'label' => 'mobile', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Mobile is required',
                    ],
                ],
                ['field' => 'website', 'label' => 'website', 'rules' => 'required',
                    'errors' => [
                        'required' => 'Website is required',
                    ],
                ],
            ];
       


        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($config);

        if ($this->form_validation->run() == FALSE) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => strip_tags(validation_errors()),
            ]);
        } else {
          

$client = Factory::createWithApiKey("4795d346-66b0-41c2-b53a-d33eda59ad6a");

$properties = [
    "company" => $this->input->post('company'),
    "email" => $this->input->post('email'),
    "firstname" => $this->input->post('fname'),
    "lastname" => $this->input->post('lname'),
    "phone" => $this->input->post('mobile'),
    "website" => $this->input->post('website')
];
$simplePublicObjectInput = new SimplePublicObjectInput(['properties' => $properties]);
try {
    $apiResponse = $client->crm()->contacts()->basicApi()->create($simplePublicObjectInput);
     return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Contact details saved successfully',
                    ]); 
} catch (ApiException $e) {
    return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Contact allready added! please used different email',
                                
                    ]); 
}
        }
    }
    
    //get sp task details
    public function getSpTask_get($user_id = NULL , $team_id = NULL , $spid = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);
        $spid = $this->uri->segment(5);

        $auth = $this->authentication($user_id, $token);

        $tasks = $this->db->order_by('id','DESC')->get_where('assigntask', ['teamid'=>$team_id , 'spid'=>$spid])->result();

        if ($tasks) {
            foreach ($tasks as $val) {
                 $feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$val->id])->row('message');
                $result[] = [
                    'id' => $val->id,
                    'user_id' =>  $val->userid,
                    'team_id' => $val->teamid,
                    'title' => $val->title,
                    'taskstatus' => ($val->taskstatus=='')?'Pending':$val->taskstatus,
                    'start_time' => $val->start_time,
                    'end_time' => $val->end_time,
                    'date' => $val->taskdate,
                    'feedback' => (!empty($feedback))?$feedback:"",
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //date wise task
    public function getSpTaskByDate_get($user_id = NULL , $team_id = NULL , $spid = NULL , $date = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $date = $this->input->get_request_header('date');
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);
        $spid = $this->uri->segment(5);
        $auth = $this->authentication($user_id, $token);

        $tasks = $this->db->order_by('id','DESC')->get_where('assigntask', ['teamid'=>$team_id , 'spid'=>$spid ,'taskdate'=>$date])->result();

        if ($tasks) {
            foreach ($tasks as $val) {
                  $approve_feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$val->id])->row();
                $result[] = [
                    'taskid' => $val->id,
                    'user_id' =>  $val->userid,
                    'team_id' => $val->teamid,
                    'title' => $val->title,
                    'taskname' => $val->task_name,
                    'taskstatus' => ($val->taskstatus=='')?'Pending':$val->taskstatus,
                    'taskdescribe' => $val->describe,
                    'comments' => ($val->comments==NULL)?"":$val->comments,
                    'start_time' => $val->start_time,
                    'end_time' => $val->end_time,
                    'date' => $val->taskdate,
                    'approve_feedback' => (isset($approve_feedback->message) && $approve_feedback->message!=null)?$approve_feedback->message:"",
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    
     //task details
     /*
    public function taskDetails_get($user_id = NULL , $task_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $task_id = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);

        $tasks = $this->db->get_where('assigntask', ['id'=>$task_id])->row();

        if ($tasks) {
             $feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$tasks->id])->row('message');
            $result = [
                    'task_id' => $tasks->id,
                    'user_id' =>  $tasks->userid,
                    'team_id' => $tasks->teamid,
                    'spid' => $tasks->spid,
                    'title' => $tasks->title,
                    'taskname' => ($tasks->task_name==NULL)?"":$tasks->task_name,
                    'taskstatus' => ($tasks->taskstatus=='')?'Pending':$tasks->taskstatus,
                    'taskdescribe' => $tasks->describe,
                    'comments' => ($tasks->comments==NULL)?"":$tasks->comments,
                    'start_time' => $tasks->start_time,
                    'end_time' => $tasks->end_time,
                    'taskdate' => $tasks->taskdate,
                    'feedback' => (!empty($feedback))?$feedback:"",
                ];
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }*/
     public function taskDetails_get($user_id = NULL , $task_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $task_id = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);

        $tasks = $this->db->get_where('assigntask', ['id'=>$task_id])->row();
        $task = $this->db->select('a.*')
                                     ->from('tbl_task_status as a')
                                     ->where(['task_id' => $task_id])
                                     ->order_by('id','DESC')
                                     ->get()
                                     ->row();

        if ($tasks) {
             $approve_feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$tasks->id])->row();
               $image_data = $this->db->select('image_path , type')->get_where('tbl_task_status_image',['task_id'=>$task_id])->result();
                $r = [];
              $r1 = [];
               if (!empty($image_data)) {

                    foreach ($image_data as $val) {
                        if ($val->type == '0') {
                            $r[] = [
                                'images' => base_url('upload/tasks/image/' . $val->image_path),
                            ];
                        } else {
                            $r1[] = [
                                'images' => base_url('upload/tasks/image1/' . $val->image_path),
                            ];
                        }
                    }
                }
            $result = [
                    'task_id' => $tasks->id,
                    'user_id' =>  $tasks->userid,
                    'team_id' => $tasks->teamid,
                    'spid' => $tasks->spid,
                    'title' => $tasks->title,
                    'taskname' => ($tasks->task_name==NULL)?"":$tasks->task_name,
                    'taskstatus' => $tasks->taskstatus,
                    'comments' => ($tasks->comments==NULL)?"":$tasks->comments,
                    'start_time' => $tasks->start_time,
                    'end_time' => $tasks->end_time,
                    'taskdate' => $tasks->taskdate,
                    'taskdescribe' => $tasks->describe,
                    'reassign_status' => $tasks->is_reassign,
                    'approve_feedback' => (isset($approve_feedback->message) && $approve_feedback->message!=null)?$approve_feedback->message:"",
                    'before_video' => (isset($task))?base_url('upload/tasks/video/'.$task->video_path):'',
                    'after_video' => (isset($task))?base_url('upload/tasks/video1/'.$task->video_path1):'',
                    'before_image' => $r,
                    'after_image' => $r1,
                ];
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
     /*------------------- Request pay---------------------*/
     public function requestForPayment_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $teamid = $this->input->post('teamid');
        $taskid = $this->input->post('taskid');
        $check_key = $this->authentication($userid , $tokenid);
        $task_data = $this->db->get_where('assigntask',['id'=>$taskid])->row();
                $time1 = strtotime($task_data->start_time);
                $time2 = strtotime($task_data->end_time);
                $hours = round(abs($time2 - $time1) / 3600,2);
                $hours = ($hours>0)?$hours:0;
                $offer_letter = $this->db->get_where('tbl_offer_letter',['provider_id'=>$userid , 'team_id'=>$teamid])->row();
                $payrate =  ($offer_letter)?$offer_letter->pay_rate:0;
                $amount = ($hours*$payrate);
               
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'teamid', 'label' => 'teamid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else if($this->db->get_where('tbl_payment_request',['request_by'=>$userid , 'taskid'=>$taskid])->num_rows()>0) {
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Payment request already exist!',
                                
                    ]); 
           }
           else{
            $check = $this->db->get_where('assigntask',['id'=>$taskid])->row();
           $myData =  [
            'user_id' => $check->userid,
            'request_by' => $userid,
            'teamid' => $teamid,
             'taskid' => $taskid,
            'date' => date('d/m/Y'),
            'time' => date('H:i'),
            'amount' => $amount,
           ];
           $this->db->insert('tbl_payment_request',$myData);
           
            if($this->db->affected_rows()>0)
            {
                $insert_id = $this->db->insert_id();

                 return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Payment request send successfully',
                                 'data' => "$insert_id"
                    ]); 
            }else{
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Payment request not send successfully!',
                                
                    ]); 
            }
           }
    }
    //get payment 
    public function getRequestPayment_get($user_id = NULL , $user_type = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $user_type = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);
        if($user_type==0)//cutomer end
        {
            $payments = $this->db->select('a.* , b.taskdate')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->order_by('a.created_at','DESC')->where(['a.user_id'=>$user_id])->get()->result();
        }else{//sp end
             $payments = $this->db->select('a.* , b.taskdate')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->order_by('a.created_at','DESC')->where(['a.request_by'=>$user_id])->get()->result();
        }
        

        if ($payments) {
            foreach($payments as $pp)
            {
            $result[] = [
                    'id' => $pp->id,
                    'user_id' =>  $pp->user_id,
                    'spid' => $pp->request_by,
                    'amount' => $pp->amount,
                    'taskdate' => $pp->taskdate,
                    'date' =>  $pp->date,
                    'time' => $pp->time,
                    'preview_url' =>  base_url('myteam/requestPayPdfView/'.uniqid().'/'.urlencode(base64_encode($pp->id))),
                    'pdf' => base_url('myteam/requestPayPdf/'.uniqid().'/'.urlencode(base64_encode($pp->id))),
                    
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
     //get payment by date
    public function getPaymentByDate_get($user_id = NULL , $user_type = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $date = $this->input->get_request_header('date');
        $user_id = $this->uri->segment(3);
        $user_type = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);
        if($user_type==0)//cutomer end
        {
            $payments = $this->db->select('a.* ,a.id as my_id, b.* , c.firstname , c.lastname')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=b.spid','left')->order_by('a.created_at','DESC')->where(['a.user_id'=>$user_id , 'a.date'=>$date])->get()->result();
        }else{//sp end
             $payments =$this->db->select('a.*,a.id as my_id , b.* , c.firstname , c.lastname')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=b.userid','left')->order_by('a.created_at','DESC')->where(['a.request_by'=>$user_id , 'a.date'=>$date])->get()->result();
        }
        

        if ($payments) {
            foreach($payments as $pp)
            {
            $result[] = [
                    'id' => $pp->id,
                    'user_id' =>  $pp->user_id,
                    'spid' => $pp->request_by,
                    'name' => $pp->firstname . ' ' . $pp->lastname,
                    'amount' => $pp->amount,
                    'taskdate' => $pp->taskdate,
                    'date' =>  $pp->date,
                    'time' => $pp->time,
                    'task_id' => $pp->id,
                    'team_id' => $pp->teamid,
                    'title' => $pp->title,
                    'taskname' => ($pp->task_name==NULL)?"":$pp->task_name,
                    'taskstatus' => $pp->taskstatus,
                    'comments' => ($pp->comments==NULL)?"":$pp->comments,
                    'start_time' => $pp->start_time,
                    'end_time' => $pp->end_time,
                    'taskdate' => $pp->taskdate,
                     'preview_url' =>  base_url('myteam/requestPayPdfView/'.uniqid().'/'.urlencode(base64_encode($pp->my_id))),
                     'pdf' => base_url('myteam/requestPayPdf/'.uniqid().'/'.urlencode(base64_encode($pp->my_id))),
                ];
            }
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //get request payment details
     //get payment by date
    public function getRequestPaymentDetails_get($user_id = NULL , $user_type = NULL , $id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);
        $user_type = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);
        if($user_type==0)//cutomer end
        {
            $pp =  $this->db->select('a.* , b.* , c.firstname , c.lastname')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=b.spid','left')->order_by('a.created_at','DESC')->where(['a.id'=>$id])->get()->row();
        }else{//sp end
            $pp =  $this->db->select('a.* , b.* , c.firstname , c.lastname')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=b.spid','left')->order_by('a.created_at','DESC')->where(['a.id'=>$id])->get()->row();
        }
        

        if ($pp) {
            $result = [
                   'id' => $pp->id,
                    'user_id' =>  $pp->user_id,
                    'spid' => $pp->request_by,
                    'name' => $pp->firstname . ' ' . $pp->lastname,
                    'amount' => $pp->amount,
                    'taskdate' => $pp->taskdate,
                    'date' =>  $pp->date,
                    'time' => $pp->time,
                    'task_id' => $pp->id,
                    'team_id' => $pp->teamid,
                    'title' => $pp->title,
                    'taskname' => ($pp->task_name==NULL)?"":$pp->task_name,
                    'taskstatus' => $pp->taskstatus,
                    'comments' => ($pp->comments==NULL)?"":$pp->comments,
                    'start_time' => $pp->start_time,
                    'end_time' => $pp->end_time,
                    'taskdate' => $pp->taskdate,
                    'preview_url' =>  base_url('myteam/requestPayPdfView/'.uniqid().'/'.urlencode(base64_encode($id))),
                    'pdf' => base_url('myteam/requestPayPdf/'.uniqid().'/'.urlencode(base64_encode($id))),
                ];
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
     //get all offer letter
    public function getAllOfferLetter_get($user_id = NULL , $user_type = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);//it can be ap or customer
        $user_type = $this->uri->segment(4);
        $auth = $this->authentication($user_id, $token);
        if($user_type==0)//cutomer end
        {
            $data = $this->db->select('a.* , b.teamname , b.teamimage , CONCAT(c.firstname," ", c.lastname) as user_name')->from('tbl_offer_letter as a')->join('myteams as b','b.id = a.team_id' ,'left')->join('logincr as c','c.id = a.user_id','left')->where(['a.user_id'=>$user_id])->order_by('a.created_at','DESC')->get()->result();
        }else{//sp end
            $data = $this->db->select('a.* , b.teamname , b.teamimage , CONCAT(c.firstname," ", c.lastname) as user_name')->from('tbl_offer_letter as a')->join('myteams as b','b.id = a.team_id' ,'left')->join('logincr as c','c.id = a.provider_id','left')->where(['a.provider_id'=>$user_id])->order_by('a.created_at','DESC')->get()->result();
        }
        

        if ($data) {
            foreach($data as $val)
            {
                if($val->status=='1')
                {
                    $my_status = 'Send to Provider';
                }else
                 if($val->status=='2')
                {
                    $my_status = 'Accept By Provider';
                }else if($val->status=='3')
                {
                     $my_status = 'Reject By provider';
                }else{
                   $my_status = 'Offer Generate';  
                }
                $result[] = [
                   'offer_id' => $val->id,
                    'interview_id' => $val->interview_id,
                    'user_name' => $val->user_name,
                    'teamname' => ($val->teamname!=null)?$val->teamname:"",
                    'teamimage' => ($val->teamimage!=null)?base_url($val->teamimage):"",
                    'preview_url' => base_url('myteam/previewofferletter/' . $val->encrypt_key),
                    'pdf_url' => base_url('myteam/downloadofferletter/' . $val->encrypt_key),
                    'status' => $my_status,
                ]; 
            }
           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //reassignd task
 public function reassignedTask_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $taskid = $this->input->post('taskid');
        $date = $this->input->post('date');
        $start_time = $this->input->post('start_time');
        $end_time = $this->input->post('end_time');
         $comment = $this->input->post('comment');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    
                    ['field' => 'taskid', 'label' => 'taskid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Task id  is required',
                            'numeric' => 'Task id  should be numeric',
                        ],
                    ],
                    ['field' => 'date', 'label' => 'date', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Team id  is required',
                        ],
                    ],
                     ['field' => 'start_time', 'label' => 'start_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Start time is required',
                        ],
                    ],
                     ['field' => 'end_time', 'label' => 'end_time', 'rules' => 'required',
                        'errors' => [
                            'required' => 'End time is required',
                        ],
                    ],
                    ['field' => 'comment', 'label' => 'comment', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Comment is required',
                        ],
                    ],
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $check = $this->db->get_where('assigntask',['id'=>$taskid])->row_array();

            if($check)
            {
                if($check['taskstatus']=='Approved')
                {
                    /*
                     $row = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2','main_id'=>$taskid])->row();
                    ($row)?$this->db->insert('tbl_all_feedback',['feedback_type'=>$row->feedback_type ,'user_type'=>$row->user_type , 'user_id'=>$row->user_id , 'main_id'=>$row->main_id , 'message'=> $row->message]):'';
                    $check['id'] = '';
                    $check['taskdate'] = $date;
                    $check['start_time'] = $start_time;
                    $check['end_time'] = $end_time;
                    $check['taskstatus'] = 'Pending';
                    $check['reassigned_comment'] = $comment;
                    $this->db->insert('assigntask',$check);
                    if($this->db->affected_rows()>0)
                    {
                        $insert_id = $this->db->insert_id();
                        $check['id'] = "$insert_id";
                        $this->response(
                        ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Task reassigned successfully!',
                        'data' => $check
                        ]); 
                    }else{
                      return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Task not reassigned successfully!',
                                
                    ]);    
                    }*/

                }else{
                    $check['taskdate'] = $date;
                    $check['start_time'] = $start_time;
                    $check['end_time'] = $end_time;
                    $check['reassigned_comment'] = $comment;
                     $check['is_reassign'] = '1';
                    $this->db->where(['id'=>$taskid])->set($check)->update('assigntask');
                     if($this->db->affected_rows()>0)
                    {
                        $insert_id = $this->db->insert_id();
                        $this->response(
                        ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Task reassigned successfully!',
                        'data' => $check
                        ]); 
                    }else{
                      return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Task not reassigned successfully!',
                                
                    ]);    
                    }

                }
            }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Task data not found!',
                                
                    ]);  
            }
           }
    }
     //for chat get sp data
    public function getAllSpChat_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);//it can be ap or customer
        $auth = $this->authentication($user_id, $token);
       $data = $this->db->select('a.* , b.id as int_id')->from('logincr as a')->join('scheduleinterview as b','b.spid = a.id' ,'left')->where('b.userid',$user_id)->or_where('i_status','0')->or_where('i_status','1')->or_where('i_status','2')->order_by('b.create_at','DESC')->group_by('a.id')->get()->result();
        if ($data) {
            foreach($data as $val)
            {
                $result[] = [
                    'interviewid' => $val->int_id,
                    'id' => $val->id,
                    'user_name' => ucwords($val->firstname.' '.$val->lastname),
                    'user_email' => $val->email,
                   'userimage' => $val->image != '' ? base_url($val->image) : base_url('upload/users/photo.png'),
                   'usertype' => $val->usertype,
                    
                ]; 
            }
           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //get all users
    public function getAllUsersChat_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);//it can be ap or customer
        $auth = $this->authentication($user_id, $token);
       $data = $this->db->select('a.* , b.id as int_id')->from('logincr as a')->join('scheduleinterview as b','b.userid = a.id' ,'left')->where('b.spid',$user_id)->or_where('i_status','0')->or_where('i_status','1')->or_where('i_status','2')->order_by('b.create_at','DESC')->group_by('a.id')->get()->result();
        if ($data) {
            foreach($data as $val)
            {
                $result[] = [
                    'interviewid' => $val->int_id,
                    'id' => $val->id,
                    'user_name' => ucwords($val->firstname.' '.$val->lastname),
                    'user_email' => $val->email,
                   'userimage' => $val->image != '' ? base_url($val->image) : base_url('upload/users/photo.png'),
                    'usertype' => $val->usertype,
                    
                ]; 
            }
           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
    //Send chat Message Notification
        public function sendMsgNotification_post()
        {
        $this->load->model('NotificationModel');
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('sender_id');
        $receiver_id = $this->input->post('receiver_id');
        $last_message = $this->input->post('last_message');
      
        if (empty($user_id)) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'User id is required',
            ]);
        }else{
            $user = $this->db->get_where('logincr',['id'=>$user_id])->row();
            $receiver = $this->db->get_where('logincr',['id'=>$receiver_id])->row();
            if($user && $receiver)
            {
            $message = [
            'type' => '0',  
            'last_message' => $last_message,  
            'user_id' => $receiver->id,
            'sender_id' => $user->id,
            'title' => 'Request for Chat',
            'body' => $user->firstname . ' ' . $user->lastname . ' wants to connect with you',
            'icon' => base_url('upload/images/notification.png'),
            ];
            $this->db->insert('tbl_notification', $message);
            $insert_id = $this->db->insert_id();
            if ($insert_id > 0) {
            $message['notification_id'] = "$insert_id";
            $dataArray = [
            'device_tpye' => $this->input->post('device_tpye'),
            'device_token' => $receiver->tokenid,
            ];
            $res = $this->NotificationModel->index($dataArray, $message);
             $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Notification send successfully',
            ]);
            }else{
              $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Notification not send!',
            ]);  
            }
        }else{
          $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);    
        }
            
            }
        }
        //send video call not
         public function sendCallNotification_post()
        {
        $this->load->model('NotificationModel');
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('sender_id');
        $receiver_id = $this->input->post('receiver_id');
        $room_name = $this->input->post('room_name');
        if (empty($user_id)) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'User id is required',
            ]);
        }else{
            $user = $this->db->get_where('logincr',['id'=>$user_id])->row();
            $receiver = $this->db->get_where('logincr',['id'=>$receiver_id])->row();
            if($user && $receiver)
            {
            $message = [
            'type' => '1',  
            'room_name' => $room_name,  
            'user_id' => $receiver->id,
            'sender_id' => $user->id,
            'title' => 'Request for video call',
            'body' => $user->firstname . ' ' . $user->lastname . ' wants to connect with you',
            'icon' => base_url('upload/images/notification.png'),
            ];
            $this->db->insert('tbl_notification', $message);
            $insert_id = $this->db->insert_id();
            if ($insert_id > 0) {
            $message['notification_id'] = "$insert_id";
            $dataArray = [
            'device_tpye' => $this->input->post('device_tpye'),
            'device_token' => $receiver->tokenid,
            ];
            $res = $this->NotificationModel->index($dataArray, $message);
             $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Notification send successfully',
            ]);
            }else{
              $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Notification not send!',
            ]);  
            }
        }else{
          $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);    
        }
            
            }
        }
        //get all soft skills
    public function getSoftSkills_get($user_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->uri->segment(3);//it can be ap or customer
        $auth = $this->authentication($user_id, $token);
        $data = $this->db->order_by('id','DESC')->get_where('tbl_softskill',['status'=>'0'])->result();
        if ($data) {
            foreach($data as $val)
            {
                $result[] = [
                    'id' => $val->id,
                    'softskill' => $val->name,
                ]; 
            }
           
            $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Record found successfully!',
                        'data' => $result
            ]);
        } else {
            $this->response(
                    [
                        'status' => 'false',
                        'responsecode' => REST_Controller::HTTP_NOT_FOUND,
                        'message' => 'Record not found!',
                        'data' => []
            ]);
        }
    }
     //promocode calculation
    public function checkPromocode_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $promocode = $this->input->post('promocode');
        $amount = $this->input->post('amount');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    
                    ['field' => 'amount', 'label' => 'amount', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Amount is required',
                            'numeric' => 'Amount should be numeric',
                        ],
                    ],
                    ['field' => 'promocode', 'label' => 'promocode', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Promocode is required',
                        ],
                    ],
                     
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $check = $this->db->get_where('tbl_promocode',['promocode'=>trim($promocode) ,'status'=>'0'])->row();
            if($check)
            {
            if($check->is_expire=='0')
            {
            $cal = ceil(round(($amount-$check->discount)));
            $disc = round($check->discount);
            $data = [
                'id' => $check->id,
                'promocode' => $check->promocode,
                'actual_amount' => $amount,
                'discount_amount' => "$disc",
                'new_amount' => ($cal>0)?"$cal":"0",
            ];
           return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Promocode applied successfully',
                                'data' => $data
                                
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Promocode already used!',
                                
                    ]);  
            }
            }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Promocode invalid!',
                                
                    ]);  
            }
           }
    }
     // call status changed
        public function callStatus_post()
        {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $notification_id = $this->input->post('notification_id');
        $status = $this->input->post('status');

        $auth = $this->authentication($user_id, $token);
       
        if (empty($notification_id)) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Notification id is required',
            ]);
        }else{
            $row = $this->db->set(['meeting_status'=>$status])->where(['id'=>$notification_id])->update('tbl_notification');
            if ($row) {
             $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Status updated successfully',
                      
            ]);
            }else{
              $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Status not updated successfully',
            ]);  
            }
            
            }
        }
         // get notificatio dATA
        public function myCallData_post()
        {
        $token = $this->input->get_request_header('Secret-Key');
        $user_id = $this->input->post('userid');
        $notification_id = $this->input->post('notification_id');

        $auth = $this->authentication($user_id, $token);
       
        if (empty($notification_id)) {
            $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Notification id is required',
            ]);
        }else{
            $row = $this->db->get_where('tbl_notification',['id'=>$notification_id])->row();
            if ($row) {
             $data = [
                'user_id' => $row->user_id,
                'type' => ($row->type=='1')?'video':'message',
                'room_name' => ($row->room_name!=NULL)?$row->room_name:'',
                'notification_id' => $row->id,
                'title' => $row->title,
                'body' => $row->body,
                'date_time' => date('d-m-Y H:i',strtotime($row->created_at)),
                'meeting_status' => $row->meeting_status,
                'last_message' => ($row->last_message!=NULL)?$row->last_message:'',
             ];
             $this->response(
                    ['status' => 'success',
                        'responsecode' => REST_Controller::HTTP_OK,
                        'message' => 'Data found successfully',
                        'data' => $data
            ]);
            }else{
              $this->response(
                    ['status' => 'false',
                        'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                        'message' => 'Data not found!',
            ]);  
            }
            
            }
        }
        public function additionalSkills_get($user_id = NULL)
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $user_id;
        $auth = $this->authentication($userid , $token);
        $rows = $this->db->get_where('tbl_additional_skills',['status'=>'0'])->result();
        if($rows)
        {
            foreach($rows as $row)
            {
                $data[] = [
                    'id' => $row->id,
                    'skill' => ucwords($row->skills),
                ];
            }
             return $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $data,
          ]);
        }else{
          return $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    public function getRelationship_get()
    {
        $rows = $this->db->order_by('relationship','ASC')->get_where('tbl_relationship',['status'=>'1' , 'parent_id'=>0])->result();
        if($rows)
        {
            foreach($rows as $row)
            {
               $my = $this->db->get_where('tbl_relationship',['status'=>'1' , 'parent_id'=>$row->id])->result();
               if($my)
               {
                foreach($my as $row1)
                $newData[] = [
                    'id' => $row1->id,
                    'title' => ucwords($row1->relationship),
                    'parent_id' => $row1->parent_id,
                ];
               }else{
                $newData = [];
               }
                $data[] = [
                    'id' => $row->id,
                    'relationship' => ucwords($row->relationship),
                    'issues' => $newData
                ];
            }
             return $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $data,
          ]);
        }else{
          return $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    //add member api
    public function addMember_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $name = $this->input->post('name');
        $relationship = $this->input->post('relationship');
        $issues = $this->input->post('issues');
        //$personality = $this->input->post('personality');
        $others = $this->input->post('others');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    
                   
                    ['field' => 'name', 'label' => 'name', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Name is required',
                        ],
                    ],
                    ['field' => 'relationship', 'label' => 'relationship', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Realationship is required',
                        ],
                    ],
                   // ['field' => 'personality', 'label' => 'personality', 'rules' => 'required',
                      //  'errors' => [
                        //    'required' => 'Personality is required',
                        //],
                   // ],

                     
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
              
                 if (!empty($_FILES['file']['name'])) {
                      $configF['upload_path'] = './upload/members_file/';
                $configF['allowed_types'] = 'jpeg|jpg|png|pdf';
                $configF['max_size'] = 50600;
                $this->load->library('upload', $configF);
                        if (!$this->upload->do_upload('file')) {
                            $response = ['status' => 'false',
                                 'responsecode' => '403',
                                 'message' => strip_tags($this->upload->display_errors()),
                            ];
                           
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $fname = 'upload/members_file/' . $this->upload->data('file_name');
                        }
                    }
            $myData = [
             'user_id' => $userid,
             'name' => $name,
             'relationship' => $relationship,
             //'personality' => $personality,
             'issues' => $issues,
             'others' => $others,
             'file' => (!empty($fname))?$fname:NULL, 
            ];
            $this->db->insert('tbl_members',$myData);
            if($this->db->affected_rows()>0)
            {
                $insert_id = $this->db->insert_id();
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Member added successfully',
                                'id' => "$insert_id"
                                
                    ]);
            }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Member not added successfully!',
                                
                    ]);  
            }
        }
    }
    //update member
     public function updateMember_post()
    {

       $tokenid = $this->input->get_request_header('Secret-Key');
        $id = $this->input->post('id');
        $userid = $this->input->post('userid');
        $name = $this->input->post('name');
        $relationship = $this->input->post('relationship');
        $issues = $this->input->post('issues');
       // $personality = $this->input->post('personality');
        $others = $this->input->post('others');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    
                   
                    ['field' => 'name', 'label' => 'name', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Name is required',
                        ],
                    ],
                    ['field' => 'relationship', 'label' => 'relationship', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Realationship is required',
                        ],
                    ],
                    /*['field' => 'personality', 'label' => 'personality', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Personality is required',
                        ],
                    ],*/

                     
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
                $row = $this->db->get_where('tbl_members',['id'=>$id])->row();
               $configF['upload_path'] = './upload/members_file/';
                $configF['allowed_types'] = 'jpeg|jpg|png|pdf';
                $configF['max_size'] = 50600;
                $this->load->library('upload', $configF);
                 if (!empty($_FILES['file']['name'])) {
                        if (!$this->upload->do_upload('file')) {
                            $response = ['status' => 'false',
                                 'responsecode' => '403',
                                 'message' => strip_tags($this->upload->display_errors()),
                            ];
                           
                        } else {
                                if(isset($row->file) && $row->file!=NULL)
                                {
                                unlink($row->file);
                                }
                            
                            $data = array('upload_data' => $this->upload->data());
                            $fname = 'upload/members_file/' . $this->upload->data('file_name');
                        }
                    }
                    if(isset($row->file) && !empty($row->file))
                    {
                        $ofile = $row->file;
                    }else{
                        $ofile = '';
                    }
            $myData = [
             'name' => $name,
             'relationship' => $relationship,
             //'personality' => $personality,
             'issues' => $issues,
             'others' => $others,
             'file' => (!empty($fname))?$fname:$ofile,
            ];
            $this->db->set($myData)->where('id',$id)->update('tbl_members');
            if($this->db->affected_rows()>0)
            {
                $insert_id = $this->db->insert_id();
                return $this->response( ['status' => 'success',
                                         'member_id'=> "$id",
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Member updated successfully',
                                
                    ]);
            }else{
                return $this->response( ['status' => 'false',
                'member_id'=> "$id",
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Member already updated!',
                                
                    ]);  
            }
        }
    }
    //delete member
    public function deleteMember_delete() {
        $token = $this->input->get_request_header('Secret-Key');
        $data = json_decode(file_get_contents("php://input"));
        $check_key = $this->authentication($data->userid , $token);
        if(isset($data->id))
        {
            $row = $this->db->get_where('tbl_members',['id'=>$data->id])->row();
        ($row->file!=NULL)?unlink($row->file):'';
        $this->db->delete('tbl_members',['id'=>$data->id]);
        if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Member deleted successfully',
                                
                    ]);
            }else{
                return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Member not deleted successfully!',
                                
                    ]);  
            }
        }else{
            return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => 'Member not deleted successfully!',
                                
                    ]);  
        }
    }
    //get all member
    public function getAllMembers_get($user_id = NULL)
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $user_id;
        $auth = $this->authentication($userid , $token);
         $rows = $this->db->select('a.* , b.id as relationship_id , b.relationship')->from('tbl_members as a')->join('tbl_relationship as b','b.id=a.relationship','left')->where(['a.status' => '0', 'a.user_id' => $userid])->get()->result();
        if($rows)
        {
            foreach($rows as $row)
            {
          $personalityData = [];
          $personalityGet = $this->db->where_in('id',explode(',', $row->personality))->get('tbl_personality')->result();
            if($personalityGet)
            {
                foreach($personalityGet as $pk)
                {
                    $personalityData[] =['id'=>$pk->id , 'personality'=>$pk->personality1];
                }
            }
            $issueData = [];
            $issueGet = $this->db->where_in('id',explode(',', $row->issues))->get('tbl_relationship')->result();
            if($issueGet)
            {
                foreach($issueGet as $sk)
                {
                    $issueData[] =['id'=>$sk->id , 'title'=>$sk->relationship , 'parent_id'=>$sk->parent_id];
                }
            }

                $data[] = [
                    'id' => $row->id,
                    'user_id' => $row->user_id,
                    'name' => $row->name,
                    'relationship_id' => $row->relationship_id,
                    'relationship' => $row->relationship,
                    'personality' => $personalityData,
                    'issues' => $issueData,
                    'others' => ($row->others!=NULL)?$row->others:"",
                    'file' => ($row->file!=NULL)?$row->file:"",
                ];
            }
             return $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $data,
          ]);
        }else{
          return $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    
     //get reason of leaving
    public function getleavingReason_get($user_id = NULL)
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $user_id;
        $auth = $this->authentication($userid , $token);
         $rows = $this->db->select('a.*')->from('tbl_reason_leaving as a')->where(['a.status' => '0'])->get()->result();
        if($rows)
        {
            foreach($rows as $row)
            {

                $data[] = [
                    'id' => $row->id,
                    'title' => $row->title,
                ];
            }
            return $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $data,
          ]);
        }else{
          return $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    //get working condition
    public function getworkingCondition_get($user_id = NULL)
    {
        $token = $this->input->get_request_header('Secret-Key');
        $userid = $user_id;
        $auth = $this->authentication($userid , $token);
         $rows = $this->db->select('a.*')->from('tbl_working_condition as a')->where(['a.status' => '0'])->get()->result();
         $rows1 = $this->db->select('a.*')->from('tbl_seniorcare_type as a')->where(['a.status' => '0'])->get()->result();
        if($rows)
        {
            if($rows1)
        {
             foreach($rows1 as $row1)
            {

                $data1[] = [
                    'id' => $row1->id,
                    'name' => $row1->name,
                ];
            }
        }
            foreach($rows as $row)
            {

                $data[] = [
                    'id' => $row->id,
                    'title' => $row->title,
                ];
            }
            return $this->response([
          'status' => 'success',  
          'message' => 'Record found successfully!',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $data,
          'seniorcaredata' => $data1
          ]);
        }else{
          return $this->response([
          'status' => 'false',  
          'message' => 'Record not found!',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);  
        }
    }
    //switch account
      public function switchAccount_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $type = $this->input->post('type');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                    ['field' => 'type', 'label' => 'type', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Type is required',
                        ],
                    ],
                     
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $this->db->where('id',$userid)->set('switch_account',$type)->update('logincr');
            if($this->db->affected_rows()>0)
            {
                 return $this->response([
          'status' => 'success',  
          'message' => 'Account switched successfully',
          'responsecode' => REST_Controller::HTTP_OK,
          'data' => $this->Common_model->getuserdata($userid),
          ]);
            }else{
                 return $this->response([
          'status' => 'false',  
          'message' => 'Account not switched successfully',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);
            }
           }
    }
     /*----------------------- Upload data------------------*/
     public function uploadRequiredDoc_post()
    {
        $this->load->library('upload');
        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $check_key = $this->authentication($userid , $tokenid);
         $config = [
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                   
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            //w4 document upload
           if(!empty($_FILES['w4_doc']['name']))
           {
            $config['file_name'] = uniqid().$_FILES['w4_doc']['name'];
            $config['upload_path'] = './upload/required_doc/w4_doc/';
            $config['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
            $this->upload->initialize($config);
            if ( ! $this->upload->do_upload('w4_doc')){
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => $this->upload->display_errors(),
                            ]);
            }
            else{
               $w4_doc_filename = $config['upload_path'].$this->upload->data('file_name');
            }
           }
            //w9 document upload
           if(!empty($_FILES['w9_doc']['name']))
           {
            $config1['file_name'] = uniqid().$_FILES['w9_doc']['name'];
            $config1['upload_path'] = './upload/required_doc/w9_doc/';
            $config1['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
            $this->upload->initialize($config1);
            if ( ! $this->upload->do_upload('w9_doc')){
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => $this->upload->display_errors(),
                            ]);
            }
            else{
               $w9_doc_filename = $config1['upload_path'].$this->upload->data('file_name');
            }
           }
           //nda agreement document upload
           if(!empty($_FILES['nda_agreement_doc']['name']))
           {
            $config2['file_name'] = uniqid().$_FILES['nda_agreement_doc']['name'];
            $config2['upload_path'] = './upload/required_doc/nda_agreement_doc/';
            $config2['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
            $this->upload->initialize($config2);
            if ( ! $this->upload->do_upload('nda_agreement_doc')){
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => $this->upload->display_errors(),
                            ]);
            }
            else{
               $nda_agreement_doc_filename = $config2['upload_path'].$this->upload->data('file_name');
            }
           }
           //non competes document upload
           if(!empty($_FILES['non_competes_doc']['name']))
           {
            $config3['file_name'] = uniqid().$_FILES['non_competes_doc']['name'];
            $config3['upload_path'] = './upload/required_doc/non_competes_doc/';
            $config3['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
           $this->upload->initialize($config3);
            if ( ! $this->upload->do_upload('non_competes_doc')){
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => $this->upload->display_errors(),
                            ]);
            }
            else{
               $non_competes_doc_filename = $config3['upload_path'].$this->upload->data('file_name');
            }
           }
           //employee handbook document upload
           if(!empty($_FILES['emp_handbook_doc']['name']))
           {
            $config4['file_name'] = uniqid().$_FILES['emp_handbook_doc']['name'];
            $config4['upload_path'] = './upload/required_doc/emp_handbook_doc/';
            $config4['allowed_types'] = 'jpeg|jpg|png|pdf|doc|docx';
            $this->upload->initialize($config4);
            if ( ! $this->upload->do_upload('emp_handbook_doc')){
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => $this->upload->display_errors(),
                            ]);
            }
            else{
               $emp_handbook_doc_filename = $config4['upload_path'].$this->upload->data('file_name');
            }
           }
           $upData = [
            'user_id' => $userid,
            'w4_doc' => (isset($w4_doc_filename))?$w4_doc_filename:'',
            'w9_doc' => (isset($w9_doc_filename))?$w9_doc_filename:'',
            'nda_agreement_doc' => (isset($nda_agreement_doc_filename))?$nda_agreement_doc_filename:'',
            'non_competes_doc' => (isset($non_competes_doc_filename))?$non_competes_doc_filename:'',
            'emp_handbook_doc' => (isset($emp_handbook_doc_filename))?$emp_handbook_doc_filename:'',
           ];
           $this->db->insert('tbl_five_requireddoc',$upData);
            if($this->db->affected_rows()>0)
            {
                return $this->response( ['status' => 'success',
                                'responsecode' => REST_Controller::HTTP_OK,
                                'message' => 'Document added Successfully...',
                    ]);
            }else{
                 return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Document not added Successfully...',
                            ]);
            }
           }
    }
    
    /*------------------- Get all sp when tem created accrding to member --------------- */
    public function getAllSpList_get($userid = NULL , $team_id = NULL , $member_id = NULL)
    {
        $tokenid = $this->input->get_request_header('Secret-Key');
        $check_key = $this->authentication($userid , $tokenid);
        if($member_id!=0)//when xai create at the time of member creation
        {
         $xaidata_data = $this->db->get_where('tbl_xai_matching', ['user_id' => $userid , 'member_id'=>$member_id , 'team_id'=>$team_id])->row();
         $industry = (isset($xaidata_data->industry_id))?$xaidata_data->industry_id:0;

         $xaidata = $this->db->get_where('tbl_xai_matching', ['user_id' => $userid , 'member_id'=>$member_id , 'team_id'=>$team_id])->result();

                if ($xaidata) {
                    foreach ($xaidata as $k => $re) {
                        $industries[] = $re->industry_id;
                        $all[$re->industry_id] = [
                            'budget' => $re->rate,
                            'skills' => $re->skill_id,
                            'experience' => $re->experience_id,
                            'personality' => $re->personality,
                            'members' => $re->members,
                            'factors' => $re->factors,
                            'available_days' => $re->available_days,
                            'start_time' => $re->start_time,
                            'end_time' => $re->end_time,
                            'seven_24' => $re->seven_24,
                            'interest' => $re->interest,
                            'backup' => $re->backup,
                            'accessment' => $re->accessment,
                            'communication_id' => $re->communication_id,
                            'expectation' => $re->expectation,
                            'driving_distance' => $re->driving_distance,
                            'xai_personality' => $re->xai_personality,
                            'motivation' => $re->motivation,
                            'language' => $re->language,
                            'softskills_id' => $re->softskills_id,
                            'additional_skills' => $re->additional_skills,
                            'frequency_id' => $re->frequency_id,
                            'options_preferences' => $re->options_preferences,
                            'covid_vaccination_proof' => $re->covid_vaccination_proof,
                            'negative_tests_proof' => $re->negative_tests_proof,
                            'none' => $re->none,
                        ];
                    }

         $getSp = $this->db->select('c.* , b.id , b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name,b.address , b.usertype,d.name as industry , d.id as industry_id , e.name as expe')
                           ->from('tbl_offer_letter as a')
                           ->join('logincr as b','b.id = a.provider_id','left')
                           ->join('tbl_xai_matching as c','c.user_id = a.provider_id' , 'left')
                           ->join('tbl_industries as d', 'c.industry_id = d.id', 'left')
                           ->join('tbl_experience as e', 'c.experience_id = e.id', 'left')
                           ->where(['c.type'=>'0' , 'c.industry_id'=>$industry , 'a.status'=>'2' , 'a.user_id'=>$userid])
                           ->group_by('b.id')
                           ->get()
                           ->result();
        if($getSp)
        {
             $total = 0;
            foreach($getSp as $val)
            {
                $per = 0;
                /*-------------------- Percent Calculate-----------------*/
                $com = count(explode(',',$all[$val->industry_id]['communication_id']));
                               $com1 = count(explode(',',$val->communication_id));
                                $soft = count(explode(',',$all[$val->industry_id]['softskills_id']));
                               $soft1 = count(explode(',',$val->softskills_id));
                                $add = count(explode(',',$all[$val->industry_id]['additional_skills']));
                               $add1 = count(explode(',',$val->additional_skills));
                                $fr = count(explode(',',$all[$val->industry_id]['frequency_id']));
                               $fr1 = count(explode(',',$val->frequency_id));
                                
                                $per += (
                                         $this->percent($all[$val->industry_id]['skills'], $val->skill_id)+ 
                                         $this->percent($all[$val->industry_id]['experience'], $val->experience_id)+ 
                                         $this->percent($all[$val->industry_id]['budget'], $val->rate)+
                                          $this->percent(is_numeric($all[$val->industry_id]['personality'])?$all[$val->industry_id]['personality']:1,is_numeric($val->personality)?$val->personality:2)+
                                            $this->percent($all[$val->industry_id]['members'], $val->members)+
                                            $this->percent(is_numeric($all[$val->industry_id]['factors']), is_numeric($val->factors))+
                                            $this->percent(is_numeric($all[$val->industry_id]['available_days'])?$all[$val->industry_id]['available_days']:1,is_numeric($val->available_days)?$val->available_days:2)+
                                             $this->percent(is_numeric($all[$val->industry_id]['start_time'])?$all[$val->industry_id]['start_time']:1,is_numeric($val->start_time)?$val->start_time:2)+
                                            $this->percent(is_numeric($all[$val->industry_id]['end_time'])?$all[$val->industry_id]['end_time']:1,is_numeric($val->end_time)?$val->end_time:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['interest'])?$all[$val->industry_id]['interest']:1,is_numeric($val->interest)?$val->interest:2)+
                                              $this->percent(($all[$val->industry_id]['backup']=='1')?is_numeric($all[$val->industry_id]['backup']):1,($val->interest=='1')?is_numeric($val->interest):0)+
                                              $this->percent(is_numeric($all[$val->industry_id]['accessment'])?$all[$val->industry_id]['accessment']:1,is_numeric($val->accessment)?$val->accessment:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['expectation'])?$all[$val->industry_id]['expectation']:1,is_numeric($val->expectation)?$val->expectation:2)+
                                                $this->percent(is_numeric($all[$val->industry_id]['xai_personality'])?$all[$val->industry_id]['xai_personality']:1,is_numeric($val->xai_personality)?$val->xai_personality:2)+
                                                 $this->percent(is_numeric($all[$val->industry_id]['motivation'])?$all[$val->industry_id]['motivation']:1,is_numeric($val->motivation)?$val->motivation:2)+
                                                   $this->percent(is_numeric($all[$val->industry_id]['language'])?$all[$val->industry_id]['language']:1,is_numeric($val->language)?$val->language:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['options_preferences'])?$all[$val->industry_id]['options_preferences']:1,is_numeric($val->options_preferences)?$val->options_preferences:2)+
                                              $this->percent(($all[$val->industry_id]['covid_vaccination_proof']=='0')?1:2,($val->covid_vaccination_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['negative_tests_proof']=='0')?1:2,($val->negative_tests_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['none']=='0')?1:2,($val->none=='1')?1:2)+
                                                   $this->percent($com?$com:0,$com1?$com1:0)+
                                                   $this->percent($soft?$soft:0,$soft1?$soft1:0)+
                                                   $this->percent($add?$add:0,$add1?$add1:0)+
                                                    $this->percent($fr?$fr:0,$fr1?$fr1:0)+
                                                    $this->percent(is_numeric($all[$val->industry_id]['driving_distance']), is_numeric($val->driving_distance))



                                         )
                                     /21;
                                $total += $per;


                /*------------ Percent Calculate end--------------------*/
           $result[] = [
                                        'provider_id' => $val->id,
                                        'usertype' => $val->usertype,
                                        'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                        'name' => $val->user_name,
                                        'fees' => '$' . $val->rate,
                                        'percent' => round($per) . '%',
                                        'industry_id' => $val->industry_id,
                                        'industry' => $val->industry,
                                       // 'skillname' => $val->skillname ? $val->skillname : '',
                                         'experience' => $val->expe,
                                        //'address' => $val->address,
                                        //'rating' => $this->Common_model->getrating($val->user_id),
                                        //'certificates' =>$cdata
                                    ];
                                }
                                      $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Record found successfully!',
                                        'data' => $result,
                            ]);
        }else{
              return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Data not found!',
                            ]);  
        }
        }else{
           return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Data not found!',
                            ]);  
        }                           
        }else //when xai create at the time of team creation
        {
             $xaidata_data = $this->db->get_where('tbl_xai_matching', ['team_id' => $team_id , 'user_id' => $userid , 'member_id'=>$member_id])->row();
         $industry = (isset($xaidata_data->industry_id))?$xaidata_data->industry_id:0;
         $xaidata = $this->db->get_where('tbl_xai_matching', ['team_id' => $team_id , 'user_id' => $userid , 'member_id'=>$member_id])->result();

                if ($xaidata) {
                    foreach ($xaidata as $k => $re) {
                        $industries[] = $re->industry_id;
                        $all[$re->industry_id] = [
                            'budget' => $re->rate,
                            'skills' => $re->skill_id,
                            'experience' => $re->experience_id,
                            'personality' => $re->personality,
                            'members' => $re->members,
                            'factors' => $re->factors,
                            'available_days' => $re->available_days,
                            'start_time' => $re->start_time,
                            'end_time' => $re->end_time,
                            'seven_24' => $re->seven_24,
                            'interest' => $re->interest,
                            'backup' => $re->backup,
                            'accessment' => $re->accessment,
                            'communication_id' => $re->communication_id,
                            'expectation' => $re->expectation,
                            'driving_distance' => $re->driving_distance,
                            'xai_personality' => $re->xai_personality,
                            'motivation' => $re->motivation,
                            'language' => $re->language,
                            'softskills_id' => $re->softskills_id,
                            'additional_skills' => $re->additional_skills,
                            'frequency_id' => $re->frequency_id,
                            'options_preferences' => $re->options_preferences,
                            'covid_vaccination_proof' => $re->covid_vaccination_proof,
                            'negative_tests_proof' => $re->negative_tests_proof,
                            'none' => $re->none,
                        ];
                    }

         $getSp = $this->db->select('c.* , b.id , b.image as profile_image,CONCAT(b.firstname, " ", b.lastname) AS user_name,b.address , b.usertype,d.name as industry , d.id as industry_id , e.name as expe')
                           ->from('tbl_offer_letter as a')
                           ->join('logincr as b','b.id = a.provider_id','left')
                           ->join('tbl_xai_matching as c','c.user_id = a.provider_id' , 'left')
                           ->join('tbl_industries as d', 'c.industry_id = d.id', 'left')
                           ->join('tbl_experience as e', 'c.experience_id = e.id', 'left')
                           ->where(['c.type'=>'0' , 'c.industry_id'=>$industry , 'a.status'=>'2' , 'a.user_id'=>$userid])
                           ->group_by('b.id')
                           ->get()
                           ->result();
        if($getSp)
        {
             $total = 0;
            foreach($getSp as $val)
            {
                $per = 0;
                /*-------------------- Percent Calculate-----------------*/
                $com = count(explode(',',$all[$val->industry_id]['communication_id']));
                               $com1 = count(explode(',',$val->communication_id));
                                $soft = count(explode(',',$all[$val->industry_id]['softskills_id']));
                               $soft1 = count(explode(',',$val->softskills_id));
                                $add = count(explode(',',$all[$val->industry_id]['additional_skills']));
                               $add1 = count(explode(',',$val->additional_skills));
                                $fr = count(explode(',',$all[$val->industry_id]['frequency_id']));
                               $fr1 = count(explode(',',$val->frequency_id));
                                
                                $per += (
                                         $this->percent($all[$val->industry_id]['skills'], $val->skill_id)+ 
                                         $this->percent($all[$val->industry_id]['experience'], $val->experience_id)+ 
                                         $this->percent($all[$val->industry_id]['budget'], $val->rate)+
                                          $this->percent(is_numeric($all[$val->industry_id]['personality'])?$all[$val->industry_id]['personality']:1,is_numeric($val->personality)?$val->personality:2)+
                                            $this->percent($all[$val->industry_id]['members'], $val->members)+
                                            $this->percent(is_numeric($all[$val->industry_id]['factors']), is_numeric($val->factors))+
                                            $this->percent(is_numeric($all[$val->industry_id]['available_days'])?$all[$val->industry_id]['available_days']:1,is_numeric($val->available_days)?$val->available_days:2)+
                                             $this->percent(is_numeric($all[$val->industry_id]['start_time'])?$all[$val->industry_id]['start_time']:1,is_numeric($val->start_time)?$val->start_time:2)+
                                            $this->percent(is_numeric($all[$val->industry_id]['end_time'])?$all[$val->industry_id]['end_time']:1,is_numeric($val->end_time)?$val->end_time:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['interest'])?$all[$val->industry_id]['interest']:1,is_numeric($val->interest)?$val->interest:2)+
                                              $this->percent(($all[$val->industry_id]['backup']=='1')?is_numeric($all[$val->industry_id]['backup']):1,($val->interest=='1')?is_numeric($val->interest):0)+
                                              $this->percent(is_numeric($all[$val->industry_id]['accessment'])?$all[$val->industry_id]['accessment']:1,is_numeric($val->accessment)?$val->accessment:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['expectation'])?$all[$val->industry_id]['expectation']:1,is_numeric($val->expectation)?$val->expectation:2)+
                                                $this->percent(is_numeric($all[$val->industry_id]['xai_personality'])?$all[$val->industry_id]['xai_personality']:1,is_numeric($val->xai_personality)?$val->xai_personality:2)+
                                                 $this->percent(is_numeric($all[$val->industry_id]['motivation'])?$all[$val->industry_id]['motivation']:1,is_numeric($val->motivation)?$val->motivation:2)+
                                                   $this->percent(is_numeric($all[$val->industry_id]['language'])?$all[$val->industry_id]['language']:1,is_numeric($val->language)?$val->language:2)+
                                              $this->percent(is_numeric($all[$val->industry_id]['options_preferences'])?$all[$val->industry_id]['options_preferences']:1,is_numeric($val->options_preferences)?$val->options_preferences:2)+
                                              $this->percent(($all[$val->industry_id]['covid_vaccination_proof']=='0')?1:2,($val->covid_vaccination_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['negative_tests_proof']=='0')?1:2,($val->negative_tests_proof=='1')?1:2)+
                                              $this->percent(($all[$val->industry_id]['none']=='0')?1:2,($val->none=='1')?1:2)+
                                                   $this->percent($com?$com:0,$com1?$com1:0)+
                                                   $this->percent($soft?$soft:0,$soft1?$soft1:0)+
                                                   $this->percent($add?$add:0,$add1?$add1:0)+
                                                    $this->percent($fr?$fr:0,$fr1?$fr1:0)+
                                                    $this->percent(is_numeric($all[$val->industry_id]['driving_distance']), is_numeric($val->driving_distance))



                                         )
                                     /21;
                                $total += $per;


                /*------------ Percent Calculate end--------------------*/
           $result[] = [
                                        'provider_id' => $val->id,
                                        'usertype' => $val->usertype,
                                        'profile_image' => $val->profile_image ? base_url($val->profile_image) : base_url('upload/users/photo.png'),
                                        'name' => $val->user_name,
                                        'fees' => '$' . $val->rate,
                                        'percent' => round($per) . '%',
                                        'industry_id' => $val->industry_id,
                                        'industry' => $val->industry,
                                        'experience' => $val->expe,
                                       // 'skillname' => $val->skillname ? $val->skillname : '',
                                        
                                        //'address' => $val->address,
                                        //'rating' => $this->Common_model->getrating($val->user_id),
                                        //'certificates' =>$cdata
                                    ];
                                }
                                      $this->response(
                                    ['status' => 'success',
                                        'responsecode' => REST_Controller::HTTP_OK,
                                        'message' => 'Record found successfully!',
                                        'data' => $result,
                            ]);
        }else{
              return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Data not found!',
                            ]);  
        }
        }else{
           return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_FORBIDDEN,
                                'message' => 'Data not found!',
                            ]);  
        } 

        

        }
    }
     public function percent($first = NULL, $second = NULL) {
        $oldFigure = $first == 0 ? 1 : $first;
        $newFigure = $first == 0 ? 1 :$second;
        if ($oldFigure < $newFigure) {
            $percentChange = (1 - $oldFigure / $newFigure) * 100;
        } else {
            $percentChange = (1 - $newFigure / $oldFigure) * 100;
        }

        return (100 - round($percentChange));
    }
    //virtual interview created
    public function virtualScheduleInterview_post()
    {

        $tokenid = $this->input->get_request_header('Secret-Key');
        $userid = $this->input->post('userid');
        $spid = $this->input->post('spid');
        $team_id = $this->input->post('team_id');
        $check_key = $this->authentication($userid , $tokenid);
        $config = [
                    
                    ['field' => 'userid', 'label' => 'userid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User id  is required',
                            'numeric' => 'User id  should be numeric',
                        ],
                    ],
                     ['field' => 'spid', 'label' => 'spid', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Sp id  is required',
                            'numeric' => 'Sp id  should be numeric',
                        ],
                    ],
                     ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id  is required',
                            'numeric' => 'Team id  should be numeric',
                        ],
                    ],
                     
                ]; 
            $this->form_validation->set_data($this->input->post());
            $this->form_validation->set_rules($config);
            if ($this->form_validation->run() == FALSE) {
               return $this->response( ['status' => 'false',
                                'responsecode' => REST_Controller::HTTP_BAD_REQUEST,
                                'message' => strip_tags(str_replace('\n', ',',validation_errors())),
                    ]);
           }else{
            $myData = [
                'userid' => $userid,
                'spid' => $spid,
                'teamid' => $team_id,
                'interviewDate' => '',
                'interviewTime' => '',
                'status' => '',
                'status1' => '',
                'result' => '',
                'rating' => 0,
                'notes' => '',
                'is_soft_status' => '1'

            ];
            $this->db->insert('scheduleinterview',$myData);
            if($this->db->affected_rows()>0)
            {
                $insert_id = $this->db->insert_id();
                 return $this->response([
          'status' => 'success',  
          'message' => 'Successfully done',
          'responsecode' => REST_Controller::HTTP_OK,
          'interviewid' => "$insert_id",
          ]);
            }else{
                 return $this->response([
          'status' => 'false',  
          'message' => 'Not done',
          'responsecode' => REST_Controller::HTTP_FORBIDDEN,
          ]);
            }
           }
    }



}
