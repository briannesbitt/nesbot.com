<?/*This blog is now running on Play 2.0 BETA*/?>

<p>Yes my blog is <b>LIVE</b> and yes this is now running on the preview <b>BETA</b> version of the Play framework 2.0.</p>

<h2>Are you crazy?</h2>

<p>The feeling I get from the release is that it's a beta release in terms of framework features but not really from a stability/performance standpoint.  The team behind Play has gathered some great experience from Play 1.X so this is hardly thier first attempt.  I haven't experienced any show stoppers during my use (granted its been short lived) or seen any large issues on the google group besides "when is this going to get implemented".  The reality is that this is my personal blog.  If it instantaneously self combusts for a couple of minutes/hours/days we all shall live ... plus it makes for a great story!</p>

<h2>Some more reasoning... however crazy!</h2>

<p>The framework is built on top of <a href="http://www.jboss.org/netty">netty</a> which has been around for years.  It doesn't automatically give you a home-run (read web framework out of the box) but it does give you an easy stand up double (stable networking layer and http implementation).</p>

<p>Another major portion of a web framework is the template engine.  Play 2.0 includes a powerful Scala-based template engine. The template engine's syntax design was inspired by ASP.NET Razor.  The template engine was first introduced in the Scala version of Play 0.9.1, about 6 months ago.  Sure its integrated into a different engine now, but I suspect it was most likely easier this time around since the core is now developed in Scala while before it was added as a plugin on top of a core Java framework.  This of course doesn't make it bullet proof but its further ahead than having just been developed in the last few weeks for 2.0.</p>

<p>I am not trying to convince anyone to start using this version.  Not surprisingly this seems to be a common question on the google group.  I just know for my personal blog I am ok with <a href="http://www.youtube.com/watch?v=7nqcL0mjMjw" target="_blank">livin' on the edge</a>.  From my current understanding (as mentioned by the project lead) they expect a final version <i>"early next year"</i> with a <i>"first release candidate end of January"</i>. If history is a good indicator (which I think it is) and with the Typesafe announcement (possible dev resources) I am sure the development will move along as smoothly as can be expected.  The first messaging about Play 2.0 was done on Sept 7 and at that time it was said there would be a BETA by end of year. Since then there was a preview released on Oct 25 and the BETA on Nov 16, nicely ahead of schedule.</p>

<h2>How my blog was setup on Play 1.2.X</h2>

<p>The old site was available on github : <a href="http://github.com/briannesbitt/v1.nesbot.com">http://github.com/briannesbitt/v1.nesbot.com</a></p>

<p>
I won't cover every little detail but will focus on the parts that played the largest role in the porting exercise. The actual posts I kept in tag template files in the <code>app/views/tags/posts/</code> directory with a file name format <code>YYYY-mm-dd-slug.html</code>.  The first line of the template source contained a comment that had the title to be used - <code>*{My post title}*</code>. I never bothered creating an admin just for myself which allowed me to just use template files and do anything in the template as necessary (like referencing similar posts in a series etc.).  I had a job that ran <code>@OnApplicationStart</code> that read the files in the directory and loaded all matched files (and parsed the title from the first line) into a <code>ConcurrentOrderedMap&lt;String, Post&gt;</code> which was backed by an ArrayList and HashMap. The Map is actually part of my <a href="https://github.com/briannesbitt/nesbot-commons">nesbot-commons</a> APIs. I created the ConcurrentOrderedMap to allow easy lookups for posts by index (0,1,2,3...) and by slug and both lookups are indexed (read fast).  The concurrency is accomplished using ReentrantReadWriteLock so there really is no contention since all of the writing is done at startup.  To pull in new posts I just restart the server, which is simple and sufficient for my site. At somepoint I could easily create a quick service point that just runs the startup job again.  Most of the features in the nesbot-commons API are available via other public APIs (apache commons, google guava, etc) but I had fun writing them, wrote a build script using Rake (ruby), performed testing and integrated cobertura which was all good learning.</p>

<p>I will now show you snippets from the old routes, controller and template so you will be able to compare later on and see the changes.</p>

<p>The route is obviously meant to match a <code>/2011/11/22/slug</code> format. <i>Don't tell anyone</i> but I ignore the date portions and just do a lookup by slug.  I hate when you visit a blog and there is no date so you can't tell how old the content you are reading is !!  But I digress. :-)</p>

