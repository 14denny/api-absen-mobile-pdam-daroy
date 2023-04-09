<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Login extends CI_Controller
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

        // header('Access-Control-Allow-Origin: *');
        // header("Access-Control-Allow-Headers: *");
        // header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        // $method = $_SERVER['REQUEST_METHOD'];
        // if ($method == "OPTIONS") {
        //     die();
        // }
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

    public function index_post()
    {
        $this->verify_request();

        $nik = $this->post('nik', true);
        $password = $this->post('password', true);

        if ($nik == null || $nik == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nik);

        if ($password == null || $password == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'nik\' tidak boleh kosong',
                    'code' => 500
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
                    'message' => 'NIK tidak dapat ditemukan',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if (!$pegawai->password) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Anda belum mendaftarkan akun, harap buat akun terlebih dahulu',
                    'code' => 404
                ]
            ];
            $this->response($response, 200);
            exit();
        }


        if (password_verify($password, $pegawai->password)) {
            $data_pegawai = $this->login_model->data_pegawai($nik);
            $is_admin = $this->main_model->select('users', '1', ['username'=>$nik]);
            
            $res = [
                'status' => true,
                'is_admin' => $is_admin ? '1' : '0',
                'message' => 'Login berhasil',
                'data_pegawai' => $data_pegawai
            ];
            $meta = [
                'message' => 'Login berhasil',
                'code' => 200
            ];
        } else {
            $res = [
                'status' => false,
                'message' => "NIK atau password salah!"
            ];
            $meta = [
                'message' => 'NIK atau password salah',
                'code' => 500
            ];
        }

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }

    public function reset_password_post()
    {
        $this->verify_request();

        $nik = $this->post('nik', true);
        $email = $this->post('email', true);

        if ($email == null || $email == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'email\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nik);

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

        if ($pegawai->email != $email) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Email yang dikirimkan tidak sama dengan email yang didaftarkan untuk pegawai ' . $pegawai->nama,
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $token = $this->login_model->create_token($pegawai->id);
        if (!$token) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Gagal membuat token reset password',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $status_kirim_email = $this->login_model->send_email($pegawai, $token);
        if (!$status_kirim_email['status']) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Gagal. ' . $status_kirim_email['msg'],
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $res = [
            'status' => true,
            'message' => 'Silahkan cek email untuk melihat token reset password',
        ];
        $meta = [
            'message' => 'Silahkan cek email untuk melihat token reset password',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }

    public function submit_reset_password_post()
    {
        $this->verify_request();

        $nik = $this->post('nik', true);
        $token = $this->post('token', true);
        $pass = $this->post('pass', true);
        $pass_conf = $this->post('pass_conf', true);

        if ($nik == null || $nik == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'token\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($token == null || $token == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'token\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($pass == null || $pass == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'pass\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($pass_conf == null || $pass_conf == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'pass_conf\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($pass_conf != $pass) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Password harus sama dengan konfirmasi password',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nik);

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

        //cek token sesuai
        $get_token = $this->main_model->select('token_reset_password', '*', ['id_pegawai' => $pegawai->id, 'token' => $token]);
        if (!$get_token) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Token tidak sesuai',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        if ($get_token->valid_until < time()) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Token sudah kadaluarsa, harap kirim email kembali',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $status_reset_pass = $this->main_model->update('pegawai', ['id' => $pegawai->id], ['password' => password_hash($pass, PASSWORD_BCRYPT)]);
        if (!$status_reset_pass) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Gagal mereset password. Harap coba lagi',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $res = [
            'status' => true,
            'message' => 'Reset password berhasil. Silahkan login menggunakan password yang baru direset',
        ];
        $meta = [
            'message' => 'Reset password berhasil. Silahkan login menggunakan password yang baru direset',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];

        $this->response($response, 200);
    }
}
