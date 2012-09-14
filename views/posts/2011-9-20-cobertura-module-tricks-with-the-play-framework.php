<?/*Tricks for using the cobertura module with the Play Framework*/?>
<p><i><a href="http://cobertura.sourceforge.net/">Cobertura</a> is a free Java tool that calculates the percentage of code accessed by tests. It can be used to identify which parts of your Java program are lacking test coverage. It is based on jcoverage.</i></p>

<p>I had a few frustrating (actually rather simple, but frustrating none the less) moments when first starting to use the cobertura module.  I have documented them here to try and help others quickly bypass the things I found.  Admittedly they are rather small issues, but apparently tricky enough as I have seen more than a few threads on the google group about them.  My current working configuration is at the bottom of this post if you want to bypass the why (and miss all the fun!!).</p>

<h2>Default configuration issues</h2>

<p>I know it mentions how to setup the configuration in the docs, but the default <code>application.conf</code> file created by <code>play new</code> adds some confusion.  It sets up the module line for cobertura but does not include the rest of the required config.  Not to mention it references a default cobertura directory, but whether you <code>play install cobertura</code> or use the <code>dependencies.yml</code> file, it gets put in a cobertura-2.2 (ie. latest version) so you have to update the referenced directory anyway.  But the real issue is if you just enable this line you <b>won't get proper code coverage results</b> - at least not consistently.</p>

<p>On the <a href="http://www.playframework.org/modules/cobertura-2.2/home">module documentation page</a> it shows the following sample configuration usage.</p>

<pre class="brush: plain">
%test.module.cobertura=&#36;{play.path}/modules/cobertura
%test.play.tmp=none
%test.cobertura.silent=false
</pre>

<p>Line 1 should actually reference the module in a directory stamped with the version.  This means that the line in your conf file should be (based on the version you have installed):</p>

<pre class="brush: plain">
%test.module.cobertura=&#36;{play.path}/modules/cobertura-2.2
</pre>

<h2>Setting the tmp folder to none</h2>

<p>Now, you can run with just the first line, but you may need to run <code>play clean</code> first otherwise you might get coverage reports of 0% everywhere if your class files have already been compiled but not enhanced.  Setting the play tmp to none ensures your class files will get regenerated and enhanced every time and will prevent the 0% coverage.</p>

<h2>Silent mode... should it be True or False?</h2>

<p>I expected this configuration setting to work exactly the opposite as it does.  Usually if I want something to output nothing, like a shell command or script, I look for a "-q" (quiet) or "-qq" (very quiet/silent) option and turn it on to suppress output.  The documentation usage sample has this set to false, which initially makes sense since I don't want it to be silent, I <b>want</b> it to generate the coverage report.  This is <b>not</b> how the configuration option works.  Lets look at the code to see what is happening.</p>

<pre class="brush: java">
String silentString = Play.configuration.getProperty("cobertura.silent", DEFAULT_SILENT_MODE);

boolean silent = Boolean.parseBoolean(silentString);

if(silent){
    Logger.trace("Cobertura plugin: Add Cobertura Shutdown Hook");
    // register a shutdown hook so that the Cobertura coverage report
    // will be generated on shutdown
    Runtime.getRuntime().addShutdownHook(new CoberturaPluginShutdownThread());
}else{
    Logger.debug("Cobertura plugin: Not add Cobertura Shutdown Hook. Work with explicit call");
}
</pre>

<p>You can easily see this code requires the <code>cobertura.silent</code> configuration set to <code>true</code> to install the shutdown hook which will generate the report at shutdown.  As I said, the opposite to what I expect.  As it turns out, the <code>DEFAULT_SILENT_MODE</code> is set to true so you can remove this configuration setting altogether and it will setup the shutdown hook by default.  If you do have it set to false, you have to trigger the report generation manually.  When your application is running in test mode you can browse to <code>http://127.0.0.1:9000/@cobertura</code> to view a generated report, generate a new one or clear the current one.</p>

<p>Since you can always use the generate report url to trigger the event regardless of the silent setting, I would suggest to rename this configuration to <code>cobertura.installShutdownHook</code>.  I leave the exercise as to what you expect to happen with true/false settings to you :-)</p>

<h2>Ignoring classes in coverage report</h2>

<p>According to the same documentation page you can apply an ignore list via comma delimited class names or comma delimited regex's.</p>

<pre class="brush: plain">
%test.cobertura.ignore=DocViewerPlugin,Cobertura,CheatSheetHelper,PlayDocumentation
%test.cobertura.ignore.regex=*Plugin
</pre>

<p>When I first tried this it <b>didn't work</b> for me.  I still got the classes I wished to ignore in the code coverage report.  As it seems to be a regular occurrence, I opened up the src.</p>

