<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Arvind extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['admin_model']);
        $this->admin_model->CheckLoginSession();
    }

    public function teamdetails($id = NULL) {
        $result = $this->db->select('a.*,b.name,CONCAT(c.firstname, " "  , c.lastname) AS user_name,c.image as user_image')->from('myteams as a')
                        ->join('tbl_language as b', 'a.language = b.id', 'left')
                        ->join('logincr as c', 'a.user_id = c.id', 'left')
                        ->where('c.id IS NOT NULL')
                        ->where('a.id', $id)->get()->row();
        $requirement = $requirement = $this->db->select('a.*,b.name as industry_name,c.name as skill_name,d.name as experience_name')
                        ->from('tbl_team_requirement as a')
                        ->join('tbl_industries as b', 'a.industry = b.id')
                        ->join('tbl_skill as c', 'a.skills = c.id')
                        ->join('tbl_experience as d', 'a.experience = d.id')->where('a.team_id', $id)->get()->result();
        $data = [
            'content' => 'teams/details',
            'title' => 'Team Details',
            'result' => $result,
            'requirement' => $requirement,
        ];
        $this->load->view('admin/template/index', $data);
    }

    public function deleteteam($id = NULL, $uid = NULL) {

        $this->db->where('id', $id)->delete('myteams');
         $this->db->where('teamid', $id)->delete('scheduleinterview');
        $this->db->where('team_id', $id)->delete('tbl_offer_letter');
        $this->session->set_flashdata('success', 'Team Deleted Successfully!');
        redirect('admin/dashboard/userview/' . $uid);
    }

}

?>