<?php
class ConfigItem
{
    public $title;
    public $url;
    public $class;

    public function __construct($cfg) {
        $this->title = $cfg['title'];
        $this->url = $cfg['url'];
        $this->class = $cfg['class'];
    }
}

class Config
{
    private $list;

    public function __construct() {
        $this->list = array(
            '1' => new ConfigItem(
                array(
                    'title' => '真田丸 Sanadamaru',
                    'url'   => 'http://f.ppxclub.com/668176-1-1',
                    'class' => 'PPX'
                )
            ),
            '31' => new ConfigItem(
                array(
                    'title' => '真田丸 Sanadamaru',
                    'url'   => 'http://www.okcili.com/list/1/cL2YFuYWRhbWFydSBwcHgO0O0O/1.html',
                    'class' => 'Okcili',
                )
            ),
            '2' => new ConfigItem(
                array(
                    'title' => 'お義父さんと呼ばせて ',
                    'url'   => 'http://www.suppig.net/forum.php?mod=viewthread&tid=1122161',
                    'class' => 'SUBPIG'
                )
            ),
            '61' => new ConfigItem(
                array(
                    'title' => 'Zhuixinfan (btcat)',
                    'url'   => 'http://www.btcat.net/search/zhuixinfan/',
                    'class' => 'BTcat'
                )
            ),
            '55' => new ConfigItem(
                array(
                    'title' => '真田丸 Sanadamau (PPX)',
                    'url'   => 'http://alicili.org/list/sanadamaru%20ppx/1-1-2/',
                    'class' => 'Alicili'
                )
            )
        );
    }

    public function get($id) {
        if (array_key_exists($id, $this->list)) {
            return $this->list[$id];
        }

        return null;
    }

    public function toHtml($prefix) {
        $lines = array();

        $lines[] = "<ul>";
        foreach ($this->list as $key => $item) {
            $title = $item->title;
            $class = $item->class;

            $li = "<li><a href=\"$prefix?id=$key\">$title ($class)</a></li>";
            $lines[] = $li;
        }
        $lines[] = "</ul>";

        return implode("\n", $lines);
    }
}
