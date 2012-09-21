<?/*Slim wildcard routes : Last but not least*/?>

<?$this->followUpTo('slim-wildcard-routes-improved')?>

<p>With the release of <a href="http://www.slimframework.com/read/version-165">Slim 1.6.5</a>, route wildcard parameters are now officially part of the framework.  Lets take at look at our past example and improve it again to use the baked in feature.  As a reminder the example URIs we want to parse into the array are:</p>

<pre><code class="bash">
http://hostname/api/getitems/seafood/fruit/meat
http://hostname/api/getitems/seafood
http://hostname/api/getitems/seafood/fruit/apples/bananas/chocolate
</code></pre>

<p>To indicate a route wildcard parameter we simply add a <code>+</code> after the name of the parameter, <code>/api/getitems/:items+</code>.  We can now specify our route as follows:</p>

<pre><code class="php">
$app->get('/api/getitems/:items+', function ($items) {
    var_dump($items);
});
</code></pre>

<p>The output is printed as before for the three urls from above:</p>

<pre><code class="bash">
array(3) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(4) "meat" }
array(1) { [0]=> string(7) "seafood" }
array(5) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(6) "apples" [3]=> string(7) "bananas" [4]=> string(9) "chocolate" }
</code></pre>

<p>I'll finish off with something you might be wondering.  You can specify additional parameters after the wildcard.  The only gotcha here is that they can't be optional or the wildcard will gobble them all up and always leave the optional as the default value.  I would describe this as expected behaviour.</p>

<pre><code class="php">
$app->get('/api/getitems/:items+/:name', function ($items, $name) {
    var_dump($items);
    var_dump($name);
});
</code></pre>

<p>The above route will not match <code>http://hostname/api/getitems/fruit</code> but will match <code>http://hostname/api/getitems/fruit/brian</code> and <code>http://hostname/api/getitems/seafood/fruit/meat/brian</code>.</p>