<?
$loader = require 'vendor/autoload.php';

$mode = '';

if(file_exists(__DIR__.'/env.live'))
{
   $mode = 'live';
}
else
{
   if(file_exists(__DIR__.'/env.local'))
   {
      $mode = 'local';
   }
}

require 'posts.php';

$app = new Slim(['templates.path' => __DIR__.'/views/', 'mode' => $mode]);
$app->view(new BlogView($app, 'template.php', $posts));
$env = $app->environment();

require 'routes.php';

$app->configureMode('live', function() use ($app, $env) {
   $env['URLBASE'] = 'http://nesbot.com';
   $env['URLIMG'] = '/img/';
   $env['URLFULLIMG'] = $env['URLBASE'] . '/img/';
   $env['URLCSS'] = '/css/';
   $env['URLJS'] = '/js/';
   $env['GATRACKER'] = 'UA-5684902-5';
   $app->config('debug', false);
});

$app->configureMode('local', function() use ($app, $env) {
   $env['URLBASE'] = 'http://127.0.0.1';
   $env['URLIMG'] = '/img/';
   $env['URLFULLIMG'] = $env['URLBASE'] . '/img/';
   $env['URLCSS'] = '/css/';
   $env['URLJS'] = '/js/';
   //$env['GATRACKER'] = '';
   $app->config('debug', true);
});

$app->run();