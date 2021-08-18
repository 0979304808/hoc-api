<?php

namespace App\Scraper;

use Goutte\Client;
use PHPOnCouch\CouchClient;
use Symfony\Component\Console\Output\ConsoleOutput;

class NewsWeek extends CrawlerFunction
{
    const url = 'https://www.newsweek.com';

    private $client;
    private $couch;
    private $output;
    private $newsEditor;

    public function __construct()
    {
        $this->client = new Client();
        $this->output = new ConsoleOutput();
        $this->couch = new CouchClient('http://127.0.0.1:5984', 'newsweek', ['username' => 'admin', 'password' => 'hoanghung1']);
        $this->newsEditor = new NewsEditor();
    }

    public function scraper()
    {
        $crawler = $this->get_content_html(self::url);
        if ($crawler !== false) {
            $homes = $this->get_news_list($crawler);
            if ($homes) {
                foreach ($homes as $key => $news) {
                    $this->store_news($news);
                };
            }
        } else {
            $this->output->writeln("Cannot get news");
        }
    }

    private function get_content_html($url)
    {
        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        if ($response->getStatusCode() == 200) {
            return $crawler;
        }
        return false;
    }

    // Get Home
    private function get_news_list($crawler)
    {

        $total = 0;
        try {
            $a = $crawler->filter('.col4 .content .feature2 article')->each(function ($node) {
                return [
                    'title' => $node->filter('.image a')->text(),
                    'link' => $node->filter('.image a')->attr('href'),
                    'description' => $node->filter('.summary')->text(),
                    'img' => $node->filter('.image img')->attr('src')
                ];
            });
            return $a;
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
        }
        return $total;
    }

    // Get Details
    private function get_detail_news($url)
    {
        $news = $this->get_content_html($url);
        if ($news !== false) {
            $a = $news->filter('.content article ')->each(function ($node) {
                $text = $node->filter('.article-body p')->each(function ($t) {
                    return $t->text();
                });
                $audio = trim(implode(',', $text));
//                $saveUrl = asset("audio/".substr($this->get_audio($audio),5,-2));
                $url_audio = "https://readspeaker.jp/ASLCLCLVVS/JMEJSYGDCHMSMHSRKPJL/" . substr($this->get_audio($audio), 5, -2);
                return [
                    'body' => trim(implode(',', $text)),
                    'images' => [$node->filter('figure img')->attr('src')],
                    'video' => 'video',
                    'audio' => $url_audio,
                    'date' => $node->filter('time')->attr('datetime'),
                    'author' => $node->filter('.author a')->text()
                ];
            });
            return $a[0];
        }
        return null;
    }

    // Lưu vào nosql
    private function store_news(array $news)
    {

        // Check exists link
        if (!$this->exists_news(self::url . $news['link'])) {
            // Crawler detail news
            try {
                $detail = $this->get_detail_news(self::url . $news['link'], $news['title']);
                if (!is_null($detail)) {

                    $data = [
                        'title' => $news['title'],
                        'link' => self::url . $news['link'],
                        'description' => trim($news['description'], ' "" '),
                        'img' => $news['img'],
                        'author' => $detail['author'],
                        'date' => $detail['date'],
                        'content' => [
                            'body' => $this->get_kanji_only($detail['body']),
                            'images' => $detail['images'],
                            'video' => $detail['video'],
                            'audio' => $this->audio($detail['audio']),
                        ],
                        'type' => 'normal',
                    ];

                    $this->couch->storeDoc((object)$data);
                    print $news['title'] . "\n";
                }
            } catch (\Exception $e) {
                $this->output->writeln($e->getMessage());
                return false;
            }
        }
        return false;
    }

    private function exists_news($link)
    {
        $doc = $this->couch->key($link)->getView('design', 'newsweek');
        return ($doc->rows) ? true : false;
    }

    private function get_kanji_only($string)
    {
        $regex = '/<ruby.*?>(.+?)<rt.*?<\/rt>.*?<\/ruby>/';
        return strip_tags(preg_replace($regex, '$1', $string));
    }

    // Lưu Audio
    public function audio($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        // Lưu file ảnh
        $fullpath = basename($url);
        if (file_exists(public_path('audio/' . $fullpath))) {
            unlink(public_path('audio/' . $fullpath));
        }
        $fp = fopen(public_path('audio/' . $fullpath), 'x');
        fwrite($fp, $result);
        fclose($fp);
        return 'http://localhost:8000/audio/' . $fullpath;
    }

}