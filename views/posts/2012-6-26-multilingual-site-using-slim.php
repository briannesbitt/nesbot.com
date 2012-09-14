<?/*Multilingual site using Slim*/?>

<p>A question entitled <a href="http://help.slimframework.com/discussions/questions/244-multilingual-site-with-slim">Multilingual site</a> with <a href="http://slimframework.com">Slim</a> was posted to the <a href="http://help.slimframework.com/">help forum</a> the other day.  Here is my take on how I would start to create such an application with 3 pages: Home, About and Login.</p>

<h2>Overview</h2>

<p>We will start by looking at how the current active language is parsed and extracted from the requested URI using a <code>slim.before</code> hook.  Extracting the language from the URI is important as it will allow us to use the same set of routes for all of the languages.  We will create a simple custom view template that will use a translator service to perform the lookups we will need to display the multilingual content.  The translations will be stored in language resource files.  We will also subclass the Slim application class to ensure <code>urlFor()</code> is still available and working for us.  By the end of this post we will have a full multilingual application that will be able to responsd to the following URI's:</p>

<pre class="brush:plain">
http://127.0.0.1/
http://127.0.0.1/en
http://127.0.0.1/de
http://127.0.0.1/ru
http://127.0.0.1/about
http://127.0.0.1/en/about
http://127.0.0.1/de/about
http://127.0.0.1/ru/about
http://127.0.0.1/login
http://127.0.0.1/en/login
http://127.0.0.1/de/login
http://127.0.0.1/ru/login
</pre>

<h2>Spoiler Alert</h2>

<p>If you want to skip my ramblings below you can run the following commands to have the application up and running in about 10 seconds, depending on typing speed.  This assumes you are using PHP 5.4+ with the embedded webserver.  If not it will take you longer.</p>

<pre class="brush:bash">
git clone git://github.com/briannesbitt/Slim-Multilingual.git
cd Slim-Multilingual
curl -s http://getcomposer.org/installer | php
php composer.phar install
php -S 127.0.0.1:80
</pre>

<p>The project uses composer to install the latest <code>1.6.*</code> version of Slim and also to generate an autoload for all of the classes in the <code>app/lib/</code> directory via the composer classmap feature.</p>

<h2>Parsing the current language and common routes</h2>

<p>As you can see from above some of the URIs implicitly specify the language while others do not.  When the language isn't specified and a request for the index is made, we first use the <code>ACCEPT_LANGUAGE</code> header to try and guess the appropriate language from our sites available languages.  If a suitable language is not matched we will fallback to the site default and go ahead and render the page.  You could put a language chooser page in place if you wanted to rather than using the default language.  Our application will simply render the requested page using english but also show a language switch option which will maintain the current page context when switching.</p>

<p>Lets start this by first showing the common route for the homepage.  We want the following route to match against URI's #1, #2, #3 and #4 from above.</p>

<pre class="brush:php">
$app->get('/', function () use ($app) {
   $app->render('home.php');
});
</pre>

<p>The only way to do that (without using optional route parameters due to their limitations) is to parse and extract the language from the URI.  This must happen before Slim performs its routing logic so the proper matches take place.  For this we will use a <code>slim.before</code> <a href="http://www.slimframework.com/documentation/stable#hooks-default">hook</a>.  Slim will perform its route matching based on the URI in <code>$env['PATH_INFO']</code> where <code>$env = $app->environment()</code>.  So as part of the <code>slim.before</code> hook we simply loop through the <code>$availableLangs</code> and see if the URI begins with <code>/lang/</code>.  If so we can <code>substr()</code> the <code>$env['PATH_INFO']</code> to extract the language and write the shorter URI back to the <code>$env['PATH_INFO']</code> variable.  If we are quiet enough then Slim won't know the difference and it will go ahead and match routes against the modified URI.</p>

<pre class="brush:php">
$pathInfo = $env['PATH_INFO'] . (substr($env['PATH_INFO'], -1) !== '/' ? '/' : '');

