<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once('.config.php');

header('Content-type: image/png');

function showBanner($state = true){
    $file = 'img/banner.png';
    $errorImg = 'img/error.png';

    if($state !== true){
        $file = $errorImg;
    }

    header('Content-Length: ' . filesize($file));
    header('Cache-Control: no-cache');
    readfile($file);
}

function showError(){
    showBanner(false);
}

function getRemoteIp(){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
    return null;
}

function getUserAgent(){
    if(isset($_SERVER['HTTP_USER_AGENT'])) {
        return $_SERVER['HTTP_USER_AGENT'];
    }
    return null;
}

function getPageUrl(){
    if(isset($_SERVER['HTTP_REFERER'])) {
        return $_SERVER['HTTP_REFERER'];
    }
    return null;
}

function getPageIdSession($ip, $userAgent, $pageUrl){
    global $mysqli;
    $stmt = $mysqli->prepare(
        "SELECT id FROM statistics 
          WHERE 
            `ip_address` = ? AND
            `user_agent` = ? AND
            `page_url` = ?
            ");


    $stmt->bind_param("sss", $ip, $userAgent, $pageUrl);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }

    return 0;
}

function registerStatistics($ip, $userAgent, $pageUrl){
    global $mysqli;
    $stmt = $mysqli->prepare(
        "INSERT INTO statistics(`ip_address`, `user_agent`, `page_url`)
          VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $ip, $userAgent, $pageUrl);
    $stmt->execute();
}

function updateStatistics($sessionId){
    global $mysqli;
    $sql = "UPDATE statistics set views_count = views_count + 1, view_date = NOW() WHERE id = $sessionId";

    if ($mysqli->query($sql) !== TRUE) {
        echo "ERROR";
    }
}

try {
    $mysqli = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
    if ($mysqli->connect_errno) {
        showError();
    }
}catch(Exception $e){}

$ip = getRemoteIp();
$userAgent = getUserAgent();
$pageUrl = getPageUrl();

if($ip === null || $userAgent === null || $pageUrl === null){
    showError();
}

$sessionId = getPageIdSession($ip, $userAgent, $pageUrl);

if($sessionId !== 0) {
    updateStatistics($sessionId);
} else {
    registerStatistics($ip, $userAgent, $pageUrl);
}

showBanner();