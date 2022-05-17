<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class SkillModel extends CI_Model {

    protected $table = 'tbl_skill';

    private function _get_datatables_query() {

        $this->column_order = array('a.id', 'a.name', 'a.status','b.name as industry');
        $this->column_search = array('a.name','b.name');
        $this->order = array('id' => 'DESC');

        $this->db->select($this->column_order)->from($this->table .' as a')->join('tbl_industries as b', 'a.industry_id = b.id', 'left');
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
      $this->db->select($this->column_order)->from($this->table. ' as a')->join('tbl_industries as b', 'a.industry_id = b.id', 'left');
        return $this->db->count_all_results();
    }

}
