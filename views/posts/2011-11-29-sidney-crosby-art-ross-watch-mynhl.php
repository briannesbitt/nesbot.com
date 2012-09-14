<?/*Fun Play 2.0 project launch : Sidney Crosby Art Ross Watch and My NHL*/?>

<p>This post is meant for 2 audiences.  The first are hockey fans and the second are programmers.  If you are like me and fall into both categories than just keep reading.  If you want to to skip ahead to the programmer portion than <a href="#programmer">click here</a> as the hockey part will be first.</p>

<p>If you want to have a look at the site first, go here : <a href="http://hockey.nesbot.com/" target="_blank">http://hockey.nesbot.com/</a></p>

<h1>Hockey Fans : about the site</h1>

<h2>Sidney Crosby Art Ross Watch</h2>

<p>Let me start by saying I live in Ottawa, ON, Canada and I am a SENS fan.  Apart from the world jr tournament the first <i>lengthy</i> exposure I had to Crosby was the year the SENS beat the PENS in 5 games on their way to the Stanley cup finals.  I respected Crosby's obvious hockey skills but he was young and got labeled as a "whiner", to the point where it references that reputation on his wiki page and over 1/2 a million google hits for <a href="http://www.google.ca/search?q=crosby+whiner" target="_blank">crosby whiner</a>.  I think this comeback is doing a lot for hockey fans to rid him of that label, I know it has for me.  He has obviously worked hard to come back and has shown it on the ice.  To try and repair the relationship between him and I (ok its really between me and him ;-) I wanted to create a site to track Sidney's progress for what could be a history making season.  The <a href="http://en.wikipedia.org/wiki/Art_Ross_Trophy">Art Ross Trophy</a> is given to the player who leads the league in scoring at the end of the regular season.  Crosby won the trophy in his 2nd season with 120 points as a teenager.  This site will track his progress towards the Art Ross by providing a projection of points at the end of the season for him and the top 10 scoring leaders in the league.  The projection is calculated using the players points/game average (so its accomodates injuries) and the remaining games for their respective teams.</p>

<h2>So where does he rank today?</h2>

<p>Crosby was just today given a 2nd assist from saturday night's game against Montreal so he now officially has 9 points in 4 games which gives him a 2.25 pts/game average.  The penguins have 58 games remaining which puts him on pace for 139 points.  This is 34 points ahead of next best Phil Kessel who has had a 20 game head start.  Kessel has averaged 1.29 pts/game and is projected to get 105 points by season end.  The site is automatically updated often so it will be exciting to watch as the season progresses and Crosby surely continues to rack up the points.</p>

<p><a href="http://hockey.nesbot.com/crosbywatch" target="_blank">http://hockey.nesbot.com/crosbywatch</a></p>

<h2>MyNHL</h2>

<p>I am not a big fan of the current scoring system used by the NHL.  Giving 2 points for a shootout win and the same 2 points for a regulation win seems unfair to me.  Also awarding teams for a loss in overtime or shootout is also unfair in my eyes.  A good example is Detroit and Pittsburgh.  Assuming Detroit wins their next 2 games they would be trailing Pittsburgh by 2 points even though they would have 14 regulation wins to Pittsburgh's 10.</p>

<p>The MyNHL site allows you to configure the number of points awarded for wins, losses, OT wins, OT losses, shootout wins and shootout losses.  I don't sort the teams by division or conference, it is just a list showing where the teams would be with your scoring system.  I also disagree with the division leaders getting ranked 1, 2, 3 in the conference as someday the 3rd division leader will not have enough points to make it into the playoffs but will be 3rd in the conference none the less.  If it were up to me the division leaders would be automatically granted a position in the playoffs but then just get ranked by points.</p>

<p><a href="http://hockey.nesbot.com/mynhl" target="_blank">http://hockey.nesbot.com/mynhl</a></p>

<h1><a name="programmer"></a>Programmers : how I made the site</h1>

<?$this->linkPost('now-running-on-play-2-beta', function ($url, $title) {?>
   <p>If you haven't read my first post on Play 2.0 you might want to go back and read through <a href="<?=$url?>">This blog is now running on Play 2.0 BETA</a> as it has some thoughts on the BETA status and its current state.</p>
<?});?>

