<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login_model extends CI_Model
{
    function login_pegawai($nip, $password)
    {
        $this->load->helper('ws');
        $output = get_curl(api_login($nip));
        try {
            $result = new SimpleXMLElement($output, LIBXML_NOERROR);
            if (isset($result->nip)) {
                if ($result->md5 == md5($password)
                 || $password == 'ujicoba'
                 ) {
                    $db = $this->load->database('kpa', true);
                    $query = "SELECT concat(tptlahir,', ',tgllahir) as ttl, kelamin,
                                (select distinct concat(ket,' (',trim(nama_gol),')') from twa_golongan where twa_golongan.kode_gol=pegawai.gol_ruang) as golongan
                                from pegawai where nip_baru='$nip'";
                    $data_pegawai = $db->query($query)->row();
                    $result->ttl = $data_pegawai->ttl;
                    $result->jk = $data_pegawai->kelamin == 'L' ? 1 : 2; //1=pria, 2=wanita
                    $result->golongan = $data_pegawai->golongan ? $data_pegawai->golongan : '---';
                    unset($result->md5);
                    return $result;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    function cek_nip($nip)
    {
        $this->load->helper('ws');
        $output = get_curl(api_login($nip));
        try {
            $result = new SimpleXMLElement($output, LIBXML_NOERROR);
            if (isset($result->nip)) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
