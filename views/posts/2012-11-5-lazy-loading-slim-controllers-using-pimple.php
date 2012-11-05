<?/*Lazy loading Slim controllers using Pimple*/?>

<p>A popular question on the <a href="http://help.slimframework.com/discussions">Slim discussion forum</a> is how to mix controllers with Slim routes.  There are a few threads discussing several implementations but all are very similar.  The other concern users have is the performance hit of creating each of the controller objects and having all routes instantiated on every request.  I have responded that most of these concerns I would categorize as early over optimizations.  Not to mention there are improvements in Slim that will be happening to increase performance internally.  Currently, on every request, all routes are matched against the request, using a regex, and a list of the passing ones are returned and the first is used to serve up the response.  The main reason this is done is to support the <a href="http://docs.slimframework.com/pages/routing-helper-methods/#pass"><code>$app->pass()</code></a> feature which, when called, will skip to the next matching route.  The optimization will execute the first matching route immediately and only continue with the others if <code>pass()</code> is used.  The first reaction for most is to reduce the number of routes added per request based on the URI, which works but limits the usefulness of <code>urlFor()</code> which we'll cover next.</p>

<h2>urlFor()</h2>

<p>The <a href="http://docs.slimframework.com/pages/routing-helper-methods/#url_for"><code>urlFor()</code></a> lets you dynamically create URLs for a named rotue so that, were a route pattern to change, your URLs would update automatically without breaking your application.  This only works if all of the applications routes are known (added) to the Slim application.  A URL can't be constructed for an unknown route.</p>

<h2>Common first attempt</h2>

<p>There are many implemenations that attempt to solve this.  The most common "non-magical" attempt at solving some of these issues are to optionally require a routes file per section of the site based on the first portion of the requested path.  The implementation is typically added to a hook which runs before the routing sequence and looks something like this:</p>

<pre><code class="php">
$app = new Slim();

$app->hook("slim.before.router",function() use ($app){
    if (strpos($app->request()->getPathInfo(), "/user") === 0) {
        require_once('user/routes.php');
    } elseif (strpos($app->request()->getPathInfo(), "/post") === 0) {
        require_once('post/routes.php');
    } elseif (strpos($app->request()->getPathInfo(), "/admin") === 0) {
        require_once('admin/routes.php');
    } else {
        require_once('routes.php');  // default routes
    }
});

$app->run();
</code></pre>

<p>The <code>user/routes.php</code> file would contain all of the user routes with a callable that is either a typical closure, or if a controller class is to be used one might create a <code>UserController</code> class and attach member functions as the route callables <code>$userController = new UserController($app); $app->get('/user/:id', array($userController, 'index'))->name('userFind');</code>.  This all works fine, but renders <code>urlFor()</code> useless.</p>

<h2>Delayed Creation with no magic</h2>

<p>If we could delay the potentially expensive creation of the controllers we could still add all the routes and use <code>urlFor()</code>.  We can delay the controller creation using various PHP magic techniques, but lets try and avoid those and stick to a simple solution.</p>

<pre><code class="php">
$app->get('/user/:id', function ($id) {
   if ($GLOBALS['UserController'] == null) {
      $GLOBALS['UserController'] = new UserController();
   }

   $GLOBALS['UserController']->find($id);
})->name('user');
</code></pre>

<p>Did you catch why this works and successfully delays the creation of the UserController?  We don't need the controller instance to define the callable as before.  Instead we implement a very simple closure that then calls the UserController. This is a rudimentary implementation.  We are storing a single instance globally and accessing it everywhere, not very DRY and not easy to test.</p>

<pre><code class="php">
class UserController
{
   private $instance;

   public static function getInstance() {
      if ($this->instance == null) {
         $this->instance = new UserController();
      }

      return $this->instance;
   }
}

$app->get('/user/:id', function ($id) {
   UserController::getInstance()->find($id);
})->name('user');
</code></pre>

<p>This is a bit better as we have now centralized the object creation making it more DRY.  The code is still highly coupled and not easy to test. Lets move on to a better solution.</p>

<h2>Pimple</h2>

<p><a href="http://pimple.sensiolabs.org/">Pimple</a> is a simple dependency injection container for PHP 5.3+.  Moving along I'll assume you have read about it.</p>

<h2>Lazy loading using Pimple</h2>

<p>We can now build on our previous ideas but now we use Pimple to lazy load our controllers, and other expensive objects, while still adding all routes to the application so <code>urlFor()</code> can be used.</p>

<p>We use Pimple's <code>share()</code> feature to associate each controller object with a closure that is responsible for creating it, when needed (ie. first read access).  Lets get to our new solution using a simple site as an example.</p>

