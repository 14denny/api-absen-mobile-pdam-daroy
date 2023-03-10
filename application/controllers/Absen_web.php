<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Absen_web extends CI_Controller
{

    use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__resTraitConstruct();
        $this->load->helper(['jwt']);
        $this->load->model(['login_model', 'verify_model', 'absen_model']);
    }

    private function verify_request()
    {
        $headers = $this->input->request_headers();
        if (array_key_exists('x-token', $headers)) {
            $token = $headers['x-token'];
            try {
                $response = JWT::validateToken($token);
                if ($response === false) {
                    $response = [
                        'response' => [
                            'message' => 'Unauthorized Access! A'
                        ],
                        'metadata' => [
                            'code' => 401,
                            'message' => 'Unauthorized Access! A'
                        ]
                    ];
                    $this->response($response, 200);
                    exit();
                } else {
                    return $response;
                }
            } catch (Exception $e) {
                $response = [
                    'response' => [
                        'message' => 'Unauthorized Access! B'
                    ],
                    'metadata' => [
                        'code' => 401,
                        'message' => 'Unauthorized Access! B'
                    ]
                ];
                $this->response($response, 200);
            }
        } else {
            $response = [
                'response' => [
                    'message' => 'Authorization Not Found!'
                ],
                'metadata' => [
                    'code' => 401,
                    'message' => 'Authorization Not Found!'
                ]
            ];
            $this->response($response, 200);
        }
    }

    public function verify_device($nik)
    {
        $headers = $this->input->request_headers();
        if (array_key_exists('x-appid', $headers)) {
            $appid = $this->db->escape_str($headers['x-appid']);
            $version = array_key_exists('x-version', $headers) ? $this->db->escape_str($headers['x-version']) : 2;
            $version = $version ?: 1;
            if (!$this->verify_model->verify_address($appid, $nik, $version)) {
                $response = [
                    'response' => [
                        'message' => 'Perangkat belum terdaftar atau menggunakan NIK yang berbeda!'
                    ],
                    'metadata' => [
                        'code' => 403,
                        'message' => 'Perangkat belum terdaftar atau menggunakan NIK yang berbeda!'
                    ]
                ];
                $this->response($response, 200);
                exit();
            }
        } else {
            $response = [
                'response' => [
                    'message' => 'Perangkat tidak terdaftar!'
                ],
                'metadata' => [
                    'code' => 403,
                    'message' => 'Perangkat tidak terdaftar!'
                ]
            ];
            $this->response($response, 200);
            exit();
        }
    }

    public function waktu_server_post()
    {
        $this->verify_request();

        $nip = $this->db->escape_str($this->post('nip'));
        if ($nip == null || $nip == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nip);

        $tanggal = date('d-m-Y');
        $jam = date('H:i:s');

        $res = [
            'status' => true,
            'waktu' => [
                'jam' => $jam,
                'tanggal' => $tanggal
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

    public function rekap_absen_today_post()
    {
        $this->verify_request();

        $nip = $this->db->escape_str($this->post('nip'));
        if ($nip == null || $nip == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nip);

        $tanggal = date('Y-m-d');

        //check kalau yang absen adalah dosen yang sudah absen di simkuliah
        if ($this->absen_model->check_absen_dosen_mk($nip, $tanggal)) {

            $res = [
                'status' => true,
                'jenis_absen' => 1,
                'tanggal' => date('Y-m-d'),
                'rekap' => [
                    'masuk' => 'MENGAJAR',
                    'pulang' => 'MENGAJAR',
                ]
            ];
            $meta = [
                'message' => 'Ok',
                'code' => 200
            ];
        } else {

            $rekap_today = $this->absen_model->get_rekap_today($nip);
            if ($rekap_today) {
                //get lokasi kerja pegawai
                $lokasi_kerja=$this->absen_model->get_lokasi_kerja_pegawai($nip);

                // if($lokasi_kerja->jenis_pegawai == 1){ //basis lokasi
                if($lokasi_kerja && $lokasi_kerja->jenis_pegawai == 1){//tanpa lokasi
                    $hadir = !!($rekap_today->jam_masuk || $rekap_today->jam_pulang);

                    $res = [
                        'status' => true,
                        'tanggal' => date('Y-m-d'),
                        'rekap' => [
                            'masuk' => $rekap_today->jam_masuk ?: ($hadir ? 'HADIR' : null),
                            'pulang' => $rekap_today->jam_pulang ?: ($hadir ? 'HADIR' : null),
                        ]
                    ];
                } else {
                    $res = [
                        'status' => true,
                        'tanggal' => date('Y-m-d'),
                        'rekap' => [
                            'masuk' => $rekap_today->jam_masuk,
                            'pulang' => $rekap_today->jam_pulang,
                        ]
                    ];
                }
                $meta = [
                    'message' => 'Ok',
                    'code' => 200
                ];
            } else {
                $res = [
                    'status' => true,
                    'tanggal' => date('Y-m-d'),
                    'rekap' => [
                        'masuk' => null,
                        'pulang' => null,
                    ]
                ];
                $meta = [
                    'message' => 'Ok',
                    'code' => 200
                ];
            }
        }


        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }

    public function absen_post()
    {
        $this->verify_request();

        $nip = $this->db->escape_str($this->post('nip'));
        if ($nip == null || $nip == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nip);

        $lat = $this->db->escape_str($this->post('lat'));
        if ($lat == null || $lat == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'lat\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'lat\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $lon = $this->db->escape_str($this->post('lon'));
        if ($lon == null || $lon == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'lon\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'lon\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $toleransi_pagi1 = '07:30:00';
        $toleransi_pagi2 = '11:00:00';

        $toleransi_sore1 = '15:00:00';
        $toleransi_sore2 = '18:00:00';


        // $toleransi_pagi1 = '19:00:00';
        // $toleransi_pagi2 = '20:30:00';

        // $toleransi_sore1 = '18:30:00';
        // $toleransi_sore2 = '19:30:00';

        $shift = 1;

        //check apakah pegawai shift
        $is_shift = $this->absen_model->check_pegawai_shift($nip);
        if ($is_shift) {
            $shift = 2;
        }

        $tanggal = date('Y-m-d');
        $jam = date('H:i:s');
        // $jam = '08:10:00';
        // $jam = '16:40:00';

        //check kalau yang absen adalah dosen yang sudah absen di simkuliah
        if ($this->absen_model->check_absen_dosen_mk($nip, $tanggal)) {

            $res = [
                'status' => true,
                'jenis_absen' => 1,
                'tanggal' => date('Y-m-d'),
                'rekap' => [
                    'masuk' => '',
                    'pulang' => '',
                ]
            ];
            $meta = [
                'message' => 'Ok',
                'code' => 200
            ];
        } else {

            $status = $this->absen_model->di_lokasi_kerja($nip, $lat, $lon);
            // if (!$status['status']) { //check apa berhasil absen
            //     if (!$status['lokasi_kerja']) { //apa gk bisa absen karna gk ada data nip di lokasi_kerja_pegawai
            //         $response = [
            //             'response' => [
            //                 'message' => 'Tidak dapat melakukan absen, Anda tidak memiliki lokasi kerja!',
            //             ],
            //             'metadata' => [
            //                 'message' => 'Tidak dapat melakukan absen, Anda tidak memiliki lokasi kerja!',
            //                 'code' => 400
            //             ]
            //         ];
            //         $this->response($response, 200);
            //         exit();
            //     } else {
            //         $response = [
            //             'response' => [
            //                 'message' => 'Tidak dapat melakukan absen DI LUAR wilayah ' . $status['lokasi_kerja']->nama_lokasi . '! Scale: ' . $status['jarak'],
            //             ],
            //             'metadata' => [
            //                 'message' => 'Tidak dapat melakukan absen DI LUAR wilayah ' . $status['lokasi_kerja']->nama_lokasi . '! Scale: ' . $status['jarak'],
            //                 'code' => 400
            //             ]
            //         ];
            //         $this->response($response, 200);
            //         exit();
            //     }
            // }

            $jenis_absen = 1;

            //kalau pegawai biasa
            if ($shift == 1) {

                // if ($status['lokasi_kerja']->jenis_pegawai == 1) { //DS (dosen biasa) //basis lokasi
                if ($status['lokasi_kerja'] && $status['lokasi_kerja']->jenis_pegawai == 1) { //DS (dosen biasa) //tanpa lokasi

                    //jam dibuat rapat antara pagi dan sore
                    $toleransi_pagi2 = '13:00:00';
                    $toleransi_sore1 = '13:00:01';
                }

                if ($jam >= $toleransi_pagi1 && $jam <= $toleransi_pagi2) { //absen masuk
                    $jenis_absen = 1;
                    $insert_absen = $this->absen_model->insert_absen_pagi_noshift($nip, $tanggal, $jam, $lat, $lon);
                } else if ($jam >= $toleransi_sore1 && $jam <= $toleransi_sore2) { //absen pulang
                    $jenis_absen = 2;
                    $insert_absen = $this->absen_model->insert_absen_sore_noshift($nip, $tanggal, $jam, $lat, $lon);
                } else {
                    //kalau gk di waktu absen
                    $response = [
                        'response' => [
                            'message' => 'Tidak dapat melakukan absen DI LUAR jam toleransi absen!',
                        ],
                        'metadata' => [
                            'message' => 'Tidak dapat melakukan absen DI LUAR jam toleransi absen!',
                            'code' => 400
                        ]
                    ];
                    $this->response($response, 200);
                    exit();
                }
            } else { //absen shift
                $insert_absen = $this->absen_model->insert_absen_shift($nip, $tanggal, $jam, $lat, $lon);
                $jenis_absen = $insert_absen;
            }

            if ($insert_absen) {
                $rekap_today = $this->absen_model->get_rekap_today($nip);
                if ($rekap_today) {
                    if ($status['lokasi_kerja'] && $status['lokasi_kerja']->jenis_pegawai == 1) {

                        $hadir = !!($rekap_today->jam_masuk || $rekap_today->jam_pulang);

                        $res = [
                            'status' => true,
                            'jenis_absen' => $jenis_absen,
                            'tanggal' => date('Y-m-d'),
                            'rekap' => [
                                'masuk' => $rekap_today->jam_masuk ?: ($hadir ? 'HADIR' : null),
                                'pulang' => $rekap_today->jam_pulang ?: ($hadir ? 'HADIR' : null),
                            ]
                        ];
                    } else {
                        $res = [
                            'status' => true,
                            'jenis_absen' => $jenis_absen,
                            'tanggal' => date('Y-m-d'),
                            'rekap' => [
                                'masuk' => $rekap_today->jam_masuk,
                                'pulang' => $rekap_today->jam_pulang,
                            ]
                        ];
                    }
                    $meta = [
                        'message' => 'Ok',
                        'code' => 200
                    ];
                } else {
                    $res = [
                        'status' => true,
                        'jenis_absen' => $jenis_absen,
                        'tanggal' => date('Y-m-d'),
                        'rekap' => [
                            'masuk' => null,
                            'pulang' => null,
                        ]
                    ];
                    $meta = [
                        'message' => 'Ok',
                        'code' => 200
                    ];
                }
            } else {
                $res = [
                    'status' => false,
                    'message' => 'Gagal, tidak dapat melakukan absen ' . ($jenis_absen == 1 ? 'masuk' : 'pulang') . ' lebih dari sekali!'
                ];
                $meta = [
                    'message' => 'Gagal, tidak dapat melakukan absen ' . ($jenis_absen == 1 ? 'masuk' : 'pulang') . ' lebih dari sekali!',
                    'code' => 200
                ];
            }
        }

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }

    public function catatan_get()
    {
        $this->verify_request();

        $res = [
            'status' => true,
            'catatan' => [
                [1, 'Jam Toleransi: Masuk Pagi Jam 7:30 sd 11:00, Pulang 15:30 sd 18:00'],
                [2, 'Absen dilakukan cukup 2 (dua) kali masuk dan pulang saja'],
                [3, 'Untuk pegawai shift absen 2 (dua) kali sesuai jam yang telah ditentukan oleh atasannya masing-masing'],
                [4, 'Untuk Dosen cukup 1 (satu) kali absen']
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
