<?php

namespace App\Scraper;

use Carbon\Carbon;
use Goutte\Client;
use PHPOnCouch\CouchClient;
use Symfony\Component\Console\Output\ConsoleOutput;

class NewsEditor extends CrawlerFunction
{

    private $client;
    private $couch;
    private $output;

    public function __construct()
    {
        $this->client = new Client();
        $this->output = new ConsoleOutput();
        $this->couch = new CouchClient('http://127.0.0.1:5984', 'newsweek', ['username' => 'admin', 'password' => 'hoanghung1']);
    }

    public function get_news_today()
    {
        $total = 0;
        $message = '';
        $newsID = '';
        $today = Carbon::now()->format('d/m/Y');
        $item = [
            'title' => 'bai viet 4',
            'link' => 'href',
            'description' => 'bai viet hn',
            'name_link' => 'name-link',
            'image' => 'image',
            'content' => 'dsadsa'
        ];
        $data = [
            "title" => $item['title']
        ];
        if (empty($this->couch->find((object)$data))) {
            $doc = $this->create_doc($item);
            $new = $this->couch->storeDoc((object)$doc);
            $this->output->writeln("Import $total news editor");

        }

        // push notification
        $hour = Carbon::now('Asia/Ho_Chi_Minh')->format('H');
        if ($total && $hour < 12) {
            push_fcm($message, $total, $newsID);
            $this->output->writeln("Push notification $total news easy");
        }
        return $total;
    }

    private function create_doc($news)
    {
        $doc = [
            'title' => $news['title'],
            'link' => $news['link'],
            'description' => $news['description'],
            'img' => "img",
            'author' => "Admin",
            'date' => Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d H:i'),
            'content' => [
                'body' => $news['content'],
                'images' => $news['image'],
                'video' => null,
                'audio' => "audio",
            ],
            'type' => 'normal'
        ];
        return $doc;
    }

    /**
     * Remove tag ruby and rt
     *
     * @return string
     */
    private function get_kanji_only($string)
    {
        $regex = '/<ruby.*?>(.+?)<rt.*?<\/rt>.*?<\/ruby>/';
        return strip_tags(preg_replace($regex, '$1', $string));
    }

    private function exists_news($link)
    {
        $doc = $this->couch->key($link)->getView('design', 'newsweek');
        return ($doc->rows) ? true : false;
    }
}