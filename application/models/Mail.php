<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Mail extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function sendmail($to = NULL, $subject = NULL, $html = NULL) {

        $this->load->config('email');
        $this->email->set_newline("\r\n");
        $this->email->from('arvind@esearchlogix.in', 'Mipece.com');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($html);
        if ($this->email->send()) {
            return TRUE;
        } else {
//            show_error($this->email->print_debugger());
            return FALSE;
        }
    }

}
