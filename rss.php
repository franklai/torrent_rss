<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/fujirou_common.php';
require __DIR__ . '/Config.php';
require __DIR__ . '/PPX.php';
require __DIR__ . '/SUBPIG.php';

Requests::register_autoloader();
use Ddeboer\Transcoder\Transcoder;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Item;

class RSS
{
    public static function output($conf, $infos)
    {
        $channel_title = "$conf->title ($conf->class)";

        $feed = new Feed();

        $channel = new Channel();
        $channel
            ->title($channel_title)
            ->url($conf->url)
            ->appendTo($feed);

        foreach ($infos as $info) {
            $item = new Item();
            $item
                ->title($info->title)
                ->url($conf->url)
                ->enclosure($info->enclosure['url'], $info->enclosure['size'], $info->enclosure['type'])
                ->pubDate($info->pubDate)
                ->appendTo($channel);
        }

        header('Content-Type: text/xml');
        echo $feed;
    }
}


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $config = new Config();
    $conf = $config->get($id);

    if (!$conf) {
        echo "wrong id: ".htmlspecialchars($id);
        exit;
    }

    $class = $conf->class;

    $parser = new $class();
    $infos = $parser->parse($conf->url);
    if (!$infos) {
        echo "Failed to parse";
        exit;
    }
    RSS::output($conf, $infos);
    exit;
}

echo "specify your id";
