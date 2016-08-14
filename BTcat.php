<?php
require __DIR__ . '/vendor/autoload.php';

Requests::register_autoloader();

// http://www.btcat.net/magnet/detail/22888713
class BTcatItem {
    public $title = '';
    public $url = '';
    public $pubDate = '';
    public $enclosure = array('url'=> '', 'size'=> 0, 'type'=> 'application/x-bittorrent');
    public $guid = '';

    public function __construct($rawString) {
        $this->parse($rawString);
    }

    private function parse($s) {
        $this->parseTitle($s);
        $this->parsePubDate($s);
        $this->parseEnclosure($s);
        $this->parseLink($s);

        $this->guid = $this->link;
    }

    private function parseTitle($s) {
        $pattern = '/<a title=\"(.*?)\".*?>.*?<\/a>/';
        $this->title = strip_tags(FujirouCommon::getFirstMatch($s, $pattern));
    }
    private function parsePubDate($s) {
        $pattern = '/ date"> (.*?)<\/div>/';
        $value = FujirouCommon::getFirstMatch($s, $pattern);
        // 2016年8月14日 15:31
        $timezone = new DateTimeZone('Asia/Taipei');
        $date = DateTime::createFromFormat('Y年n月j日 H:i', $value, $timezone);
        $this->pubDate = $date->getTimestamp();
    }
    private function parseEnclosure($s) {
        $pattern = '/ href=\"(\/magnet\/.*?)\">.*?<\/a>/';
        $page = FujirouCommon::getFirstMatch($s, $pattern);
        $uri = $_SERVER['REQUEST_URI'];
        $host = $_SERVER['HTTP_HOST'];
        $this->enclosure['url'] = "http://$host$uri&page=$page";

        $pattern = '/ size\"> (.*?)<\/div>/';
        $raw_size = FujirouCommon::getFirstMatch($s, $pattern);
        $raw_size = str_replace("\xc2\xa0", " ", $raw_size);

        $size = FujirouCommon::convertSize($raw_size);
        $this->enclosure['size'] = $size;
    }
    private function parseLink($s) {
        $pattern = '/ href=\"(\/magnet\/.*?)\">.*?<\/a>/';
        $path = FujirouCommon::getFirstMatch($s, $pattern);
        $this->url = "http://www.btcat.net$path";
    }
}

// http://www.btcat.net/search/zhuixinfan/
class BTcat {
    private $site = '';

    public function __construct() {
    }

    public function prepare($url) {
        try {
            $req = Requests::get($url);
        } catch (Exception $e) {
            return null;
        }

        $html = $req->body;

        return $html;
    }

    public function parse($url) {
        $html = $this->prepare($url);

        if (!$html) {
            return null;
        }

        $html = FujirouCommon::toOneLine($html);

        $pattern = "/<div class=\"x-item row\">(.*? date\">.*?<\/div>)/";
        $matches = FujirouCommon::getAllFirstMatch($html, $pattern);
        
        $infos = array();

        foreach ($matches as $item) {
            $info = new BTcatItem($item);

            if ($info) {
                $infos[] = $info;
            }
        }

        return $infos;
    }

    public function parsePage($id) {
        // validate $id
        // http://www.btcat.net/magnet/detail/22852600
        // $id will be "/magnet/detail/22852600"
        $pattern = '/^(\/magnet\/detail\/[0-9]+)$/';
        if (!FujirouCommon::getFirstMatch($id, $pattern)) {
            exit;
        }

        $url = "http://www.btcat.net$id";
        $html = $this->prepare($url);
        if (!$html) {
            exit;
        }

        $pattern = '/<a href=\"(magnet:.*?)\"/';
        $magnet = FujirouCommon::getFirstMatch($html, $pattern);
        if (!$magnet) {
            return null;
        }

        return FujirouCommon::decodeHTML($magnet);
    }
}

