<?php
$loader = require 'vendor/autoload.php';

$mode = 'live';

if (file_exists(__DIR__.'/env.live')) {
   $mode = 'live';
} else {
   if (file_exists(__DIR__.'/env.local')) {
      $mode = 'local';
   }
}


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
   exec(sprintf("php %s/genposts.php", __DIR__), $out);

   if (count($out) > 1) {
      printf('<div><pre><code>%s</code></pre></div>', implode(PHP_EOL, $out));
   }
});

require 'posts.php';
$app->view(new BlogView($app, 'template.php', $posts));

require 'routes.php';

$app->run();