<p>Opening the CoberturaPlugin class <code>playframework\modules\cobertura-2.2\src\play\modules\cobertura\CoberturaPlugin.java</code> you want to look at the <code>public void enhance(ApplicationClass applicationClass)</code> method.  This is the method that gets called by the framework to enhance the compiled bytecode and inject the necessary cobertura instrumentation for tracking code coverage.  The code in that method we want to check is:</p>

<pre class="brush: java">
// - don't instrument specific classes define in cobertura.ignore
String ignoreString = Play.configuration.getProperty("cobertura.ignore");

if(ignoreString != null){
   String[] ignoreTab = ignoreString.split(",");
   for (String ignore : ignoreTab) {
      if(applicationClass.name.equals(ignore)){
         return;
      }
   }
}
</pre>

<p>After that the following code traces the class that passed the ignore list and is about to be instrumented:</p>

<pre class="brush: java">
Logger.trace("Cobertura plugin: Instrumenting class %s", applicationClass.name);
</pre>

<p>If we see this trace it means the class didn't get ignored and it would get included in the coverage report. So if we set the conf file to log at the trace level "application.log=TRACE" and run the application in test mode <code>play test</code> we can watch the traces to see what is going on.</p>

<pre class="brush: java">
16:29:08,805 TRACE ~ Cobertura plugin: Instrumenting class helpers.CheatSheetHelper$1
...
16:29:08,956 TRACE ~ Cobertura plugin: Instrumenting class helpers.CheatSheetHelper
...
16:29:09,259 TRACE ~ Cobertura plugin: Instrumenting class controllers.Cobertura
...
</pre>

<p>You can quickly see that the <code>applicationClass.name</code> is the full name with package and this is why the class is not getting ignored. The provided sample doesn't use the full name.  So the easy fix is to specify the ignore list like so:</p>

<pre class="brush: plain">
%test.cobertura.ignore=DocViewerPlugin,controllers.Cobertura,helpers.CheatSheetHelper,helpers.CheatSheetHelper$1,helpers.CheatSheetHelper$2,helpers.CheatSheetHelper$3,controllers.PlayDocumentation
</pre>

<p>Now when you run the tests and regenerate the code coverage report you will only see your classes.</p>

<h2>Ignore classes in coverage reports using regular expressions</h2>

<p>On Aug 30, 2011 the module was updated to version 2.2 and included a feature allowing you to ignore classes using a regular expression.  This will help us simplify our ignore statement.  The new code uses the same <code>applicationClass.name</code> property when matching against the regex so it still includes the package name.  The new minimized ignore configuration looks like this:</p>

<pre class="brush: plain">
%test.cobertura.ignore=DocViewerPlugin,controllers.PlayDocumentation
%test.cobertura.ignore.regex=*Cobertura*,helpers.CheatSheetHelper*
</pre>

<p>Of course there are a few different ways to write those, but I went with what you see and haven't seen any unwanted classes being shown.  You'll also want to add any other plugins that you use that would muddy your coverage report.</p>
<h2>Automatically ignoring your test classes</h2>

<p>The author of the plugin has added code to automatically ignore some default class names.  First, if you name your test classes ending with <code>Test</code> then it will get ignored.  The have also auto ignored the play TestRunner.  However, I am not so sure this is necessary since we have the ignore feature.  Its probably best in the configuration rather than hard coded.</p>

<pre class="brush: java">
// check if we should instrument this class or not
// - don't instrument Test classes (**/*Test.java)
// - don't instrument the TestRunner class from the test-runner module
if (applicationClass.name.endsWith("Test") || applicationClass.name.equals("controllers.TestRunner")) {
    return;
}
</pre>

<h2>My current working configuration</h2>

<p>I have included my current configuration here. It is only 5 lines, but hopefully it will be smoother for the next person.</p>

<pre class="brush: plain">
%test.module.cobertura=&#36;{play.path}/modules/cobertura-2.2
%test.play.tmp=none
%test.cobertura.silent=true
%test.cobertura.ignore=DocViewerPlugin,controllers.PlayDocumentation
%test.cobertura.ignore.regex=*Cobertura*,helpers.CheatSheetHelper*
</pre>

<p>I will just finish this by saying that this is <b>not meant</b> to be a dig on the author of the module in any way.  The author's github profile shows that he works from Paris, France and maybe some of the confusion is from english not being their first language.  From the looks of the comments and module documentation its possible, but I can safely say his english is better than my french :-)  Maybe next time I will just fork his repo and submit a pull request with some of these changes and contribute, like they did, and save myself from writing this much again.  Either way I'll be continuing to use the module.</p>