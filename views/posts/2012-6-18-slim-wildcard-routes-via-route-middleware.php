<?/*Slim wildcard routes via route middleware*/?>

<p>As I dig further into the <a href="http://slimframework.com">Slim framework</a> I started reviewing some of the community forum questions.  A fun way to both learn and help is to answer questions from users.  I think its one of the best ways to contribute back as it helps increase the audience of the framework.  It also aleviates the main author from being the only one having to do it all the time.</p>

<p>This morning I read the following question about a wildcard catchall route, <a href="http://help.slimframework.com/discussions/questions/230-can-i-have-a-get-request-with-variable-number-of-parameters-in-the-url">Can I have a GET request with variable number of parameters in the url?</a>  It seems this is something the framework currently doesn't support but is potentially slated for a 1.7 release.  In the meantime I thought a simple solution might be to use route conditions in combination with route middleware to parse the incoming parameter into an array for us.  Lets see how we can get this done in a generic reusable fashion.</p>

<p>If you don't know what <a href="http://www.slimframework.com/documentation/stable#routing-conditions">route conditions</a> and <a href="http://www.slimframework.com/documentation/stable#routing-middleware">route middleware</a> are for the Slim framework, I suggest you go and read about them first.</p>

<p>The GET request the user wants to parse is <code>http://hostname/api/getitems/seafood/fruit/meat</code>.  They want to get the <code>seafood</code>, <code>fruit</code> and <code>meat</code> part as an array.  Those parameters are also wildcard in length which means there are a variable number of them, so it might be shorter like <code>http://hostname/api/getitems/seafood</code> or longer like <code>http://hostname/api/getitems/seafood/fruit/apples/bananas/chocolate</code>.</p>

<p>We can start by setting up our route and applying a catch all regular expression to the route conditions.</p>

<pre class="brush: php">
$app->get('/api/getitems/:items', function ($items) use ($app) {
   echo $items;
})->conditions(['items' => '.+']);
</pre>

<p>When this route is matched the <code>$items</code> parameter will be a string that contains the remainder of the GET request URL.  If we were to load the urls from earlier we would get the following output:</p>

<pre class="brush: plain">
seafood/fruit/meat
seafood
seafood/fruit/apples/bananas/chocolate
</pre>

<p>Since we used a <code>+</code> in our condition regular expression we don't have to worry about handling a blank string as that won't match our route.  So no we can use a route middleware to perform the <code>explode</code> on our string.  We use PHP's closure support to wrap an anonymous function while passing in the name of the parameter we want parsed.  In our example that name is <code>items</code> as seen above.</p>

<p>We need access to the current route that was matched to read it's parameters.  This didn't seem to be easily done except after looking at the source for the <code>Slim_Route</code> I saw that the route middleware is called with three parameters.  The <code>Slim_Http_Request</code>, <code>Slim_Http_Response</code> and the currently matched <code>Slim_Route</code>.</p>

<p>So here is our middleware callable.</p>

<pre class="brush: php">
$parseWildcardToArray = function ($param_name) use ($app) {
   return function ($req, $res, $route) use ($param_name, $app) {

      $env = $app->environment();
      $params = $route->getParams();

      $env[$param_name.'_array'] = array();

      //Is there a useful url parameter?
      if (!isset($params[$param_name]))
      {
         return;
      }

      $val = $params[$param_name];

      //Handle  /api/getitems/seafood//fruit////meat
      if (strpos($val, '//') !== false)
      {
         $val = preg_replace("#//+#", "/", $val);
      }

      //Remove the last slash
      if (substr($val, -1) === '/')
      {
         $val = substr($val, 0, strlen($val) - 1);
      }

      //explode or create array depending if there are 1 or many parameters
      if (strpos($val, '/') !== false)
      {
         $values = explode('/', $val);
      }
      else
      {
         $values = array($val);
      }

      $env[$param_name.'_array'] = $values;
   };
};
</pre>

<p>Its more preventitive code than real code.  The comments above indicate the checks in place.</p>

<p>From what I can tell from the Slim source there is no way to inject a new parameter back into the <code>$route->params</code>.  So for now this code injects the newly parsed array into an environment variable for the route to read.  With this in place we can now augment our route to use the middleware and access the newly created array via the environment.</p>

<pre class="brush: php">
$app->get('/api/getitems/:items', $parseWildcardToArray('items'), function ($items) use ($app) {
   $env = $app->environment();
   var_dump($env['items_array']);
})->conditions(['items' => '.+']);
</pre>

<p>This now prints the following arrays for the same three urls as before:</p>

<pre class="brush: plain">
array(3) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(4) "meat" }
array(1) { [0]=> string(7) "seafood" }
array(5) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(6) "apples" [3]=> string(7) "bananas" [4]=> string(9) "chocolate" }
</pre>

<p>Also, if you are using PHP 5.4+ and what to make use of array dereferencing you can change the above route to be one line like so:</p>

<pre class="brush: php">
$app->get('/api/getitems/:items', $parseWildcardToArray('items'), function ($items) use ($app) {
   var_dump($app->environment()['items_array']);
})->conditions(['items' => '.+']);
</pre>

<p>As mentioned this could be better if the route middleware was able to inject the new array back into the parameters rather than storing it into the <code>environment</code>.  For now this is an easy solution to implement and is reuseable until the feature is added to the framework itself.</p>

<?$this->linkPost("slim-wildcard-routes-improved", function ($url, $title) {?>
   <p class="quote">Read the follow up to see how it was improved in conjuction with the 1.6.4 release... <a href="<?=$url?>"><?=$title?></a></p>
<?});?>