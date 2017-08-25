<?php

class SongClass {
    protected $site = 'netease';
    public function getByNmae($query) {
        if (empty($query)) {
            return false;
        }
        $urls = $this->getSongUrls($query, 'query');
        if ($urls == false) {
            return $ralse;
        }
        $radio_result = curl($urls);

        if (empty($radio_result)) {
            return false;
        }

        $radio_data = json_decode($radio_result, true);
        if (empty($radio_data['result']) || empty($radio_data['result']['songs'])) {
            return;
        }
        foreach ($radio_data['result']['songs'] as $key => $val) {
            $songids[] = $val['id'];
        }
        return $this->getById($songids, true);
    }

    // 获取音频信息 - 歌曲ID
    public function getById($songids, $multi = false) {
        if (empty($songids)) {
            return false;
        }
        $radio_song_urls = array();
        if ($multi) {
            if (!is_array($songids)) {
                return false;
            }
            foreach ($songids as $key => $val) {
                $radio_song_urls[] = $this->getSongUrls($val, 'songid');
            }
        } else {
            $radio_song_urls[] = $this->getSongUrls($songids, 'songid');
        }
        if (empty($radio_song_urls) || !array_key_exists(0, $radio_song_urls)) {
            return;
        }
        $radio_result = array();
        foreach ($radio_song_urls as $key => $val) {
            $radio_result[] = curl($val);
        }
        if (empty($radio_result) || !array_key_exists(0, $radio_result)) {
            return;
        }
        $radio_songs = array();

        foreach ($radio_result as $key => $val) {
            $radio_data = json_decode($val, true);
            $radio_detail = $radio_data['songs'];
            if (!empty($radio_detail)) {
                $radio_song_id = $radio_detail[0]['id'];
                $radio_authors = array();
                foreach ($radio_detail[0]['artists'] as $key => $val) {
                    $radio_authors[] = $val['name'];
                }
                $radio_author = implode('/', $radio_authors);
                $radio_music_url = $radio_detail[0]['mp3Url'];
                if (!$radio_music_url) {
                    $radio_streams = array(
                        'method' => 'POST',
                        'url' => 'http://music.163.com/api/linux/forward',
                        'referer' => 'http://music.163.com/',
                        'proxy' => false,
                        'body' => encode_netease_data(array(
                            'method' => 'POST',
                            'url' => 'http://music.163.com/api/song/enhance/player/url',
                            'params' => array(
                                'ids' => array($radio_song_id),
                                'br' => 320000,
                            ),
                        )),
                    );
                    $radio_streams_info = json_decode(curl($radio_streams), true);
                    if (!empty($radio_streams_info)) {
                        $radio_music_url = $radio_streams_info['data'][0]['url'];
                    }
                }
                $radio_songs[] = array(
                    'type' => 'netease',
                    'link' => 'http://music.163.com/#/song?id=' . $radio_song_id,
                    'songid' => $radio_song_id,
                    'name' => $radio_detail[0]['name'],
                    'author' => $radio_author,
                    'music' => $radio_music_url,
                    'pic' => $radio_detail[0]['album']['picUrl'] . '?param=100x100',
                );
            }
        }
        return !empty($radio_songs) ? $radio_songs : '';
    }

    // 音频数据接口地址
    protected function getSongUrls($value, $type = 'query') {
        $hash = array('query', 'songid');
        if (!$value || !in_array($type, $hash)) {
            return false;
        }

        $arr = array(
            'query' => array(
                'method' => 'POST',
                'url' => 'http://music.163.com/api/linux/forward',
                'referer' => 'http://music.163.com/',
                'proxy' => false,
                'body' => encode_netease_data(array(
                    'method' => 'POST',
                    'url' => 'http://music.163.com/api/cloudsearch/pc',
                    'params' => array(
                        's' => $value,
                        'type' => '1',
                        'offset' => '0',
                        'limit' => '10',
                    ),
                )),
            ),
            'songid' => array(
                'method' => 'POST',
                'url' => 'http://music.163.com/api/linux/forward',
                'referer' => 'http://music.163.com/',
                'proxy' => false,
                'body' => encode_netease_data(array(
                    'method' => 'GET',
                    'url' => 'http://music.163.com/api/song/detail',
                    'params' => array(
                        'id' => $value,
                        'ids' => '[' . $value . ']',
                    ),
                )),
            ),
        );
        return $arr[$type];
    }

}