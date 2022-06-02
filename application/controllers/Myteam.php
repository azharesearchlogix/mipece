<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Myteam extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['NotificationModel', 'Common_model']);
        date_default_timezone_set('Asia/Kolkata');
        $militime = round(microtime(true) * 1000);
        $datetime = date('Y-m-d h:i:s');
        define('militime', $militime);
        define('datetime', $datetime);
    }

    function _remap($method) {
        if (method_exists($this, $method)) {
            call_user_func(array($this, $method));
            return false;
        } else {

            $dataa_array['methdodcheck'][] = array(
                'status' => 'failed',
                'message' => 'Method not found',
                'responsecode' => '404'
            );
        }
        header("content-type: application/json");
        echo json_encode($dataa_array);
    }
    
     public function authentication($user_id = NULL, $token = NULL) {
        header("content-type: application/json");
        $result = $this->Common_model->Access($user_id, $token);
        if (!key_exists('error', $result)) {
            return $result;
        } else {

            $response = ['status' => 'false',
                'responsecode' => '403',
                'message' => $result['error'],
            ];
            echo json_encode($response);
        }
    }

    function random_password($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

     public function createTeam_old() {
        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if (!empty($userdata)) {               

                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User is required',
                            'numeric' => 'User should be  numeric',
                        ],
                    ],
                    ['field' => 'team_id', 'label' => 'team_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Team id is required',
                            'numeric' => 'Team id should be  numeric',
                        ],
                    ],
                    ['field' => 'industry', 'label' => 'industry', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Industry is required',
                            'numeric' => 'Industry should be  numeric',
                        ],
                    ],
                    ['field' => 'skills', 'label' => 'skills', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Skills is required',
                            'numeric' => 'Skills should be  numeric',
                        ],
                    ],
                    ['field' => 'experience', 'label' => 'experience', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Experience is required',
                        ],
                    ],
                    ['field' => 'budget', 'label' => 'budget', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Budget is required',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $response = ['status' => 'false',
                        'responsecode' => '403',
                        'message' => ($this->form_validation->error_array()),
                    ];
                } else {


                    $formArray = [
                        'industry' => $this->input->post('industry'),
                        'skills' => $this->input->post('skills'),
                        'experience' => $this->input->post('experience'),
                        'budget' => $this->input->post('budget'),                        
                    ];
                    
                    $this->db->update('myteams', $formArray,['id'=>$this->input->post('team_id')]);                   
                    if ($this->db->affected_rows() > 0) {
                        $response = [
                            'responsecode' => '200',
                            'status' => 'success',
                            'message' => 'Your team data update successfully!',
                        ];
                    } else {
                        $response = [
                            'responsecode' => '200',
                            'status' => 'success',
                            'message' => 'Your team data not update successfully!',
                        ];
                    }
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
     public function createTeam() {
         $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if (!empty($userdata)) {
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $key => $value) {
                            if(!is_numeric($value['skills']))//when skill not found according to industry id
                            {
                            $this->db->insert('tbl_skill',['industry_id'=>$value['industry'] , 'name'=> $value['skills'] ]);
                            $insert_id = $this->db->insert_id();
                            $value['skills'] = "$insert_id";
                            }
                        $final = array_merge($value, ['user_id' => $data['user_id'], 'team_id' => $data['team_id']]);
                        $formData[] = $final;
                    }


                    $this->db->delete('tbl_team_requirement', array('user_id' => $data['user_id'], 'team_id' => $data['team_id']));
                    $res = $this->db->insert_batch('tbl_team_requirement', $formData);
                    if ($res) {
                        $response = [
                            'responsecode' => '200',
                            'status' => 'success',
                            'message' => 'Requirement added successfully!',
                            'data' => $formData
                        ];
                    } else {
                        $response = [
                            'responsecode' => '404',
                            'status' => 'false',
                            'message' => 'Something went wrong!',
                        ];
                    }
                } else {
                    $this->db->delete('tbl_team_requirement', array('user_id' => $data['user_id'], 'team_id' => $data['team_id']));
                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Requirement updated successfully!',
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
    
    public function teamList($user_id= NULL) {

       $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
                 $user_type = $userdata->usertype;
                 $switch = $userdata->switch_account;
                if($switch == '2')
                {
                $teams = $this->db->select('a.*,b.name as language')->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')->where(['a.user_id' => $user_id, 'a.status' => '0' ,'agreement_id!='=>0])->order_by('a.created_at DESC')->get()->result();
                }else{
                $teams = $this->db->select('a.*,b.name as language')->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')->where(['a.user_id' => $user_id, 'a.status' => '0' , 'agreement_id'=>0])->or_where(['a.agreement_sendby_id'=>$user_id])->order_by('a.created_at DESC')->get()->result();
                 }
               // $teams = $this->db->select('a.*,b.name as language')->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')->where(['a.user_id' => $user_id, 'a.status' => '0'])->order_by('a.created_at DESC')->get()->result();
//                echo '<pre>';
//                print_r($teams);
//                die;
 $member_count = $this->db->get_where('tbl_members',['user_id'=>$user_id])->num_rows();
                if ($teams) {
                  
                    foreach ($teams as $t) {
                        $agreement_id = $t->agreement_id;
                        if($switch == '1')
                        {
                            
                            //$totla_member = $this->db->get_where('scheduleinterview', ['userid' => $user_id, 'i_status' => '2' , 'teamid' => $t->id])->num_rows();
                         $sp_detail = $this->db->select('b.id, b.firstname , b.lastname , b.image , b.profile_pic')
                                                ->from('scheduleinterview as a')
                                                 ->join('logincr as b' , 'a.spid = b.id' ,'left')
                                                 ->where(['a.userid' => $t->user_id, 'a.i_status' => '2' , 'a.teamid' => $t->id])
                                                 ->get()
                                                 ->result();
                                                 $spData = array();
                                                if($sp_detail)
                                                {
                                                    foreach($sp_detail as $sp)
                                                    {
                                                        $spData[] = [
                                                            'spid' => $sp->id,
                                                            'name' => $sp->firstname.' '.$sp->lastname,
                                                            'image' => (!empty($sp->profile_pic) && $sp->profile_pic!=NULL)?$sp->profile_pic:base_url($sp->image),
                                                            
                                                            ];
                                                    }
                                                }
                            
                        }else{
                         //$totla_member = $this->db->get_where('scheduleinterview', ['userid' => $user_id, 'i_status' => '2' , 'teamid' => $t->id])->num_rows();
                         $sp_detail = $this->db->select('b.id, b.firstname , b.lastname , b.image , b.profile_pic')
                                                ->from('scheduleinterview as a')
                                                 ->join('logincr as b' , 'a.spid = b.id' ,'left')
                                                 ->where(['a.userid' => $user_id, 'a.i_status' => '2' , 'a.teamid' => $t->id])
                                                 ->get()
                                                 ->result();
                                                 $spData = array();
                                                if($sp_detail)
                                                {
                                                    foreach($sp_detail as $sp)
                                                    {
                                                        $spData[] = [
                                                            'spid' => $sp->id,
                                                            'name' => $sp->firstname.' '.$sp->lastname,
                                                            'image' => (!empty($sp->profile_pic) && $sp->profile_pic!=NULL)?$sp->profile_pic:base_url($sp->image),
                                                            
                                                            ];
                                                    }
                                                }
                        }
                        $co = count($sp_detail);
                        $result[] = [
                            'teamid' => $t->id,
                            'user_id' => $t->user_id,
                            'reqiured_members' => $t->members,
                            'language' => $t->language ?  $t->language : '',
                            'teamname' => $t->teamname,
                            'teamimage' => base_url($t->teamimage),
                            'description' => $t->description,
                            'created_by' => ($t->agreement_id!=0)?'sc':'client',
                            'totalmember' => "$co",
                            'members' => $spData,
                        ];
                    }

                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Record found successfully!',
                        'data' => $result,
                        'member_count' => "$member_count",
                    ];
                } else {
                    $response = [
                        'responsecode' => '404',
                        'status' => 'false',
                        'message' => 'Record not found!',
                        'member_count' => "$member_count",
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

   public function teamDetails($user_id = NULL, $team_id = NULL) {

        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->uri->segment(3);
        $team_id = $this->uri->segment(4);

        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if ($userdata) {
              
                $teams = $this->db->select('a.*,b.name as language')
                ->from('myteams as a')->join('tbl_language as b', 'a.language = b.id', 'left')
                ->where(['a.id' => $team_id, 'a.status' => '0'])->get()->row();

                if ($teams) {
                 
                  $result['teamdetails'] = [
                        'team_id' => $teams->id,
                        'user_id' => $teams->user_id,
                        'member_id' => $teams->member_id,
                        'required_members' => $teams->members,
                        'language' => $teams->language,
                        'teamname' => $teams->teamname,
                        'teamimage' => base_url($teams->teamimage),
                        'description' => $teams->description,
                        'created_by' => ($teams->agreement_id!=0)?'sc':'client',
                    ];
                    $result['teamdetails']['selected_members'] = '0';
                    $result['requirement'] = [];
                    $result['comments'] = [];
                    $result['teammember'] = [];
                    $requirement = $this->db->select('a.*,b.name as industry_name,c.name as skill_name,d.name as experience_name')->from('tbl_team_requirement as a')
                                    ->join('tbl_industries as b', 'a.industry = b.id', 'left')
                                    ->join('tbl_skill as c', 'a.skills = c.id', 'left')
                                    ->join('tbl_experience as d', 'a.experience = d.id')
                                   // ->join('tbl_certification as e', 'a.certificate = e.id')
                                    ->where(['team_id' => $team_id, 'user_id' => $user_id])->get()->result();
                    if ($requirement) {
                        foreach ($requirement as $re) {
                            $result['requirement'][] = [
                                'team_id' => $re->team_id,
                                'industry' => $re->industry,
                                'industry_name' => $re->industry_name,
                                'skills' => $re->skills,
                                'skill_name' => $re->skill_name,
                                'experience' => $re->experience,
                                'experience_name' => $re->experience_name,
                                'certificate_id' => $re->certificate ? $re->certificate : '',
                                'certificate_title' => $re->certificate ? $this->db->get_where('tbl_certification',['id'=>$re->certificate])->row()->title : '',
                                'budget' => $re->budget,
                            ];
                        }
                    }
                    if($userdata->switch_account == '1')
                    {
                         $teammember = $this->db->select("a.*,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image")
                                    ->from('scheduleinterview as a')
                                    ->join('logincr as b', 'b.id = a.spid', 'left')
                                    ->join('myteams as c', 'c.id = a.teamid', 'left')
                                    ->where('b.id IS NOT NULL')
                                    ->where('c.teamname IS NOT NULL')
                                    ->where(['a.i_status' => '2' , 'a.userid' => $teams->user_id])
                                  
                                    ->where('a.teamid', $team_id)->get()->result();
                    }else{
                    $teammember = $this->db->select("a.*,b.id as client_id,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image")
                                    ->from('scheduleinterview as a')
                                    ->join('logincr as b', 'b.id = a.spid', 'left')
                                    ->join('myteams as c', 'c.id = a.teamid', 'left')
                                    ->where('b.id IS NOT NULL')
                                    ->where('c.teamname IS NOT NULL')
                                    ->where(['a.i_status' => '2' , 'a.userid' => $user_id])
                                  
                                    ->where('a.teamid', $team_id)->get()->result();
                    }
                    if ($teammember) {
                        $result['teamdetails']['selected_members'] = count($teammember);
                        foreach ($teammember as $mem) {
                            $result['teammember'][] = [
                                'member_d' => $mem->client_id,
                                'name' => $mem->name,
                                'image' => $mem->image ? base_url($mem->image) : base_url('upload/user/phpto.png'),
                            ];
                        }
                    }

                    $comments = $this->db->select('a.*,CONCAT(b.firstname, " ", b.lastname) AS user_name,b.image')->from('myteamnotes as a')->join('logincr as b', 'b.id = a.userid', 'left')
                                    ->where('a.teamid', $team_id)->get()->result();
                    if ($comments) {
                        foreach ($comments as $co) {
                            $result['comments'][] = [
                                'user_image' => $co->image ? base_url($co->image) : base_url('upload/user/phpto.png'),
                                'user_name' => $co->user_name,
                                'notes' => $co->notes,
                                'date' => date('d-m-Y', strtotime($co->create_at)),
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

    public function spList() {

        $this->load->model('Common_model');
        $data_array = array();

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        $experience = $this->input->post('experience');
        $industry = $this->input->post('industry');
        $skills = $this->input->post('skills');

        $data = $this->db->select('a.*,c.name as experience')->from('logincr as a')
                        ->join('tbl_xai_matching as b', 'b.user_id=a.id', 'left')
                        ->join('tbl_experience as c', 'c.id=b.experience_id', 'left')
                        ->where(['b.industry_id' => $industry])
                        ->or_where(['b.skill_id' => $skills])
                        ->or_where(['b.experience_id' => $experience])
                        ->get()->result();

        foreach ($data as $row) {
            $ratio = 0;
            $ratio_res = $this->db->select('rating, SUM(rating) AS total', FALSE)->where('provider_id', $row->id)->group_by("rating")->get('tbl_user_ratings')->result();
            if (!empty($ratio_res)) {
                $devident = 0;
                $devisor = 0;
                foreach ($ratio_res as $r) {
                    $devident += ($r->rating * $r->total);
                    $devisor += $r->total;
                }

                $ratio = round($devident / $devisor, 2);
            }

            $data_array[] = array(
                'spid' => $row->id,
                'photo' => $row->image ? base_url() . 'upload/users/' . $row->image : base_url() . 'upload/users/photo.png',
                'firstname' => $row->firstname,
                'lastname' => $row->lastname,
                'email' => $row->email,
                'contact' => $row->contact,
                'ssnnum' => $row->ssnnum,
                'address' => $row->address,
                'country' => $row->country,
                'city' => $row->city,
                'postalcode' => $row->postalcode,
                'bio' => $row->about,
                'rating' => "$ratio",
                'experience' => $row->experience,
            );
        }

        if (count($data_array) > 0) {
            $final_output['responsecode'] = '200';
            $final_output['status'] = 'success';
            $final_output['data'] = $data_array;
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spList_old() {

        $this->load->model('Common_model');
        $data_array = array();

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        $experience = $this->input->post('experience');
        $industry = $this->input->post('industry');
        $skills = $this->input->post('skills');

        //echo "SELECT * FROM `userskill` WHERE experience='".$experience."' OR industry='".$industry."' OR skills='".$skills."' ";   
        $query = $this->db->query("SELECT * FROM `userskill` WHERE experience='" . $experience . "' OR industry='" . $industry . "' OR skills='" . $skills . "' ");
        $record = $query->result();
        $total = $query->num_rows();
        if ($total > 0) {

            foreach ($record as $list) {

                $spid = $list->userid;


                //echo "SELECT * FROM `logincr` INNER JOIN usereducation ON logincr.id=usereducation.userid INNER JOIN userskill ON userskill.userid=logincr.id WHERE logincr.id='".$spid."' AND logincr.usertype='serviceprovider'";

                $userQuery = $this->db->query("SELECT * FROM `logincr` INNER JOIN userskill ON userskill.userid=logincr.id WHERE logincr.id='" . $spid . "' AND logincr.usertype='0' ");
                $userRecord = $userQuery->result();

                foreach ($userRecord as $row) {

                    $photo = $row->image;
                    if ($photo != '') {
                        $uphoto = base_url() . 'upload/users/' . $photo;
                    } else {
                        $uphoto = base_url() . "upload/users/photo.png";
                    }

                    $data_array[] = array(
                        'spid' => $row->userid,
                        'photo' => $uphoto,
                        'firstname' => $row->firstname,
                        'lastname' => $row->lastname,
                        'email' => $row->email,
                        'contact' => $row->contact,
                        'ssnnum' => $row->ssnnum,
                        'address' => $row->address,
                        'country' => $row->country,
                        'city' => $row->city,
                        'postalcode' => $row->postalcode,
                        'bio' => $row->about,
                        'rating' => $row->rating,
                        //'education'      => $row->education,
                        //'collegename'    => $row->collegename,        
                        //'passingyear'    => $row->passingyear,                                
                        'experience' => $row->experience,
                            //'industry'       => $row->industry,
                            //'skills'         => $row->skills, 
                    );
                }
            }



            $final_output['responsecode'] = '200';
            $final_output['status'] = 'success';
            $final_output['data'] = $data_array;
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function getspProfile() {

        $this->load->model('Common_model');
    $oData = [];
        //$userid  = $this->input->post('id');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');


        if ($spid != '') {
            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'usertype' => '0', 'status' => '1'));
            if ($check_record != '') {

                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;

                if ($photo != '') {
                    $uphoto = $basepath . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }



                // $edu_record = $this->Common_model->common_getRow('usereducation', array('userid'=>$spid));

                $edu_record = $this->db->get_where('usereducation', ['userid' => $spid])->result();
                $otherData = $this->db->get_where('tbl_user_certificate', ['user_id' => $spid])->result();
                if ($edu_record != '') {
                    foreach ($edu_record as $val) {
                        $data_edu[] = [
                            'id' => $val->id,
                            'userid' => $val->userid,
                            'education' => $val->education,
                            'passingyear' => $val->passingyear,
                            'certificate' => $val->certificate ? base_url('upload/users/') . $val->certificate : '',
                            'collegename' => $val->collegename,
                        ];
                    }
                } else {
                    $data_edu = [];
                }
                
                if ($otherData != '') {
                    foreach ($otherData as $val1) {
                        $oData[] = [
                            'id' => $val1->id,
                            'certificate' => ($val1->certificate!=null)?base_url($val1->certificate):'',
                            'passing_date' => ($val1->passing_date!=null)?$val1->passing_date:'',
                            'renewal_date' => ($val1->renewal_date!=null)?$val1->renewal_date:'',
                            'license' => ($val1->license!=null)?base_url($val1->license):'',
                            'continue_passing_date' => ($val1->continue_passing_date!=null)?$val1->continue_passing_date:'',
                            'continue_renewal_date' => ($val1->continue_renewal_date!=null)?$val1->continue_renewal_date:'',
                            'certification_education' => ($val1->certification_education!=null)?base_url($val1->certification_education):'',
                           // 'passing_date' => ($val1->passing_date!=null)?val1->passing_date:'',
                        ];
                    }
                } else {
                    $oData = [];
                }

                $exp_record = $this->db->select('b.name as experience, c.name as industry, d.name as skills')->from('tbl_xai_matching as a')
                                ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                                ->join('tbl_industries as c', 'a.industry_id = c.id ', 'left')
                                ->join('tbl_skill as d', 'a.skill_id = d.id ', 'left')
                                ->where('a.user_id', $spid)->get()->row();

                $data_exp = [];
                if ($exp_record) {

                    $data_exp[] = array(
                        'experience' => $exp_record->experience,
                        'industry' => $exp_record->industry,
                        'skills' => $exp_record->skills,
                    );
                }

                $interviewdata = $this->Common_model->common_getRow('scheduleinterview', array('spid' => $spid, 'teamid' => $teamid, 'status' => 'pending'));

                if ($interviewdata != '') {
                    $data_interview[] = array(
                        'interviewdate' => $interviewdata->interviewDate,
                        'interviewtime' => $interviewdata->interviewTime,
                    );
                } else {
                    $data_interview = [];
                }
                
                $certificates = [];
                $certificate = $this->db->select('a.*,b.title')->from('tbl_user_certificate as a')
                                ->join('tbl_certification as b', 'a.certification_id=b.id', 'left')
                                ->where('a.user_id', $spid)
                                ->get()->result();
                if ($certificate) {
                    foreach ($certificate as $cert) {
                        $certificates[] = [
                            'id' => $cert->id,
                            'title' => $cert->title,
                            'mime_type' => pathinfo($cert->certificate, PATHINFO_EXTENSION),
                            'certificate' => base_url($cert->certificate),
                        ];
                    }
                }

                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'bio' => $check_record->about,
                    'audio_file' => (!empty($check_record->audio_file) || $check_record->audio_file!=NULL)?base_url($check_record->audio_file):'',
                    'rating' => $this->Common_model->getrating($spid),
                    'interviewdatetime' => $data_interview,
                    'educationdata' => $data_edu,
                    'experiencedata' => $data_exp,
                    'certificates' => $certificates,
                    'otherData' => $oData,
                );

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $data_array;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'SPid not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function scheduleInterview() {

        $this->load->model('Common_model');

        $date = date("Y-m-d h:i");
        $userid = $this->input->post('id');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');

        if ($userid != '' && $spid != '' && $teamid != '') {
           
           

            $provider_data = $this->db->get_where('logincr', ['id' => $spid])->row();
            $user_data = $this->db->get_where('logincr', ['id' => $userid])->row();

            $interviewid = $this->input->post('interviewid');

            if ($interviewid != '') {
                  $already = $this->db->get_where('scheduleinterview',['interviewDate'=>$this->input->post('interviewdate') , 'interviewTime'=>$this->input->post('interviewtime') , 'userid'=>$userid , 'teamid'=>$teamid])->num_rows();
                if($already)
                {
                     $final_output['responsecode'] = '400';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Interview already scheduled in this team at date or time';
                }else{

                $data = array(
                    'interviewDate' => $this->input->post('interviewdate'),
                    'interviewTime' => $this->input->post('interviewtime'),
                    'update_at' => $date
                );

                $update_value = $this->Common_model->updateData('scheduleinterview', $data, 'id="' . $interviewid . '" ');

                if ($update_value) {
                        $device_type = device_type($provider_data->sourcemedia);
                    $message = [
                        'title' => 'Interview rescheduled',
                        'body' => 'Your interview is rescheduled',
                        'icon' => base_url('upload/images/notification.png')
                    ];
                    $notification_data = [
                        'device_tpye' => "$device_type",
                        'device_token' => $provider_data->tokenid,
                    ];
                    $response = $this->NotificationModel->index($notification_data, $message);

                    $message['user_id'] = $spid;
                    $this->db->insert('tbl_notification', $message);

                    $final_output['responsecode'] = '200';
                    $final_output['status'] = 'success';
                    $final_output['message'] = 'Your interview is rescheduled.';
                } else {

                    $final_output['responsecode'] = '400';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Sorry! Try Again.';
                }
            }
            } else {
                 $already = $this->db->get_where('scheduleinterview',['interviewDate'=>$this->input->post('interviewdate') , 'interviewTime'=>$this->input->post('interviewtime') , 'userid'=>$userid , 'teamid'=>$teamid])->num_rows();
                if($already)
                {
                     $final_output['responsecode'] = '400';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Interview already scheduled in this team at date or time';
                }else
                {
                $insert_array = array();

                $insert_array['userid'] = $userid;
                $insert_array['spid'] = $spid;
                $insert_array['teamid'] = $teamid;
                $insert_array['interviewDate'] = $this->input->post('interviewdate');
                $insert_array['interviewTime'] = $this->input->post('interviewtime');

                $insert_array['create_at'] = $date;
                $insert_array['update_at'] = $date;
                $insert_array['status'] = 'Pending';
                if($user_data->switch_account == '2')
                {
                  $insert_array['schedule_by'] = '1';  
                }

                $data = $this->db->get_where('scheduleinterview', ['spid' => $spid, 'teamid' => $teamid, 'status!=' => 'Cancel'])->result();
                if (empty($data)) {

                    $insertId = $this->db->insert('scheduleinterview', $insert_array);
                    $lastid = $this->db->insert_id();
                    if ($insertId) {
                         $device_type = device_type($provider_data->sourcemedia);
                        $message = [
                            'title' => 'Interview Scheduled',
                            'body' => 'Your interview is scheduled',
                            'icon' => base_url('upload/images/notification.png')
                        ];
                        $notification_data = [
                            'device_tpye' => "$device_type",
                            'device_token' => $provider_data->tokenid,
                        ];
                        $response = $this->NotificationModel->index($notification_data, $message);
                        $message['user_id'] = $spid;
                         $this->db->insert('tbl_notification', $message);

                        $final_output['responsecode'] = '200';
                        $final_output['status'] = 'success';
                        $final_output['message'] = 'Thank you! Your interview is scheduled.';
                        $final_output['interviewid'] = $lastid;
                        $final_output['firebase_response'] = $response;
                    } else {
                        $final_output['responsecode'] = '400';
                        $final_output['status'] = 'failed';
                        $final_output['message'] = 'Sorry! Try Again.';
                    }
                } else {
                    $final_output['responsecode'] = '400';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Your interview is already scheduled';
                }
            }
        }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Sorry! your field is empty.';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function cancelInterview() {

        $this->load->model('Common_model');

        $date = date("Y-m-d h:i");


        $interviewid = $this->input->post('interviewid');

        if ($interviewid != '') {

            $data = array(
                'status' => 'Cancel',
                'update_at' => $date
            );

            $spid = $this->db->get_where('scheduleinterview', ['id' => $interviewid])->row()->spid;
            $provider_data = $this->db->get_where('logincr', ['id' => $spid])->row();


            $update_value = $this->Common_model->updateData('scheduleinterview', $data, 'id="' . $interviewid . '" ');

            if ($update_value) {
                  $device_type = device_type($provider_data->sourcemedia);
                $message = [
                    'title' => 'Interview canceled',
                    'body' => 'Your Interview canceled',
                    'icon' => base_url('upload/images/notification.png')
                ];
                $notification_data = [
                    'device_tpye' => "$device_type",
                    'device_token' => $provider_data->tokenid,
                ];
                $response = $this->NotificationModel->index($notification_data, $message);
                $message['user_id'] = $spid;
                $this->db->insert('tbl_notification', $message);


                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Interview is cancel.';
            } else {

                $final_output['responsecode'] = '400';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Sorry! Try Again.';
            }
        } else {

            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Interviewid is not found.';
        }


        header("content-type: application/json");
        echo json_encode($final_output);
    }
    
    public function interviewstatus() {
        header("content-type: application/json");
        $token = $this->input->get_request_header('Secret-Key');
        $auth = $this->authentication($this->input->post('user_id'), $token);
        if ($auth) {

            $data = $this->input->post();
            $config = [
                ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                    'errors' => [
                        'required' => 'User is required',
                        'numeric' => 'User should be  numeric',
                    ],
                ],
                ['field' => 'interview_id', 'label' => 'interview_id', 'rules' => 'required|numeric',
                    'errors' => [
                        'required' => 'Interview id is required',
                        'numeric' => 'Interview should be  numeric',
                    ],
                ]
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($config);

            if ($this->form_validation->run() == FALSE) {
                $response = ['status' => 'false',
                    'responsecode' => '403',
                    'message' => strip_tags(validation_errors()),
                ];
            } else {

                $this->db->update('scheduleinterview', ['i_status' => '1'], ['id' => $this->input->post('interview_id'), 'userid' => $this->input->post('user_id')]);
                if ($this->db->affected_rows() > 0) {
                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'You have change interview status successfully!',
                    ];
                } else {
                    $response = [
                        'responsecode' => '200',
                        'status' => 'success',
                        'message' => 'Your interview status already changed!',
                    ];
                }
            }
            echo json_encode($response);
        }
    }

    public function spInterviewlist() {
    $data_array = [];
        $this->load->model('Common_model');

         $userid = $this->input->post('id'); 
        $user_data = $this->db->get_where('logincr', ['id' => $userid])->row();
        if($user_data->switch_account == '2')
        {
          $query = $this->db->query("SELECT * FROM `scheduleinterview` WHERE userid='" . $userid . "' AND (status='pending' OR status='Pending') AND is_soft_status='0' AND schedule_by='1' ORDER BY id DESC");  
        }else{

        $query = $this->db->query("SELECT * FROM `scheduleinterview` WHERE userid='" . $userid . "' AND (status='pending' OR status='Pending') AND is_soft_status='0' AND schedule_by='0' ORDER BY id DESC");
        }
        $record = $query->result();
        // echo $this->db->last_query(); die;
        $total = $query->num_rows();
        if ($total > 0) {

            foreach ($record as $list) {

                $spid = $list->spid;

                $userQuery = $this->db->query("SELECT * FROM `logincr` left JOIN userskill ON userskill.userid=logincr.id WHERE logincr.id='" . $spid . "' AND logincr.usertype='0'");

                $exp_record = $this->db->select('b.name as experience')->from('tbl_xai_matching as a')
                                ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                                ->where('a.user_id', $spid)->get()->row();
                $experience = '';
                if ($exp_record) {
                    $experience = $exp_record->experience;
                }

                $numrow = $userQuery->num_rows();
                if ($numrow > 0) {
                    $userRecord = $userQuery->result();
                    foreach ($userRecord as $row) {

                        $photo = $row->image;
                        if ($photo != '') {
                            $uphoto = base_url() . $photo;
                        } else {
                            $uphoto = base_url() . "upload/users/photo.png";
                        }
                        $check_offer = $this->db->get_where('tbl_offer_letter',['provider_id'=>$spid , 'team_id'=>$list->teamid])->num_rows();
                        if($check_offer>0)
                        {
                            continue;
                        }else{

                        $data_array[] = array(
                            'interviewid' => $list->id,
                            'teamid' => $list->teamid,
                            'spid' => $spid,
                            'photo' => $uphoto,
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'email' => $row->email,
                            'contact' => $row->contact,
                            'ssnnum' => $row->ssnnum,
                            'address' => $row->address,
                            'country' => $row->country,
                            'city' => $row->city,
                            'postalcode' => $row->postalcode,
                            'bio' => $row->about,
                            'rating' => $row->rating,
                            'rating' => $this->Common_model->getrating($spid),
                            'experience' => $experience,
                            'interviewdate' => $list->interviewDate,
                            'interviewtime' => $list->interviewTime,
                            'status' => $list->i_status,
                        );
                        }
                    }

                    $final_output['responsecode'] = '200';
                    $final_output['status'] = 'success';
                    $final_output['data'] = $data_array;
                    $final_output['message'] = 'Record found';
                } else {
                    $final_output['responsecode'] = '200';
                    $final_output['status'] = 'success';
                    $final_output['message'] = 'Record found';
                }
            }
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spInterviewResult() {

        $this->load->model('Common_model');

        $date = date("Y-m-d h:i");
        $userid = $this->input->post('id');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');
        $interviewid = $this->input->post('interviewid');
        $result = $this->input->post('result');
        $rating = $this->input->post('rating');
        $notes = $this->input->post('notes');

        if ($userid != '' && $spid != '' && $teamid != '') {

            if ($result == '1') {
                $status = 'Approved';
            } else {
                $status = 'Reject';
            }

            if ($rating != '') {
                $ratings = $rating;
            } else {
                $ratings = '';
            }

            if ($notes != '') {
                $notess = $notes;
            } else {
                $notess = '';
            }

            $data = array(
                'status' => $status,
                'result' => $result,
                'rating' => $ratings,
                'notes' => $notess,
                'update_at' => $date
            );

            $update_rating = $this->Common_model->updateData('logincr', array('rating' => $ratings), array('id' => $spid));

            $update_value = $this->Common_model->updateData('scheduleinterview', $data, 'id="' . $interviewid . '" AND userid="' . $userid . '" AND spid="' . $spid . '" ');

            if ($update_value) {

                $provider_data = $this->db->get_where('logincr', ['id' => $spid])->row();
                $device_type = device_type($provider_data->sourcemedia);
                $message = [
                    'title' => 'Status is ' . $status,
                    'body' => 'Sp Interview Status is ' . $status,
                    'icon' => base_url('upload/images/notification.png')
                ];
                $notification_data = [
                    'device_tpye' => "$device_type",
                    'device_token' => $provider_data->tokenid,
                ];
                
                $response = $this->NotificationModel->index($notification_data, $message);
                $message['user_id'] = $spid;
                $this->db->insert('tbl_notification', $message);

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Status is ' . $status;
            } else {

                $final_output['responsecode'] = '400';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Sorry! Try Again.';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Sorry! your field is empty.';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function createTeamName() {
        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if (!empty($userdata)) {
                $configF['upload_path'] = './upload/images/';
                $configF['allowed_types'] = 'jpeg|jpg|png';
                $configF['max_size'] = 50600;
                $this->load->library('upload', $configF);

                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User is required',
                            'numeric' => 'User should be  numeric',
                        ],
                    ],
                    ['field' => 'required_members', 'label' => 'required_members', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Numbers of members is required',
                            'numeric' => 'Members should be  numeric',
                        ],
                    ],
                    ['field' => 'teamname', 'label' => 'teamname', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Team name is required',
                           // 'is_unique' => 'Team name has already be taken!',
                        ],
                    ],
                    ['field' => 'language', 'label' => 'language', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Perfered language  is required',
                        ],
                    ],
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description is required',
                        ],
                    ],
                   ['field' => 'zipcode', 'label' => 'zipcode', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Zipcode is required',
                            'numeric' => 'Zipcode should be  numeric',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $response = ['status' => 'false',
                        'responsecode' => '403',
                         'message' => strip_tags(validation_errors()),
                    ];
                } else {

                    $teamnameexist = $this->db->get_where('myteams', ['user_id' => $user_id, 'teamname' => $this->input->post('teamname')])->row();
                    if ($teamnameexist) {
                        $response = [
                            'responsecode' => '403',
                            'status' => 'failed',
                            'message' => 'Team name has already be taken!',
                        ];
                        echo json_encode($response);
                        die;
                    }

                    $formArray = [
                        'user_id' => $this->input->post('user_id'),
                        'members' => $this->input->post('required_members'),
                        'teamname' => $this->input->post('teamname'),
                        'language' => $this->input->post('language'),
                        'zipcode' => $this->input->post('zipcode'),
                        'description' => $this->input->post('description'),
                    ];
                    if (!empty($_FILES['teamimage']['name'])) {
                        if (!$this->upload->do_upload('teamimage')) {
                            $response = ['status' => 'false',
                                 'responsecode' => '403',
                                 'message' => strip_tags($this->upload->display_errors()),
                            ];
                           
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $formArray['teamimage'] = 'upload/images/' . $this->upload->data('file_name');
                        }
                    }
                    $this->db->insert('myteams', $formArray);
                    $affected = $this->db->insert_id();
                    if ($affected > 0) {
                        $response = [
                            'team_id' => "$affected",
                            'responsecode' => '200',
                            'status' => 'success',
                            'message' => 'Your team create successfully!',
                        ];
                    } else {
                        $response = [
                            'responsecode' => '403',
                            'status' => 'failed',
                            'message' => 'Your team not create successfully!',
                        ];
                    }
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

    public function createTeamName_OLD() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');
        //$spid    = $this->input->post('spid');

        $teamname = $this->input->post('teamname');
        $images = $this->input->post('teamimage');
        $data = trim($images);
        $data = str_replace('data:image/png;base64,', '', $data);
        $data = str_replace(' ', '+', $data);

        $data1 = base64_decode($data); // base64 decoded image data

        $imgname = uniqid() . '.png';
        $file_paths = $imgname;
        $file = 'upload/images/' . $imgname;
        $success = file_put_contents($file, $data1);

        $notes = $this->input->post('notes');
        $date = date("Y-m-d h:i");

        $final_output = array();


        if ($userid != '' && $teamid != '') {

            $data = array(
                'userid' => $userid,
                //'spid'      =>$spid,
                'teamid' => $teamid,
                'notes' => $notes,
                'create_at' => $date,
                'update_at' => $date
            );

            $insertId = $this->db->insert('myteamnotes', $data);

            $dataupdate = array(
                'teamName' => $teamname,
                'image' => $file_paths,
                'notes' => $notes,
                'update_at' => $date,
                'status' => '1'
            );

            $update_data = $this->Common_model->updateData('myteams', $dataupdate, array('id' => $teamid));


            if ($update_data) {
                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Your Team Name is updated.';
            } else {
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Something went wrong! please try again.';
                $final_output['responsecode'] = '400';
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function teamWiseNotes() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        $notes = $this->input->post('notes');
        $date = date("Y-m-d h:i");

        $final_output = array();


        if ($userid != '' && $teamid != '') {

            $data = array(
                'userid' => $userid,
                'teamid' => $teamid,
                'notes' => $notes,
                'create_at' => $date,
                'update_at' => $date
            );

            $insertId = $this->db->insert('myteamnotes', $data);

            //  $get_recordD = $this->Common_model->common_getRow('myteamnotes', 'teamid="'.$teamid.'" ORDER BY id DESC');

            $userQuery = $this->db->query("SELECT * FROM `myteamnotes` WHERE teamid='" . $teamid . "' ORDER BY id ASC");
            $get_records = $userQuery->result();

            foreach ($get_records as $get_recordD) {
                $datavalue[] = array(
                    'notesid' => $get_recordD->id,
                    'userid' => $get_recordD->userid,
                    //'spid'      => $get_recordD->spid,
                    'teamid' => $get_recordD->teamid,
                    'notes' => $get_recordD->notes,
                    'date' => $get_recordD->create_at
                );
            }

            if ($insertId) {
                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Your Notes has been successfully add.';
                $final_output['data'] = $datavalue;
            } else {
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Something went wrong! please try again.';
                $final_output['responsecode'] = '400';
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function assignTask_old() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');
        $spid = $this->input->post('spid');

        $title = $this->input->post('title');
        $describe = $this->input->post('describe');
        $taskdate = $this->input->post('taskdate');

        $date = date("Y-m-d h:i");

        $final_output = array();


        if ($userid != '' && $teamid != '') {

            $data = array(
                'userid' => $userid,
                'spid' => $spid,
                'teamid' => $teamid,
                'title' => $title,
                'describe' => $describe,
                'taskstatus' => 'Pending',
                'taskdate' => $taskdate,
                'create_at' => $date,
                'update_at' => $date,
                'status' => '0'
            );

            //   print_r($data); die;

            $insertId = $this->db->insert('assigntask', $data);
            $lastid = $this->db->insert_id();

            if ($insertId) {
                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Your task has been successfully add.';
                $final_output['taskid'] = $lastid;
            } else {
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Something went wrong! please try again.';
                $final_output['responsecode'] = '400';
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function assignTask() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        if ($userid != '' && $teamid != '') {
           
            $formArray = [
                'userid' => $this->input->post('id'),
                'teamid' => $this->input->post('teamid'),
                'spid' => $this->input->post('spid'),
                'title' => $this->input->post('title'),
                'taskdate' => $this->input->post('taskdate'),
                'describe' => $this->input->post('describe'),
                'task_name' => $this->input->post('task_name'),
                'member_type' => $this->input->post('member_type'),
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
            ];

            $insertId = $this->db->insert('assigntask', $formArray);
            $lastid = $this->db->insert_id();

            if ($insertId) {
                $final_output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'message' => 'Data Posted Successfully. To add another member please fill the details, else go back.',
                    'taskid' => $lastid,
                ];
            } else {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'Something went wrong! please try again',
                ];
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function SPreplacement() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');
        $spid = $this->input->post('spid');
//  $taskid    = $this->input->post('taskid');

        $reason1 = $this->input->post('reason1');
        $reason2 = $this->input->post('reason2');
        $reason3 = $this->input->post('reason3');
        $reason4 = $this->input->post('reason4');
        $feedback = $this->input->post('feedback');

        $date = date("Y-m-d h:i");

        $final_output = array();


        if ($userid != '' && $teamid != '' && $spid != '') {

            $check_record = $this->Common_model->common_getRow('spreplacement', array('spid' => $spid, 'teamid' => $teamid));
            if ($check_record != '') {

                $final_output['status'] = 'failed';
                $final_output['message'] = 'Already requested to replacement';
                $final_output['responsecode'] = '402';
            } else {

                $data = array(
                    'userid' => $userid,
                    'spid' => $spid,
                    'teamid' => $teamid,
                    //'taskid'    =>$taskid,
                    'reason1' => $reason1,
                    'reason2' => $reason2,
                    'reason3' => $reason3,
                    'reason4' => $reason4,
                    'feedback' => $feedback,
                    'status' => 'Pending',
                    'create_at' => $date,
                    'update_at' => $date,
                );
                //   echo '<pre>';
                //   print_r($data);
                //   die;

                $task = $this->db->get_where('assigntask', ['spid' => $spid, 'teamid' => $teamid])->result();
                if (count($task) > 0) {
                    $insertId = $this->db->insert('spreplacement', $data);
                    $lastid = $this->db->insert_id();

                    $dataupdate = array(
                        'spreplacementid' => $lastid,
                        'taskstatus' => 'Replacement Pending',
                        'update_at' => $date,
                    );

                    //  $update_data = $this->Common_model->updateData('assigntask', $dataupdate, 'id="'.$taskid.'"');
                    $update_data = $this->Common_model->updateData('assigntask', $dataupdate, 'spid="' . $spid . '"');

                    if ($insertId) {
                        $final_output['responsecode'] = '200';
                        $final_output['status'] = 'success';
                        $final_output['message'] = 'Request sent successfully.';
                        //$final_output['taskid'] = $lastid ;
                    } else {
                        $final_output['status'] = 'failed';
                        $final_output['message'] = 'Something went wrong! please try again.';
                        $final_output['responsecode'] = '400';
                    }
                } else {
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Task not assign of this user.';
                    $final_output['responsecode'] = '400';
                }
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid/teamid/spid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function teamClose() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        $date = date("Y-m-d h:i");

        $final_output = array();


        if ($userid != '' && $teamid != '') {

            $dataupdate = array(
                'status' => '0',
                'update_at' => $date
            );

            $update_data = $this->Common_model->updateData('myteams', $dataupdate, 'id="' . $teamid . '" AND userid="' . $userid . '"');

            if ($update_data) {
                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['message'] = 'Your team is close.';
            } else {
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Something went wrong! please try again.';
                $final_output['responsecode'] = '400';
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spTasklist() {



        $this->load->model('Common_model');



        //$userid  = $this->input->post('id');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');



        if ($spid != '') {



            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'usertype' => '0', 'status' => '1'));
            
            if ($check_record != '') {



                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;



                if ($photo != '') {
                    $uphoto = $basepath . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }




                $query = $this->db->query("SELECT * FROM `assigntask` WHERE spid='" . $spid . "' AND teamid='" . $teamid . "' AND taskstatus!='Replacement Pending'");
                $record = $query->result();
                $total = $query->num_rows();
                if ($total > 0) {



                    foreach ($record as $assigntask) {
                     $approve_feedback = $this->db->get_where('tbl_all_feedback',['feedback_type'=>'2' ,'main_id'=>$assigntask->id])->row();


                        $taskdata[] = array(
                            //'teamid' => $assigntask->teamid,
                            'taskid' => $assigntask->id,
                            'taskname' => $assigntask->title,
                            'taskdescribe' => $assigntask->describe,
                            'taskstatus' => $assigntask->taskstatus == '' ? 'Pending' : $assigntask->taskstatus,
                            'taskdate' => $assigntask->taskdate,
                            'comments' => ($assigntask->comments)?$assigntask->comments:"",
                            'approve_feedback' => (isset($approve_feedback->message) && $approve_feedback->message!=null)?$approve_feedback->message:"",
                        );
                    }
                } else {
                    $taskdata = [];
                }

                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'tasklist' => $taskdata,
                );



                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $data_array;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'SPid not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spTasklist_old() {

        $this->load->model('Common_model');

        //$userid  = $this->input->post('id');
        $spid = $this->input->post('spid');

        if ($spid != '') {

            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'usertype' => '0', 'status' => '1'));
            if ($check_record != '') {

                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;

                if ($photo != '') {
                    $uphoto = $basepath . 'upload/users/' . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }


                $query = $this->db->query("SELECT * FROM `assigntask` WHERE spid='" . $spid . "' AND taskstatus!='Replacement Pending'");
                $record = $query->result();
//      print_r($record);
                $total = $query->num_rows();
                if ($total > 0) {

                    foreach ($record as $assigntask) {

                        $taskdata[] = array(
                            'taskid' => $assigntask->id,
                            'taskname' => $assigntask->task_name,
                            'taskdescribe' => $assigntask->describe,
                            'taskstatus' => $assigntask->taskstatus == '' ? 'Pending' : $assigntask->taskstatus,
                            'taskdate' => $assigntask->taskdate
                        );
                    }
                } else {
                    $taskdata = array();
                }



                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'tasklist' => $taskdata,
                );

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $data_array;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'SPid not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spReplacementList() {

        $this->load->model('Common_model');
        $basepath = base_url();

        $member_array = array();

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        $query = $this->db->query("SELECT * FROM `spreplacement` WHERE userid='" . $userid . "' AND teamid='" . $teamid . "' ORDER BY id DESC");
        $record = $query->result();
        $total = $query->num_rows();

        if ($total > 0) {
            foreach ($record as $list) {

                $spid = $list->spid;
                $taskid = $list->taskid;



                $userQuery = $this->db->query("SELECT * FROM `logincr` INNER JOIN `assigntask` ON logincr.id=assigntask.spid WHERE assigntask.spid='" . $spid . "' AND assigntask.taskstatus='Replacement Pending' ");
                $userRecord = $userQuery->result();

                $totals = $userQuery->num_rows();
                if ($totals > 0) {
                    foreach ($userRecord as $row) {
                        //$countspid = $row->$contsp;

                        $photo = $row->image;
                        if ($photo != '') {
                            $uphoto = base_url() . 'upload/users/' . $photo;
                        } else {
                            $uphoto = base_url() . "upload/users/photo.png";
                        }



                        $member_array[] = array(
                            'spid' => $row->spid,
                            'teamid' => $teamid,
                            'photo' => $uphoto,
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'taskname' => $row->title,
                            'taskstatus' => 'Pending',
                        );
                    }

                    $final_output['responsecode'] = '200';
                    $final_output['status'] = 'success';
                    $final_output['data'] = $member_array;
                } else {
                    $final_output['responsecode'] = '402';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Record not found';
                }
            }
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function spReplacementdetail() {

        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $spid = $this->input->post('spid');
        $teamid = $this->input->post('teamid');

        if ($userid != '' && $spid != '' && $teamid != '') {

            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'usertype' => '0', 'status' => '1'));
            if ($check_record != '') {

                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;

                if ($photo != '') {
                    $uphoto = $basepath . 'upload/users/' . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }


                $check_status = $this->Common_model->common_getRow('spreplacement', array('spid' => $spid));
                if ($check_status != '') {
                    $userstatus = $check_status->status;
                }


                $query = $this->db->query("SELECT * FROM `assigntask` WHERE spid='" . $spid . "' ");
                $record = $query->result();
                $total = $query->num_rows();
                if ($total > 0) {

                    foreach ($record as $assigntask) {

                        $taskdata[] = array(
                            'taskid' => $assigntask->id,
                            'taskname' => $assigntask->title,
                            'taskdescribe' => $assigntask->describe,
                            'taskstatus' => $assigntask->taskstatus,
                            'taskdate' => $assigntask->taskdate
                        );
                    }
                } else {
                    $taskdata = array();
                }




                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'bio' => $check_record->about,
                    'rating' => $check_record->rating,
                    'userstatus' => $userstatus,
                    'tasklist' => $taskdata,
                );

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $data_array;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Required parameter not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    public function teamNoteslist() {

        $this->load->model('Common_model');

        //$userid  = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        if ($teamid != '') {

            $query = $this->db->query("SELECT * FROM `myteamnotes` WHERE teamid='" . $teamid . "' ");
            $record = $query->result();
            $total = $query->num_rows();
            if ($total > 0) {

                foreach ($record as $assigntask) {


                    $userid = $assigntask->userid;

                    $check_record = $this->Common_model->common_getRow('logincr', array('id' => $userid, 'status' => '1'));
                    if ($check_record != '') {

                        $basepath = base_url();
                        $photo = $check_record->image;

                        if ($photo != '') {
                            $uphoto = $basepath . 'upload/users/' . $photo;
                        } else {
                            $uphoto = $basepath . "upload/users/photo.png";
                        }



                        $taskdata[] = array(
                            'notesid' => $assigntask->id,
                            'notes' => $assigntask->notes,
                            'datetime' => $assigntask->create_at,
                            'firstname' => $check_record->firstname,
                            'lastname' => $check_record->lastname,
                            'photo' => $uphoto,
                        );
                    }
                }

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $taskdata;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Required parameter not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    function rand_string($length) {
        $str = "";
        $chars = "subinsblogabcdefghijklmanopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $size = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[rand(0, $size - 1)];
        }
        return $str;
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

    //add by zubear test data start
    function save_data() {
        //print_r(file_get_contents('php://input'));exit;
        $json = file_get_contents('php://input');
        $json_array = json_decode($json);
        $final_output = array();
        if (!empty($json_array)) {
            if ($json_array->user_email != '' && $json_array->user_password != '') {
                $data_array = array(
                    'user_name' => $json_array->user_email,
                    'password' => $json_array->user_password,
                );

                $insertId = $this->Common_model->common_insert('extention_user_password', $data_array);
                if (!empty($insertId)) {

                    $final_output['status'] = 'success';
                    $final_output['message'] = 'Login Successfully';
                    $final_output['data'] = $data_array;
                } else {
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'failed3';
                }
            } else {
                $final_output['status'] = 'failed';
                $final_output['message'] = 'failed4';
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = "No Request Parameter Found.";
        }
        header("content-type: application/json");
        echo json_encode($final_output);
    }

    //end login (Y) 

    public function questionlist($industry_id = NULL) {

        $industry_id = $this->uri->segment(3);
        if ($industry_id != '') {
            $data = $this->db->get_where('tbl_questions', ['industry_id' => $industry_id, 'status' => '0'])->result();
            if (count($data) > 0) {
                foreach ($data as $val) {
                    $dataArray[] = array(
                        'id' => $val->id,
                        'question' => $val->question,
                    );
                }
                $Output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'data' => $dataArray
                ];
            } else {
                $Output = [
                    'responsecode' => '402',
                    'status' => 'failed',
                    'message' => 'Record not found'
                ];
            }
        } else {
            $Output = [
                'responsecode' => '404',
                'status' => 'failed',
                'message' => 'Industry id required'
            ];
        }
        header("content-type: application/json");
        echo json_encode($Output);
    }
    
     public function previewofferletter($offer_id = NULL) {     

          $result = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image , b.address , b.city , b.postalcode , b.country , CONCAT(c.firstname, ' '  , c.lastname) AS cname , c.address as caddress , c.city as ccity , c.postalcode as cpostalcode , c.country as ccountry , d.teamname , e.title as pmode")
        ->from('tbl_offer_letter as a')
        ->join('logincr as b', 'b.id = a.provider_id', 'left')
        ->join('logincr as c', 'c.id = a.user_id', 'left')
        ->join('myteams as d', 'd.id = a.team_id', 'left')
        ->join('tbl_payment_mode as e', 'e.id = a.payment_method', 'left')
        ->where(['a.encrypt_key' => $this->uri->segment(3)])->get()->row();
        if( $result){
           $requiredoc = $this->db->select('*')->from('tbl_required_doc')->where_in('id',explode(',', $result->required_doc))->get()->result();
           $benifits = $this->db->select('*')->from('tbl_benefits')->where_in('id', explode(',', $result->benifits))->get()->result();           
            $data = [
                'result' => $result,
                'requiredoc' => $requiredoc,
                'benifits' => $benifits,
            ];
        $this->load->view('offer/offer',$data); 
    }else{
        echo "<h2>Data not found!</h2>";
    }


}

public function downloadofferletter($offer_id = NULL) {


    $result = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image , b.address , b.city , b.postalcode , b.country , CONCAT(c.firstname, ' '  , c.lastname) AS cname , c.address as caddress , c.city as ccity , c.postalcode as cpostalcode , c.country as ccountry , d.teamname , e.title as pmode")
        ->from('tbl_offer_letter as a')
        ->join('logincr as b', 'b.id = a.provider_id', 'left')
        ->join('logincr as c', 'c.id = a.user_id', 'left')
        ->join('myteams as d', 'd.id = a.team_id', 'left')
        ->join('tbl_payment_mode as e', 'e.id = a.payment_method', 'left')
        ->where(['a.encrypt_key' => $this->uri->segment(3)])->get()->row();
    if( $result){

        $requiredoc = $this->db->select('*')->from('tbl_required_doc')->where_in('id',explode(',', $result->required_doc))->get()->result();
        $benifits = $this->db->select('*')->from('tbl_benefits')->where_in('id', explode(',', $result->benifits))->get()->result();           
            $data = [
                'result' => $result,
                'requiredoc' => $requiredoc,
                'benifits' => $benifits,
            ];
        $html = $this->load->view('offer/offer_pdf',$data,TRUE);
        $mpdf = new Mpdf\Mpdf();
        $mpdf->SetHeader('Mipece.com||Page: {PAGENO}');
        $mpdf->SetFooter('Mipece.com||Footer');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }else{
        echo "<h2>Data not found!</h2>";
    }
}
//show interview details and service provider by interview date
     public function spInterviewlistByDate() {

        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $interview_date = trim($this->input->post('interviewDate'));

        $query = $this->db->query("SELECT * FROM `scheduleinterview` WHERE userid='" . $userid . "' AND (status='pending' OR status='Pending') AND interviewDate='" . $interview_date . "'");
        $record = $query->result();
        $total = $query->num_rows();
        if ($total > 0) {

            foreach ($record as $list) {

                $spid = $list->spid;
                $userid = $list->userid;

                $userQuery = $this->db->query("SELECT * FROM `logincr` left JOIN userskill ON userskill.userid=logincr.id WHERE logincr.id='" . $spid . "' AND logincr.usertype='0'");

                $exp_record = $this->db->select('b.name as experience')->from('tbl_xai_matching as a')
                                ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                                ->where('a.user_id', $spid)->get()->row();
                $experience = '';
                if ($exp_record) {
                    $experience = $exp_record->experience;
                }

                $numrow = $userQuery->num_rows();
                if ($numrow > 0) {
                    $userRecord = $userQuery->result();
                    foreach ($userRecord as $row) {

                        $photo = $row->image;
                        if ($photo != '') {
                            $uphoto = base_url() .$photo;
                        } else {
                            $uphoto = base_url() . "upload/users/photo.png";
                        }

                        $data_array[] = array(
                            'interviewid' => $list->id,
                            'teamid' => $list->teamid,
                            'userid' => $userid,//customer id
                            'spid' => $spid,//service provider id
                            'photo' => $uphoto,
                            'firstname' => $row->firstname,
                            'lastname' => $row->lastname,
                            'email' => $row->email,
                            'contact' => $row->contact,
                            'ssnnum' => $row->ssnnum,
                            'address' => $row->address,
                            'country' => $row->country,
                            'city' => $row->city,
                            'postalcode' => $row->postalcode,
                            'bio' => $row->about,
                            'rating' => $row->rating,
                            'rating' => $this->Common_model->getrating($spid),
                            'experience' => $experience,
                            'interviewdate' => $list->interviewDate,
                            'interviewtime' => $list->interviewTime,
                        );
                    }

                    $final_output['responsecode'] = '200';
                    $final_output['status'] = 'success';
                    $final_output['data'] = $data_array;
                } else {
                    $final_output['responsecode'] = '402';
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Record not found';
                }
            }
        } else {
            $final_output['responsecode'] = '402';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Record not found';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }
    
     public function downloadInvoice($invoice_id = NULL) {
    require_once APPPATH . 'third_party/mpdf/vendor/autoload.php';
 $result = $this->db->select("a.*, CONCAT(b.firstname, ' '  , b.lastname) AS name, c.teamimage,c.teamname , c.id as teamid ,  b.image , b.profile_pic")
        ->from('tbl_invoice as a')
        ->join('logincr as b', 'b.id = a.user_id', 'left')
        ->join('myteams as c', 'c.id = a.team_id', 'left')
        ->where(['a.id' => $this->uri->segment(3)])->get()->row();
        if( $result){
    if( $result){
        $filename = md5(uniqid());
         $sp_detail = $this->db->get_where('logincr',['id'=>$result->spid])->row();
         $data['invoice'] = $result;
         $data['sp'] = $sp_detail;
        $html = $this->load->view('invoice/invoice',$data,TRUE);
        $mpdf = new Mpdf\Mpdf();
        $mpdf->SetHeader('Mipece.com||Page: {PAGENO}');
        $mpdf->SetFooter('Mipece.com||');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }else{
        echo "<h2>Data not found!</h2>";
    }
}

}

//assign task new
/*
 public function assignTaskNew() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');

        if ($userid != '' && $teamid != '') {
            $formArray = [
                'userid' => $this->input->post('id'),
                'teamid' => $this->input->post('teamid'),
                'spid' => $this->input->post('spid'),
                'title' => $this->input->post('title'),
                'taskdate' => $this->input->post('taskdate'),
                'describe' => $this->input->post('describe'),
                'task_name' => $this->input->post('task_name'),
                'member_type' => $this->input->post('member_type'),
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
                'relative_member' => $this->input->post('relative_member'),
            ];

            $insertId = $this->db->insert('assigntask', $formArray);
            $lastid = $this->db->insert_id();

            if ($insertId) {
                $final_output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'message' => 'Data Posted Successfully. To add another member please fill the details, else go back.',
                    'taskid' => $lastid,
                ];
            } else {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'Something went wrong! please try again',
                ];
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }*/
    public function assignTaskNew() {
        $this->load->model('Common_model');

        $userid = $this->input->post('id');
        $teamid = $this->input->post('teamid');
        $day = $this->input->post('day');

        if ($userid != '' && $teamid != '') {
            if($day>30)
            {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'day value more then 30 please enter below or equal 30',
                ];
            }else
            {
             $user = $this->db->get_where('logincr', ['id' => $userid])->row();
             $team = $this->db->get_where('myteams', ['id' => $teamid])->row();
             if($team->agreement_id !=0)
             {
                
              if($user->switch_account == 1)
            {
                
                $user_id = $team->agreement_sendby_id;
                $sc_id = $team->user_id ;
                 
            }else if($user->switch_account == 2)
            {
                 
                 $user_id = $team->user_id;
                $sc_id = $team->agreement_sendby_id ;
            } 
             }else{
               $user_id = $userid;
               $sc_id = 0;
             }
           

            $formArray = [
                'userid' => $user_id,
                'sc_id' => $sc_id,
                'teamid' => $this->input->post('teamid'),
                'spid' => $this->input->post('spid'),
                'title' => $this->input->post('title'),
                'taskdate' => $this->input->post('taskdate'),
                'describe' => $this->input->post('describe'),
                'task_name' => $this->input->post('task_name'),
                'member_type' => $this->input->post('member_type'),
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
                'relative_member' => $this->input->post('relative_member'),
            ];

            $insertId = $this->db->insert('assigntask', $formArray);
            $lastid = $this->db->insert_id();

            if ($insertId) {
            if($day>0 && !empty($day))
            {
                if($team->agreement_id !=0)
             {
                
              if($user->switch_account == 1)
            {
                
                $user_id = $team->agreement_sendby_id;
                $sc_id = $team->user_id ;
                 
            }else if($user->switch_account == 2)
            {
                 
                 $user_id = $team->user_id;
                $sc_id = $team->agreement_sendby_id ;
            } 
             }else{
               $user_id = $userid;
               $sc_id = 0;
             }
           
                for($i=1; $i<=$day; $i++)
                {
                    $date = str_replace('/','-',$this->input->post('taskdate'));
                    $dd = date('Y-m-d',strtotime($date));
               $formArray = [
                 'userid' => $user_id,
                'sc_id' => $sc_id,
                'teamid' => $this->input->post('teamid'),
                'spid' => $this->input->post('spid'),
                'title' => $this->input->post('title'),
                'taskdate' => date('j/n/Y', strtotime($dd. ' + '.$i.' days')),
                'describe' => $this->input->post('describe'),
                'task_name' => $this->input->post('task_name'),
                'member_type' => $this->input->post('member_type'),
                'start_time' => $this->input->post('start_time'),
                'end_time' => $this->input->post('end_time'),
                'relative_member' => $this->input->post('relative_member'),
              ];

            $this->db->insert('assigntask', $formArray);
             }  
            }
                $final_output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'message' => 'Data Posted Successfully. To add another member please fill the details, else go back.',
                    'taskid' => $lastid,
                ];
            } else {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'Something went wrong! please try again',
                ];
            }
        } 
    }else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }
     //update task api
    public function updateTaskNew() {
        $this->load->model('Common_model');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($data);
        $user_id = $data['user_id'];
        $task_id = $data['task_id'];
        $teamid = $data['teamid'];

        if ($user_id != '' && $teamid != '') {
            $row = $this->db->where(['id'=>$task_id ,'taskstatus'=>'Pending'])->or_where(['id'=>$task_id ,'taskstatus'=>''])->get('assigntask')->num_rows();
            if($row<1)
            {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'You can only update pending task',
                ];
            }else{
            $formArray = [
                'userid' => $user_id,
                'teamid' => $data['teamid'],
                'title' => $data['title'],
                'taskdate' => $data['taskdate'],
                'describe' => $data['describe'],
                'task_name' => $data['task_name'],
                'member_type' => 'NA',
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'relative_member' => $data['relative_member'],
            ];

           

            if ($this->db->set($formArray)->where(['id'=>$task_id])->update('assigntask')) {
                $final_output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'message' => 'Data updated successfully!',
                ];
            } else {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'Something went wrong! please try again',
                ];
            }
        }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or Teamid is not found.';
            $final_output['responsecode'] = '403';
        }


        header("content-type: application/json");
        echo json_encode($final_output);
    }
    //delete task
     public function deleteTaskNew() {
        $this->load->model('Common_model');
        $data = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_data($data);
        $task_id = $data['task_id'];

        if ($task_id != '') {
            if ($this->db->delete('assigntask',['id'=>$task_id])) {
                $final_output = [
                    'responsecode' => '200',
                    'status' => 'success',
                    'message' => 'Task deleted successfully!',
                ];
            } else {
                $final_output = [
                    'responsecode' => '400',
                    'status' => 'failed',
                    'message' => 'Something went wrong! please try again',
                ];
            }
        } else {
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Userid or taskid is not found.';
            $final_output['responsecode'] = '403';
        }

        header("content-type: application/json");
        echo json_encode($final_output);
    }
    //request to pay pdf
    public function requestPayPdf() {
        $id = base64_decode(urldecode($this->uri->segment(4)));
        $data =  $this->db->select('a.* , b.task_name , b.describe as description , b.title , b.taskdate , b.id as task_id  , b.start_time , b.end_time , b.taskstatus , b.comments , CONCAT(c.firstname ," " ,c.lastname) as spname , c.contact as sp_contact, CONCAT(d.firstname ," " ,d.lastname) as client_name , d.address as client_address , d.country as client_country , d.city as client_city , d.postalcode as client_postalcode')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=a.request_by','left')->join('logincr as d','d.id=a.user_id','left')->order_by('a.created_at','DESC')->where(['a.id'=>$id])->get()->row();
        if($data)
        {
        $my['data'] = $data;    
        $html = $this->load->view('invoice/invoice_request_pay',$my,TRUE);
        $mpdf = new Mpdf\Mpdf();
        $stylesheet = file_get_contents('https://esldevstudio.com/app/myteam/design/css/request_pay.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->SetHeader('Mipece.com||Page: {PAGENO}');
        $mpdf->SetFooter('Mipece.com||Footer');
        $mpdf->WriteHTML($html);
        $mpdf->Output(md5($id).'.pdf',"I");
    }else{
        echo "<h2>Data not found!</h2>";
    }
}
//request pay view
 public function requestPayPdfView() {
        $id = base64_decode(urldecode($this->uri->segment(4)));
        $data =  $this->db->select('a.* , b.task_name , b.describe as description , b.title , b.taskdate , b.id as task_id , b.taskdate , b.start_time , b.end_time , b.taskstatus , b.comments , CONCAT(c.firstname ," " ,c.lastname) as spname , c.contact as sp_contact, CONCAT(d.firstname ," " ,d.lastname) as client_name , d.address as client_address , d.country as client_country , d.city as client_city , d.postalcode as client_postalcode')->from('tbl_payment_request as a')->join('assigntask as b' ,'b.id = a.taskid','left')->join('logincr as c','c.id=a.request_by','left')->join('logincr as d','d.id=a.user_id','left')->order_by('a.created_at','DESC')->where(['a.id'=>$id])->get()->row();
        if($data)
        {
        $my['data'] = $data;    
        $html = $this->load->view('invoice/invoice_request_pay_web',$my);
    }else{
        echo "<h2>Data not found!</h2>";
    }
}

//
    public function createTeamNameNew() {
        $token = $this->input->get_request_header('Secret-Key');
        header("content-type: application/json");
        $user_id = $this->input->post('user_id');
        if ($token != '' && $user_id != '') {
            $userdata = $this->db->get_where('logincr', ['id' => $user_id, 'token_security' => $token])->row();
            if (!empty($userdata)) {
                $configF['upload_path'] = './upload/images/';
                $configF['allowed_types'] = 'jpeg|jpg|png';
                $configF['max_size'] = 50600;
                $this->load->library('upload', $configF);

                $data = $this->input->post();
                $config = [
                    ['field' => 'user_id', 'label' => 'user_id', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'User is required',
                            'numeric' => 'User should be  numeric',
                        ],
                    ],
                    ['field' => 'required_members', 'label' => 'required_members', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Numbers of members is required',
                            'numeric' => 'Members should be  numeric',
                        ],
                    ],
                    ['field' => 'teamname', 'label' => 'teamname', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Team name is required',
                           // 'is_unique' => 'Team name has already be taken!',
                        ],
                    ],
                    ['field' => 'language', 'label' => 'language', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Perfered language  is required',
                        ],
                    ],
                    ['field' => 'description', 'label' => 'description', 'rules' => 'required',
                        'errors' => [
                            'required' => 'Description is required',
                        ],
                    ],
                   ['field' => 'zipcode', 'label' => 'zipcode', 'rules' => 'required|numeric',
                        'errors' => [
                            'required' => 'Zipcode is required',
                            'numeric' => 'Zipcode should be  numeric',
                        ],
                    ],
                ];

                $this->form_validation->set_data($data);
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE) {
                    $response = ['status' => 'false',
                        'responsecode' => '403',
                         'message' => strip_tags(validation_errors()),
                    ];
                } else {

                    $teamnameexist = $this->db->get_where('myteams', ['user_id' => $user_id, 'teamname' => $this->input->post('teamname')])->row();
                    if ($teamnameexist) {
                        $response = [
                            'responsecode' => '403',
                            'status' => 'failed',
                            'message' => 'Team name has already be taken!',
                        ];
                        echo json_encode($response);
                        die;
                    }

                    $formArray = [
                        'user_id' => $this->input->post('user_id'),
                        'members' => $this->input->post('required_members'),
                        'teamname' => $this->input->post('teamname'),
                        'language' => $this->input->post('language'),
                        'zipcode' => $this->input->post('zipcode'),
                        'description' => $this->input->post('description'),
                        'member_id' => $this->input->post('member_id')
                    ];
                    if (!empty($_FILES['teamimage']['name'])) {
                        if (!$this->upload->do_upload('teamimage')) {
                            $response = ['status' => 'false',
                                 'responsecode' => '403',
                                 'message' => strip_tags($this->upload->display_errors()),
                            ];
                           
                        } else {
                            $data = array('upload_data' => $this->upload->data());
                            $formArray['teamimage'] = 'upload/images/' . $this->upload->data('file_name');
                        }
                    }
                    $this->db->insert('myteams', $formArray);
                    $affected = $this->db->insert_id();
                    $mem = $this->input->post('member_id');
                    if ($affected > 0) {
                        
                        if($mem!=0)
                        {
                            $my_arr = $this->db->get_where('tbl_xai_matching',['user_id'=>$user_id , 'team_id'=>0 , 'member_id'=>$mem])->row_array();
                            $my_arr['id'] = '';
                            $my_arr['team_id'] = $affected;
                            
                            $this->db->insert('tbl_xai_matching',$my_arr);
                        }
                        $response = [
                            'team_id' => "$affected",
                            "member_id" => "$mem",
                            'responsecode' => '200',
                            'status' => 'success',
                            'message' => 'Your team create successfully!',
                        ];
                    } else {
                        $response = [
                            'responsecode' => '403',
                            'status' => 'failed',
                            'message' => 'Your team not create successfully!',
                        ];
                    }
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
     //get sp details new
    public function getspProfileNew() {

        $this->load->model('Common_model');
    $oData = [];
        //$userid  = $this->input->post('id');
        $spid = $this->input->post('spid');
        //$teamid = $this->input->post('teamid');


        if ($spid != '') {
            $check_record = $this->Common_model->common_getRow('logincr', array('id' => $spid, 'status' => '1'));
            if ($check_record != '') {

                $uids = $check_record->id;
                $basepath = base_url();
                $photo = $check_record->image;

                if ($photo != '') {
                    $uphoto = $basepath . $photo;
                } else {
                    $uphoto = $basepath . "upload/users/photo.png";
                }



                // $edu_record = $this->Common_model->common_getRow('usereducation', array('userid'=>$spid));

                $edu_record = $this->db->get_where('usereducation', ['userid' => $spid])->result();
                $otherData = $this->db->get_where('tbl_user_certificate', ['user_id' => $spid])->result();
                if ($edu_record != '') {
                    foreach ($edu_record as $val) {
                        $data_edu[] = [
                            'id' => $val->id,
                            'userid' => $val->userid,
                            'education' => $val->education,
                            'passingyear' => $val->passingyear,
                            'certificate' => $val->certificate ? base_url('upload/users/') . $val->certificate : '',
                            'collegename' => $val->collegename,
                        ];
                    }
                } else {
                    $data_edu = [];
                }
                
                if ($otherData != '') {
                    foreach ($otherData as $val1) {
                        $oData[] = [
                            'id' => $val1->id,
                            'certificate' => ($val1->certificate!=null)?base_url($val1->certificate):'',
                            'passing_date' => ($val1->passing_date!=null)?$val1->passing_date:'',
                            'renewal_date' => ($val1->renewal_date!=null)?$val1->renewal_date:'',
                            'license' => ($val1->license!=null)?base_url($val1->license):'',
                            'continue_passing_date' => ($val1->continue_passing_date!=null)?$val1->continue_passing_date:'',
                            'continue_renewal_date' => ($val1->continue_renewal_date!=null)?$val1->continue_renewal_date:'',
                            'certification_education' => ($val1->certification_education!=null)?base_url($val1->certification_education):'',
                           // 'passing_date' => ($val1->passing_date!=null)?val1->passing_date:'',
                        ];
                    }
                } else {
                    $oData = [];
                }

                $exp_record = $this->db->select('b.name as experience, c.name as industry, d.name as skills')->from('tbl_xai_matching as a')
                                ->join('tbl_experience as b', 'a.experience_id = b.id ', 'left')
                                ->join('tbl_industries as c', 'a.industry_id = c.id ', 'left')
                                ->join('tbl_skill as d', 'a.skill_id = d.id ', 'left')
                                ->where('a.user_id', $spid)->get()->row();

                $data_exp = [];
                if ($exp_record) {

                    $data_exp[] = array(
                        'experience' => $exp_record->experience,
                        'industry' => $exp_record->industry,
                        'skills' => $exp_record->skills,
                    );
                }

                $interviewdata = $this->Common_model->common_getRow('scheduleinterview', array('spid' => $spid,  'status' => 'pending'));

                if ($interviewdata != '') {
                    $data_interview[] = array(
                        'interviewdate' => $interviewdata->interviewDate,
                        'interviewtime' => $interviewdata->interviewTime,
                    );
                } else {
                    $data_interview = [];
                }
                
                $certificates = [];
                $certificate = $this->db->select('a.*,b.title')->from('tbl_user_certificate as a')
                                ->join('tbl_certification as b', 'a.certification_id=b.id', 'left')
                                ->where('a.user_id', $spid)
                                ->get()->result();
                if ($certificate) {
                    foreach ($certificate as $cert) {
                        $certificates[] = [
                            'id' => $cert->id,
                            'title' => $cert->title,
                            'mime_type' => pathinfo($cert->certificate, PATHINFO_EXTENSION),
                            'certificate' => base_url($cert->certificate),
                        ];
                    }
                }

                $data_array[] = array(
                    'spid' => $check_record->id,
                    'photo' => $uphoto,
                    'firstname' => $check_record->firstname,
                    'lastname' => $check_record->lastname,
                    'email' => $check_record->email,
                    'contact' => $check_record->contact,
                    'ssnnum' => $check_record->ssnnum,
                    'address' => $check_record->address,
                    'country' => $check_record->country,
                    'city' => $check_record->city,
                    'postalcode' => $check_record->postalcode,
                    'bio' => $check_record->about,
                    'audio_file' => (!empty($check_record->audio_file) || $check_record->audio_file!=NULL)?base_url($check_record->audio_file):'',
                    'rating' => $this->Common_model->getrating($spid),
                    'interviewdatetime' => $data_interview,
                    'educationdata' => $data_edu,
                    'experiencedata' => $data_exp,
                    'certificates' => $certificates,
                    'otherData' => $oData,
                );

                $final_output['responsecode'] = '200';
                $final_output['status'] = 'success';
                $final_output['data'] = $data_array;
            } else {
                $final_output['responsecode'] = '402';
                $final_output['status'] = 'failed';
                $final_output['message'] = 'Record not found';
            }
        } else {
            $final_output['responsecode'] = '403';
            $final_output['status'] = 'failed';
            $final_output['message'] = 'Spid not found.';
        }
        //die;
        header("content-type: application/json");
        echo json_encode($final_output);
    }
     //preview agreement letter
    public function previewAgreementLetter() {     
        $agreement_id = $this->uri->segment(3);

          $result = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image , b.address , b.city , b.postalcode , b.country , CONCAT(c.firstname, ' '  , c.lastname) AS cname , c.address as caddress , c.city as ccity , c.postalcode as cpostalcode , c.country as ccountry , d.name as lang , e.title as payment_mode")
        ->from('tbl_client_agreement as a')
        ->join('logincr as b', 'b.id = a.sc_id', 'left')
        ->join('logincr as c', 'c.id = a.user_id', 'left')
        ->join('tbl_language as d', 'd.id = a.language', 'left')
        ->join('tbl_payment_mode as e', 'e.id = a.payment_terms', 'left')
        ->where(['a.encrypt_key' => $agreement_id])->get()->row();
        if( $result){    
            $data = [
                'result' => $result,
            ];
        $this->load->view('offer/client_agreement',$data); 
    }else{
        echo "<h2>Data not found!</h2>";
    }


}
public function downloadAgreementLetter($agreement_id = NULL) {

 $agreement_id = $this->uri->segment(3);

         $result = $this->db->select("a.*,CONCAT(b.firstname, ' '  , b.lastname) AS name,b.image , b.address , b.city , b.postalcode , b.country , CONCAT(c.firstname, ' '  , c.lastname) AS cname , c.address as caddress , c.city as ccity , c.postalcode as cpostalcode , c.country as ccountry , d.name as lang , e.title as payment_mode")
        ->from('tbl_client_agreement as a')
        ->join('logincr as b', 'b.id = a.sc_id', 'left')
        ->join('logincr as c', 'c.id = a.user_id', 'left')
        ->join('tbl_language as d', 'd.id = a.language', 'left')
        ->join('tbl_payment_mode as e', 'e.id = a.payment_terms', 'left')
        ->where(['a.encrypt_key' => $agreement_id])->get()->row();
    if( $result){

          
            $data = [
                'result' => $result,
               
            ];
        $html = $this->load->view('offer/client_agreement_pdf.php',$data,TRUE);
        $mpdf = new Mpdf\Mpdf();
        $mpdf->SetHeader('Mipece.com||Page: {PAGENO}');
        $mpdf->SetFooter('Mipece.com||Footer');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
    }else{
        echo "<h2>Data not found!</h2>";
    }
}



}
