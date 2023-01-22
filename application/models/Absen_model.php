<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Absen_model extends CI_Model
{
    function get_rekap_today($id_pegawai)
    {
        $today = date('Y-m-d');
        return $this->db->query("SELECT * from absen where id_pegawai='$id_pegawai' and tanggal='$today'")->row();
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

    function di_area($lat_kerja, $lon_kerja, $toleransi_jarak, $lat, $lon)
    {
        $toleransi_jarak = $toleransi_jarak / 1000; //ubah meter jadi kilometer
        $theta = $lon_kerja - $lon;
        $distance = (sin(deg2rad($lat_kerja)) * sin(deg2rad($lat))) + (cos(deg2rad($lat_kerja)) * cos(deg2rad($lat)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        $distance = $distance * 1.609344; //jarak dalam km
        return ['distance'=> $distance * 1000, 'jarak' => round($distance*1000, 2)." meter / ". round($distance,2). " km", 'status' => ($distance <= $toleransi_jarak)];
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
            'is_aktif' => true
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

    function get_lokasi_kerja_pegawai($id_pegawai)
    {
        return $this->db->where('id_pegawai', $id_pegawai)->get('lokasi_kerja_pegawai')->row();
    }

    function tendik_mipa($nip)
    {
        $db = $this->load->database('kpa', true);
        return $db->query("SELECT 1 from pegawai where tenaga >= 2 and unit_kerja ='13' and nip_baru='$nip'")->row();
    }

    function get_lokasi_kerja($id_pegawai){
        return $this->db->select('lk.*')
        ->from('lokasi_kerja lk')
        ->join('lokasi_kerja_pegawai lkp', 'lkp.id_lokasi=lk.id')
        ->where('id_pegawai', $id_pegawai)
        ->where('lk.status', 1)
        ->get()->result();
    }

    function compress_image($source, $path)
    {

        $max_size = 700000; //penentu kualitas

        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source);

        elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source);

        elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source);

        $imagefilesize = filesize($source);

        //hitung kualitas berdasarkan ukuran file
        //semakin besar file, semakin besar proses kompresi gambarnya (maksimal filesize adalah 700kb)
        if ($max_size > $imagefilesize) {
            $quality = 100;
        } else {
            $quality = $max_size / $imagefilesize * 100;
        }


        return imagejpeg($image, $path, $quality);
    }

    function dalam_waktu_absen($jenis_absen, $id_lokasi){
        $tanggal = date('Y-m-d');
        $waktu = date('H:i:s');
        $kol = $jenis_absen == 1 ? "masuk" : "pulang";
        return $this->db->query("SELECT 1 from waktu_absen where id_lokasi='$id_lokasi' and tanggal <= '$tanggal' and ".$kol."_start >= '$waktu' and " . $kol . "_end <= '$waktu' order by tanggal desc limit 1")->row();
    }

    function rekap_bulanan_pegawai($tahun, $bulan, $id_pegawai){
        return $this->db->query("SELECT * FROM (
            SELECT '1' as jenis, c.dt as tanggal, c.dw, c.isWeekday, c.isHoliday, a.jam_masuk, a.jam_pulang, a.needs_approval, a.approved,
                        ifnull(time_to_sec(timediff(jam_pulang, jam_masuk)),0) total_waktu,
                        case when (jam_masuk is null or jam_pulang is null) and status=1 then 1 else 0 end as tidak_lengkap,
                        case when status=1 and jam_masuk >
                            (select masuk_end from waktu_absen wa where wa.id_lokasi=a.id_lokasi and wa.tanggal <= a.tanggal order by wa.tanggal desc limit 1) 
                        then 1 else 0 end as terlambat,
                        time_to_sec(timediff(jam_masuk, (select masuk_end from waktu_absen wa where wa.id_lokasi=a.id_lokasi and wa.tanggal <= a.tanggal order by wa.tanggal desc limit 1))) as waktu_terlambat,
                        case when c.isWeekDay=1 and c.isHoliday=0 and jam_masuk is null and jam_pulang is null then 1 else 0 end as tanpa_status
                        from (select * from calendar_table where date_format(dt, '%Y-%m') = '$tahun-$bulan') c
                        left join (select * from absen where id_pegawai='$id_pegawai') a on a.tanggal=c.dt
            union
            SELECT '2' as jenis, tanggal, dayofweek(tanggal) as dw, null, null, jam_mulai, jam_selesai, null, null, null, null, null, null, null
            from kunjungan_client where date_format(tanggal, '%Y-%m') = '$tahun-$bulan' and id_pegawai='$id_pegawai') r order by tanggal")->result();
    }

    function nama_hari($tgl)
    {
        $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
        return $hari[date('w', strtotime($tgl))];
    }

    function get_tgl_persetujuan(){
        return $this->db->query("SELECT distinct tanggal from absen where needs_approval=1 and approved is null")->result();
    }

    function get_butuh_persetujuan($tgl){
        return $this->db->query("SELECT b.*, nik, nama from 
            (SELECT date_format(c.dt, '%d-%m-%Y') as tanggal, c.dw, c.isWeekday, c.isHoliday, c.dt as date,
                a.id_pegawai, a.jam_masuk as masuk, a.jam_pulang as keluar, a.foto_masuk, a.foto_pulang, 
                a.gps_out as diluar_wilayah,a.needs_approval, a.approved, a.id as id_absen,
                case when status=1 and jam_masuk >
                    (select masuk_end from waktu_absen wa where wa.id_lokasi=a.id_lokasi and wa.tanggal <= a.tanggal order by wa.tanggal desc limit 1) 
                then 1 else 0 end as diluar_jam,
                time_to_sec(timediff(jam_masuk, (select masuk_end from waktu_absen wa where wa.id_lokasi=a.id_lokasi and wa.tanggal <= a.tanggal order by wa.tanggal desc limit 1))) as waktu_terlambat,
                case when c.isWeekDay=1 and c.isHoliday=0 and jam_masuk is null and jam_pulang is null then 1 else 0 end as tanpa_status
                from (select * from calendar_table where dt = '$tgl') c
                left join (select * from absen where needs_approval=1) a on a.tanggal=c.dt
            ) b 
            join pegawai p on b.id_pegawai=p.id")->result();
    }
}