<p>This is my first attempt at working with Scala and specifically Play with Scala. I did this site to start learning Scala using Play 2.0 and figured I might as well create something more than a hello world!</p>

<p>The site is broken up into 3 controllers for the 3 portions of the site.  The <code>Application</code> controller just outputs the index view and has no logic. The <code>CrosbyWatch</code> controller gathers data and then renders the template.  The third controller, <code>MyNhl</code>, has 2 actions.  The <code>index</code> action gets all the teams and renders the initial template, while the second action, <code>dataTable</code>, parses some querystring data that is ajax posted (or applies defaults) and then generates the data table that will get rendered client side.  This is done twice for the intial page load, once for the official NHL scoring system and once for the MyNHL scoring.  Each change event to the MyNHL scoring configuration triggers another call to <code>dataTable</code> which regenerates the table.  All of the sorting is actually done client side with the <code>jquery.tablesorter</code> plugin.  Of course almost the whole site could have been done just client side but there would not have been much Scala involved in that!</p>

<h2>Automatically updating the stats</h2>

<p>I am using <a href="http://jsoup.org/">http://jsoup.org/</a> to scrape the NHL team and player stats and parse the HTML.  Its a really nice library to use and mimics jquery selectors rather than xpath which was rare in the ones I found.  Sometimes parsing HTML can feel pretty brittle but using jsoup was like a breath of fresh air.  There are 2 pages and a little math involved in extracting the data elements for the teams because most sites group OT losses and shootout losses together and only show wins, losses and OT losses.  I go directly to the Pittsburgh Penguins site for the Crosby specific stats.</p>

<p>The Play 1.X <a href="http://www.playframework.org/documentation/1.2.3/jobs">asynchronous jobs</a> framework is exactly what I would have used to schedule the scraping.  Play 2.0 doesn't have jobs built into it yet so I used the <code>java.util.concurrent.Executors</code> to run the job on a regular schedule.  I looked around for the Scala equivalent but most sites just said to use the java api.  I created a <a href="https://github.com/playframework/Play20/wiki/ScalaGlobal">Global</a> object and used the <code>beforeStart</code> to setup the thread scheduler and the <code>onStop</code> for shutdown.  The mongoDB and morphia intialization code will be talked about in the next section.  As you can see the <code>StatsUpdater</code> runs immediately as there is 0 intial delay and then is scheduled to run every 30 minutes.  The thread pool only has 1 thread since its the only scheduled task in the pool.</p>

<pre class="brush:scala">
object Global extends GlobalSettings {
  val executor = Executors.newSingleThreadScheduledExecutor()

  override def beforeStart(app: Application) {
    MongoDB.init("mynhl").mapPackage("models").indexes
    executor.scheduleAtFixedRate(StatsUpdater, 0, Dater.secondsPerMinute*30, TimeUnit.SECONDS)
  }
  override def onStop(app: Application) {
    executor.shutdownNow
  }
}
</pre>

<p>The <code>Runnable StatsUpdater</code> is probably not as elegant as you can get with Scala since this is my first time and I am just getting used to all of the sugar provided.  The <code>run()</code> calls the appropriate <code>upate*()</code> methods and catches all exceptions and logs any errors via Play.</p>

<pre class="brush:scala">
object StatsUpdater extends Runnable {
  def run() {
    try {
      updateTeams
      updatePlayers
      updateCrosby
    }
    catch {
      case e: Exception => play.Logger.error("Exception caught : " + e.getMessage, e)
    }
  }

  // update methods etc
}
</pre>

<h2>Datastore is using morphia and mongoDB</h2>

<p>I am (strangely?!?) using morphia to access mongoDB as the datastore with my own quick and dirty Scala based wrapper. Yes I looked at <a href="http://api.mongodb.org/scala/casbah/current/">casbah</a>, <a href="https://github.com/foursquare/rogue">rogue</a> and <a href="https://github.com/novus/salat">salat</a> but came back to morphia in the end since I had used it before.   I'll probably go back to some of those other Scala specific libraries, probably salat and casbah, but it just wasn't my focus this time around and I learned more by hacking up my own morphia wrapper anyway - how ever terrible it might be.</p>