// extract lang from PATH_INFO
foreach($availableLangs as $availableLang) {
   $match = '/'.$availableLang;
   if (strpos($pathInfo, $match.'/') === 0) {
      $lang = $availableLang;
      $env['PATH_INFO'] = substr($env['PATH_INFO'], strlen($match));

      if (strlen($env['PATH_INFO']) == 0) {
         $env['PATH_INFO'] = '/';
      }
   }
}
</pre>

<p>The first line of code from above is necesssary to match against a URI like <code>/en</code>.  Our attempted match string will be <code>/en/</code> so we need to append the trailing <code>/</code>.  If we only match against <code>/en</code> then we could improperly intercept other routes like <code>/entertain</code> which would be a request for the entertain page without a language specified.  The full <code>slim.before</code> hook can be seen at <a href="https://github.com/briannesbitt/Slim-Multilingual/blob/master/app/hooks.php">https://github.com/briannesbitt/Slim-Multilingual/blob/master/app/hooks.php</a></p>

<p>Once we have the <code>$lang</code> determined we can set it in our view at the end of the hook as the following code shows.  We also initialize some variables that will always be available to the view. The custom view will be examined in a bit.</p>

<pre class="brush:php">
$app->view()->setLang($lang);
$app->view()->setAvailableLangs($availableLangs);
$app->view()->setPathInfo($env['PATH_INFO']);
</pre>

<p>With the URI modifications completed Slim will go ahead and perform its matching as usual, not knowing anything about the language that was once there.  This allows us to create our 3 page application with the following 4 routes.</p>

<pre class="brush:php">
&lt;?
$app->get('/', function () use ($app) {
    $app->render('home.php');
})->name('home');

$app->get('/about', function () use ($app) {
    $app->render('about.php');
})->name('about');

$app->get('/login', function () use ($app) {
    $app->render('login.php');
})->name('login');

$app->post('/login', function () use ($app) {
    $app->render('login.php', array('error' => $app->view()->tr('login-error-dne', array('email' => $app->request()->post('email')))));
})->name('loginPost');
</pre>

<p>You can see the login page requires 2 routes, one for displaying the form and a second to receive the form POSTing.</p>

<h2>Custom view with Master Template</h2>

<p>The templating provided with Slim is pretty basic (there is a <a href="https://github.com/codeguy/Slim-Extras">Slim-Extras</a> repo that integrates other templating engines into Slim).  To prevent the duplication of html the typical Slim template will look like this:

<pre class="brush:php">
&lt;?
include 'header.php';
<p>This is my content.</p>
include 'footer.php';
</pre>

<p>I would suggest using a Slim-Extras template but to keep this application simple and from having any external dependencies we will turn the tables.  With no change to your routes and very little code you can add master template functionality to your custom template and thus preventing the duplication of including the header and footer on each page.</p>

<p>First we create a custom view class <code>MasterView</code> that extends <code>Slim_View</code>.  We setup a constructor that accepts a master template parameter and stores it.</p>

<p>Now to complete the view code we just need to override the <code>render()</code> function.  If the <code>masterTemplate</code> was set then <code>render()</code> swaps the template with the master template and sets up a <code>$childView</code> variable so the master template can <code>require</code> it.  Here is the full <code>MasterView</code> class in all of its 15 lines of glory.</p>

<pre class="brush:php">
&lt;?
class MasterView extends Slim_View {
    private $masterTemplate;

    public function __construct($masterTemplate) {
        parent::__construct();
        $this->masterTemplate = $masterTemplate;
    }

    public function render($template) {
        $this->setData('childView', $template);
        $template = $this->masterTemplate;
        return parent::render($template);
    }
}
</pre>

<h2>The Translator and the MultilingualView</h2>

<p>Is it just me or does that sound like a cheesy horror movie name?  Anyway, of course our view needs to provide translation of content for us.  This I did using a translator service that gets injected into the <code>MultilingualView</code> constructor.  The view then provides a simple helper <code>tr()</code> to make it easier for our templates to access.  With a simple <code>str_replace()</code> the translator provides a way to inject variables into the translated content.  So you can setup error messages like <code>Sorry, there is no user with an email of "{{email}}".</code></p>

