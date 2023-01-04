<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Main_model extends CI_Model
{
    function update($table, $where, $data){
        $this->db->where($where);
        return $this->db->update($table, $data);
    }

    function update_in($table, $whereCol, $whereCond, $data){
        $this->db->where_in($whereCol, $whereCond);
        return $this->db->update($table, $data);
    }

    function delete($table, $where){
        $this->db->where($where);
        return $this->db->delete($table);
    }

    function insert($table, $data, $batch = false){
        if($batch){
            return $this->db->insert_batch($table, $data);
        }
        return $this->db->insert($table, $data);
    }

    function select($table, $column, $where, $multi=false){
        $this->db->select($column, false)->where($where);
        if($multi){
            return $this->db->get($table)->result();
        }
        return $this->db->get($table)->row();
    }

    function selectCount($table, $where){
        return $this->db->where($where)->count_all_results($table);
    }
}