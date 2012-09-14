<?/*PHP on a diet : Up and running with Slim*/?>

<p>It has been said that if there are <code>n</code> PHP developers in the world then there are <code>n+1</code> PHP frameworks.  While that is most likely undeniably true, I have been perusing the so-called micro frameworks lately of which most are inspired (at least in part) by <a href="http://www.sinatrarb.com/">sinatra</a>.  I tend to enjoy working with the micro frameworks as they typically don't get in your way but just gently get you going.  They don't force a whole stack on you as you generally select your best components (view engine, datastore, etc) and piece them together.  For me it helps to quickly gain a good understanding of the framework and its inner workings and makes it easier to go through its source since its not trying to be everything to everyone. On the <a href="http://www.slimframework.com/">Slim</a> homepage it even says, and I believe it to be true so far... </p>

<p class="quote">"The Slim micro framework is everything you need and nothing you don't."</p>

<p>Up until recently getting a PHP site running locally required the use of apache (using wammp/xampp), nginx or some other local webserver.  With the release of 5.4 a development server is now included in PHP and makes using it locally a breeze.  Even though it was introduced in 2010 I recently came across the Slim Framework.  This post will quickly get you up and running with Slim using <a href="http://getcomposer.org/">composer</a> and the new internal development server.</p>

<pre class="brush: bash">
mkdir slimapp
cd slimapp
curl -s http://getcomposer.org/installer | php
</pre>

<p>Next create a <code>composer.json</code> file in the web root that indicates your dependency on Slim:</p>

<pre class="brush: jscript">
{
   "require": {"slim/slim": "1.6.*"}
}
</pre>

<p>Next we kick off the install of Slim via composer and start the development server:</p>

<pre class="brush: bash">
php composer.phar install
php -S 127.0.0.1:80
</pre>

<p>There is a nice feature in the PHP 5.4+ development server that helps us out when using friendly urls.</p>

<p class="quote">If a URI request does not specify a file, then either index.php or index.html in the given directory are returned.</p>

<p>As long as we use friendly urls and name our bootstrap file <code>index.php</code> then we can start the development server in our web root and it will all just work.  For those of you who want to use <code>app.php</code>, <code>router.php</code> or something else, it also has the ability to accept another command line parameter to act as a router script.  You can read more about it <a href="http://php.net/manual/en/features.commandline.webserver.php">here</a>.</p>

<p>Finally, create an <code>index.php</code> with the following contents:</p>

<pre class="brush: php">
&lt;?
require 'vendor/autoload.php';
$app = new Slim();

$app->get('/', function () {
   echo "Hello World!";
});

$app->run();
</pre>

<p>Then goto <code>http://127.0.0.1</code> in your browser and you should see <code>Hello World!</code>.</p>

<p>You can expect more from me on using Slim and you can read more about this micro framework <a href="http://www.slimframework.com/">here</a>.</p>