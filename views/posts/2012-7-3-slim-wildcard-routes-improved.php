<?/*Slim wildcard routes improved*/?>

<?$this->followUpTo('slim-wildcard-routes-via-route-middleware')?>

<p>The method used in the previous post was sufficient to get the job done but was not ideal as the resulting array was stored in the slim <code>environment</code>.  At the time it was the "best" spot to store it without introducing a new dependency.  Here is what I said in the previous post:</p>

<p class="quote">... this could be better if the route middleware was able to inject the new array back into the parameters rather than storing it into the environment. For now this is an easy solution to implement and is reuseable until the feature is added to the framework itself.</p>

<p>With the release of <a href="http://www.slimframework.com/read/version-164">Slim 1.6.4</a> the feature to <code>let the Slim_Route inject custom parameter values</code> was added.  We can use this to improve our wildcard routes example with a few simple changes.  Instead of using the <code>environment</code>, our array will be injected back into the parameters being passed directly to the route.  As a reminder the example URIs we want to parse into the array are:</p>

<pre><code class="bash">
http://hostname/api/getitems/seafood/fruit/meat
http://hostname/api/getitems/seafood
http://hostname/api/getitems/seafood/fruit/apples/bananas/chocolate
</code></pre>

<p>Here is the diff with the changes to our route middleware:</p>

<pre><code class="php">
-$parseWildcardToArray = function ($param_name) use ($app) &#123;
-   return function ($req, $res, $route) use ($param_name, $app) &#123;
+$parseWildcardToArray = function ($param_name) {
+   return function ($req, $res, $route) use ($param_name) {

-      $env = $app->environment();
-      $params = $route->getParams();
-
-      $env[$param_name.'_array'] = array();
-
-      //Is there a useful url parameter?
-      if (!isset($params[$param_name]))
-      {
-         return;
-      }
-
-      $val = $params[$param_name];
+      $val = $route->getParam($param_name);

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

-      $env[$param_name.'_array'] = $values;
+      $route->setParam($param_name, $values);
    };
 };
</code></pre>

<p>First we removed the <code>use</code> of <code>$app</code> for the closure since we don't need it anymore.  Next we simplified the code as we were able to remove lines 6-17.  We no longer need to access the environment.  The check for a value can be removed since we are only getting the specific value and our route condition <code>/.+/</code> ensures there is something in there.  Previously we were getting all route parameters as an associative array so we had a check to ensure the key existed and was set.  All of that is replaced by a simple <code>getParam()</code> call.  The final difference is how the modification takes place.  We now write it back into the route parameters directly rather than to the environment.</p>

<p>Here is our new route middleware:</p>

<pre><code class="php">
$parseWildcardToArray = function ($param_name) {
   return function ($req, $res, $route) use ($param_name) {

      $val = $route->getParam($param_name);

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

      $route->setParam($param_name, $values);
   };
};
</code></pre>

<p>You can see that our route is a little easier as the parsed array is now just in the parameter.</p>

<pre><code class="php">
$app->get('/api/getitems/:items', $parseWildcardToArray('items'), function ($items) {
    var_dump($items);
})->conditions(array('items' => '.+'));
</code></pre>

<p>As before, this just prints the following arrays for the three urls from above:</p>

<pre><code class="bash">
array(3) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(4) "meat" }
array(1) { [0]=> string(7) "seafood" }
array(5) { [0]=> string(7) "seafood" [1]=> string(5) "fruit" [2]=> string(6) "apples" [3]=> string(7) "bananas" [4]=> string(9) "chocolate" }
</code></pre>

<?$this->linkPost("slim-wildcard-routes-last-but-not-least", function ($url, $title) {?>
   <p class="quote">Read the last follow up to see it integrated into Slim in conjuction with the 1.6.5 release... <a href="<?=$url?>"><?=$title?></a></p>
<?});?>