<?/*Play framework accesslog module update*/?>
<p>A few weeks ago my first Play framework module got accepted and published. The module performs request logging similar to an access log file in nginx or apache.  I just released a small update (v1.1) to the module.  It now attempts to create the full log path if it doesn't exist.</p>

<p>This post is not meant to duplicate the documentation I already have written and published on github and playframwork.org.  I will first refer you to those sites and then continue this post with a quick sample application using the module.</p>

<p><a href="http://www.playframework.org/modules/accesslog">http://www.playframework.org/modules/accesslog</a><br/>
<a href="https://github.com/briannesbitt/play-accesslog">https://github.com/briannesbitt/play-accesslog</a></p>

<p>Let's now create a new Play application.</p>

<pre class="brush: bash">
play new accessLogSample
cd accessLogSample
</pre>

<p>Now add <code>- play -> accesslog 1.1</code> to your <code>conf/dependencies.yml</code> file.</p>

<pre class="brush: bash">
play dependencies
play run
</pre>

<p>Now load <code>http://127.0.0.1:9000/</code> in your browser.  Thats it! You should see the requests and responses get logged to your configured accesslog file.</p>

<h2>v1.0 Warning</h2>

<p>The initial 1.0 version would have generated the following warning if you did have the full path to the log file pre-created:</p>

<p><code>23:57:55,286 WARN  ~ AccessLogPlugin: No accesslog will be used (cannot open file handle) (Z:\dev\accessLogSample\logs\access.log (The system cannot find the
path specified))</code></p>

<p>I have updated the module to recursively create the directory structure to the configured <code>accesslog.path</code> file provided.  If you use the default config and are running the 1.0 version, all you need to do if you see the warning above is <code>mkdir logs</code> in your project root or better yet just upgrade to the lastest version.</p>

<h2>Logging to Play console</h2>

<p>Although having the log file populated is nice, during dev its also nice to see the logs in the console.  To get this working just add <code>accesslog.log2play=true</code> in your <code>conf/application.conf</code> and restart.  This will cause the logs to be written to the <code>play.Logger</code> at the <code>INFO</code> level so it also relies on your <code>application.log=INFO</code> being set which is the default so for most it will just work.</p>

<h2>Production Usage</h2>

<p>There is nothing special done in the module with respect to performance.  It is threadsafe as the log method is synchronized.  I could have done something more like push the log strings to an in-memory queue and run a seperate thread for the IO, but it wasn't worth the effort.  Logging is done in <code>invocationFinally()</code> so it shouldn't slow the response to the browser as that should already be sent out.  I would recommend using this in dev along with <a href="http://getfirebug.com/">firebug</a> / <a href="http://www.fiddler2.com">fiddler</a> to help debug or track down any issues you might be having.  If you use it in production I would do so only under a watchful eye and for a short amount of time.  For a longer term production logging solution you should setup a reverse proxy like nginx and use it's logging capabilities as described here: <a href="http://wiki.nginx.org/HttpLogModule">nginx HttpLogModule</a>.

<p>And yes I repeated that last part, but its important enough to do so!</p>