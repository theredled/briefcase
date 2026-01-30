<?php

/**
 * Created by PhpStorm.
 * User: Benoît Guchet
 * Date: 29/01/2026
 * Time: 18:41
 */

$curl = curl_init();
$curl_options = array(
    CURLOPT_URL => 'http://127.0.0.1:8000/api/login_check',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(array(
        'username' => 'benoit.guchet@gmail.com',
        'password' => 'ftiy'
    )),
);
curl_setopt_array($curl, $curl_options);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));

$response = curl_exec($curl);
echo $response;