<pre><code class="bash">
GET /{<[0-9]{4}>year}/{<[0-9]{1,2}>month}/{<[0-9]{1,2}>day}/{slug}/?   Application.show
</code></pre>

<p>The controller is simple.  It gets the Post by slug, does a couple of error checks and finally renders the template also passing in the older and newer posts (which must be local variables for that magic to work) which are used for the navigation at the bottom of the post.</p>

<pre><code class="java">
public static void show(Long year, Long month, Long day, String slug)
{
   Post post = Post.findBySlug(slug);

   if (post == null)
   {
      notFound(slug);
   }

   if (!templateExists(PostExtensions.tagName(post)))
   {
      notFound("template for : " + PostExtensions.tagName(post));
   }

   Post older = post.next();
   Post newer = post.previous();

   render(post, older, newer);
}
</code></pre>

<p>The final snippet is from the show.html template. It extends the master template and simply shows the post.  The inclusion of the actual post tag template is easy since Groovy allows the dynamic include of another template by a variables value.</p>

<pre><code class="bash">
&lt;h1>${post.title}&lt;/h1>
&lt;div class="date">${post.prettyUpdated()}&lt;/div>

#{share uTag:'Top',urlimages:urlimages,title:post.title,url:post.fullUrl() /}
#{include post.tagName() /}
#{share uTag:'Bottom',urlimages:urlimages,title:post.title,url:post.fullUrl() /}
</code></pre>

<p>Its all quite simple.  No "real" data source and no html form processing so the move to 2.0 shouldn't be too bad, I think!  The only other part I'll mention at this point is that there is an RSS feed generated as well.</p>

<p><h1>Things I encountered during the conversion to Play 2.0 BETA</h1></p>

<h2>Scala templates</h2>

<p>I have started to do some reading about Scala but my current skills are still to be desired at this point. The templates I needed to port were not hard to write and the early Play 2.0 documentation on github combined with the samples provided (3 complete samples for a BETA... nicely done) gave me enough information that I didn't have any trouble with them. During dev, all of the templates <code>app/views/**/*.scala.html</code> are parsed and compiled to a Scala singleton object.  The old groovy templates from 1.X were only first touched at runtime.  This provides a pretty nice development workflow and the compile errors are generally well displayed.  The only issue I saw was if you have a long line of text, say a paragraph in a blog post, and you forgot to esacpe a <code>@</code> near the end of the line, then the error shows the line but there is no horizontal scrollbar so the actual error is off the page to the right.  You need to view source or use firebug to actually see it.</p>

<p>Writing this post I actually just came across what appears to be an error.  If you have &quot;&quot;&quot; (3 quotes) in your template it fails to compile.</p>

<p>Being a BETA release all of the sugar that was there in 1.X is just not available yet (FastTags, Extensions, absolute reverse routing), but nothing you can't just implement yourself.  You can basically write a Java/Scala class and just statically import it into the template and start calling the methods on it. For example to perform a url encode on a string you can write a <code>public static String urlencode(String s)</code> method in a <code>app/helpers/Html.java</code> file.  To use this function, your template might look like:</p>

<pre><code class="scala">
@(searchTxt: String)

@import helpers.Html._

&lt;a href="http://www.google.com?q=@urlencode(searchTxt)"&gt;Search for @searchTxt via google.&lt;/a&gt;
</code></pre>

<p>Since the templates get compiled down to Scala code, you can overload functions and everything works as expected.  For example to output the "pretty" post date I have these helper functions:</p>

<pre><code class="java">
public static String prettyTimestamp(long ts)
{
   return Dater.create(ts).toString("MMM d, yyyy");
}
public static String prettyTimestamp(Post post)
{
   return prettyTimestamp(post.updated);
}
</code></pre>

