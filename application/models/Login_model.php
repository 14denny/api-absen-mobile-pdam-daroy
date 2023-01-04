<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require APPPATH . "third_party/PHPMailer/src/Exception.php";
require APPPATH . "third_party/PHPMailer/src/PHPMailer.php";
require APPPATH . "third_party/PHPMailer/src/SMTP.php";

class Login_model extends CI_Model
{
    function data_pegawai($nik)
    {
        return $this->db->from("pegawai p")
            ->select('nik, nama, jenis_kelamin, l.id as id_lokasi, l.nama_lokasi')
            ->join('lokasi_kerja_pegawai lkp', 'lkp.id_pegawai=p.id')
            ->join('lokasi_kerja l', 'l.id=lkp.id_lokasi')
            ->where('p.nik', $nik)
            ->get()->row();
    }

    function create_token($id_pegawai)
    {

        $token = $this->generateRandomNumber(8);
        $data = array(
            'id_pegawai' => $id_pegawai,
            'token' => $token,
            'valid_until' => time() + (30 * 60) // valid until next 30 minute
        );

        $this->db->where('id_pegawai', $id_pegawai)
            ->delete('token_reset_password');

        $this->db->insert('token_reset_password', $data);
        return $token;
    }

    function generateRandomString($length = 50)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function generateRandomNumber($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function send_email($pegawai, $token)
    {
        $this->load->helper(['mailer']);
        $mail = new PHPMailer(true);

        $email_kirim = email();
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Disable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = $email_kirim;                     // SMTP username
            $mail->From   = $email_kirim;                     // SMTP username
            $mail->Password   = pass();                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->SMTPAuth = true;
            $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            //Recipients
            $mail->setFrom($email_kirim, 'Absen Mobile PDAM Tirta Daroy');
            $mail->addAddress($pegawai->email, $pegawai->nama);     // Add a recipient
            $mail->addReplyTo($email_kirim, 'Absen Mobile PDAM Tirta Daroy');

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Reset Password Absen Mobile PDAM Tirta Daroy';
            $mail->Body    = $this->load->view('email-html', ['pegawai'=>$pegawai, 'token'=>$token], TRUE);

            $mail->send();
            // $this->model->insert_log_mail_sent($email_kirim, $pegawai->email, 1); //berhasil
            return array('status' => true);
        } catch (Exception $e) {
            // $this->model->insert_log_mail_sent($email_kirim, $pegawai->email, 0); //gagal

            return array('status' => false, 'msg' => $e->getMessage());
            // return false;
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

}
