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
        echo "Xin chờ chút nhé ..."."\n";
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

    // Get Page Home
    private function get_news_list($crawler)
    {

        $total = 0;
        try {
            $a = $crawler->filter('.col4 .content article')->each(function ($node) {
                return [
                    'title' => $node->filter('.image a')->count() ? $node->filter('.image a')->text() : null,
                    'link' => $node->filter('.image a')->count() ? $node->filter('.image a')->attr('href') : null,
                    'description' => $node->filter('.summary')->count() ? $node->filter('.summary')->text() : null,
                    'img' => $node->filter('.image img')->count() ? $node->filter('.image img')->attr('src') : null
                ];
            });
            return $a;
        } catch (\Exception $e) {
            $this->output->writeln($e->getMessage());
        }
        return $total;
    }

    // Get Page Details
    private function get_detail_news($url)
    {
        $news = $this->get_content_html($url);
        if ($news !== false) {
            $a = $news->filter('.content article ')->each(function ($node) {
                $text = $node->filter('.article-body p')->each(function ($t) {
                    return $t->text();
                });
                $url_audio = "https://readspeaker.jp/ASLCLCLVVS/JMEJSYGDCHMSMHSRKPJL/" . substr($this->get_audio($text), 5, -2);
                return [
                    'body' => trim(implode(',', $text)),
                    'images' => [($node->filter('figure img')->count()) ? $node->filter('figure img')->attr('src') : null],
                    'video' => 'video',
                    'audio' => $url_audio,
                    'date' => $node->filter('time')->count() ? $node->filter('time')->attr('datetime') : null,
                    'author' => $node->filter('.author a')->count() ? $node->filter('.author a')->text() : null,
                ];
            });
            return $a[0];
        }
        return null;
    }

    // Lưu vào nosql
    private function store_news(array $news)
    {
        if (!$this->exists_news(self::url . $news['link'])) {
            try{
                $detail = $this->get_detail_news(self::url . $news['link'], $news['title']);
                if (!is_null($detail)) {
                    $data = [
                        'title' => CheckRG($news['title']) ,
                        'link' => self::url . $news['link'],
                        'description' => CheckRG(trim($news['description'], ' "" ')) ,
                        'img' => $news['img'],
                        'author' => $detail['author'],
                        'date' => $detail['date'],
                        'content' => [
                            'body' => json_encode(CheckRG(($detail['body']))),
                            'images' => $detail['images'],
                            'video' => $detail['video'],
                            'audio' => audio($detail['audio']),
                        ],
                        'type' => 'normal',
                        'level_ielts' => Level($news['title'].$news['description'].$detail['body'],'Ielts'),
                        'level_toefl' => Level($news['title'].$news['description'].$detail['body'],'Toefl'),
                        'level_toeic' => Level($news['title'].$news['description'].$detail['body'],'Toeic'),
                    ];

                    $this->couch->storeDoc((object)$data);
                    print $news['title'] . "\n";
                }
            }catch (\Exception $e){
                echo $e->getMessage().' ---Line: '.$e->getTrace()[0]['line'].' ---File: '.$e->getTrace()[0]['file']."\n";die;
            }
        }
        return false;
    }

    private function exists_news($link)
    {
        $doc = $this->couch->key($link)->getView('design', 'newsweek');
        return ($doc->rows) ? true : false;
    }

}