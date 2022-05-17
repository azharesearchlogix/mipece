<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CommunityModel extends CI_Model {

    protected $table = 'tbl_question_post';
    protected $column_order = array('a.id', 'a.question', 'a.description', 'a.user_id', 'DATE_FORMAT(a.created_at, "%d-%m-%Y") as created_at', 'a.status');
    protected $column_search = array('a.question', 'a.description', 'a.user_id', 'DATE_FORMAT(a.created_at, "%d-%m-%Y")');
    protected $order = array('id' => 'DESC');

    private function _get_datatables_query() {
        $this->db->select($this->column_order)->from($this->table . ' as a')
                ->join('logincr as b', 'b.id = a.user_id', 'left')
                ->where('b.id IS NOT NULL');
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($_POST['search']['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) { // here order processing
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables() {
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
//echo $this->db->last_query(); die;
        return $query->result();
    }

    function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all() {
        $this->db->select($this->column_order)->from($this->table . ' as a')
                ->join('logincr as b', 'b.id = a.user_id', 'left')
                ->where('b.id IS NOT NULL');
        return $this->db->count_all_results();
    }

}

?>