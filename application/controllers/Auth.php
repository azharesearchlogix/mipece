<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include APPPATH . 'third_party/twilio/vendor/autoload.php';
require_once APPPATH . '/libraries/REST_Controller.php';

use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Jwt\Grants\VideoGrant;

class Auth extends REST_Controller {

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

    public function authentication($user_id = NULL, $token = NULL) {
        $this->load->model('Common_model');
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

    public function chattoken_get($id = Null) {

        $token = $this->input->get_request_header('Secret-Key');

        if ($token != '') {

            $check_key = $this->checktoken($token, $id);
            if ($check_key['status'] == 'true') {

                $identity = $check_key['data']->firstname . ' ' . $check_key['data']->lastname;
                $token = new AccessToken(
                        $this->config->item('twilioAccountSid'), $this->config->item('twilioApiKey'), $this->config->item('twilioApiSecret'), 3600, $identity
                );
                // Create Chat grant
                $chatGrant = new ChatGrant();
                $chatGrant->setServiceSid($this->config->item('serviceSid'));
                // Add grant to token
                $token->addGrant($chatGrant);
                // render token to string
                //echo $token->toJWT();
                $this->response(
                        ['status' => 'success',
                            'responsecode' => REST_Controller::HTTP_OK,
                            'token' => $token->toJWT(),
                            'message' => 'Token get successfully!',
                ]);
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

    public function videotoken_get($user_id = NULL) {
        $user_id = $this->uri->segment(3);
        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($user_id, $token);
      
        $identity = $auth['success']->firstname . ' ' . $auth['success']->lastname;
        $token = new AccessToken(
                $this->config->item('twilioAccountSid'), $this->config->item('twilioApiKey'), $this->config->item('twilioApiSecret'), 3600, $identity
        );
        $grant = new VideoGrant();
        $grant->setRoom('cool room');
        $token->addGrant($grant);
        // echo $token->toJWT();
        $this->response(
                ['status' => 'success',
                    'responsecode' => REST_Controller::HTTP_OK,
                    'message' => 'Token get successfully!',
                    'token' => $token->toJWT(),
        ]);
    }

}
