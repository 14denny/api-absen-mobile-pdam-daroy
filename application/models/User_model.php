<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    function verify_login($username, $password){
        $user = $this->db->where('username', $username)->get('users_api')->row();

        if ($user != null){
            if(password_verify($password, $user->password)){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function get_user($username){
        return $this->db->where('username', $username)->get('users_api')->row();
    }

    function get_all_user(){
        return $this->db->get('users_api')->result();
    }
}