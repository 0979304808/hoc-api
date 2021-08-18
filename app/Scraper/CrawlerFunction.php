<?php

namespace App\Scraper;

use Pinyin;

class CrawlerFunction
{

    const url_api_get_audio = 'https://readspeaker.jp/tomcat/servlet/vt';

    protected function get_audio($text){
        $params = "text=$text&talkID=103&volume=100&speed=100&pitch=100&feeling=2&dict=0";
        $response = curl_post(self::url_api_get_audio, $params);
        if($response){
            return $response ;
        }
        return null;
    }
}