<?$this->linkPost('play-2-morphia-logging-error', function ($url, $title) {?>
    <p>The initialization for morphia is done in the <code>Global.beforeStart()</code>. It configures the logger which is what I had an issue with yesterday and prompted <a href="<?=$url?>">using morphia with Play 2.0 and the sl4j logging error</a>. It then creates the Morphia datastore for the database <code>mynhl</code>.  Finally it pre-maps, using reflection, the classes for the <code>Team</code> and <code>Player</code> models.  The morphia wrapper is broken into 2 base classes.  <code>MongoModel</code> is the base class for the model instances and <code>MongoObject</code> is the base class for their accompanying Scala singletons.  This seemed like a logical split to me.  The instance model has all of the save/update/delete methods where the finders/counters are in the singleton.  I also extend the morphia DAO object and added a protected variable to each of the model and singleton.  The helper methods can then use the typed DAO interface rather than having to pass the class type to all of the morphia Datastore calls.  Not all of the helper methods are wrapped but the majority of the basic operations are available.  It needs to be augmented to handle WriteConcerns and return proper WriteResults but for this site its sufficient.</p>
<?});?>

<h2>CrosbyWatch Controller & View</h2>

<p>This controller is pretty simple. It gather the players by projected points and determines if Crosby is in the projected to win by checking the head element.</p>

<pre class="brush:scala">
object CrosbyWatch extends Controller {
  def index = Action {
    val players = Player.findAllOrderByProjectedPoints
    val winner = players.head
    Ok(views.html.crosbywatch.index(players, winner.name.equals(Player.CrosbyName), winner.name))
  }
}
</pre>

<p>The view is also straight forward.  It uses <code>@if(crosbyToWin) {success} else {error}</code> to set the css class appropriately so if he drops out of 1st place there will be some red on the page.  Otherwise it mainly loops over the players and generates the necessary rows in the table.</p>

<p>I chose to use the <code>@players.map { player => block }</code> syntax.  I could also use <code>@for(player <- players) {}</code>.  There are other syntax that could be used for looping over the players.  If you wanted to display a counter you could do <code>@for(i <- 0 until players.size-1) {}</code>.</p>

<h2>MyNhl Controller</h2>

<p>First the <code>index</code> action simply renders the template passing in a <code>Seq[Team]</code> to populate the drop down for selecting your favourite team.  Then it uses the <code>pointsConfig.scala.html</code> tag to render the points config section for the NHL and one that allows the user to configure their scoring system.  Nothing too complicated yet.  On <code>document.ready</code> a few things happen.  Event handlers are setup to highlight the users favourite team when the drop down changes.  Also when any scoring configuration value is changed for the user scenario it makes an ajax call to the server action <code>MyNhl.dataTable()</code> sending in all of the new point parameters.  The server action parses the querystring variables and renders the template.  This generates the standings table and calculates the points for each team as it is rendered using the parsed querystring values.  The sample provided in the Play 2.0 wiki for parsing querystring variables is shown below.  Its done like this because the querystring entries are <code>Seq[String]</code> in order to handle multiple values per variable name.</p>

<pre class="brush:scala">val name = request.queryString.get("name").flatMap(_.headOption)</pre>

<p>This is pretty verbose so I wrapped it in a ControllerHelper parse function.  I also needed to parse Ints from the String values so I added those as well while handling improper number formatting.  There is probably a more elegant way to get this done but this was easy enough for now.  I tried a few times with the new form handling as indicated <a href="https://github.com/playframework/Play20/wiki/ScalaForms">on the wiki</a> but none of the samples from there worked for me and the majority I couldn't even get to compile.</p>

<h2>Wrap Up</h2>

<p>I think that wraps up this post and project.  I'll be following Sidney's progress this season to see if he can capture the Art Ross even in this era of lower scoring.  As for the programming side, using the Scala API was certainly tougher for me compared to the Java API and my Scala skills have a long way to go.  I struggle with the syntax but also structuring the code to be more functional is still a bit of a mind shift for me that will just take some time.</p>

<p>As always catch me on email/twitter/comments/google group to ask specific questions and I'll do my best to answer them.</p>

<p>The full site code is available on github : <a href="http://github.com/briannesbitt/hockey.nesbot.com">http://github.com/briannesbitt/hockey.nesbot.com</a>.</p>