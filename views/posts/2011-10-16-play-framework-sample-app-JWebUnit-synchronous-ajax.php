<?/*Play framework sample application with JWebUnit and synchronous ajax*/?>
<p>I had posted awhile ago on the Play framework google group that I had successfully started using <a href="http://jwebunit.sourceforge.net/">JWebUnit</a> for testing rather than the bundled <a href="http://seleniumhq.org/">selenium</a> suite.  At the time it was partly because the IDE autocomplete support for the test helper assert functions in JWebUnit were way more convenient compared to learning the selenium commands.  It also runs headless which makes CI easier, although Play 1.2 had already fixed that for selenium.  That post, after sitting dormant for awhile, recently had another user trying to get an ajax test working but was running into a timing issue.  Before you can assert the result of the ajax call you need to wait for the call to be completed.  By default, the ajax calls are performed asynchronously, so you don't have much choice but to call <code>Thread.sleep(X)</code> and hope you select an appropriate value for X.  This type of test is pretty fragile in my mind and in the future could possibly break and raise some false negatives about the underlying functionality.</p>

<p>Sure enough there was a solution already in place.  Registering an instance of <code>NicelyResynchronizingAjaxController</code> as the testing engine ajax controller makes the ajax call synchronous and removes the dependency on the <code>Thread.sleep(x)</code> call.  I reposted my findings and that user was then off to "write tons of code lines".</p>

<p>Shortly after another user requested if someone could share a sample application showing how to make it all work together as our posts had bits and pieces of code here and there. So I am about to share a sample and explain the tests a little.  I won't go into much detail about the rest of the sample (its pretty simple) but you can always ask questions if needed in the comments/email/twitter/google group and I'll try to help.</p>

<p>For reference, here is the <a href="https://groups.google.com/d/topic/play-framework/ut9DQ1numsA/discussion">google group thread</a>.</p>

<h2>Stupid simple Events sample application</h2>

<p><a href="https://github.com/briannesbitt/PlaySampleWithJWebUnitWithAjax">https://github.com/briannesbitt/PlaySampleWithJWebUnitWithAjax</a></p>

<p>The application is very simple and only has 1 page.  It allows a user to create a new Event (id,title) and uses ajax to submit that to the server.  A notification result (OK,ERROR) is displayed for the user.  All existing Events are listed, and dynamically updated, at the bottom in a simple &lt;ul&gt; list.  The application uses the bundled in-memory database H2 so there really isn't anything to setup or configure.  The json responses from the server are in the following form:</p>

<pre class="brush: java">
{ "status": "OK", "msg": "some message" }
{ "status": "ERROR", "msg": "some message" }
</pre>

<h2>Try it out!</h2>

<pre class="brush: plain">
git clone git://github.com/briannesbitt/PlaySampleWithJWebUnitWithAjax.git
cd PlaySampleWithJWebUnitWithAjax
play autotest
</pre>

<p>You can also run <code>play test</code> and then browse to <code>http://127.0.0.1:9000</code> to try it or <code>http://127.0.0.1:9000/@tests</code> to run the tests manually.</p>

<h2>Lets look at the tests</h2>

<p>I have a <code>BaseFunctionalTest</code> class that extends the Play framework <code>FunctionalTest</code>.  This creates the <code>WebTester</code>, configures the default browser to mimic and initializes the base url using <code>setBaseUrl()</code>.  There is a <a href="http://jwebunit.sourceforge.net/quickstart.html">JWebUnit quick start guide</a> if you need to familiarize yourself first.</p>

<pre class="brush: java">
public abstract class BaseFunctionalTest extends FunctionalTest
{
   protected WebTester wt;
   protected BrowserVersion defaultBrowserVersion = BrowserVersion.INTERNET_EXPLORER_8;

   @Before
   public void before()
   {
      wt = new WebTester();
      wt.getTestContext().setUserAgent(defaultBrowserVersion.getUserAgent());
      if (wt.getTestingEngine() instanceof HtmlUnitTestingEngineImpl)
      {
         ((HtmlUnitTestingEngineImpl) wt.getTestingEngine()).setDefaultBrowserVersion(defaultBrowserVersion);
      }
      wt.setBaseUrl(getRouteAbsolute("Application.index"));
      wt.getTestingEngine().setIgnoreFailingStatusCodes(false);
   }
   protected String getRouteAbsolute(String action)
   {
      Router.ActionDefinition route = Router.reverse(action);
      route.absolute();
      return route.url;
   }
   protected String getRoute(String action)
   {
      return Router.reverse(action).url;
   }
}
</pre>

