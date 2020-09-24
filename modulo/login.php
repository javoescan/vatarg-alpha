<?php
@session_start();
if(isset($_GET['code'])){
    $grant_type='authorization_code';
    $client_id='492';
    $client_secret='CsFlhGjFXWbKlebM2HQqyqVFrFrBJRxZ0NqUoAJR';
    $redirect_uri='https://argentina.vatsur.org/site/alpha/modulo/login.php';
    $code= $_GET['code'];
    $url="https://auth.vatsim.net/oauth/token";
    $ch = curl_init();
    // configure the post request to VATSIM
    curl_setopt_array($ch, array(
            CURLOPT_URL => $url, // the url to make the request to
            CURLOPT_POST => 1, // we are sending this via post
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS =>"grant_type=$grant_type&client_id=$client_id&client_secret=$client_secret&redirect_uri=$redirect_uri&code=$code",
        ));
        
        // perform the request
        $response = curl_exec($ch);
        $token_access=json_decode($response,true);
        $_SESSION['token']=$token_access['access_token'];
        header("location:../index.php");
        exit;

}
if(isset($_GET['login'])){
 header("location:https://auth.vatsim.net/oauth/authorize?client_id=492&redirect_uri=https://argentina.vatsur.org/site/alpha/modulo/login.php&response_type=code&scope=full_name+vatsim_details+email+country");
   
}
if(isset($_GET['logged'])){
    $url2="https://auth.vatsim.net/api/user";
        $header=['Authorization:Bearer '.$_GET['logged'] ,'Accept: application/json'];
    $ch2 = curl_init();
    // configure the post request to VATSIM
    curl_setopt_array($ch2, array(
            CURLOPT_URL => $url2, // the url to make the request to
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER=>$header
        ));
        
        // perform the request
        $response2 = curl_exec($ch2);
       echo $response2;
}
?>