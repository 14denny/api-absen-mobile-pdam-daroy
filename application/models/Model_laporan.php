<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Model_laporan extends CI_Model
{
    function tempat_tidur($year)
    {

        $hasil = $this->db->query("	select yt.nama,sum(yt.vvip) as vvip,sum(yt.vip) as vip,sum(yt.i) as i,
        sum(yt.ii) as ii,sum(yt.iii) as iii,sum(yt.non) as non from(
                select p.nama,
                                case when kelas = 'VVIP' then jumlah end as vvip,
                                case when kelas = 'VIP' then jumlah end as vip,
                                case when kelas = 'I' then jumlah end as i,
                                case when kelas = 'II' then jumlah end as ii,
                                case when kelas = 'III' then jumlah end as iii,
                                case when kelas in ('-','3') then jumlah end as non
                                from 
                                (
                                                select y.kelas,y.nama,sum(y.jum_tmp_tdr) as jumlah from (
                                                                select r.*,k.kelas,k.id as kode_kelas,km.jum_tmp_tdr from ruang_rawat r 
                                                                inner join kelas k 
                                                                on r.kode=k.kode_ruang 
                                                                inner join 
                                                                kamar km 
                                                                on km.kelas=k.id) y group by 1,2 order by kelas
                                ) p
        ) yt GROUP BY yt.nama");
        return $hasil->result();
    }

    function ketenagaan($year)
    {
        $hasil = $this->db->query("SELECT ww.kelompok_tenaga,ww.kategori_tenaga,ww.nama,
        case when jenis_kelamin = '1' then jumlah end as jumlah_laki,
        case when jenis_kelamin = '2' then jumlah end as jumlah_perempuan,
        ww.jumlah_butuh_laki,
        ww.jumlah_butuh_perempuan,
        ww.jumlah_kurang_laki,
        ww.jumlah_kurang_perempuan
        from 
        (
                select w.*,kp.jumlah_butuh_laki,kp.jumlah_butuh_perempuan,
                kp.jumlah_kurang_laki,
                kp.jumlah_kurang_perempuan from (
                        select y.id_kelompok_tenaga,y.id_kategori_tenaga,y.id,y.kelompok_tenaga,y.kategori_tenaga,y.nama,y.jenis_kelamin,			count(nama_dokter) as jumlah  from 
                        (
                                select k.id,k.nama, (select nama from kategori_tenaga 
                                where id=k.id_kategori_tenaga) as kategori_tenaga,
                                (select nama from kelompok_tenaga 
                                where id=k.id_kelompok_tenaga) as kelompok_tenaga,
                                k.id_kelompok_tenaga,
                                k.id_kategori_tenaga,
                                d.jenis_kelamin,
                                d.nama as nama_dokter
                                from kualifikasi_pendidikan k 
                                left join 
                                dokter d 
                                on k.id=d.id_pendidikan order by k.id_kelompok_tenaga ASC, k.id_kategori_tenaga ASC, k.id
                                ) y  group by 1,2,3,4,5,6,7) w
                inner join 
                kualifikasi_pendidikan kp 
                on w.id=kp.id
        ) ww order by ww.kelompok_tenaga
        ");
        return $hasil->result();
    }
    function rl_rawat_inap($year)
    {
        $hasil = $this->db->query("select jm.kode,jm.nama,jm.jumlah_pasien as jumlah_pasien_masuk,
        jma.jumlah_pasien as jumlah_pasien_awal_tahun,
        jmat.jumlah_pasien as jumlah_pasien_akhir_tahun,
        jmak.jumlah_pasien as jumlah_pasien_keluar,
        jmkt.jumlah_pasien as jumlah_pasien_kurang_delapan_jam,
        jmlt.jumlah_pasien as jumlah_pasien_lebih_delapan_jam,
        jll.jumlah_hari as jumlah_lama_dirawat,
        jllk.vvip,
        jllk.vip,
        jllk.i,
        jllk.ii,
        jllk.iii,
        jllk.non
        from(
                select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
                left join 
                (select *from kunjungan_pasien where status_kunjungan='2' 
                and year(tanggal_masuk)='$year') kp 
                on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jm
        inner join		
        /*jumlah masuk awal tahun*/
                (select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
                left join 
                (select *from kunjungan_pasien where status_kunjungan='2' 
                and month(tanggal_masuk)='1' and year(tanggal_masuk)='$year') kp 
                on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jma
        on jm.kode=jma.kode		
        inner join
        /*jumlah masuk akhir tahun*/
                (select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
                left join 
                (select *from kunjungan_pasien where status_kunjungan='2' 
                and month(tanggal_masuk)='12' and year(tanggal_masuk)='$year') kp 
                on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jmat 
        on jm.kode=jmat.kode		
        inner join	
        /*jumlah keluar*/
            (select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
            left join 
            (select *from kunjungan_pasien where status_kunjungan='2' 
            and year(tanggal_masuk)='$year' and cara_pulang!='') kp 
            on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jmak
        on jm.kode=jmak.kode		
        inner join	
        /*jumlah meninggal <48 jam tahun*/
                (select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
                left join 
                (select *from kunjungan_pasien where status_kunjungan='2' 
                and year(tanggal_masuk)='$year' and cara_pulang='4' and pasien_keluar_meninggal='1') kp 
                on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jmkt
        on jm.kode=jmkt.kode			
        inner join
            /*jumlah meninggal >48 jam tahun*/
            (select r.*,COUNT(kp.nomor_mr) as jumlah_pasien from ruang_rawat r 
            left join 
            (select *from kunjungan_pasien where status_kunjungan='2' 
            and year(tanggal_masuk)='$year' and cara_pulang='4' and pasien_keluar_meninggal='2') kp 
            on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jmlt
        on 	jm.kode=jmlt.kode
        inner join
                /*jumlah lama di rawat*/
                (select r.*,kp.jumlah_hari from ruang_rawat r 
                left join 
                (select m.ruang_rawat, max(m.jumlah_hari) as jumlah_hari from (
                        select ruang_rawat, date(tanggal_masuk) as tanggal_masuk, date(tanggal_pulang) as tanggal_keluar,
                        DATEDIFF(date(tanggal_pulang),date(tanggal_masuk)) as jumlah_hari
                        from kunjungan_pasien where    status_kunjungan='2' 
                        and year(tanggal_masuk)='$year' and cara_pulang!='') m group by ruang_rawat) kp 
                on r.kode=kp.ruang_rawat group by r.kode,nama,koderuang) jll 
            on 	jm.kode=jll.kode
      inner join
            /* jumlah hari perawatan berdasarkan kelas*/
                    (select yttt.kode,yttt.nama
                    ,sum(yttt.vvip) as vvip,sum(yttt.vip) as vip,sum(yttt.i) as i,
                        sum(yttt.ii) as ii,sum(yttt.iii) as iii,sum(yttt.non) as non
                        from
                        (
                                select ytt.kode,ytt.nama,
                                        case when kelas = 'VVIP' then jumlah_hari end as vvip,
                                            case when kelas = 'VIP' then jumlah_hari end as vip,
                                            case when kelas = 'I' then jumlah_hari end as i,
                                            case when kelas = 'II' then jumlah_hari end as ii,
                                            case when kelas = 'III' then jumlah_hari end as iii,
                                            case when kelas in ('-','3') then jumlah_hari end as non
                                            from
                                        (select yt.*,kp.jumlah_hari from (select r.*,k.kelas,k.id as kode_kelas from ruang_rawat r 
                                            inner join kelas k 
                                            on r.kode=k.kode_ruang) yt
                                            left join 
                                            (
                                            select m.ruang_rawat,m.kelas_rawat, sum(m.jumlah_hari) as jumlah_hari from (
                                                    select ruang_rawat,kelas_rawat, date(tanggal_masuk) as tanggal_masuk, date(tanggal_pulang) as 												tanggal_keluar,
                                                    DATEDIFF(date(tanggal_pulang),date(tanggal_masuk)) as jumlah_hari
                                                    from kunjungan_pasien where    status_kunjungan='2' 
                                                    and year(tanggal_masuk)='$year' and cara_pulang!='') m group by ruang_rawat,kelas_rawat)
                                            kp 
                                            on yt.kode=kp.ruang_rawat and yt.kode_kelas=kp.kelas_rawat) ytt) 
                                            yttt group by yttt.kode,yttt.nama) jllk
            on 	jm.kode=jllk.kode");
        return $hasil->result();
    }
    function rl_igd($year)
    {
        $hasil = $this->db->query("select a.jumlah_pasien_rujukan,b.jumlah_pasien_non_rujukan,c.jumlah_pasien_dirawat,
        d.jumlah_pasien_dirujuk,e.jumlah_pasien_pulang,f.jumlah_pasien_meninggal
        from (
            /* rujukan */
                select COUNT(nomor_mr) as jumlah_pasien_rujukan from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk='1') a,
            /* non rujukan */
                (select COUNT(nomor_mr) as jumlah_pasien_non_rujukan from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk='2') b,
            /* dirawat */
                (select COUNT(nomor_mr) as jumlah_pasien_dirawat from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk in (1,2) and status_pulang='0') c,
            /* dirujuk */
                (select COUNT(nomor_mr) as jumlah_pasien_dirujuk from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk in (1,2) and status_pulang='8') d,
            /* pulang */
                (select COUNT(nomor_mr) as jumlah_pasien_pulang from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk in (1,2) and status_pulang='3') e,
            /* meninggal */	
            (select COUNT(nomor_mr) as jumlah_pasien_meninggal from 
                kunjungan_pasien where status_kunjungan='3' and year(tanggal_masuk)='$year' 
                and cara_masuk in (1,2) and status_pulang in (11,12)) f");
        return $hasil->result();
    }
    function rl_gigi($year)
    {
        $hasil = $this->db->query("select a.tindakan_medis,count(a.tindakan_medis) as jumlah_tindakan
        from(
                select kp.*,ct.tindakan_medis from kunjungan_pasien kp 
                inner join
                catatan_tindakan ct
                on kp.nomor_mr=ct.nomor_mr
                and kp.nomor_transaksi=ct.nomor_transaksi
                where kp.status_kunjungan='1' 
                and kp.poli_tujuan='8'  and substring(ct.tindakan_medis,1,3) in 
                (520,521,522,523,524,525,526,527,528,529)
                and year(kp.tanggal_masuk)='$year'
                ) a GROUP BY a.tindakan_medis");
        return $hasil->result();
    }

    function rl_kebidanan($year)
    {
        $hasil = $this->db->query("select aa.tindakan_medis,aa.jumlah_rujukan_hidup,bb.jumlah_rujukan_meninggal,
        cc.jumlah_nonrujukan_hidup,dd.jumlah_nonrujukan_meninggal
                from 
                    (select a.tindakan_medis, COUNT(a.tindakan_medis) as jumlah_rujukan_hidup
                    from (
                            select kp.*,ct.tindakan_medis from (
                                    select *from kunjungan_pasien
                                    where status_kunjungan='2' 
                                    and year(tanggal_masuk)='$year' and cara_masuk='1' and status_pulang='0') kp 
                            inner join 		
                            catatan_tindakan ct
                                        on kp.nomor_mr=ct.nomor_mr
                                        and kp.nomor_transaksi=ct.nomor_transaksi
                            where	substring(ct.tindakan_medis,1,3) in (630,631,632,633,634,635,636,637,638,639,640,641,642,643,644
                            ,645,646,647,648,649,650,651,652,653,654,655,656,657,658,659,660,661,
                            662,663,664,665,666,667,668,669,670)) a group by a.tindakan_medis) aa
                inner join 			
                    /*rujukan meninggal*/
                    (select a.tindakan_medis, COUNT(a.tindakan_medis) as jumlah_rujukan_meninggal
                    from (
                            select kp.*,ct.tindakan_medis from (
                                    select *from kunjungan_pasien
                                    where status_kunjungan='2' 
                                    and year(tanggal_masuk)='$year' and cara_masuk='1' and status_pulang in (11,12)) kp 
                            inner join 		
                            catatan_tindakan ct
                                        on kp.nomor_mr=ct.nomor_mr
                                        and kp.nomor_transaksi=ct.nomor_transaksi
                            where	substring(ct.tindakan_medis,1,3) in (630,631,632,633,634,635,636,637,638,639,640,641,642,643,644
                            ,645,646,647,648,649,650,651,652,653,654,655,656,657,658,659,660,661,
                            662,663,664,665,666,667,668,669,670)) a group by a.tindakan_medis) bb 
                    on aa.tindakan_medis=bb.tindakan_medis		
                inner join			
                            /*non rujukan hidup*/
                    (select a.tindakan_medis, COUNT(a.tindakan_medis) as jumlah_nonrujukan_hidup
                    from (
                            select kp.*,ct.tindakan_medis from (
                                    select *from kunjungan_pasien
                                    where status_kunjungan='2' 
                                    and year(tanggal_masuk)='$year' and cara_masuk='2' and status_pulang='0') kp 
                            inner join 		
                            catatan_tindakan ct
                                        on kp.nomor_mr=ct.nomor_mr
                                        and kp.nomor_transaksi=ct.nomor_transaksi
                            where	substring(ct.tindakan_medis,1,3) in (630,631,632,633,634,635,636,637,638,639,640,641,642,643,644
                            ,645,646,647,648,649,650,651,652,653,654,655,656,657,658,659,660,661,
                            662,663,664,665,666,667,668,669,670)) a group by a.tindakan_medis)
                    cc on aa.tindakan_medis=cc.tindakan_medis
                    /*non rujukan meninggal*/
            inner join		
                    (select a.tindakan_medis, COUNT(a.tindakan_medis) as jumlah_nonrujukan_meninggal
                    from (
                            select kp.*,ct.tindakan_medis from (
                                    select *from kunjungan_pasien
                                    where status_kunjungan='2' 
                                    and year(tanggal_masuk)='$year' and cara_masuk='2' and status_pulang in (11,12)) kp 
                            inner join 		
                            catatan_tindakan ct
                                        on kp.nomor_mr=ct.nomor_mr
                                        and kp.nomor_transaksi=ct.nomor_transaksi
                            where	substring(ct.tindakan_medis,1,3) in (630,631,632,633,634,635,636,637,638,639,640,641,642,643,644
                            ,645,646,647,648,649,650,651,652,653,654,655,656,657,658,659,660,661,
                            662,663,664,665,666,667,668,669,670)) a group by a.tindakan_medis) dd 
                on aa.tindakan_medis=dd.tindakan_medis");
        return $hasil->result();
    }
    function rl_bedah($year)
    {
        $hasil = $this->db->query("select a.tindakan_medis,count(a.tindakan_medis) as jumlah_tindakan
        from(
                select kp.*,ct.tindakan_medis from kunjungan_pasien kp 
                inner join
                catatan_tindakan ct
                on kp.nomor_mr=ct.nomor_mr
                and kp.nomor_transaksi=ct.nomor_transaksi
                where kp.status_kunjungan in ('1','2') 
                and substring(ct.tindakan_medis,1,3) in 
                ('E878')
                and year(kp.tanggal_masuk)='$year'
                ) a GROUP BY a.tindakan_medis");
        return $hasil->result();
    }
    function rl_radiologi($year)
    {
        $hasil = $this->db->query("select y.produk_radiologi,y.nama_radiologi,count(y.produk_radiologi) as jumlah 
        from (
                select cr.produk_radiologi,(select nama_radiologi from jenis_radiologi 
                where id=cr.produk_radiologi) as nama_radiologi,(select id_kategori from jenis_radiologi 
                where id=cr.produk_radiologi) as id_kategori	from kunjungan_pasien kp 
                inner join catatan_radiologi cr 
                on cr.nomor_mr=kp.nomor_mr and kp.nomor_transaksi=cr.nomor_transaksi 
                where year(kp.tanggal_masuk)='$year'
                ) y where id_kategori!='10' group by 1,2");
        return $hasil->result();
    }
    function rl_fisioterapi($year)
    {
        $hasil = $this->db->query("select y.produk_fisioterapi,y.nama_fisioterapi,count(y.produk_fisioterapi) as jumlah 
        from (
                select cr.produk_fisioterapi,(select nama_fisioterapi from jenis_fisioterapi 
                where id=cr.produk_fisioterapi) as nama_fisioterapi,(select id_kategori from jenis_fisioterapi 
                where id=cr.produk_fisioterapi) as id_kategori	from kunjungan_pasien kp 
                inner join catatan_fisioterapi cr 
                on cr.nomor_mr=kp.nomor_mr and kp.nomor_transaksi=cr.nomor_transaksi 
                where year(kp.tanggal_masuk)='$year'
                ) y where id_kategori!='10' group by 1,2");
        return $hasil->result();
    }
    function rl_pelayanan_khusus($year)
    {
        $hasil = $this->db->query("select y.produk_radiologi,y.nama_radiologi,count(y.produk_radiologi) as jumlah 
        from (
                select cr.produk_radiologi,(select nama_radiologi from jenis_radiologi 
                where id=cr.produk_radiologi) as nama_radiologi,(select id_kategori from jenis_radiologi 
                where id=cr.produk_radiologi) as id_kategori	from kunjungan_pasien kp 
                inner join catatan_radiologi cr 
                on cr.nomor_mr=kp.nomor_mr and kp.nomor_transaksi=cr.nomor_transaksi 
                where year(kp.tanggal_masuk)='$year'
                ) y where id_kategori='10' group by 1,2");
        return $hasil->result();
    }
    function rl_kejiwaan($year)
    {
        $hasil = $this->db->query("select 'konsultasi' as konsultasi, count(nomor_mr) as jumlah_kunjungan 
        from kunjungan_pasien where 
        status_kunjungan='1' and poli_tujuan='13' and year(tanggal_masuk)='$year'
        ");
        return $hasil->row();
    }
    function rl_pengadaan_obat($year)
    {
        $hasil = $this->db->query("select kk.*,gp.jumlah as jumlah_pengadaan,gs.jumlah as gs_stok_rs from kategori_barang kk
        left join
            (select y.kd_kategoribarang,
                        (select nama from kategori_barang where kd_jenis=y.kd_kategoribarang) as nama_jenis
                        ,sum(y.jumlah) as jumlah 
                        from 
                        (
                                select pg.kd_barang,b.nama_barang,pg.jumlah,b.kd_kategoribarang from persediaan_gudang pg 
                                inner join 
                                barang b 
                                on pg.kd_barang=b.kd_barang where kd_jenisbarang='2'
                        ) y)
                gp on kk.kd_jenis=gp.kd_kategoribarang
                left join 
                    (select y.kd_kategoribarang,
                    (select nama from kategori_barang where kd_jenis=y.kd_kategoribarang) as nama_jenis
                    ,sum(y.jumlah) as jumlah 
                    from 
                    (
                            select pg.kd_barang,b.nama_barang,pg.stok as jumlah,b.kd_kategoribarang from stok_persediaan pg 
                            inner join 
                            barang b 
                            on pg.kd_barang=b.kd_barang where kd_jenisbarang='2'
                    ) y)
                    gs on kk.kd_jenis=gs.kd_kategoribarang");
        return $hasil->result();
    }
    function rl_apotik_obat($year)
    {
        $sql = $this->db->query("select kk.*,gp.rawat_jalan,gp.rawat_inap,gp.igd from kategori_barang kk
        left join			
                    (SELECT z.kd_kategoribarang,	
                max(case when z.nama_unit = 'Rawat Jalan' then jumlah else 0 end) rawat_jalan,
                max(case when z.nama_unit = 'Rawat Inap' then jumlah else 0 end) rawat_inap,
                max(case when z.nama_unit = 'IGD' then jumlah else 0 end) igd,
                max(case when z.nama_unit = 'UMUM' then jumlah else 0 end) umum,
                max(case when z.nama_unit = 'Non Resep' then jumlah else 0 end) non_resep
                 from (
                    select t.nama_unit,t.nama_barang,sum(t.jumlah) as jumlah,t.kd_kategoribarang  from (
                    select  tp.tgl_input,tp.kd_unit_apotik,u.keterangan as nama_unit,dt.kd_barang,b.nama_barang,
                                b.kd_kategoribarang,
                                dt.jumlah,		DATE_FORMAT(tp.tgl_input, '%d-%m-%Y') as tanggal
                    from transaksi_pasien_apotik tp 
                    inner join detail_transaksi_pasien_apotik dt 
                    on tp.kd_transaksi_pasien=dt.kd_transaksi_pasien
                    inner join unit_apotik u  on tp.kd_unit_apotik=u.id
                    inner join barang b on dt.kd_barang=b.kd_barang
                    where tp.flag_close_transaksi='1' and tp.kd_unit_farmasi in (1,14,15)) 
                    t where year(t.tgl_input)='$year' GROUP BY kd_kategoribarang
                            ) z group by z.kd_kategoribarang) gp 
                on kk.kd_jenis=gp.kd_kategoribarang							
                    ");
        return $sql->result();
    }
    function rl_rujukan($year)
    {
        $sql = $this->db->query("/* rujukan asuransi*/
        select p.*,ps.jumlah_rujukan_pukesmas,pk.jumlah_rujukan_pukesmas_kembali,
        pr.jumlah_rujukan_rs, prk.jumlah_rujukan_rs_kembali,pd.jumlah_dirujuk
             from poli p left join  (
                /*faskes 1 diterima*/
                select  poli_tujuan,COUNT(nomor_mr) as jumlah_rujukan_pukesmas from kunjungan_pasien where status_kunjungan='1' 
                and jaminan='4' and asal_rujukan_bpjs='1' group by poli_tujuan)
            ps	
             on p.id=ps.poli_tujuan
                /*faskes 1 dikembalikan*/
                    left join (select  poli_tujuan,COUNT(nomor_mr) as jumlah_rujukan_pukesmas_kembali from kunjungan_pasien where status_kunjungan='1' 
                and jaminan='4' and asal_rujukan_bpjs='1' and status_pulang='1' group by poli_tujuan)
            pk	 
             on p.id=pk.poli_tujuan
                /*faskes 2*/
            left join (		
                select  poli_tujuan,COUNT(nomor_mr) as jumlah_rujukan_rs from kunjungan_pasien where status_kunjungan='1' 
                and jaminan='4' and asal_rujukan_bpjs='2' group by poli_tujuan) pr
         on p.id=pr.poli_tujuan
                /*faskes 2 dikembalikan*/
        left join (			
                select  poli_tujuan,COUNT(nomor_mr) as jumlah_rujukan_rs_kembali from kunjungan_pasien where status_kunjungan='1' 
                and jaminan='4' and asal_rujukan_bpjs='2' and status_pulang='1' group by poli_tujuan) prk
         on p.id=prk.poli_tujuan
        /* dirujuk*/
        left join (		
                select  poli_tujuan,COUNT(nomor_mr) as jumlah_dirujuk from kunjungan_pasien where status_kunjungan='1' 
                and jaminan='4' and status_pulang='3' group by poli_tujuan) pd 
        on p.id=pd.poli_tujuan");
        return $sql->result();
    }
    function rl_cara_bayar($year)
    {
        $sql = $this->db->query("/*cara bayar*/
        select cm.*,
        pri.jumlah_pasien as jumlah_pasien_inap_keluar,
        jlr.jumlah_hari as jumlah_lama_inap_dirawat,
        jpr.jumlah_pasien as jumlah_pasien_rawat_jalan,
        jpl.jumlah_pasien_lab as jumlah_pasien_lab,
        jpf.jumlah_pasien_fisio as jumlah_pasien_fisio,
        jprd.jumlah_pasien_radio jumlah_pasien_radio
        from cara_masuk cm 
        left join (
                        /*jumlah pasien keluar*/
                        select cara_masuk,count(nomor_mr) as jumlah_pasien from kunjungan_pasien where status_kunjungan='2' 
                        and cara_pulang!='' and year(tanggal_masuk)='$year' group by cara_masuk) pri 
        on cm.id=pri.cara_masuk		
        left join 
                        /*jumlah lama di rawat*/
                        (select y.cara_masuk, max(y.jumlah_hari) as jumlah_hari from
                                        (select cara_masuk,
                                        DATEDIFF(date(tanggal_pulang),date(tanggal_masuk)) as jumlah_hari from kunjungan_pasien where 
                                        status_kunjungan='2' and year(tanggal_masuk)='$year' and cara_pulang!='') y 
                        group by y.cara_masuk) jlr 
        on cm.id=jlr.cara_masuk	
        left join 
                        /*jumlah pasien rawat jalan*/
                        (select cara_masuk,count(nomor_mr) as jumlah_pasien 
                        from kunjungan_pasien where status_kunjungan='1' and year(tanggal_masuk)='$year'
                        group by cara_masuk) jpr 
        on cm.id=jpr.cara_masuk		
        left join		
                        /*jumlah pasien laboratorium rawat jalan*/
                        (select a.cara_masuk,count(a.nomor_mr) as jumlah_pasien_lab from (
                                        select kp.* from kunjungan_pasien kp 
                                        inner join 
                                        catatan_laboratorium cl 
                                        on kp.nomor_mr=cl.nomor_mr where status_kunjungan='1' and year(tanggal_masuk)='$year') a 
                        group by a.cara_masuk) jpl
        on cm.id=jpl.cara_masuk			
        left join		
                        /*jumlah pasien fisioterapi rawat jalan*/
                        (select a.cara_masuk,count(a.nomor_mr) as jumlah_pasien_fisio from (
                                        select kp.* from kunjungan_pasien kp 
                                        inner join 
                                        catatan_fisioterapi cl 
                                        on kp.nomor_mr=cl.nomor_mr where status_kunjungan='1' and year(tanggal_masuk)='$year') a 
                        group by a.cara_masuk) jpf
        on cm.id=jpf.cara_masuk		
        left join		
                        /*jumlah pasien radiologi rawat jalan*/
                        (select a.cara_masuk,count(a.nomor_mr) as jumlah_pasien_radio from (
                                        select kp.* from kunjungan_pasien kp 
                                        inner join 
                                        catatan_radiologi cl 
                                        on kp.nomor_mr=cl.nomor_mr where status_kunjungan='1' and year(tanggal_masuk)='$year') a 
                        group by a.cara_masuk) jprd 
        on cm.id=jprd.cara_masuk");
        return $sql->result();
    }

    function rl_pengunjung_baru($year)
    {
        $sql = $this->db->query("
        /* pengunjung baru */
                select sum(t.jumlah_pengunjung) as jumlah_pengunjung_pertama
                from
                    (select jpp.* from (
                        select k.nomor_mr, count(k.nomor_mr) as jumlah_pengunjung from pasien_baru p 
                        inner join kunjungan_pasien k 
                        on p.nomor_mr=k.nomor_mr and year(tanggal_masuk)='$year' group by k.nomor_mr)
                         jpp where jpp.jumlah_pengunjung='1') t");
        return intval($sql->row()->jumlah_pengunjung_pertama);
    }
    function rl_pengunjung_lama($year)
    {
        $sql = $this->db->query("
        /* pengujung lama*/
		select sum(t.jumlah_pengunjung) as jumlah_pengunjung_lama from
			(select jpp.* from (
				select k.nomor_mr, count(k.nomor_mr) as jumlah_pengunjung from pasien_baru p 
				inner join kunjungan_pasien k 
                on p.nomor_mr=k.nomor_mr and year(tanggal_masuk)='$year' group by k.nomor_mr) 
                jpp where jpp.jumlah_pengunjung>'1') t ");
        return intval($sql->row()->jumlah_pengunjung_lama);
    }
    function rl_laporan_rawat_jalan($year)
    {
        $sql = $this->db->query("select p.id,p.keterangan as jenis_kegiatan,k.jumlah as jumlah_pasien from poli p
        left join
            (select poli_tujuan, count(nomor_mr) as jumlah from 
            kunjungan_pasien where status_kunjungan='1' and year(tanggal_masuk)='$year' 
            group by poli_tujuan) k 
        on p.id=k.poli_tujuan");
        return $sql->result();
    }
    function rl_sepuluh_besar_rawat_inap($year)
    {
        $sql = $this->db->query("select SUBSTRING_INDEX(www.diagnosa_masuk,'-',1) as kode_diagnosa,
		SUBSTRING_INDEX(www.diagnosa_masuk,'-',-1) as nama_diagnosa,
www.jumlah_hidup_laki as jumlah_keluar_hidup_laki,
www.jumlah_hidup_perempuan as jumlah_keluar_hidup_perempuan,
yyy.jumlah_hidup_laki_mati as jumlah_keluar_mati_laki,
yyy.jumlah_hidup_perempuan_mati as jumlah_keluar_mati_perempuan
from (
		select ww.diagnosa_masuk, 
		sum(ww.jumlah_hidup_laki) as jumlah_hidup_laki,sum(ww.jumlah_hidup_perempuan) as jumlah_hidup_perempuan
		from 
					(select w.diagnosa_masuk,
					case when w.jenis_kelamin = '1' then jumlah end as jumlah_hidup_laki,
					case when w.jenis_kelamin = '2' then jumlah end as jumlah_hidup_perempuan
					from (
							select f.diagnosa_masuk, kp.jenis_kelamin, count(kp.jenis_kelamin) as jumlah
							from (
									select k.* from (
											select diagnosa_masuk,count(nomor_mr) as jumlah from 
											kunjungan_pasien where year(tanggal_masuk)='$year' and status_kunjungan='2' group by diagnosa_masuk 
											) k order by k.jumlah DESC limit 10)  f
							inner join 
							(	select k.*, (select jenis_kelamin from pasien_baru where nomor_mr=k.nomor_mr) 
													as jenis_kelamin
													from kunjungan_pasien k where year(tanggal_masuk)='$year' and cara_pulang in (1,3,5)) kp on f.diagnosa_masuk=kp.diagnosa_masuk 
							group by 1,2) w) ww 
				group by 1
) www				
left join 
		(select ww.diagnosa_masuk, 
		sum(ww.jumlah_hidup_laki) as jumlah_hidup_laki_mati, 
		sum(ww.jumlah_hidup_perempuan) as jumlah_hidup_perempuan_mati
		from 
					(select w.diagnosa_masuk,
					case when w.jenis_kelamin = '1' then jumlah end as jumlah_hidup_laki,
					case when w.jenis_kelamin = '2' then jumlah end as jumlah_hidup_perempuan
					from (
							select f.diagnosa_masuk, kp.jenis_kelamin, count(kp.jenis_kelamin) as jumlah
							from (
									select k.* from (
											select diagnosa_masuk,count(nomor_mr) as jumlah from 
											kunjungan_pasien where year(tanggal_masuk)='$year' and status_kunjungan='2' group by 	diagnosa_masuk 
											) k order by k.jumlah DESC limit 10)  f
							inner join 
							(	select k.*, (select jenis_kelamin from pasien_baru where nomor_mr=k.nomor_mr) 
													as jenis_kelamin
													from kunjungan_pasien k where year(tanggal_masuk)='$year' and status_kunjungan='2' and cara_pulang in (4)) kp on f.diagnosa_masuk=kp.diagnosa_masuk 
							group by 1,2) w) ww 
				group by 1) yyy 
on www.diagnosa_masuk=yyy.diagnosa_masuk");
        return $sql->result();
    }
    function rl_sepuluh_besar_rawat_jalan($year)
    {
        $sql = $this->db->query("select SUBSTRING_INDEX(wpy.diagnosa_masuk,'-',1) as kode_diagnosa,
		SUBSTRING_INDEX(wpy.diagnosa_masuk,'-',-1) as nama_diagnosa
		,wpy.jumlah_b_laki as jumlah_kasus_baru_laki,wpy.jumlah_b_perempuan as jumlah_kasus_baru_perempuan,wpk.jumlah_kunjungan from (
		select wkk.diagnosa_masuk,
						sum(wkk.jumlah_b_laki) as jumlah_b_laki,
						sum(wkk.jumlah_b_perempuan) as jumlah_b_perempuan
						from (
											select wyy.diagnosa_masuk,
											case when wyy.jenis_kelamin = '1' then jumlah end as jumlah_b_laki,
											case when wyy.jenis_kelamin = '2' then jumlah end as jumlah_b_perempuan
											from 
											(
														select y.diagnosa_masuk,wal.jenis_kelamin,count(jenis_kelamin) as jumlah from 
														(
																select k.* from (
																select diagnosa_masuk,count(nomor_mr) as jumlah from 
																kunjungan_pasien where year(tanggal_masuk)='$year' and status_kunjungan='1' group by diagnosa_masuk 
																) k order by k.jumlah DESC limit 10
														) y 
														left join 
																(
														select jpp.jenis_kelamin, kkp.diagnosa_masuk from (
																select jp.nomor_mr,(select jenis_kelamin from pasien_baru where nomor_mr=jp.nomor_mr) 
																											as jenis_kelamin from (
																		select p.nomor_mr,count(p.nomor_mr) as jumlah_hit from pasien_baru p 
																			inner join 
																		kunjungan_pasien k on p.nomor_mr=k.nomor_mr group by 1) jp where jp.jumlah_hit='1') jpp 
														inner join 
														kunjungan_pasien kkp  on jpp.nomor_mr=kkp.nomor_mr where year(kkp.tanggal_masuk)='$year' 
														and kkp.status_kunjungan='1') wal
														on y.diagnosa_masuk=wal.diagnosa_masuk group by 1,2
											) wyy
							) wkk group by 1 order by jumlah_b_laki DESC,jumlah_b_perempuan DESC
					
) wpy
left join 
			(select k.diagnosa_masuk, k.jumlah as jumlah_kunjungan from (
													select diagnosa_masuk,count(nomor_mr) as jumlah from 
													kunjungan_pasien where year(tanggal_masuk)='$year' and status_kunjungan='1' group by diagnosa_masuk ) k order by k.jumlah DESC limit 10
) wpk 
on wpy.diagnosa_masuk=wpk.diagnosa_masuk");
        return $sql->result();
    }
}
