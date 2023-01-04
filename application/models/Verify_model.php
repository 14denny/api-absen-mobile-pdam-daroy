<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Verify_model extends CI_Model
{

    function verify_address($appid, $nik, $version)
    {
        $res_appid = $this->db->query("SELECT 1 from registered_device where appid='$appid' and version='$version'")->row();
        $res_nik = $this->db->query("SELECT 1 from registered_device r join pegawai p on p.id=r.id_pegawai where p.nik='$nik' and version='$version'")->row();
        return $res_appid || $res_nik;
    }

    function insert_device($appid, $id_pegawai, $version)
    {
        $data = [
            'appid' => $appid,
            'id_pegawai' => $id_pegawai,
            'version' => $version
        ];
        return $this->db->insert('registered_device', $data);
    }

    function nik_registered($nik, $version)
    {
        $result = $this->db->select('1')
        ->from('registered_device rd')
        ->join('pegawai p', 'p.id=rd.id_pegawai')
        ->where('p.nik', $nik)
        ->where('version', $version)->get()->row();

        return $result != null;
    }

    function appid_registered($appid, $version)
    {
        $result = $this->db->select('1')
        ->from('registered_device rd')
        ->where('appid', $appid)
        ->where('version', $version)
        ->get()->row();
        return $result != null;
    }

    function get_nip_by_address($appid, $version)
    {
        return $this->db->query("SELECT * from registered_device where appid='$appid' and version='$version'")->row();
    }
}
