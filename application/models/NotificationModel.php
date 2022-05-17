<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class NotificationModel extends CI_Model {

    protected $API_ACCESS_KEY = 'AIzaSyBvcqiJ4l_fbFSqI8YY9_12WTXRWy1ZaJI';
    
    protected $token_web = 'AAAAkB95Fso:APA91bFhEd-wvcWEwNnZYXiJ5tqKfBevwZqnZ-a51MS03H96ft8brdVxqedBmQF9TwGeMadXWkuad0nEbkEfyyQkrdTiRgQuP0dBxvY3dwTM38XKqTMTr-oSt3-7pJ-ifXe1EB0Xv4E3';
    protected $token_android = 'AAAAqvvIt5s:APA91bF4lMgoZxp4WuP_ZDKb8xSNMEseDdFlupbsqRbjYIs0cF0msoAu1P0G0B_H2NazHRgTW45sNMUI4Cdbflt5iqc_H4DH5faW4QklxENP0iA9CJFnumXwMIzo2-4QAWvkOpsLOmik';
    protected $token_ios = 'AAAAkB95Fso:APA91bFhEd-wvcWEwNnZYXiJ5tqKfBevwZqnZ-a51MS03H96ft8brdVxqedBmQF9TwGeMadXWkuad0nEbkEfyyQkrdTiRgQuP0dBxvY3dwTM38XKqTMTr-oSt3-7pJ-ifXe1EB0Xv4E3';


    public function index($dataArray,$message) {

        if ($dataArray['device_tpye'] == '0') {
            $token = $this->token_android;
        } elseif ($dataArray['device_tpye'] == '1') {
            $token = $this->token_ios;
        } else {
            $token = $this->token_web;
        }
       $data = [
            "to" => $dataArray['device_token'],
            "notification" => $message
        ];
        
        $data_string = json_encode($data);
        $headers = array(
            'Authorization: key=' . $token . '',
            'Content-Type: application/json'
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}
