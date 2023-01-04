<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class ApiLokasiUTBK extends CI_Controller
{

    use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__resTraitConstruct();
    }

    public function get_list_ruangan_post()
    {
        $res = [
            'status' => true,
            'list' => [
                // ["Fakultas Teknik, Lab  Jaringan Komputer Teknik Elektro Lantai  2 Gedung A2", "95° 22' 5.117\" E", "5° 33' 59.610\" N"],
                // ["Fakultas Teknik, Lab  Komputer FT Lantai  2 Gedung A1", "95° 22' 6.564\" E", "5° 34' 2.169\" N"],
                // ["Fakultas Teknik, Lab  Teknik Sipil Lantai 2 Gedung A1", "95° 22' 5.257\" E", "5° 34' 1.947\" N"],
                // ["Fakultas Teknik, Lab  Multimedia Arsitektur  Lantai 2 Gedung A1", "95° 22' 5.186\" E", "5° 34' 4.483\" N"],
                ["Gedung ICT Center, Auditorium Lantai  1", "95° 22' 1.274\" E", "5° 34' 12.928\" N"],
                ["Gedung ICT Center, Main Lab  Lantai  2", "95° 22' 1.234\" E", "5° 34' 12.707\" N"],
                ["Gedung ICT Center, Lab  ADOC Lantai  3", "95° 22' 1.878\" E", "5° 34' 12.664\" N"],
                ["Gedung ICT Center, Lab  Cyber Lantai  3", "95° 22' 1.080\" E", "5° 34' 12.701\" N"],
                ["Gedung ICT Center, Minilab Lantai  3", "95° 22' 1.066\" E", "5° 34' 12.896\" N"],
                ["Gedung ICT Center, Lab  Multiguna Lantai  3", "95° 22' 1.836\" E", "5° 34' 13.512\" N"],
                ["Gedung ICT Center, Lab  Digital Lantai 3", "95° 22' 1.901\" E", "5° 34' 13.317\" N"],
                // ["Gedung Magister Manajemen, Lab  Komputer", "95° 22' 1.995\" E", "5° 34' 3.233\" N"],
                // ["Fakultas Ekonomi dan Bisnis, Lab  Komputer Diploma III Lantai  1", "95° 22' 0.181\" E", "5° 34' 25.669\" N"],
                // ["Fakultas Ekonomi dan Bisnis, Lab  Komputer EKP Lantai  I", "95° 21' 58.248\" E", "5° 34' 24.586\" N"],
                // ["Fakultas Ekonomi dan Bisnis, Lab  Komputer Perbankan Lantai 2", "95° 22' 0.929\" E", "5° 34' 25.670\" N"],
                ["Fakultas Kedokteran, Lab  Komputer FK Lantai 2 Blok A", "95° 22' 15.021\" E", "5° 33' 56.268\" N"],
                ["Fakultas Kedokteran, Lab  Komputer FK Lantai 2 Blok B", "95° 22' 14.731\" E", "5° 33' 56.284\" N"],
                // ["Fakultas Kedokteran  Gigi, Lab  CBT FKG Lantai 2", "95° 22' 9.863\" E", "5° 33' 51.871\" N"],
                // ["Fakultas Kedokteran  Hewan, Lab  Komputer FKH", "95° 22' 15.8\" E", "5° 34' 04.2\" N"],
                // ["Fakultas Kelautan  dan Perikanan, Lab  Pemodelan Laut", "95° 22' 9.881\" E", "5° 34' 5.882\" N"],
                // ["Fakultas Keperawatan, Lab  Komputer FKEP", "95° 22' 12.698\" E", "5° 33' 55.204\" N"],
                // ["Fakultas Pertanian, Lab  Komputer FP Lantai  1", "95° 22' 20.293\" E", "5° 33' 57.852\" N"],
                // ["SMA Laboratorium  Unsyiah, Lab  Komputer Lantai 2", "95° 21' 56.150\" E", "5° 34' 27.746\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  1, Matematika Terapan", "95° 22' 5.251\" E", "5° 34' 10.494\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  1, Komputasi dan Pemrograman Statistika", "95° 22' 4.952\" E", "5° 34' 9.632\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  1, Pengembangan Media Pembelajaran  Matematika", "95° 22' 4.899\" E", "5° 34' 10.514\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  1, Modelling, Permodelan  dan Simulasi Matematika", "95° 22' 5.588\" E", "5° 34' 10.474\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Optimasi Dinamik", "95° 22' 5.457\" E", "5° 34' 10.840\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Metode dan Komputasi Numerik", "95° 22' 5.120\" E", "5° 34' 10.839\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Data Sains dan Kecerdasaan  Artifisial", "95° 22' 4.784\" E", "5° 34' 10.877\" N"],
                ["Gedung FMIPA, Blok  A, Lantai  3, Sistem Informasi  dan Database", "95° 22' 5.004\" E", "5° 34' 8.443\" N"],
                ["Gedung FMIPA, Blok  A, Lantai  3, Sistem Komputer dan Jaringan", "95° 22' 4.989\" E", "5° 34' 8.182\" N"],
                ["Gedung FMIPA, Blok  A, Lantai  3, Informatika Terapan", "95° 22' 4.905\" E", "5° 34' 7.885\" N"],
                ["Gedung FMIPA, Blok  A, Lantai  3, Geospasial", "95° 22' 4.941\" E", "5° 34' 8.740\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Biostatistika", "95° 22' 5.479\" E", "5° 34' 9.599\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Multimedia", "95° 22' 5.033\" E", "5° 34' 9.859\" N"],
                ["Gedung FMIPA, Blok  C, Lantai  3, Rekayasa Perangkat Lunak", "95° 22' 4.729\" E", "5° 34' 9.642\" N"],
                ["Gedung FMIPA, Blok  A, Lantai  2, Multimedia", "95° 22' 5.259\" E", "5° 34' 8.819\" N"]
            ]
        ];
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }
}
