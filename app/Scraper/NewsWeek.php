<?php

namespace App\Scraper;

use Goutte\Client;
use PHPOnCouch\CouchClient;
use Symfony\Component\Console\Output\ConsoleOutput;

// scrape:NewsWeek
// DB newsweek
// _design : design
// _view : newsweek
// emit( doc.link, doc )


class NewsWeek extends CrawlerFunction
{
    const url = 'https://www.newsweek.com';

    private $client;
    private $couch;
    private $output;

    public function __construct()
    {
        $this->client = new Client();
        $this->couch = new CouchClient(config('couch.host'), config('couch.db'), ['username' => config('couch.username'), 'password' => config('couch.password')]);
    }

    public function scraper()
    {
        echo "Xin chờ chút nhé ..."."\n";
        $crawler = $this->get_content_html(self::url);
        if ($crawler !== false) {
            $this->get_news_list($crawler);
        } else {
            $this->output->writeln("Cannot get news");
        }
    }

    // Lấy dữ liệu website theo url
    private function get_content_html($url)
    {
        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        if ($response->getStatusCode() == 200) {
            return $crawler;
        }
        return false;
    }

    // Get Page Home
    private function get_news_list($crawler)
    {
        $total = 0;
        try {
            $crawler->filter('.col4 .content article')->each(function ($node) use (&$total) {
                $data =  [
                    'title' => $node->filter('.image a')->count() ? $node->filter('.image a')->text() : null,
                    'link' => $node->filter('.image a')->count() ? $node->filter('.image a')->attr('href') : null,
                    'description' => $node->filter('.summary')->count() ? $node->filter('.summary')->text() : null,
                    'img' => $node->filter('.image img')->count() ? $node->filter('.image img')->attr('src') : null
                ];
                $insert = $this->store_news($data);
                ($insert) ? $total++ : null;

            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        echo "Import $total news "."\n";
    }

    // Get Page Details
    private function get_detail_news($url)
    {
        $news = $this->get_content_html($url);
        if ($news !== false) {
            $details = $news->filter('.content article ')->each(function ($node) {
                $text = $node->filter('.article-body p')->each(function ($t) {
                    return $t->text();
                });

                $image = $node->filter('figure img')->each(function ($im){
                    return $im->attr('src');
                });
                $url_audio = "https://readspeaker.jp/ASLCLCLVVS/JMEJSYGDCHMSMHSRKPJL/" . substr($this->get_audio($text), 5, -2);
                return [
                    'body' => implode(' ', $text),
                    'images' => ($node->filter('figure img')->count()) ? $image : null,
                    'video' => isset($video) ? $video : null,
                    'audio' => $url_audio,
                    'date' => $node->filter('time')->count() ? $node->filter('time')->attr('datetime') : null,
                    'author' => $node->filter('.author a')->count() ? $node->filter('.author a')->text() : null,
                ];
            });
            return $details[0];
        }
        return null;
    }

    // Lưu vào nosql
    private function store_news(array $news)
    {
        if ($this->exists_news(self::url . $news['link'])) {
            try{
                $detail = $this->get_detail_news(self::url . $news['link'], $news['title']);
                dd( explode(' ',$detail['body']));
                if (!is_null($detail)) {
                    $data = [
                        'title' => CheckLevel($news['title']) ,
                        'link' => self::url . $news['link'],
                        'description' => CheckLevel($news['description']) ,
                        'img' => $news['img'],
                        'author' => $detail['author'],
                        'date' => date_format(date_create($detail['date']),"Y/m/d H:i:s"),
                        'content' => [
                            'body' =>  CheckLevel($detail['body']) ,
                            'images' => $detail['images'],
                            'video' => $detail['video'],
                            'audio' => audio($detail['audio']),
                        ],
                        'type' => 'normal',
                        'level_ielts' => Level($news['title']." ".$news['description']." ".$detail['body'],'Ielts'),
                        'level_toefl' => Level($news['title']." ".$news['description']." ".$detail['body'],'Toefl'),
                        'level_toeic' => Level($news['title']." ".$news['description']." ".$detail['body'],'Toeic'),
                    ];

                    if ($this->couch->storeDoc((object)$data)){
                        print $news['title'] . "\n";
                        return true;
                    };
                }
            }catch (\Exception $e){
                echo $e->getMessage().' ---Line: '.$e->getTrace()[0]['line'].' ---File: '.$e->getTrace()[0]['file']."\n";
            }
        }
    }

    // Check document đã tồn tại hay chưa bằng link
    private function exists_news($link)
    {
        $doc = $this->couch->key($link)->getView('design', 'newsweek');
        return ($doc->rows) ? false : true;
    }

}