<p>The accompanying template would call <code>@prettyTimestamp(post)</code> to display the post date but you can also use the same function if you have a timestamp <code>@prettyTimestamp(post.updated)</code>.  The Dater class is part of nesbot-commons (I have never liked star wars and therefore don't care for joda-time ;).  There is a way to do this in scala where you can actually have the template syntax be <code>@searchTxt.encode</code>.  I haven't bothered to branch over to Scala yet, but I am sure these helpers will be in the framework by the time RC1 is released anyway.  I'll give you one more example for url reverse routing for posts.  Reverse routing for posts is done like <code>@routes.Application.show(year, month, date, slug)</code>.  This is used to link to similar posts and for the next/previous post navigation. Absolute reverse routing is not implemented yet so I had to implement my own (I need the absolute for the RSS feed). Not to mention if you have a post object in the template calling the above routing code in many places doesn't feel very DRY.  I created 2 functions to help out.</p>

<pre><code class="java">
public static String url(Post post)
{
   Dater d = Dater.create(post.updated);
   return routes.Application.show(d.year(), d.month(), d.date(), post.slug).url();
}
public static String urlFull(Post post)
{
   return Config.urlbaseabsoluteNoSlash() + url(post);
}
</code></pre>

<p>This makes post linking simple. After statically importing the Html helper class the home page, for example, can simply do <code>@url(post)</code>.  Wherever an absolute url is required you use the 2nd function, <code>@urlFull(post)</code>.  The Config class I'll talk about in a bit.  It was also mentioned in the google group that a config value might be added to allow us to specify some default classes to import for templates.  This would eliminate the need for the <code>@import helpers.Html.*</code> at the top of each template that makes use of the helpers.</p>

<h2>Blank lines in rendered HTML</h2>

<p>
When I was getting the <code>/rss</code> feed working I kept getting the open/save option in Firefox rather than the nice rss page that is usually shown.  It turns out that there were some blank lines at the top of the rendered xml. For most pages this does not matter. If you generate an RSS page though, blank lines at the top do matter - so it seems.  Tweaking the Scala template a bit I determined:</p>

<ul class="list">
<li>If you just have a simple no args template file then there are no blank lines.</li>
<li>If you specify arguments at the top of the template "@(s : String)" then you will get 1 blank line.</li>
<li>If you start calling layouts... more blank lines.</li>
<li>@import lines don't generate a blank line.</li>
</ul>

<p>At first I used my trusty hammer (you know...the tool that can fix everything!) and hacked up the controller.</p>

<pre><code class="java">
public static Result rss()
{
   response().setContentType("application/rss+xml");
   String body = rss.render(Post.findAll()).body();

   /* HACK TO REMOVE FIRST BLANK LINE */
   char c = body.charAt(0);
   while (c == '\r' || c == '\n')
   {
      body = body.substring(1);
      c = body.charAt(0);
   }
   /* HACK TO REMOVE FIRST BLANK LINE */

   return ok(body);
}
</code></pre>

<p>This worked just fine, but smelled bad.  I looked into the code for the generated Scala template code file <code>target/scala-2.9.1/src_managed</code> and I learned more about the template system which was a win.  In the end I figured out that if you put your content up on the first line after the arguments it prevents the blank line, which even though is a minus for readability, its not that bad and a better solution than the hack above.</p>

<pre><code class="scala">
@(arg : String)&lt;?xml version="1.0" encoding="UTF-8" ?>
</code></pre>

<h2>Application Global settings</h2>

<p>In Play 1.X you could create a job that ran <code>@OnApplicationStart</code> or write a plugin the handled the start/stop events.  In Play 2.0 there is the concept of a Global object that allows you to handle <code>beforeStart()</code>, <code>onStart()</code>, <code>onStop()</code> and some other events for errors.  I used this to trigger the configuration initialization and loading of the post template files.</p>

<h2>Compiling assets</h2>

<p>Just a quick note to say that I had no problems getting the <a href="http://lesscss.org/">LESS</a> styleshseets compiled and routed.  I quickly tried the same with the javascript files but didn't have much luck.  I am using syntaxhighlighter for the code snippets on the site and I think it already does some "require" calls and I kept getting compiler errors.  I left it for now and will revisit later.</p>

<h2>Public assets routing</h2>

<p>If you stick with the defaults here everything works as expected.  I think the syntax is quite cumbersome <code>&lt;img src="@routes.Assets.at("images/play.png")" /></code>. In the future I will wrap this in my own helper function which would then just use the Assets helper anyway but at least centralize it.  The other thing I tried, is I generally prefer to serve images from <code>/images</code>, css from <code>/css</code>, js from <code>/js</code> etc. If you do this you need to add a 2nd parameter, folder, to the at() call.  Its explained on the <a href="https://github.com/playframework/Play20/wiki/AssetsPublic">wiki</a> but I really didn't get it at first glance.  The old routes and syntax for static files seemed easier but this is a small issue in the grand scheme.  Either way once I add my wrappers I will be able to change it easily anyway.</p>

<h2>Configuration for several environments</h2>

<p>I didn't see anything in the wiki yet about this so I wrote a quick interface using the supplied helper Configuration methods.  This is another feature that I am sure will be in for the RC release. There is already a helper <code>play.Configuration.getSub()</code> to get all values in a sub-configuration so I am thinking this will get integrated into the various <code>play.Configuration.getString()</code> methods to see if there is a <code>mode.value</code> or just a value defined.  After a quick look today it appears there is something for this in the Scala API but nothing in the Java API as of yet.</p>

<p>For my purposes I have implemented a Config class. The <code>init()</code> function is called by the <code>Global.beforeStart()</code> handler.</p>

<pre><code class="java">
public class Config
{
   private static Configuration _envConfig;

   public static void init(Configuration configuration)
   {
      _envConfig = isDev() ? configuration.getSub("dev") : configuration.getSub("prod");
   }

   public static boolean isDev()
   {
      return Play.isDev(Play.current());
   }
   public static boolean isProd()
   {
      return Play.isProd(Play.current());
   }
   private static String getString(String key)
   {
      String envValue = _envConfig.getString(key);
      return (envValue != null) ? envValue : Configuration.root().getString(key);
   }

   public static String postsPath()
   {
      return getString("posts.path");
   }
}
</code></pre>

<p>So now you can just call <code>Config.postsPath()</code> and you get either the global value or the environment specific value. In any template you can call <code>@helpers.Config.postsPath</code> or <code>@import helpers.Config._</code> and then <code>@postsPath</code> if the configuration value was required in your templates.</p>

<p>Also from what I can tell the server starts in a particular mode depending on how its initiated.  <code>play run</code> will start a reloading application in DEV mode and <code>play start</code> will start a precompiled application in PROD mode.  If you wanted to add a stage or qa you would have a few options but I think I would do it via an environment variable defined maybe as <code>{application.name}.ENV</code> or something specific like that.  Then just augment the init() function to be a switch and you are all set.  Of course this would only be in your application as I don't think you can pass in a mode yet like how "--%mode" worked for 1.X.</p>

<h2>Where and how I keep the post template files now</h2>

<p>Initially I ported over the code pretty much as is, moving the previous <code>@OnApplicationStart</code> job code to the new <code>Global.beforeStart()</code> handler.  I translated the templates to the new Scala templates and tried running the application.  It failed miserably!  What happens is the <code>app/views/**/*.scala.html</code> DSL templates get compiled via the framework and are managed in the <code>target/scala-2.9.1/src_managed/main/views/html</code> directory.  So my first post <code>app/views/posts/2011-9-7-carpenters-house-last-to-get-attention.scala.html</code> is compiled to <code>target/scala-2.9.1/src_managed/main/views/html/posts/2011-9-7-carpenters-house-last-to-get-attention.template.scala</code>.  Here is a snippet of the compiled Scala template code:</p>

<pre><code class="scala">
package views.html.posts

object 2011-9-7-carpenters-house-last-to-get-attention extends BaseScalaTemplate[play.api.templates.Html,Format[play.api.templates.Html]](play.api.templates.HtmlFormat) with play.api.templates.Template0[play.api.templates.Html] {

    def apply():play.api.templates.Html = {
        _display_ {

          Seq(format.raw/*1.76*/(&quot;&quot;&quot;
      &lt;p>post content is here&lt;/p> &quot;&quot;&quot;))}
    }

    def render() = apply()
}
</code></pre>

<p>The issue I discovered is that the template file name gets used as the compiled Scala object name.  In case you have forgotten, java class naming dictates that a class can not start with a number and "-" is not allowed in the name.  I had to change my filename format so that it would be a valid class name to get it to compile.  I changed the file name format to <code>pYYYY_mm_dd_slug.scala.html</code>. I use "p" to indicate a post but mostly to satisfy the can't start with a number rule.  I changed the "-" as the seperator to "_" as underscores are allowed.  The slug portion also uses "_" to seperate words and I simply replace them in favour of "-" to use in the actual url's produced.  The title is still in the template file as the first line but uses the new comment syntax <code>*@post title@*</code>.  A bit of an aside, when I am working on a new post I go ahead and create the new file but with a prefix of "a_" which will do a few things for me.  Without it showing up on the live site I can go ahead and push it to github so I won't lose anything I write if its not done in one sitting (also allows me to work on it from multiple computers if necessary) and gets it sorted to the top in a directory listing so I can always see which post I am currently working on. I could branch for each new post but that just seems like overkill.  This got the posts being populated and the templates compiling successfully and so I moved on to the controller and rendering.</p>

<p>Finally lets take a look at the same snippets as above for the routes, controller and template but I will also include the Html.java code to show dynamically calling the template render() function.</p>

<p>Only minor syntax changes here.</p>

<pre><code class="bash">
GET /$year<[0-9]{4}>/$month<[0-9]{1,2}>/$day<[0-9]{1,2}>/:slug    controllers.Application.show(year : Long, month: Long, day: Long, slug)
</code></pre>

<p>The controller really didn't change much either.  Besides using the new API for template rendering the only other minor change was not having to create the local variables for <code>older</code> and <code>newer</code> posts to pass to the template as now they are real parameters to the template's render() function.</p>

<pre><code class="java">
public static Result show(Long year, Long month, Long day, String slug)
{
   Post post = Post.findBySlug(slug);

   if (post == null)
   {
      return notFound(notFound.render(slug));
   }

   if (!Html.viewExits(Html.tagName(post)))
   {
      return notFound(notFound.render("template for : " + Html.tagName(post)));
   }

   return ok(show.render(post, post.next(), post.previous()));
}
</code></pre>

<p>In the view I could no longer dynamically include the post template like the previous verison did easily <code>#{include post.tagName() /}</code> thanks to Groovy.  I could however just use reflection to load the class and invoke the render method.  The compiled template is a Scala object.  This represents a singleton in Scala and the methods defined can be called staticly.  Its then just an easy matter of loading the class, getting the declared method and invoking it. Even though I am invoking the template at runtime, all of the posts are still compiled and type checked at compile time.</p>

<p>The template really isn't that different looking.  You see the use of the Scala template <code>@</code> begin the code statements.  You see the <code>prettyTimestamp</code> Html helper being called - it was done with an extension before but wasn't typesafe as it is now.  You can see the share tag being called as before except the arguments are now typed rather than just names.  The <code>helpers.Html.render(post)</code> as seen below is where the post template gets rendered.  It makes a couple of other calls to convert the slug back to the template filename and then passes that on to <code>renderDynamic</code> to perform the invoke.</p>

<pre><code class="scala">
&lt;div class="title">@post.title&lt;/div>
&lt;div class="date">@prettyTimestamp(post)&lt;/div>

@share(post.title, urlFull(post), "Top")
@helpers.Html.render(post)
@share(post.title, urlFull(post), "Bottom")
</code></pre>

<p>And the snippet from Html.java for the helpers.</p>

<pre><code class="java">
public static String view(Post post)
{
   return Strings.format("{0}p{1}_{2}", Strings.replace(Config.postsPath(), "app/views/", "views.html."), Dater.create(post.updated).toString("yyyy_M_d"), post.slug.replace('-', '_')).replace('/', '.');
}
public static play.api.templates.Html render(Post post)
{
   return renderDynamic(view(post));
}
public static play.api.templates.Html renderDynamic(String viewClazz)
{
   try
   {
      Class&lt;?> clazz = Play.current().classloader().loadClass(viewClazz);
      Method render = clazz.getDeclaredMethod("render");
      return (play.api.templates.Html)render.invoke(clazz);
   }
   catch(ClassNotFoundException ex)
   {
      Logger.error("Html.renderDynamic() : could not find view " + viewClazz, ex);
   }
   catch(NoSuchMethodException ex)
   {
      Logger.error("Html.renderDynamic() : could not find render method " + viewClazz, ex);
   }
   catch(IllegalAccessException ex)
   {
      Logger.error("Html.renderDynamic() : could not invoke render method " + viewClazz, ex);
   }
   catch(InvocationTargetException ex)
   {
      Logger.error("Html.renderDynamic() : could not invoke render method " + viewClazz, ex);
   }
   return play.api.templates.Html.empty();
}
</code></pre>

<h2>Deployment</h2>

<p>When executing in DEV mode by using <code>play run</code> from the shell or <code>run</code> from the new Play console I don't think you can change the port it binds to.  If you are using <code>start</code> to execute in PROD mode, it will read the port number from a PORT environment variable if it exists. Of course I am sure that control will be enhanced by the release candidate.  Also I haven't seen a <code>play stop</code> command yet.  For now you can kill the task from the pid in the <code>RUNNING_PID</code> file, simple enough but I am sure a stop will be added.  I use this script to restart the server when I deploy a new blog post.  I have nginx running in front as a reverse proxy and it serves up a simple 50x.html page when the proxy isn't responding - most likely during the few seconds it takes to precompile on startup.</p>

<pre><code class="bash">
#!/bin/sh

cd /vol/www/nesbot.com
git pull

if [ -f RUNNING_PID ]
then
  kill `cat RUNNING_PID`
  rm RUNNING_PID
fi

PORT=9001
export PORT

#play2 stop   #this doesn't exist yet!
play2 clean
play2 start
</code></pre>

<h2>Cannot run program "javac"</h2>

<p>Remember you need to have a JDK installed not just the JRE.  This seems obvious enough now, but at the time it caught me by surprise.  Play 1.X was bundled with the eclipse java compiler so you never needed the JDK and on my live server I had only installed the JRE.  Not a huge deal, just something else to remember and do before hand.</p>

<h2>Useless and unscientific benchmarks</h2>

<p>Well maybe not completely useless as they show a definite consistent trend.  Play 2.0 is faster than Play 1.X.  I would hazard a guess that most of this time is eaten up by the templates being moved from Groovy to Scala.  My guess though is that the engine as a whole will generally scale better.</p>

<p>First I ran some simple benchmarks on my local development machine. I have an Intel Core i7 860 (4 cores/8 threads), 8 GB ram and an OCZ 120 GB SSD and am running WINDOWS 7 x64.</p>

<p>
<code>ab -n 10000 -c 6 http://127.0.0.1:9000/</code><br/>

<table class="bordered">
<tr><th></th><th>1.2.3</th><th>2.0 BETA</th></tr>
<tr><td>Requests/sec</td><td>2,800</td><td>4,000</td></tr>
<tr><td>99% requests done in (ms)</td><td>4</td><td>3</td></tr>
</table>
</p>

<p>Then I tried it with a larger template.</p>

<p>
<code>ab -n 10000 -c 6 http://127.0.0.1:9000/2011/9/20/cobertura-module-tricks-with-the-play-framework</code><br/>

<table class="bordered">
<tr><th></th><th>1.2.3</th><th>2.0 BETA</th></tr>
<tr><td>Requests/sec</td><td>1,000</td><td>1,600</td></tr>
<tr><td>99% requests done in (ms)</td><td>7</td><td>6</td></tr>
</table>
</p>

<p>When I launched the site (this past saturday night) I ran some live benchmarks.  The live site runs on a 2GB <a href="https://www.stormondemand.com/">https://www.stormondemand.com/</a> instance running CentOS 5.4.</p>

<p>
<code>ab -n 10000 -c 6 http://127.0.0.1:9000/</code><br/>

<table class="bordered">
<tr><th></th><th>1.2.3</th><th>2.0 BETA</th></tr>
<tr><td>Requests/sec</td><td>1,200</td><td>3,000</td></tr>
<tr><td>99% requests done in (ms)</td><td>11</td><td>5</td></tr>
</table>
</p>

<p>The 2.0 version on the live server was almost as good as on my development machine, which I'd have to chalk up mostly to Linux vs Windows.  The numbers that surprised me the most were the final ones.  The 2.0 version was dramatically better than the 1.2.3 version on my live linux server.  I thought this was pretty impressive since its only a 2 GB instance with 2 cores (4 threads), it runs 3 other play 1.2.3 sites, a couple of PHP sites, mysql and mongodb.  None of the sites are very busy, but its all running none the less. A <code>more /proc/cpuinfo</code> shows an Intel X3440 @ 2.53 GHz which is actually a 4 core (8 threads) CPU but I believe they use something like XEN to partition the hardware.</p>

<p>All in all I am happy with how things went considering this is just a BETA that was released a month earlier than expected.  I haven't touched the testing framework as of yet (shame on me!) but that will be next along with form processing and handling.</p>

<p>Feel free to ask any specific questions via email/twitter/google group and I'll do my best to answer them.</p>

<p>The full blog code is available on github : <a href="http://github.com/briannesbitt/nesbot.com">http://github.com/briannesbitt/nesbot.com</a>.</p>