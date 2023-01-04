<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Verify_model extends CI_Model
{

    function verify_address($appid, $nip, $version)
    {
        $res_appid = $this->db->query("SELECT * from registered_device where appid='$appid' and version='$version'")->row();
        $res_nip = $this->db->query("SELECT * from registered_device where nip='$nip' and version='$version'")->row();
        return $res_appid || $res_nip;
    }

    function insert_device($appid, $nip, $version)
    {
        $data = [
            'appid' => $appid,
            'nip' => $nip,
            'version' => $version
        ];
        return $this->db->insert('registered_device', $data);
    }

    function nip_registered($nip, $version)
    {
        $result = $this->db->query("SELECT * from registered_device where nip='$nip' and version='$version'")->row();
        return $result != null;
    }

    function appid_registered($appid, $version)
    {
        $result = $this->db->query("SELECT * from registered_device where appid='$appid' and version='$version'")->row();
        return $result != null;
    }

    function get_nip_by_address($appid, $version)
    {
        return $this->db->query("SELECT * from registered_device where appid='$appid' and version='$version'")->row();
    }
}
