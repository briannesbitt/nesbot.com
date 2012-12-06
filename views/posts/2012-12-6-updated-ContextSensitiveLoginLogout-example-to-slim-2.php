<?/*Updated ContextSensitiveLoginLogout example to Slim 2.x*/?>

<p>A question, awhile back, on the <a href="http://help.slimframework.com/discussions/questions/341-loginlogout-with-redirect">Slim discussion forum</a> asked how to create a login/logout workflow with a redirect to the originally requested page on login.  I had created an example to show this using Slim 1.6.x.  Recently I updated the example to work with the 2.x version of Slim.  Lets review the changes that were applied to make this happen.</p>

<p>The example can be seen here <a href="https://github.com/briannesbitt/Slim-ContextSensitiveLoginLogout">https://github.com/briannesbitt/Slim-ContextSensitiveLoginLogout</a></p>

<h2>Namespaces</h2>

<p>The Slim 2.x branch added <a href="http://www.phptherightway.com/#namespaces">namespaces</a>.  This means we need to update our Slim references with the equivalent namespaced version.  There are two ways we can do this.  First lets see how it was done in our example.  The following is the <a href="https://github.com/briannesbitt/Slim-ContextSensitiveLoginLogout/commit/a06f78fffaecf57c39a6e249ac48651dce170643#index.php">diff listing for our commit</a> on the index.php file.</p>

<pre><code class="php">
&lt;?php
require 'vendor/autoload.php';

- $app = new Slim();
+ $app = new \Slim\Slim();

- $app->add(new Slim_Middleware_SessionCookie(array('secret' => 'myappsecret')));
+ $app->add(new \Slim\Middleware\SessionCookie(array('secret' => 'myappsecret')));
</code></pre>

<p>What you see above are the only code changes that were necessary to make this work with Slim 2.x.  The other changes in the commit were to the readme and composer.json file.</p>

<p>The second method we could have implemented is to introduce the usage of the <a href="http://php.net/manual/en/language.namespaces.importing.php">PHP keyword use</a>.  Importing the namespaces with the <code>use</code> keyword at the top of the file means we don't have to supply the full namespaced class when actually using and referencing the class.  Lets see how that would have been done.</p>

<pre><code class="php">
&lt;?php
require 'vendor/autoload.php';

+ use Slim\Slim;
+ use Slim\Middleware\SessionCookie;

$app = new Slim();

- $app->add(new Slim_Middleware_SessionCookie(array('secret' => 'myappsecret')));
+ $app->add(new SessionCookie(array('secret' => 'myappsecret')));
</code></pre>

<p>Adding the <code>use</code> imports at the top prevents us from having to add <code>\Slim\</code> in front of all of the Slim class references.  Our example is simple so in this instance, I think either method is fine, but in a longer example you can see how using <code>use</code> might be the better option, however making the code less explicit.</p>

<p>This is a simple example so as we discussed the code changes were very minimal. You can refer to the following sites to see what else changed and further tips on upgrading:</p>

<p>
   <a href="http://slimframework.com/news/moving-forward">http://slimframework.com/news/moving-forward</a><br/>
   <a href="http://slimframework.com/news/version-2">http://slimframework.com/news/version-2</a><br/>
   <a href="http://help.slimframework.com/kb/upgrading/upgrading-to-slim-2">http://help.slimframework.com/kb/upgrading/upgrading-to-slim-2</a><br/>
</p>