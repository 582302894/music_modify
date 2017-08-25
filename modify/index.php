<?php

define('MC_CORE', true);

define('MC_VERSION', '1.2.6');

// SoundCloud 客户端 ID，如果失效请更改
define('MC_SC_CLIENT_ID', '2t9loNQH90kzJcsFCODdigxfp325aq4z');

// Curl 代理地址，解决翻墙问题。例如：define('MC_PROXY', 'http://10.10.10.10:8123');
define('MC_PROXY', false);

// 核心文件目录;
define('MC_CORE_DIR', __DIR__ . '/core');

// 模版文件目录;
define('MC_TPL_DIR', __DIR__ . '/template');

// PHP 版本判断
if (version_compare(phpversion(), '5.4', '<')) {
    echo sprintf(
        '<h3>程序运行失败：</h3><blockquote>您的 PHP 版本低于最低要求 5.4，当前版本为 %s</blockquote>',
        phpversion()
    );
    exit;
}

include 'class\search.class.php';
include 'function.php';

$name = htmlentities($_REQUEST['name']);
$search = new Search();
$res = $search->search($name);

echo json_encode($res);