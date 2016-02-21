<?php
require __DIR__ . '/vendor/autoload.php';

Requests::register_autoloader();
use Ddeboer\Transcoder\Transcoder;

class OkciliItem {
    public $title = '';
    public $url = '';
    public $pubDate = '';
    public $enclosure = array('url'=> '', 'size'=> 0, 'type'=> 'application/x-bittorrent');
    public $guid = '';

    private $link_prefix = 'http://www.okcili.com/';

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
        $pattern = '/<a href=\'\/infos\/.*?\'.*?>(.*?)<\/a>/';
        $this->title = FujirouCommon::getFirstMatch($s, $pattern);
    }
    private function parsePubDate($s) {
        $pattern = '/时间:<label>(.*?)<\/label>/';
        $date = FujirouCommon::getFirstMatch($s, $pattern);
        $this->pubDate = strtotime($date);
    }
    private function parseEnclosure($s) {
        $pattern = '/<a href=\'(magnet:.*?)\'.*?>磁力链接<\/a>/';
        $this->enclosure['url'] = FujirouCommon::getFirstMatch($s, $pattern);

        $pattern = '/大小:<label>(.*?)<\/label>/';
        $raw_size = FujirouCommon::getFirstMatch($s, $pattern);

        $this->enclosure['size'] = FujirouCommon::convertSize($raw_size);
    }
    private function parseLink($s) {
        $pattern = '/<a href=\'(\/infos\/.*?)\'.*?>.*?<\/a>/';
        $path = FujirouCommon::getFirstMatch($s, $pattern);
        $this->link = $this->link_prefix . $path;
    }
}

class Okcili {
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

        $pattern = '/<li>(.*?)<\/li>/';
        $matches = FujirouCommon::getAllFirstMatch($html, $pattern);
        
        $infos = array();

        foreach ($matches as $item) {
            $info = new OkciliItem($item);

            if ($info) {
                if (strpos($info->enclosure['url'], 'magnet:') === false) {
                    // not torrent link
                    continue;
                }
                $infos[] = $info;
            }
        }

        return $infos;
    }
}

