<?php
$loader = require __DIR__.'/vendor/autoload.php';

$mode = 'live';

if (array_key_exists('MODE', $_SERVER)) {
   $mode = $_SERVER['MODE'];
} else {
   if (file_exists(__DIR__.'/env.live')) {
      $mode = 'live';
   } elseif (file_exists(__DIR__.'/env.local')) {
      $mode = 'local';
   }
}

$logWriter = null;

$app = new \Slim\Slim(array('templates.path' => __DIR__.'/views/', 'mode' => $mode));
$env = $app->environment();

$app->configureMode('live', function () use ($app, $env) {
   $env['URLBASE'] = 'http://nesbot.com';
   $env['URLIMG'] = '/img/';
   $env['URLFULLIMG'] = $env['URLBASE'] . $env['URLIMG'];
   $env['URLCSS'] = '/css/';
   $env['URLJS'] = '/js/';
   $env['GATRACKER'] = 'UA-5684902-5';
   $app->config('debug', false);

   $logWriter = new \Slim\Extras\Log\DateTimeFileWriter(array('path' => __DIR__.'/../logs'));
});

$app->configureMode('local', function () use ($app, $env) {
   $env['URLBASE'] = 'http://127.0.0.1';
   $env['URLIMG'] = '/img/';
   $env['URLFULLIMG'] = $env['URLBASE'] . $env['URLIMG'];
   $env['URLCSS'] = '/css/';
   $env['URLJS'] = '/js/';
   //$env['GATRACKER'] = '';
   $app->config('debug', true);

   $out = array();
   exec(sprintf("php %s/bundle.php", __DIR__), $out);

   if (count($out) > 1) {
      printf('<div><pre><code>%s</code></pre></div>', implode(PHP_EOL, $out));
   }

   $logWriter = new \Slim\Extras\Log\DateTimeFileWriter(array('path' => __DIR__.'/logs'));
});

$app->getLog()->setWriter($logWriter);

$posts = require 'posts.php';

$app->view(new BlogView($app, 'template.php', $posts));

require 'routes.php';

$app->run();
