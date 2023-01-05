<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Absen extends CI_Controller
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
            $version = array_key_exists('x-version', $headers) ? $this->db->escape_str($headers['x-version']) : 1;
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

        $nik = $this->db->escape_str($this->post('nik'));
        if ($nik == null || $nik == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $this->verify_device($nik);

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

        $nik = $this->db->escape_str($this->post('nik'));
        if ($nik == null || $nik == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $this->verify_device($nik);

        //cek pegawai
        $pegawai = $this->main_model->select('pegawai', '*', ['nik' => $nik]);
        if (!$pegawai) {
            $response = [
                'response' => [
                    'message' => 'Data pegawai tidak ditemukan',
                ],
                'metadata' => [
                    'message' => 'Data pegawai tidak ditemukan',
                    'code' => 404
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $rekap_today = $this->absen_model->get_rekap_today($pegawai->id);
        if ($rekap_today) {
            $res = [
                'status' => true,
                'tanggal' => date('Y-m-d'),
                'rekap' => [
                    'masuk' => $rekap_today->jam_masuk ? date('H:i', strtotime($rekap_today->jam_masuk)) : null,
                    'pulang' => $rekap_today->jam_pulang ? date('H:i', strtotime($rekap_today->jam_pulang)) : null,
                    'needs_approval' => $rekap_today->needs_approval,
                    'approved' => $rekap_today->approved
                ]
            ];
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


        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }

    public function absen_post()
    {
        $this->verify_request();

        $nik = $this->db->escape_str($this->post('nik'));
        if ($nik == null || $nik == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $this->verify_device($nik);

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

        if ($_FILES["foto"]["error"]) {
            $response = [
                'response' => [
                    'message' => 'Foto error. ',
                ],
                'metadata' => [
                    'message' => 'Foto error',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $jenis = $this->post('jenis', true);
        if ($jenis == null || $jenis == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'jenis\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'jenis\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $pegawai = $this->main_model->select('pegawai', '*', ['nik' => $nik]);
        if (!$pegawai) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'NIK pegawai tidak dapat ditemukan',
                    'nik' => $nik,
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        //cek nik yang dikirm sama dengan nik yang terdaftar dengan appid
        $headers = $this->input->request_headers();
        $version = $headers['x-version'];
        $appid = $headers['x-appid'];
        $registered = $this->main_model->select('registered_device r', '*, (select nik from pegawai p where p.id=r.id_pegawai) as nik', ['appid' => $appid, 'version' => $version]);
        if (!$registered) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Perangkat belum terdaftar',
                    'code' => 403
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($registered->nik != $nik) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'NIK yang dikirimkan tidak sama dengan NIK yang didaftarkan untuk perangkat ini',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $tanggal = date('Y-m-d');

        //cek apa sudah absen
        $absen = $this->main_model->select('absen', '*', ['id_pegawai' => $pegawai->id, 'tanggal' => $tanggal]);

        if ($jenis == 1 || $jenis == 2) { //absen masuk dan keluar

            $kol = ($jenis == 1 ? "jam_masuk" : "jam_pulang");
            if ($absen && $absen->$kol) {
                $response = [
                    'response' => [
                        'message' => 'Tidak dapat melakukan absen ' . ($jenis == 1 ? "masuk" : "keluar") . ' lebih dari sekali',
                    ],
                    'metadata' => [
                        'message' => 'Tidak dapat melakukan absen ' . ($jenis == 1 ? "masuk" : "keluar") . ' lebih dari sekali',
                        'code' => 400
                    ]
                ];
                $this->response($response, 200);
                exit();
            }
        }

        $config['file_name'] = "$nik-$tanggal-$jenis";
        $config['upload_path'] = './foto-absen';
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['overwrite'] = true;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('foto')) {
            $error = $this->upload->display_errors('', '');
            $response = [
                'response' => [
                    'message' => 'Gagal upload foto: ' . $error,
                ],
                'metadata' => [
                    'message' => 'Gagal upload foto: ' . $error,
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $data_upload = $this->upload->data();
        $source = $data_upload['full_path'];
        $path = $source;
        if ($this->absen_model->compress_image($source, $path)) {
            //cek data absen
            if ($jenis == 1 || $jenis == 2) {
                $lk = $this->absen_model->get_lokasi_kerja($pegawai->id);
                $di_area = $this->absen_model->di_area($lk->lat, $lk->lon, $lk->toleransi_jarak, $lat, $lon);
                $kol = $jenis == 1 ? 'masuk' : 'pulang';
                $dalam_waktu = $this->absen_model->dalam_waktu_absen($jenis, $lk->id);
                $needs_approval = !$di_area['status'] || !$dalam_waktu;
                $data_absen = array(
                    "id_pegawai" => $pegawai->id,
                    "tanggal" => $tanggal,
                    "foto_$kol" => "foto-absen/" . $data_upload['file_name'],
                    "lon_$kol" => $lon,
                    "lat_$kol" => $lat,
                    "jam_$kol" => date('H:i:s'),
                    "needs_approval" => $needs_approval,
                    "gps_out" => !$di_area['status']
                );

                if ($absen) {
                    $status = $this->main_model->update('absen', ['id' => $absen->id], $data_absen);
                } else {
                    $status = $this->main_model->insert('absen', $data_absen);
                }

                if ($status) {
                    $res = [
                        'status' => true,
                        'message' => 'Berhasil',
                        'di_area' => $di_area['status'],
                        'di_waktu' => !!$dalam_waktu
                    ];
                    $meta = [
                        'message' => 'Berhasil',
                        'code' => 200
                    ];
                } else {
                    $res = [
                        'status' => false,
                        'message' => 'Gagal melakukan absensi',
                    ];
                    $meta = [
                        'message' => 'Gagal melakukan absensi',
                        'code' => 200
                    ];
                }
            } else {
                $res = [
                    'status' => true,
                    'message' => 'Berhasil',
                ];
                $meta = [
                    'message' => 'Gagal, tidak dapat melakukan absen lebih dari sekali!',
                    'code' => 200
                ];
            }
        } else {
            $response = [
                'response' => [
                    'message' => 'Gagal mengunggah foto',
                ],
                'metadata' => [
                    'message' => 'Gagal mengunggah foto',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
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
                [1, 'APLIKASI ABSEN MOBILE INI HANYA DAPAT DIGUNAKAN OLEH DOSEN!'],
                [2, 'Untuk Dosen DS cukup 1 (satu) kali absen'],
                [3, 'Jam toleransi absen dosen DS: Jam 7:30 sd 18:00'],
                // [3, 'Absen dilakukan cukup 2 (dua) kali masuk dan pulang saja'],
                // [4, 'Untuk pegawai shift absen 2 (dua) kali sesuai jam yang telah ditentukan oleh atasannya masing-masing'],
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

    public function in_area_post()
    {
        $this->verify_request();

        $nik = $this->post('nik', true);
        $lat = $this->post('lat', true);
        $lon = $this->post('lon', true);

        $this->verify_device($nik);

        if ($nik == null || $nik == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

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

        //cek pegawai
        $pegawai = $this->main_model->select('pegawai', '*', ['nik' => $nik]);
        if (!$pegawai) {
            $response = [
                'response' => [
                    'message' => "NIK pegawai tidak dapat ditemukan",
                ],
                'metadata' => [
                    'message' => 'NIK pegawai tidak dapat ditemukan',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if (!$pegawai->status) {
            $response = [
                'response' => [
                    'message' => "Anda bukan pegawai aktif, tidak dapat melakukan absensi",
                ],
                'metadata' => [
                    'message' => 'Anda bukan pegawai aktif, tidak dapat melakukan absensi',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $lokasi_kerja = $this->absen_model->get_lokasi_kerja($pegawai->id);
        if (!$lokasi_kerja->status) {
            $response = [
                'response' => [
                    'message' => "Lokasi kerja bukan lokasi aktif dan tidak dapat digunakan untuk absensi. Harap hubungi admin untuk informasi lebih lanjut",
                ],
                'metadata' => [
                    'message' => 'Lokasi kerja bukan lokasi aktif dan tidak dapat digunakan untuk absensi. Harap hubungi admin untuk informasi lebih lanjut',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $res = $this->absen_model->di_area($lokasi_kerja->lat, $lokasi_kerja->lon, $lokasi_kerja->toleransi_jarak, $lat, $lon);
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
