<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function api_login($nip)
{
    return "http://ws.unsyiah.ac.id/webservice/dosen/cdosen/login/nip/$nip/key/uywjg688h1";
}

function get_curl($url)
{
    $ch = curl_init();

    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);

    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // curl_setopt($ch, CURLOPT_USERPWD, "$username:$pwd");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // $output contains the output string 
    $output = curl_exec($ch);

    // tutup curl 
    curl_close($ch);
    return $output;
}

function post_curl($url, $data, $username, $pwd)
{

    $ch = curl_init();

    $payload = json_encode($data);

    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);

    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($ch, CURLOPT_USERPWD, "$username:$pwd");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Set HTTP Header for POST request 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ));


    // $output contains the output string 
    $output = curl_exec($ch);

    // tutup curl 
    curl_close($ch);
    return $output;
}