<p>As usual, we'll manage our dependencies using <a href="http://getcomposer.org">composer</a>.</p>

composer.json

<pre><code class="javascript">
{
   "require": {
      "slim/slim": "2.*",
      "pimple/pimple": "*"
   },
   "minimum-stability": "dev"
}
</code></pre>

index.php

<pre><code class="php">
&lt;?
require 'vendor/autoload.php';
require 'controllers.php';
require 'services.php';
require 'db.php';

$app = new \Slim\Slim();

$pimple = new Pimple();
$pimple['app'] = $app;

$pimple['UserController'] = $pimple->share(function ($pimple) {
    echo '<hr/>Created UserController<hr/>';
    return new UserController($pimple);
});

$pimple['UserService'] = $pimple->share(function ($pimple) {
    echo '<hr/>Created UserService<hr/>';
    return new UserService($pimple);
});

$pimple['db'] = $pimple->share(function ($pimple) {
    echo '<hr/>Created Db<hr/>';
    return new Db($pimple);
});

$app->get('/', function () use ($pimple) {
   //$pimple['app']->render('index.php', array('userCount' => $pimple['UserService']->count()));
   echo 'Root.  Current User count is ' . $pimple['UserService']->count();
});

$app->get('/contact', function () use ($pimple) {
   //$pimple['app']->render('contact.php');
   printf('Simple contact page.  Link to <a href="%s">User 11</a>', $pimple['app']->urlFor('user', array('id' => 11)));
});

$app->get('/user/:id', function ($id) use ($pimple) {
   $pimple['UserController']->find($id);
})->name('user');

$app->get('/users', function () use ($pimple) {
   $pimple['UserController']->all();
})->name('users');

$app->run();
</code></pre>

controllers.php

<pre><code class="php">
&lt;?
abstract class Controller
{
   protected $app;
   protected $service;

   public function __construct(Pimple $di) {
      $this->app = $di['app'];
      $this->init($di);
   }

   public abstract function init(Pimple $di);
}

class UserController extends Controller
{
   public function init(Pimple $di) {
      $this->service = $di['UserService'];
   }

   public function find($id) {
      //$this->app->render('user.php', array('user' => $this->service->find($id)));
      echo 'Found the user with id = ' . $id . '<br/>';
      var_dump($this->service->find($id));
   }

   public function all() {
      //$this->app->render('users.php', array('users' => $this->service->all()));
      echo 'Found all users.<br/>';
      var_dump($this->service->all());
   }
}
</code></pre>

services.php

<pre><code class="php">
&lt;?
class UserService
{
   protected $db;
   protected $app;

   public function __construct(Pimple $di) {
      $this->db = $di['db'];
      $this->app = $di['app'];
   }

   public function find($id) {
      return $this->db->findUser($id);
   }

   public function all() {
      return $this->db->allUsers();
   }

   public function count() {
      return $this->db->countUser();
   }
}
</code></pre>

db.php

<pre><code class="php">
&lt;?

/***** replace with real db access *****/

class Db
{
   public function __construct(Pimple $di) {
   }

   private function createUser($id) {
      $user = new stdClass();
      $user->id = $id;
      return $user;
   }

   public function findUser($id) {
      return $this->createUser($id);
   }

   public function allUsers() {
      return array($this->createUser(1), $this->createUser(2), $this->createUser(3));
   }

   public function countUser() {
      return rand(1000000,2000000);
   }
}
</code></pre>

<p>The UserController is not created until it is accessed via <code>$pimple['UserController']</code>.  This doesn't happen until the closure for either the <code>/user/:id</code> or <code>/user/all</code> routes are actually executed.  Their route callables are simple wrappers to the controller member function.  If we had used our previous example of <code>$app->get('/user/:id', array($pimple['UserController'], 'find'))->name('user');</code> then the controller would have been created when the route was added to Slim rather than lazily when it was actually executed.  This is a pretty simple implementation that doesn't use any PHP magic and therefore should be simple to follow.  Its also very apparent that your application is pretty easy to test as using Pimple makes it easy to mock all of the various layers.</p>

<p>Now we can add all of our routes and save the expensive object creation until its actually used.  This allows us to use <code>$app->urlFor('user', array('id' => 11))</code> to provide a URL like <code>/user/11</code> as you can see on the contact page from above.  If ever that URL was to change we don't need to change our code everywhere.</p>

<p>You will also notice that the index page shows a count of all of the registered users.  This is done with an instance of the UserService and does not need an instance of the controller to be created.  Finally note that the simple contact page can be rendered quickly and avoids the controller, service or db object creations.</p>

<p>I have commented out the tmeplate renders so you can at least see something meaningful in the responses.</p>