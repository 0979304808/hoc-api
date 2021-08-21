<?php

namespace App\Scraper;

use App\Http\Middleware\CheckUser;
use Carbon\Carbon;
use Goutte\Client;
use PHPOnCouch\CouchClient;

// scrape:NewsWeek
// DB newsweek
// _design : design
// _view : newsweek
// emit( doc.link, doc )


class NewsWeek extends CrawlerFunction
{
    const url = 'https://www.newsweek.com/world';
    const urli = 'https://www.newsweek.com';

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
        echo  Carbon::now('Asia/Ho_Chi_Minh')."\n";
        echo 'Xin chờ chút nhé ...' . "\n";
        $crawler = $this->get_content_html(self::url);
        if ($crawler !== false) {
            $this->get_news_list_home($crawler);
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
    private function get_news_list_home($crawler)
    {
        $total = 0;
        try {
            $crawler->filter('.content article')->each(function ($node) use (&$total) {
                $title = $node->filter('.image a')->count() ? $node->filter('.image a')->text() : null;
                $description = $node->filter('.summary')->count() ? $node->filter('.summary')->text() : null;
                $link = $node->filter('.image a')->count() ? $node->filter('.image a')->attr('href') : null;
                $img = $node->filter('.image img')->count() ? ($node->filter('.image img')->attr('src') == null ? $node->filter('.image img')->attr('data-src') : $node->filter('.image img')->attr('src')) : null;
                $data = [
                    'title' => $title,
                    'link' => $link,
                    'description' => $description,
                    'img' => $img
                ];
                if ($this->store_news($data)) {
                    printf($title) . "\n";
                    echo  Carbon::now('Asia/Ho_Chi_Minh')."\n";
                    $total++;
                }
            });
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        echo "Import $total news " . "\n";
    }

    // Get Page Details
    private function get_detail_news($url)
    {
        $news = $this->get_content_html($url);
        if ($news !== false) {
            $text = $news->filter('.article-body p')->each(function ($t) {
                return $t->text();
            });
            $image = $news->filter('figure img')->each(function ($im) {
                return $im->attr('src');
            });
            $url_audio = "https://readspeaker.jp/ASLCLCLVVS/JMEJSYGDCHMSMHSRKPJL/" . substr($this->get_audio($text), 5, -2);
            return [
                'body' => (implode(' ', $text)),
                'images' => ($news->filter('figure img')->count()) ? $image : null,
                'video' => isset($video) ? $video : null,
                'audio' => audio($url_audio),
                'date' => $news->filter('time')->count() ? $news->filter('time')->attr('datetime') : null,
                'author' => $news->filter('.author a')->count() ? $news->filter('.author a')->text() : null,
            ];
        }
        return null;
    }

    // Lưu vào nosql
    private function store_news(array $news)
    {
        if ($this->exists_news(self::urli . $news['link'])) {
            try {
                $detail = $this->get_detail_news(self::urli . $news['link']);
                if (!is_null($detail)) {

                    $data = [
                        'title' => CheckLevel($news['title']),
                        'link' => self::urli . $news['link'],
                        'description' => CheckLevel($news['description']),
                        'img' => $news['img'],
                        'author' => $detail['author'],
                        'date' => date_format(date_create($detail['date']), "Y/m/d H:i:s"),
                        'content' => [
                            'body' => CheckLevel($detail['body']),
                            'images' => $detail['images'],
                            'video' => $detail['video'],
                            'audio' => $detail['audio'],
                        ],
                        'type' => 'normal',
                    ];

                    $text = $news['title'] . ' ' . $news['description'] . ' ' . $detail['body'];
                    $data['level_Toeic'] = Level($text, 'Toeic');
                    $level_Toeic = isset($data['level_Toeic']) ? $data['level_Toeic'] : null;
                    if ($level_Toeic !== null) {
                        foreach ($level_Toeic as $key => $value) {
                            $data['Toeic'][$key] = count($value);
                        }
                    }


                    $data['level_Toefl'] = Level($text, 'Toefl');
                    $level_Toefl = isset($data['level_Toefl']) ? $data['level_Toefl'] : null;
                    if ($level_Toefl !== null) {
                        foreach ($level_Toefl as $key => $value) {
                            $data['Toefl'][$key] = count($value);
                        }
                    }

                    $data['level_Ielts'] = Level($text, 'Ielts');
                    $level_Ielts = isset($data['level_Ielts']) ? $data['level_Ielts'] : null;
                    if ($level_Ielts !== null) {
                        foreach ($level_Ielts as $key => $value) {
                            $data['Ielts'][$key] = count($value);
                        }
                    }

                    if ($this->couch->storeDoc((object)$data)) {
                        return true;
                    };
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . ' ---Line: ' . $e->getTrace()[0]['line'] . ' ---File: ' . $e->getTrace()[0]['file'] . "\n";
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