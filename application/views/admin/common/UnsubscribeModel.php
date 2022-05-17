<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class UnsubscribeModel extends CI_Model {

    protected $table = 'tbl_unsubscribe_user';

    private function _get_datatables_query() {

        $this->column_order = array('a.id', 'a.comments', 'CONCAT(b.firstname, " ", b.lastname) AS user_name', 'c.name as package_name', 'd.question', 'DATE_FORMAT(a.created_at, "%d-%m-%Y") as created_at');
        $this->column_search = array('a.comments', 'b.firstname', 'b.lastname', 'c.name', 'd.question', 'DATE_FORMAT(a.created_at, "%d-%m-%Y") ');
        $this->order = array('a.id' => 'DESC');

        $this->db->select($this->column_order);
        $this->db->from($this->table . ' as a')
                ->join('logincr as b', 'a.user_id = b.id', 'left')
                ->join('tbl_subscription_package as c', 'a.package_id = c.id', 'left')
                ->join('tbl_unsubscribe_question as d', 'a.question_id = d.id', 'left')
                ->where('b.id is not null');
        $i = 0;

        foreach ($this->column_search as $item) { // loop column 
            if ($_POST['search']['value']) { // if datatable send POST for search
                if ($i === 0) { // first loop
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }
                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
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
        $this->db->from($this->table . ' as a')
                ->join('logincr as b', 'a.user_id = b.id', 'left')
                ->join('tbl_subscription_package as c', 'a.package_id = c.id', 'left')
                ->join('tbl_unsubscribe_question as d', 'a.question_id = d.id', 'left')
                ->where('b.id is not null');
        return $this->db->count_all_results();
    }

}

?>