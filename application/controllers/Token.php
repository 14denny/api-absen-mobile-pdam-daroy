<?php

/**
 * @OA\Info(title="API Pelaporan SIMRS", version="1.0")
 */

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Token extends CI_Controller
{

    use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__resTraitConstruct();
        $this->load->helper(['jwt']);
        $this->load->model('user_model');
    }

    public function index_post()
    {
        $username = $this->post('username');
        $password = $this->post('password');
        if ($this->user_model->verify_login($username, $password)) {
            $token = JWT::generateToken(['username' => $username]);
            $response = [
                "response" => [
                    'token' => $token
                ],
                'metadata' => [
                    'message' => 'Ok',
                    'code' => 200
                ]
            ];
            $this->response($response, 200);
        } else {
            $response = [
                "response" => null,
                'metadata' => [
                    'message' => 'Username atau password salah',
                    'code' => 400
                ]
            ];
            $this->response($response, 200);
        }
    }
}
