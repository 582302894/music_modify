<?php

// 加密网易云音乐 api 参数
function encode_netease_data($data) {
    $_key = '7246674226682325323F5E6544673A51';
    $data = json_encode($data);
    if (function_exists('openssl_encrypt')) {
        $data = openssl_encrypt($data, 'aes-128-ecb', pack('H*', $_key));
    } else {
        $_pad = 16 - (strlen($data) % 16);
        $data = base64_encode(mcrypt_encrypt(
            MCRYPT_RIJNDAEL_128,
            hex2bin($_key),
            $data . str_repeat(chr($_pad), $_pad),
            MCRYPT_MODE_ECB
        ));
    }
    $data = strtoupper(bin2hex(base64_decode($data)));
    return array('eparams' => $data);
}

// 参数处理
function stripslashes_deep($value) {
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key}
            = stripslashes_deep($data);
        }
    } elseif (is_string($value)) {
        $value = stripslashes($value);
    }
    return $value;
}
function maicong_parse_str($string, &$array) {
    parse_str($string, $array);
    if (get_magic_quotes_gpc()) {
        $array = stripslashes_deep($array);
    }
}
function maicong_parse_args($args, $defaults = array()) {
    if (is_object($args)) {
        $r = get_object_vars($args);
    } elseif (is_array($args)) {
        $r = &$args;
    } else {
        maicong_parse_str($args, $r);
    }
    if (is_array($defaults)) {
        return array_merge($defaults, $r);
    }
    return $r;
}
// Curl 内容获取
function curl($args = array()) {
    $default = array(
        'method' => 'GET',
        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36',
        'url' => null,
        'referer' => 'https://www.google.co.uk',
        'headers' => null,
        'body' => null,
        'sslverify' => false,
        'proxy' => false,
        'range' => false,
    );
    $args = maicong_parse_args($args, $default);
    $method = mb_strtolower($args['method']);
    $method_allow = array('get', 'post', 'put', 'patch', 'delete', 'head', 'options');
    if (null === $args['url'] || !in_array($method, $method_allow, true)) {
        return;
    }
    require_once 'class/curl.class.php';
    $curl = new Curl();
    $curl->setOpt(CURLOPT_SSL_VERIFYPEER, $args['sslverify']);
    $curl->setUserAgent($args['user-agent']);
    $curl->setReferrer($args['referer']);
    $curl->setTimeout(20);
    $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
    if ($args['proxy'] && define('MC_PROXY') && MC_PROXY) {
        $curl->setOpt(CURLOPT_PROXY, MC_PROXY);
    }
    if (!empty($args['range'])) {
        $curl->setOpt(CURLOPT_RANGE, $args['range']);
    }
    if (!empty($args['headers'])) {
        foreach ($args['headers'] as $key => $val) {
            $curl->setHeader($key, $val);
        }
    }
    $curl->$method($args['url'], $args['body']);
    $curl->close();
    $response = $curl->raw_response;
    if (!empty($response)) {
        return $response;
    }
    return;
}