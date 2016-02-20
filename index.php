<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>RSS for Drama</title>
</head>
<body>
<div id="container">
<?php
require __DIR__ . '/Config.php';

$config = new Config();
$html = $config->toHtml('rss.php');
echo $html;
?>
</div>
</body>
</html>