<p>There are 4 simple tests in the <code>test\ApplicationTest.java</code> file.</p>

<h2>testIndexRendersSuccessfully()</h2>

<pre class="brush: java">
@Test
public void testIndexRendersSuccessfully()
{
   wt.beginAt(getRoute("Application.index"));
   wt.assertElementPresent("createEvent");
   assertEquals(wt.getElementById("error").getTextContent(), "");
   assertEquals(wt.getElementById("success").getTextContent(), "");
}
</pre>

<p>This first test is a simple test to ensure the index page gets rendered properly and just checks a few html elements on the page.</p>

<h2>testCreateEventFailsWithBlankTitle()</h2>

<pre class="brush: java">
@Test
public void testCreateEventFailsWithBlankTitle() throws InterruptedException
{
   wt.beginAt(getRoute("Application.index"));
   wt.setTextField("title", "");
   wt.clickButton("createEvent");

   Thread.sleep(2000); // <--- Required since you have to wait for the round trip !!

   wt.assertTextInElement("error", "Required");
   assertEquals(wt.getElementById("success").getTextContent(), "");
}
</pre>

<p>The second test tries to submit the form using ajax with a blank title value.  We want to check the <code>&lt;div id="error"&gt;</code> for the error message, but we have to wait for a length of time to allow the ajax call to complete.  I choose 2 seconds since that <b>seems</b> like a reasonable amout of time.</p>

<h2>testCreateEventSuccessAjaxAsync()</h2>

<pre class="brush: java">
@Test
public void testCreateEventSuccessAjaxAsync() throws InterruptedException
{
   wt.beginAt(getRoute("Application.index"));
   wt.setTextField("title", "My New Event");
   wt.clickButton("createEvent");

   Thread.sleep(2000); // <--- Required since you have to wait for the round trip !!

   assertEquals(wt.getElementById("error").getTextContent(), "");
   wt.assertTextInElement("success", "Created Event with Id:");
   assertEquals(1, Event.count());
}
</pre>


<p>The third test successfully creates a new Event via ajax.  Again we need to <code>Thread.sleep(2000)</code> to wait for the ajax call to return so we can assert the <code>&lt;div id="success"&gt;</code> gets populated with the success nofication text and that the DB has an Event.</p>

<h2>testCreateEventSuccessAjaxSync()</h2>

<pre class="brush: java">
@Test
public void testCreateEventSuccessAjaxSync()
{
   wt.beginAt(getRoute("Application.index"));

   // This will make the ajax call synchronous - no more Thread.sleep() !
   if (wt.getTestingEngine() instanceof HtmlUnitTestingEngineImpl)
   {
      ((HtmlUnitTestingEngineImpl) wt.getTestingEngine()).getWebClient().setAjaxController(new NicelyResynchronizingAjaxController());
   }

   wt.setTextField("title", "My New Event Title");
   wt.clickButton("createEvent"); // <--- ajax call becomes synchronous
   assertEquals(wt.getElementById("error").getTextContent(), "");
   assertEquals(1, Event.count());
   List<Event> events = Event.findAll();
   wt.assertTextInElement("success", "Created Event with Id:" + events.get(0).id);
}
</pre>

<p>The final test successfully creates a new Event via ajax, but this time we have setup an instance of <code>NicelyResynchronizingAjaxController</code> as the testing engine ajax controller.  This makes the ajax call synchronous.  This allows us to avoid the unknown length of <code>Thread.sleep()</code> time and we can continue our test ensuring that the <code>&lt;div id="success"&gt;</code> gets populated correctly and infact there is a new Event in the db.</p>

<p>I would of course push this code to the base class but have left it here in the test for the purposes of this sample.</p>

<p>This is not meant to be an exhaustive test suite, but simply serves to show how to setup JWebUnit to process ajax requests synchronously to make your tests more robust and increase their dependability.</p>

<h2>%test.play.pool=2</h2>

<p>Also just thought I would mention this since it took me a moment to realize what was happening when I first started using JWebUnit.  When you run play in dev or test mode it by default only creates the server execution pool with 1 thread.  When I ran my first JWebUnit functional test it worked when testing against the <code>http://www.playframework.org</code> homepage but failed (read hung forever) when I started using <code>http://127.0.0.1:9000</code>.  I realized that the 1 executor thread was responding to my test run and I was causing a deadlock when making the test call back to the server again.  Adding the line <code>%test.play.pool=2</code> to the <code>application.conf</code> was the easy solution.</p>