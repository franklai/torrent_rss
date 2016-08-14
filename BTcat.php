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
        $pattern = '/<a title=\"(.*)\".*?>.*?<\/a>/';
//         var_dump(FujirouCommon::getFirstMatch($s, $pattern));
        $this->title = strip_tags(FujirouCommon::getFirstMatch($s, $pattern));
    }
    private function parsePubDate($s) {
        $pattern = '/ date"> (.*?)<\/div>';
        $date = FujirouCommon::getFirstMatch($s, $pattern);
        $this->pubDate = strtotime($date);
    }
    private function parseEnclosure($s) {
        $pattern = '/ href=\"(\/magnet\/.*?)\">.*?<\/a>/';
        $this->enclosure['url'] = FujirouCommon::getFirstMatch($s, $pattern);

        $pattern = '/ size\"> (.*?)<\/div>/';
        $raw_size = FujirouCommon::getFirstMatch($s, $pattern);

        $this->enclosure['size'] = FujirouCommon::convertSize($raw_size);
    }
    private function parseLink($s) {
        $pattern = '/ href=\"(\/magnet\/.*?)\">.*?<\/a>/';
        $path = FujirouCommon::getFirstMatch($s, $pattern);
        $this->url = $path;
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

        $pattern = "/<div class=\"x-item row\">""(.*? date\">.*?)<\/div>/";
        $matches = FujirouCommon::getAllFirstMatch($html, $pattern);
        
        $infos = array();

        foreach ($matches as $item) {
            $info = new BTkissItem($item);

            if ($info) {
//                 if (strpos($info->enclosure['url'], 'magnet:') === false) {
//                     // not torrent link
//                     continue;
//                 }
                $infos[] = $info;
            }
        }

        return $infos;
    }
}

