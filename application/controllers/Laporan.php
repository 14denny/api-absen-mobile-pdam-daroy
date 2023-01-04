<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Laporan extends CI_Controller
{

    use REST_Controller {
        REST_Controller::__construct as private __resTraitConstruct;
    }

    public function __construct()
    {
        parent::__construct();
        $this->__resTraitConstruct();
        $this->load->helper(['jwt']);
        $this->load->model('model_laporan');
    }

    private function verify_request()
    {
        $headers = $this->input->request_headers();
        if (array_key_exists('x-token', $headers)) {
            $token = $headers['x-token'];
            try {
                $data = JWT::validateToken($token);
                if ($data === false) {
                    $response = ['code' => 401, 'message' => 'Unauthorized Access! A'];
                    $this->response($response, 200);
                    exit();
                } else {
                    return $data;
                }
            } catch (Exception $e) {
                $response = ['code' => 401, 'message' => 'Unauthorized Access!'];
                $this->response($response, 200);
            }
        } else {
            $response = ['code' => 401, 'message' => 'Authorization Not Found'];
            $this->response($response, 200);
        }
    }

    public function indikator_pelayanan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = array(
            array(
                'bor' => 0.4,
                'los' => 2,
                'bto' => 12.2,
                'toi' => 0.01,
                'ndr' => 0.02,
                'rata_kunjungan' => 40
            )
        );

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }




    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/tempat_tidur",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function tempat_tidur_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->tempat_tidur($tahun);
        foreach ($data as $d) {
            $d->vvip = intval($d->vvip);
            $d->vip = intval($d->vip);
            $d->i = intval($d->i);
            $d->ii = intval($d->ii);
            $d->iii = intval($d->iii);
            $d->non = intval($d->non);
            $total = $d->vvip + $d->vip + $d->i + $d->ii + $d->iii + $d->non;
            $d->total = $total;
        }

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }




    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/ketenagaan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function ketenagaan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->ketenagaan($tahun);
        foreach ($data as $d) {
            $d->jumlah_laki = intval($d->jumlah_laki);
            $d->jumlah_perempuan = intval($d->jumlah_perempuan);
            $d->jumlah_butuh_laki = intval($d->jumlah_butuh_laki);
            $d->jumlah_butuh_perempuan = intval($d->jumlah_butuh_perempuan);
            $d->jumlah_kurang_laki = intval($d->jumlah_kurang_laki);
            $d->jumlah_kurang_perempuan = intval($d->jumlah_kurang_perempuan);
        }

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/rawat_inap",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function rawat_inap_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }



        $data = $this->model_laporan->rl_rawat_inap($tahun);

        foreach ($data as $d) {
            $d->jumlah_pasien_masuk = intval($d->jumlah_pasien_masuk);
            $d->jumlah_pasien_awal_tahun = intval($d->jumlah_pasien_awal_tahun);
            $d->jumlah_pasien_akhir_tahun = intval($d->jumlah_pasien_akhir_tahun);
            $d->jumlah_pasien_keluar = intval($d->jumlah_pasien_keluar);
            $d->jumlah_pasien_kurang_delapan_jam = intval($d->jumlah_pasien_kurang_delapan_jam);
            $d->jumlah_pasien_lebih_delapan_jam = intval($d->jumlah_pasien_lebih_delapan_jam);
            $d->jumlah_lama_dirawat = intval($d->jumlah_lama_dirawat);
            $d->vvip = intval($d->vvip);
            $d->vip = intval($d->vip);
            $d->i = intval($d->i);
            $d->ii = intval($d->ii);
            $d->iii = intval($d->iii);
            $d->non = intval($d->non);
        }

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/gawat_darurat",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function gawat_darurat_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_igd($tahun)[0];

        $data->jumlah_pasien_rujukan = intval($data->jumlah_pasien_rujukan);
        $data->jumlah_pasien_non_rujukan = intval($data->jumlah_pasien_non_rujukan);
        $data->jumlah_pasien_dirawat = intval($data->jumlah_pasien_dirawat);
        $data->jumlah_pasien_dirujuk = intval($data->jumlah_pasien_dirujuk);
        $data->jumlah_pasien_pulang = intval($data->jumlah_pasien_pulang);
        $data->jumlah_pasien_meninggal = intval($data->jumlah_pasien_meninggal);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/gigi_mulut",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function gigi_mulut_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_gigi($tahun);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/kebidanan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function kebidanan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_kebidanan($tahun);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/perinatologi",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function perinatologi_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = array(
            "jenis_kegiatan" => "-",
            "rm_rumah_sakit" => "-",
            "rm_bidan" => "-",
            "rm_puskesmas" => "-",
            "rm_faskes_lainnya" => "-",
            "rm_mati" => "-",
            "rm_total" => "-",
            "rnm_mati" => "-",
            "rnm_total" => "-",
            "nr_mati" => "-",
            "nr_total" => "-",
            "nr_dirujuk" => "-"
        );

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/pembedahan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function pembedahan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_bedah($tahun);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/radiologi",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function radiologi_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_radiologi($tahun);
        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'nama_radiologi' => $d->nama_radiologi,
                'jumlah' => intval($d->jumlah)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/laboratorium",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function laboratorium_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = array(
            array(
                'jenis_kegiatan' => "1",
                'jumlah' => 1
            )
        );

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/rehab_medik",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function rehab_medik_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_fisioterapi($tahun);

        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'nama_fisioterapi' => $d->nama_fisioterapi,
                'jumlah' => intval($d->jumlah)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/pelayanan_khusus",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function pelayanan_khusus_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_pelayanan_khusus($tahun);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/kesehatan_jiwa",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function kesehatan_jiwa_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_kejiwaan($tahun);

        $data->jumlah_kunjungan = intval($data->jumlah_kunjungan);

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/keluarga_berencana",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function keluarga_berencana_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = array(
            array(
                'metoda' => '-',
                'konseling_anc' => '-',
                'konseling_pasca_persalinan' => '-',
                'kb_baru_bukan_rujukan' => '-',
                'kb_baru_rujukan_rawat_inap' => '-',
                'kb_baru_rujukan_rawat_jalan' => '-',
                'kb_baru_total' => '-',
                'kb_baru_cara_masuk_total' => '-',
                'kb_baru_pasca_persalinan' => '-',
                'kb_baru_kondisi_abortus' => '-',
                'kb_baru_kondisi_lainnya' => '-',
                'kunjugan_ulang' => '-',
                'keluhan_efek_samping_jumlah' => '-',
                'keluhan_efek_samping_dirujuk' => '-'
            )
        );

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/obat_pengadaan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function obat_pengadaan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_pengadaan_obat($tahun);

        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'nama' => $d->nama,
                'obat_tersedia' => intval($d->gs_stok_rs),
                'obat_formularium_tersedia' => intval($d->gs_stok_rs)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/obat_pelayanan_resep",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function obat_pelayanan_resep_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_apotik_obat($tahun);

        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'nama' => $d->nama,
                'rawat_jalan' => intval($d->rawat_jalan),
                'rawat_inap' => intval($d->rawat_inap),
                'igd' => intval($d->igd)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/rujukan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function rujukan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_rujukan($tahun);

        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'jenis_spesialis' => $d->keterangan,
                'kdbpjs' => $d->kodebojs,
                'jumlah_rujukan_pukesmas' => intval($d->jumlah_rujukan_pukesmas),
                'jumlah_rujukan_pukesmas_kembali' => intval($d->jumlah_rujukan_pukesmas_kembali),
                'jumlah_rujukan_rs' => intval($d->jumlah_rujukan_rs),
                'jumlah_rujukan_rs_kembali' => intval($d->jumlah_rujukan_rs_kembali),
                'jumlah_dirujuk' => intval($d->jumlah_dirujuk)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/cara_bayar",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function cara_bayar_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_cara_bayar($tahun);
        $new_data = [];
        foreach ($data as $d) {
            array_push($new_data, array(
                'cara_bayar' => $d->keterangan,
                'jumlah_pasien_inap_keluar' => intval($d->jumlah_pasien_inap_keluar),
                'jumlah_lama_inap_dirawat' => intval($d->jumlah_lama_inap_dirawat),
                'jumlah_pasien_rawat_jalan' => intval($d->jumlah_pasien_rawat_jalan),
                'jumlah_pasien_lab' => intval($d->jumlah_pasien_lab),
                'jumlah_pasien_fisio' => intval($d->jumlah_pasien_fisio),
                'jumlah_pasien_radio' => intval($d->jumlah_pasien_radio)
            ));
        }

        $res = $new_data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/pengunjung",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function pengunjung_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = array(
            array(
                'jenis_kegiatan' => 'Jumlah Pengunjung Pertama',
                'jumlah' => $this->model_laporan->rl_pengunjung_baru($tahun)
            ),
            array(
                'jenis_kegiatan' => 'Jumlah Pengunjung Lama',
                'jumlah' => $this->model_laporan->rl_pengunjung_lama($tahun)
            )
        );

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/kunjungan_rawat_jalan",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function kunjungan_rawat_jalan_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_laporan_rawat_jalan($tahun);

        foreach ($data as $d) {
            unset($d->id);
            $d->jumlah_pasien = intval($d->jumlah_pasien);
        }
        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/rawat_inap_10",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function rawat_inap_10_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_sepuluh_besar_rawat_inap($tahun);

        foreach($data as $d){
            $d->jumlah_keluar_hidup_laki = intval($d->jumlah_keluar_hidup_laki);
            $d->jumlah_keluar_hidup_perempuan = intval($d->jumlah_keluar_hidup_perempuan);
            $d->jumlah_keluar_mati_laki = intval($d->jumlah_keluar_mati_laki);
            $d->jumlah_keluar_mati_perempuan = intval($d->jumlah_keluar_mati_perempuan);
        }
        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }



    /**
     * @OA\Post(
     *      path="/api-simrs/laporan/rawat_jalan_10",
     *      tags={"pelaporan"},
     *      @OA\Response(response="200", description="Success"),
     *      @OA\Response(response="404", description="Not Found"),
     *      @OA\Response(response="400", description="Bad Request"),
     *      @OA\Response(response="500", description="Internal Server Error"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="rs",
     *                      type="string",
     *                      description="Parameter Nama Rumah Sakit"
     *                  ),
     *                  @OA\Property(
     *                      property="tahun",
     *                      type="string",
     *                      description="Tahun yang akan dihitung"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="header",
     *          name="x-token",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      )
     * )
     */
    public function rawat_jalan_10_post()
    {
        $this->verify_request();

        $headers = $this->input->request_headers();
        $token = $headers['x-token'];

        $rs = $this->post("rs");
        if ($rs == null || $rs == "") {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'rs\' tidak boleh kosong',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $tahun = intval($this->post("tahun"));
        if ($tahun == null || $tahun == 0 || $tahun < 1990 || $tahun > intval(date("Y"))) {
            $response = [
                'response' => null,
                'metadata' => [
                    'message' => 'Parameter \'tahun\' tidak valid',
                    'code' => 400
                ]
            ];
            $this->output->set_header('x-token: ' . $token);
            $this->response($response, 200);
            exit();
        }

        $data = $this->model_laporan->rl_sepuluh_besar_rawat_jalan($tahun);
        foreach($data as $d){
            $d->jumlah_kasus_baru_laki = intval($d->jumlah_kasus_baru_laki);
            $d->jumlah_kasus_baru_perempuan = intval($d->jumlah_kasus_baru_perempuan);
            $d->jumlah_kunjungan = intval($d->jumlah_kunjungan);
        }

        $res = $data;
        $meta = [
            'message' => 'Ok',
            'code' => 200
        ];

        $response = [
            'response' => $res,
            'metadata' => $meta
        ];
        $this->output->set_header('x-token: ' . $token);
        $this->response($response, 200);
    }
}
