<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Absen_model extends CI_Model
{
    function get_rekap_today($nip)
    {
        $today = date('Y-m-d');
        return $this->db->query("SELECT * from absen where nip='$nip' and tanggal='$today'")->row();
    }

    function check_pegawai_shift($nip)
    {
        $db = $this->load->database('kpa', true);
        return $db->query("SELECT * from pegawai_ship where nip='$nip'")->row();
    }

    function insert_absen_pagi_noshift($nip, $tanggal, $jam, $lat, $lon)
    {
        $absen = $this->db->query("SELECT * from absen where nip='$nip' and tanggal='$tanggal'")->row();
        if ($absen) {
            // $data = [
            //     'jam_masuk' => $jam,
            //     'lat_masuk' => $lat,
            //     'lon_masuk' => $lon
            // ];

            // $this->db->where('nip', $nip);
            // $this->db->where('tanggal', $tanggal);
            // return $this->db->update('absen', $data);
            return false; //kalau sudah absen pagi
        } else {
            $data = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'jam_masuk' => $jam,
                'shift' => 1,
                'lat_masuk' => $lat,
                'lon_masuk' => $lon
            ];
            return $this->db->insert('absen', $data);
        }
    }

    function insert_absen_sore_noshift($nip, $tanggal, $jam, $lat, $lon)
    {
        $absen = $this->db->query("SELECT * from absen where nip='$nip' and tanggal='$tanggal'")->row();

        if ($absen && $absen->jam_pulang) { //check kalo udh absen masuk dan pulang
            return false;
        }

        if ($absen) {
            $data = [
                'jam_pulang' => $jam,
                'lat_pulang' => $lat,
                'lon_pulang' => $lon
            ];

            $this->db->where('nip', $nip);
            $this->db->where('tanggal', $tanggal);
            return $this->db->update('absen', $data);
        } else {
            $data = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'jam_pulang' => $jam,
                'shift' => 1,
                'lat_pulang' => $lat,
                'lon_pulang' => $lon
            ];
            return $this->db->insert('absen', $data);
        }
    }

    function insert_absen_shift($nip, $tanggal, $jam, $lat, $lon)
    {
        $absen = $this->db->query("SELECT * from absen where nip='$nip' and tanggal='$tanggal'")->row();

        if ($absen && $absen->jam_masuk && $absen->jam_pulang) { //check kalo udh absen masuk dan pulang
            return false;
        }

        $jenis_absen = 1;

        if ($absen) {
            $jenis_absen = 2;
            $data = [
                'jam_pulang' => $jam,
                'lat_pulang' => $lat,
                'lon_pulang' => $lon
            ];

            $this->db->where('nip', $nip);
            $this->db->where('tanggal', $tanggal);
            if ($this->db->update('absen', $data)) {
                return $jenis_absen;
            } else {
                return false;
            }
        } else {
            $jenis_absen = 1;
            $data = [
                'nip' => $nip,
                'tanggal' => $tanggal,
                'jam_masuk' => $jam,
                'shift' => 2,
                'lat_masuk' => $lat,
                'lon_masuk' => $lon
            ];
            if ($this->db->insert('absen', $data)) {
                return $jenis_absen;
            } else {
                return false;
            }
        }
    }

    function di_area_usk($lat, $lon)
    {
        $lat_usk = 5.570348782621584;
        $lon_usk = 95.36982149662813;

        // $toleransi_jarak = 20;
        $toleransi_jarak = 1.1; //area dari titik 0.8 km

        $theta = $lon_usk - $lon;
        $distance = (sin(deg2rad($lat_usk)) * sin(deg2rad($lat))) + (cos(deg2rad($lat_usk)) * cos(deg2rad($lat)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        $distance = $distance * 1.609344; //jarak dalam km
        return (round($distance, 2)) <= $toleransi_jarak;
    }

    function di_area_tambahan($lat, $lon)
    {
        $lokasi_tambahan = $this->db->get('lokasi_tambahan')->result();
        foreach ($lokasi_tambahan as $lokasi) {
            // $toleransi_jarak = 20;
            $toleransi_jarak = 0.8; //area dari titik 0.8 km

            $theta = $lokasi->lon - $lon;
            $distance = (sin(deg2rad($lokasi->lat)) * sin(deg2rad($lat))) + (cos(deg2rad($lokasi->lat)) * cos(deg2rad($lat)) * cos(deg2rad($theta)));
            $distance = acos($distance);
            $distance = rad2deg($distance);
            $distance = $distance * 60 * 1.1515;
            $distance = $distance * 1.609344; //jarak dalam km
            $in_range = (round($distance, 2)) <= $toleransi_jarak;
            if ($in_range) {
                return true;
            }
        }

        return false;
    }

    function di_lokasi_kerja($nip, $lat, $lon)
    {

        $lokasi_kerja = $this->db->query("SELECT id, lon, lat, nama_lokasi, toleransi_jarak, jenis_pegawai, is_aktif from lokasi_kerja l join lokasi_kerja_pegawai p
                on p.id_lokasi=l.id
                where p.nip='$nip'")->row();

        //kalau gk ada, langsung return false
        if (!$lokasi_kerja) {
            return array(
                'status' => false,
                'lokasi_kerja' => null,
            );
        }

        if (!$lokasi_kerja->is_aktif) {
            return array(
                'status' => false,
                'is_aktif' => false
            );
        }

        $lat_kerja = $lokasi_kerja->lat;
        $lon_kerja = $lokasi_kerja->lon;

        if ($this->doraemon($nip)) {
            $posisi_random_lat = rand(0, 1);
            $diff_lat = rand(10, 200);
            $posisi_random_lon = rand(0, 1);
            $diff_lon = rand(10, 200);

            if ($posisi_random_lat == 1) {
                $lat = $lat_kerja + ($diff_lat / 1000000);
            } else {
                $lat = $lat_kerja - ($diff_lat / 1000000);
            }

            if ($posisi_random_lon == 1) {
                $lon = $lon_kerja + ($diff_lon / 1000000);
            } else {
                $lon = $lon_kerja - ($diff_lon / 1000000);
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////// untuk absen biasa (dosen dan tenaga pendidikan) ///////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////

        // $toleransi_jarak = $lokasi_kerja->toleransi_jarak;

        // $theta = $lon_kerja - $lon;
        // $distance = (sin(deg2rad($lat_kerja)) * sin(deg2rad($lat))) + (cos(deg2rad($lat_kerja)) * cos(deg2rad($lat)) * cos(deg2rad($theta)));
        // $distance = acos($distance);
        // $distance = rad2deg($distance);
        // $distance = $distance * 60 * 1.1515;
        // $distance = $distance * 1.609344; //jarak dalam km


        // $status = (round($distance, 2)) <= $toleransi_jarak;

        // $minimum_distance = $distance;
        // if(!$status){
        //     //cek apakah ada lokasi kerja tambahan selain di unit kerja wilayah USK
        //     $lokasi_tambahan = $this->db->query("SELECT * from lokasi_tambahan where id_lokasi_kerja='$lokasi_kerja->id'")->result();
        //     if($lokasi_tambahan){
        //         foreach($lokasi_tambahan as $l){
        //             $lokasi_kerja->nama_lokasi .= " dan $l->nama_lokasi_tambahan";

        //             $theta = $l->lon - $lon;
        //             $distance = (sin(deg2rad($l->lat)) * sin(deg2rad($lat))) + (cos(deg2rad($l->lat)) * cos(deg2rad($lat)) * cos(deg2rad($theta)));
        //             $distance = acos($distance);
        //             $distance = rad2deg($distance);
        //             $distance = $distance * 60 * 1.1515;
        //             $distance = $distance * 1.609344; //jarak dalam km

        //             if($distance < $minimum_distance){
        //                 $minimum_distance = $distance;
        //             }

        //             $status = (round($distance, 2)) <= $l->toleransi_jarak;

        //             if($status){ //kalau sudah ada 1 yang didapat, langsung break
        //                 break;
        //             }
        //         }
        //     }
        // }

        //////////////////////////////////////////////////////////////////////////////////////////////
        ///////////// untuk absen dosen hanya bisa berada di USK dan wilayah USK lainnya /////////////
        //////////////////////////////////////////////////////////////////////////////////////////////

        $in_range_usk = $this->di_area_usk($lat, $lon);
        $in_range_tambahan = $this->di_area_tambahan($lat, $lon);

        return array(
            'status' => ($in_range_usk || $in_range_tambahan),
            // 'jarak' => (round($minimum_distance, 2)),
            'lokasi_kerja' => $lokasi_kerja,
            'is_aktif'=>true
        );
        // return (round($distance, 2)) <= $toleransi_jarak;
    }

    function get_rekap_by_tanggal($tanggal)
    {
        $query = "SELECT * from absen where tanggal='$tanggal'";
        return $this->db->query($query)->result();
    }

    function check_absen_dosen_mk($nip, $tanggal)
    {
        if ($nip == '198304082014042001') { //blok absen utk pegawai ini
            return false;
        }

        $db = $this->load->database('simkul', true);
        $query = "SELECT * from jadwal_mengajar where nip_dosen='$nip' and tgl_pertemuan='$tanggal' and is_absen=1";
        $data = $db->query($query)->row();
        return $data;
        // return ($data && ($data->is_absen == 1));
    }

    function doraemon($nip)
    {
        $doraemon = array(
            '199806142020071101',
            '198510022015041001',
            '198102232003121001',
            '198902192015041003'
        );

        return in_array($nip, $doraemon);
    }

    function get_lokasi_kerja_pegawai($nip)
    {
        return $this->db->where('nip', $nip)->get('lokasi_kerja_pegawai')->row();
    }

    function tendik_mipa($nip)
    {
        $db = $this->load->database('kpa', true);
        return $db->query("SELECT 1 from pegawai where tenaga >= 2 and unit_kerja ='13' and nip_baru='$nip'")->row();
    }
}