<p>As we saw earlier, the <code>slim.before</code> hook is where the language is determined.  At the end of that hook the langauge, available languages and path info is set.  These setter functions simply use the parent <code>setData()</code> function to create variables that will exist in the context of the view.  So in the view if you want to know the current language you can just do <code>$lang</code> or looping through the available lanagues can be done like <code>foreach($availableLangs as $availableLang)</code>.  This also makes the helper methods to perform the translation easier to access as the current language is known and doesn't have to be passed in, <code>$this->tr('home-content')</code>.</p>

<p>The language resoure files follow the naming convention of <code>lang.en.php</code> where <code>en</code> is the language code.  These are simple PHP files that use an associative array to setup the translations.  Since it is just PHP you can require other files, load them from a <a href="http://redis.io">datastore</a> or use <a href="http://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc">HEREDOC</a> for longer paragraphs.  If a translation is requested for a key that does not exist a blank string is returned and an error is written to the Slim application log.  This could be changed to throw an exception to ensure its not missed during testing.  I also auto require a <code>lang.common.php</code> file that has common terms.  A good example for using this is the language chooser.  You don't display <code>German</code> when on the english site.  You always dipslay <code>Deutsch</code> for all languages so someone looking for the German version will be able to find it.</p>

<p>Sometimes its easier to view all of the content in the context of the page and html rather than in the language resource file. Here you have a few choices.  Say you have an about page that is broken into 4 &lt;p&gt; tags.  You can split the p tags over a few language resource keys, use a HEREDOC and put the html in a single resource key or include a language specific sub template.  Here are the implementation options for the <code>about.php</code> view file:</p>

<pre class="brush:php">
<p>&lt;?php echo $this->tr('about-p1')?></p>
<p>&lt;?php echo $this->tr('about-p2')?></p>
<p>&lt;?php echo $this->tr('about-p3')?></p>
<p>&lt;?php echo $this->tr('about-p4')?></p>
</pre>

<pre class="brush:php">
&lt;?php echo $this->tr('about-content')?>
</pre>

<pre class="brush:php">
&lt;?php require 'about_'.$lang.'.php'?>
</pre>

<p>I think for this example I would choose the 3rd option.  Anything more complicated would require mixins, more child templates etc. etc. and I would really start to lean to looking at the many templating engines out there.  What I do like about this one is that its really simple, easy to understand and flexible since it is just PHP.</p>

<h2>Ensuring $app->urlFor() still works</h2>

<p>This is actually much easier that it seems.  You can subclass the Slim application and override the <code>urlFor()</code> function.  It just needs to prepend <code>/lang</code> (lang being the current language) to the url returned by the parent <code>urlFor()</code>.  The full 6 line class is shown here:</p>

<pre class="brush:php">
&lt;?
class MultilingualSlim extends Slim {
    public function urlFor( $name, $params = array() ) {
        return sprintf('/%s%s', $this->view()->getLang(), parent::urlFor($name, $params));
    }
}
</pre>

<p>Follow this up by changing your application creation from <code>$app = new Slim(array('templates.path' => './app/views/'));</code> to <code>$app = new MultilingualSlim(array('templates.path' => './app/views/'));</code> and your done.</p>

<p>I'll also mention that rather than setting the custom view when the application is created, I used the <code>$app->view()</code> function to pass in an instance of the <code>MultilingualView</code> class.  This allowed me to pass some arguments when constructing the view class.</p>

<h2>Wrap up</h2>

<p>Thats it!  This is a first pass at creating a simple multilingual site using Slim.  There isn't much code here so I think its safe to say someone could to take a look and be up and running in a few minutes.  Oh and to try out the repo, use composer to install Slim and setup the application autoload you will be up and running in no time!  Jump back to the Spoiler Alert at the beginning to see the commands necessary to try this out.</p>

<h2>Links</h2>

<p>
   <a href="http://slimframework.com">http://slimframework.com</a><br/>
   <a href="http://github.com/briannesbitt/Slim-Multilingual">http://github.com/briannesbitt/Slim-Multilingual</a><br/>
   <a href="http://help.slimframework.com/discussions/questions/244-multilingual-site-with-slim">http://help.slimframework.com/discussions/questions/244-multilingual-site-with-slim</a>
</p>

<br/>