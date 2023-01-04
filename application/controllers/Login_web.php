<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Login_web extends CI_Controller
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

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization, X-Token");
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

    public function verify_device($nip)
    {
        $headers = $this->input->request_headers();
        if (array_key_exists('x-appid', $headers)) {
            $appid = $this->db->escape_str($headers['x-appid']);
            $version = array_key_exists('x-version', $headers) ? $this->db->escape_str($headers['x-version']) : 2;
            $version = $version ?: 1;
            if (!$this->verify_model->verify_address($appid, $nip, $version)) {
                $response = [
                    'response' => [
                        'message' => 'Perangkat belum terdaftar atau menggunakan NIP yang berbeda!'
                    ],
                    'metadata' => [
                        'code' => 403,
                        'message' => 'Perangkat belum terdaftar atau menggunakan NIP yang berbeda!'
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

        $nip = $this->db->escape_str($this->post('nip'));
        $password = $this->db->escape_str($this->post('password'));

        if ($nip == null || $nip == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        // $this->verify_device($nip);

        if ($password == null || $password == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'nip\' tidak boleh kosong',
                    'code' => 500
                ]
            ];
            $this->response($response, 200);
            exit();
        }

        $result = $this->login_model->login_pegawai($nip, $password);
        if ($result) {
            $res = [
                'status' => true,
                'message' => 'Login berhasil',
                'data_pegawai' => $result
            ];
            $meta = [
                'message' => 'Login berhasil',
                'code' => 200
            ];
        } else {
            $res = [
                'status' => false,
                'message' => "NIP atau password SIMPEG salah!"
            ];
            $meta = [
                'message' => 'NIP atau password SIMPEG salah',
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
