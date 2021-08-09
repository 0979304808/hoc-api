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

    protected function is_corona($string)
    {
        $regex = '(疫情|新型冠状病毒|肺炎疫情|确诊病例|死亡病例|接触者|重症病例|疑似病例|接种|疫苗|冠状病毒疫苗接种|新型冠状病毒|冠狀)';

        return preg_match($regex, $string);
    }

    protected function get_level($string)
    {
        $string = preg_replace('/(\n+\s+)/', '', $string);
        $result = [];
        $params = [
            'text' => $string,
            'type_data' => 'json'
        ];
        $response = curl_post(self::url_api_attach_level, $params);
        if ($response->status == 200) {
            foreach ($response->seg as $item) {
                $tocfl = is_null($item->lv_tocfl) ? 'unknown' : $item->lv_tocfl;
                $hsk = is_null($item->lv_hsk) ? 'unknown' : $item->lv_hsk;
                if (is_chinese($item->word)) {
                    $result['tocfl'][$tocfl][] = $item->word;
                    $result['hsk'][$hsk][] = $item->word;
                }
            }
            try {
                foreach ($result['tocfl'] as $key => $value) {
                    $result['tocfl'][$key] = array_values(array_unique($value));
                }
                foreach ($result['hsk'] as $k => $val) {
                    $result['hsk'][$k] = array_values(array_unique($val));
                }
            } catch (\Exception $e) {
                $this->output->writeln("Cannot unique array");
            }
        }
        return $result;
    }

    protected function attach_level($str)
    {
        $str = preg_replace('/(\n+\s+)/', '', $str);
        $params = [
            'text' => $str,
            'type_data' => 'html'
        ];
        $response = curl_post(self::url_api_attach_level, $params);
        if ($response->status == 200) {
            $str = $response->seg;
        }
        return $str;
    }

    protected function attach_pinyin($str)
    {
        $text = '';
        for ($i = 0; $i < mb_strlen($str, 'UTF-8'); $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            if ($char != '。' && $char != '，' && is_chinese($char)) {
                $pinyin = Pinyin::sentence($char, PINYIN_TONE);
                $text .= "<ruby>$char<rt>$pinyin</rt></ruby>";
            } else {
                $text .= $char;
            }
        }
        return $text;
    }

    protected function rmTagFont_FigureImg($str)
    {
        $regex_font = '/<font.*?>|<\/font>/';
        $regex_figure = '/<figure.+?<img src=(.|\n)+?><\/figure>/';
        $str = preg_replace($regex_font, '', $str);
        $str = preg_replace($regex_figure, '', $str);
        return $str;
    }
}