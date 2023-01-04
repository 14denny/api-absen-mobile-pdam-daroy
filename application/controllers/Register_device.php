<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Register_device extends CI_Controller
{

    use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__resTraitConstruct();
        $this->load->helper(['jwt']);
        $this->load->model(['login_model', 'verify_model']);
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

    function register_post()
    {
        $this->verify_request();

        $appid = $this->post("appid");
        $nik = $this->post("nik");
        $version = $this->post("version");
        $version = $version ? $version : 1;

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

        if ($appid == null || $appid == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'appid\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'appid\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($version == null || $version == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'version\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'version\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        //check apakah nip sudah pernah mendaftarkan perangkatnya
        if ($this->verify_model->nip_registered($nik, $version)) {
            $response = [
                'response' => [
                    'message' => 'Sudah ada NIK yang didaftarkan untuk perangkat ini',
                ],
                'metadata' => [
                    'message' => 'Sudah ada NIK yang didaftarkan untuk perangkat ini',
                    'code' => 500
                ]
            ];

            $this->response($response, 200);
            exit();
        }

        //check apakah appid sudah pernah didaftarkan
        if ($this->verify_model->appid_registered($appid, $version)) {
            $response = [
                'response' => [
                    'message' => 'Perangkat ini sudah pernah didaftarkan sebelumnya!'
                ],
                'metadata' => [
                    'message' => 'Perangkat ini sudah pernah didaftarkan sebelumnya!',
                    'code' => 500
                ]
            ];

            $this->response($response, 200);
            exit();
        }

        //check apakah nip ada di database
        if (!$this->login_model->cek_nip($nik)) {
            $response = [
                'response' => [
                    'message' => "NIK $nik tidak ditemukan!"
                ],
                'metadata' => [
                    'message' => "NIK $nik tidak ditemukan!",
                    'code' => 500
                ]
            ];

            $this->response($response, 200);
            exit();
        }


        //daftarkan perangkat dan NIK
        $insert = $this->verify_model->insert_device(strtoupper($appid), $nik, $version);

        if ($insert) {
            $res = [
                'status' => true,
                'message' => "Berhasil mendaftarkan perangkat!"
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
        } else {
            $response = [
                'response' => [
                    'message' => 'Tidak dapat mendaftarkan perangkat, harap coba lagi!'
                ],
                'metadata' => [
                    'message' => 'Tidak dapat mendaftarkan perangkat, harap coba lagi!',
                    'code' => 500
                ]
            ];

            $this->response($response, 200);
            exit();
        }
    }

    function check_device_post()
    {
        $this->verify_request();

        $appid = strtoupper($this->post("appid"));
        $version = $this->post("version");
        $version = $version ? $version : 1;

        if ($appid == null || $appid == "") {
            $response = [
                'response' => [
                    'message' => 'Parameter \'appid\' tidak boleh kosong',
                ],
                'metadata' => [
                    'message' => 'Parameter \'appid\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $data = $this->main_model->select('registered_device r', '*, (select nik from pegawai p where p.id=r.id_pegawai)', ['appid' => $appid, 'version' => $version]);
        if ($data) {
            $res = [
                'appid' => $appid,
                'nik' => $data->nik
            ];
            $meta = [
                'message' => 'Ok',
                'code' => 200
            ];
        } else {
            $res = [
                'status' => false,
                'message' => "Perangkat belum terdaftar!"
            ];
            $meta = [
                'message' => 'Error',
                'code' => 500
            ];
        }

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->response($response, 200);
    }
}
