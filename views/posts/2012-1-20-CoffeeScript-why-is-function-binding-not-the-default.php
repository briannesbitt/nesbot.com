<?/*CoffeeScript : Why is function binding not the default?*/?>

<p>As of a few days ago I started to play around with <a href="http://coffeescript.org/" target="_blank">CoffeeScript</a> and <a href="http://nodejs.org/" target="_blank">node.js</a>.  Sure I have used JavaScript in the past, like everyone else, but I have never used it enough or taken the time to fully understand the commonly misunderstood scope / context / prototype aspects of the language.  If you don't yet understand it, I direct you to the plethera of <a href="http://www.digital-web.com/articles/scope_in_javascript/" target="_blank">articles</a> about the topic.</p>

<p>I wanted to talk about the syntax subtlety of <code>-></code> vs <code>=></code>.  The CoffeeScript site briefly mentions it as <a href="http://coffeescript.org/#fat_arrow" target="_blank">function binding</a>, aka the Fat Arrow.  Using <code>=></code> ensures that the <code>this</code> context is always the same as when the function was defined, rather than in the context of the object it is currently attached to.  As the site mentions the fat arrow is particullary helpful when using callbacks, of which you will be using a "few" ;).  I think this is probably the most common mistake when first programming JavaScript / CoffeeScript.  You make a function call passing a callback which references <code>this</code> and just expect it to work... surprise!</p>

<p>Its a very subtle difference and most resources skim over the subject or simply ignore it. The free book, <a href="http://arcturo.github.com/library/coffeescript/index.html" target="_blank">The Little Book on CoffeeScript</a>, has a chapter on <a href="http://arcturo.github.com/library/coffeescript/03_classes.html" target="_blank">classes</a> and does a good job explaining the important syntax difference.</p>

<p>I am not the only one who has <a href="http://twitter.com/#!/karlseguin/status/160200811501203456" target="_blank">mentioned this</a>.  I guess the argument is that you can generally avoid the extra call to <code>__bind</code> because some/most of your functions may never be used in a different context and the <code>this</code> scope will always be the same. Thats fine, but this creates many inconsistencies in the code, and for what real benefit?  While I agree there is merit to having the deeper understanding of when to use <code>=></code> vs <code>-></code> properly, I tried to figure out why the unexpected has been adopted as the default.</p>

<h2>Why not show the fat arrow some love?</h2>

<p>The most widely found explanation says to not use it all the time for performance reasons.  Everyone seems to just say ok, makes sense, in the name of performance I'll just use <code>-></code> as the standard and remember to use the fatty <code>=></code> when I need to.  Inevitably you will forget and waste time tracking it down.  Is this potential premature optimization worth the inconsistencies and unexpected behaviour? This smells of my old PHP days when everyone would <b>always</b> use single quotes for strings unless of course you had to inject a variable, then you would drop back to double quotes.  I also remember even Facebook developers saying they didn't worry about the type of quotes they used - of course they "simply" built a <a href="https://github.com/facebook/hiphop-php" target="_blank">PHP to C++ compiler</a> to overcome their challenges.</p>

<h2>Performance? Really?</h2>

<p>To see if the madness should continue in the name of performance I have created a simple little benchmark.</p>

<pre><code class="coffeescript">
class FunctionBinding
  constructor: ->

  thisIsNotBound: ->

  thisIsBound: =>
</code></pre>

<p>You can see below that the compiled JavaScript wraps the <code>thisIsBound</code> function with the <code>__bind</code> call to bind the <code>this</code> context for all calls to that function.  This is where the extra overhead comes from.</p>

<pre><code class="coffeescript">
(function() {
  var FunctionBinding, fb, i,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

  FunctionBinding = (function() {

    function FunctionBinding() {
      this.thisIsBound = __bind(this.thisIsBound, this);
    }

    FunctionBinding.prototype.thisIsNotBound = function() {};

    FunctionBinding.prototype.thisIsBound = function() {};

    return FunctionBinding;

  })();

}).call(this);
</code></pre>

<p>Here is the benchmark code that executes the two functions and performs the necessary timing thanks to the node.js STDIO api console.time() and console.timeEnd(), that according to <a href="https://github.com/joyent/node/blob/master/lib/console.js" target="_blank">console.js</a> is just a simple wrapper on <code>Date.now()</code>.</p>

<pre><code class="coffeescript">
fb = new FunctionBinding()

console.time('thisIsNotBound');
fb.thisIsNotBound() for i in [1..1000000]
console.timeEnd('thisIsNotBound');

console.time('thisIsBound');
fb.thisIsBound() for i in [1..1000000]
console.timeEnd('thisIsBound');
</code></pre>

<p>I first ran this with 10, 100, 1000, 10000 iterations but both timings were 0ms throughout.  It wasn't until I got to the 100k and 1 M mark that I saw real numbers.  These are averages of about 10 runs each.

<table class="bordered">
   <tr><th>console.time('marker')</th><th>100k</th><th>1 M</th></tr>
   <tr><td>thisIsNotBinded</td><td>1 ms</td><td>10 ms</td></tr>
   <tr><td>thisIsBinded</td><td>5 ms</td><td>22 ms</td></tr>
</table>

</p>

<p>Looking at the numbers you could make the argument that it takes over twice as long to execute the function with the <code>__bind</code> compared to without.  That would be a fair statement.  I think the numbers also show that the inconsistency in the code and potential for unexpected behaviour this causes is not worth the negligable performance improvement.  After running this I can confidently say that a 12 ms difference on 1 M functions calls is most definitly a shallow over optimization.</p>

<h2>What about memory?</h2>

<p>From what I know so far the methods and properties of the prototype object are not duplicated for each instance and therefore don't add memory overhead with each instance.  The binding code from above adds an instance function to call the prototype with the proper <code>this</code> scope.  This would add overhead which would be a reason to not use unnecessarily.</p>

<h2>Enjoying the tangent</h2>

<p>Don't get me wrong here.  Despite this post, I am enjoying my current tangent.  I am experiencing first hand the positive productivity and conciseness of CoffeeScript and the single threaded event loop of node.js.  I see another blog rewrite in my future... <a href="http://expressjs.com/" target="_blank">express.js</a> anyone!</p>

<p>CoffeeScript is only one of the many projects listed on the <a href="http://altjs.org/">altJS</a> page.  Some of those are fairly interesting but it seems CoffeeScript is the most popular, at least according to github.</p>

<p>Are you drinking the new kool-aid? uh Java? I mean Coffee?</p>












































