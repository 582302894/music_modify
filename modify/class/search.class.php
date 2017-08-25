<?php
/**
 *搜索
 */
class Search {

    public function __construct() {
        # code...
    }

    public function search($input, $type = 'name', $urlType = 'netease') {
        switch ($type) {
        case 'name':
            $res = $this->searchName($input, $urlType);
            break;
        case 'id':
            $res = $this->searchId($input, $urlType);
            break;
        case 'url':
            $res = $this->searchUrl($input);
            break;
        }
        return $res;
    }

    // 获取音频信息 - 关键词搜索
    protected function searchName($query, $site = 'netease') {
        if (!$query) {
            return;
        }

        switch ($site) {
        case '1ting':
            require 'class/lib/1ting.class.php';
            break;
        case 'baidu':
            require 'class/lib/baidu.class.php';
            break;
        case 'kugou':
            require 'class/lib/kugou.class.php';
            break;
        case 'kuwo':
            require 'class/lib/kuwo.class.php';
            break;
        case 'qq':
            require 'class/lib/qq.class.php';
            break;
        case 'xiami':
            require 'class/lib/xiami.class.php';
            break;
        case '5sing':
            require 'class/lib/5sing.class.php';
            break;
        case 'migu':
            require 'class/lib/migu.class.php';
            break;
        case 'lizhi':
            require 'class/lib/lizhi.class.php';
            break;
        case 'qingting':
            require 'class/lib/qingting.class.php';
            break;
        case 'ximalaya':
            require 'class/lib/ximalaya.class.php';
            break;
        case 'soundcloud':
            require 'class/lib/soundcloud.class.php';
            break;
        case 'netease':
        default:
            require 'class/lib/netease.class.php';
        }
        $songClass = new SongClass();
        $res = $songClass->getByNmae($query);
        if ($res == false) {
            return;
        }
        return $res;
    }
}