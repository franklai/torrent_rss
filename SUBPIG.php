<?php
require __DIR__ . '/vendor/autoload.php';

Requests::register_autoloader();
use Ddeboer\Transcoder\Transcoder;

class SUBPIGItem {
    public $title = '';
    public $url = '';
    public $pubDate = '';
    public $enclosure = array('url'=> '', 'size'=> 0, 'type'=> 'application/x-bittorrent');

    private $download_prefix = 'http://www.suppig.net/';

    public function __construct($rawString) {
        $this->parse($rawString);
    }

    private function parse($s) {
        $this->parseTitle($s);
        $this->parsePubDate($s);
        $this->parseEnclosure($s);
    }

    private function parseTitle($s) {
        $pattern = '/<a href=".*?".*?>(.*?)<\/a>/';
        $this->title = FujirouCommon::getFirstMatch($s, $pattern);
    }
    private function parsePubDate($s) {
        $pattern = '/<div class="y"><span title="(.*?)">/';
        $date = FujirouCommon::getFirstMatch($s, $pattern);
        if (!$date) {
            $pattern = '/<div class="y">(.*?) 上传<\/div>/';
            $date = FujirouCommon::getFirstMatch($s, $pattern);
        }
        $this->pubDate = strtotime($date);
    }
    private function parseEnclosure($s) {
        $pattern = '/<a href="(.*?)".*?>.*?<\/a>/';
        $this->enclosure['url'] = $this->download_prefix . htmlspecialchars_decode(FujirouCommon::getFirstMatch($s, $pattern));

        $pattern = '/<em class="xg1">\(([0-9\.]+) KB,/';
        $size = FujirouCommon::getFirstMatch($s, $pattern);

        $this->enclosure['size'] = round(floatval($size) * 1024);
    }
}

class SUBPIG {
    private $site = '';

    public function __construct() {
    }

    public function prepare($url) {
        try {
            $req = Requests::get($url);
        } catch (Exception $e) {
            return null;
        }

        $transcoder = Transcoder::create();
        $html = $transcoder->transcode($req->body, 'gbk');

        return $html;
    }

    public function parse($url) {
        $html = $this->prepare($url);

        if (!$html) {
            return null;
        }

        $html = FujirouCommon::toOneLine($html);

        $pattern = '/<ignore_js_op>(.*?)<\/ignore_js_op>/';
        $matches = FujirouCommon::getAllFirstMatch($html, $pattern);

        
        $infos = array();

        foreach ($matches as $item) {
            $info = new SUBPIGItem($item);

            if ($info) {
                if (strpos($info->title, '.torrent') === false) {
                    // not torrent link
                    continue;
                }
                $infos[] = $info;
            }
        }

        $infos = array_reverse($infos);

        return $infos;
    }
}

