<?/*Adding initial Windows support for the Play! Framework 2.0 preview*/?>

<p>As many of you saw the Play framework team released their plans on the upcoming <a href="http://www.playframework.org/2.0">2.0 release</a>.  I tried it out on a remote linux machine and it worked as expected.  My desktop runs Windows though and the current preview package doesn't work on Windows yet, but its a pretty simple task to get it up and running for now until official support is added.</p>

<p>Download the <a href="http://download.playframework.org/releases/play-2.0-preview.zip">preview package</a> and unzip to <code>c:\</code> (or a directory you prefer, but the rest of the post will assume <code>c:\</code>) so you now have a <code>c:\play-2.0</code> directory.</p>
<p>Download <a href="https://github.com/briannesbitt/Play20/blob/windows-support/play.bat">play.bat</a> and put it in <code>c:\play-2.0</code></p>
<p>Download <a href="https://github.com/briannesbitt/Play20/blob/windows-support/framework/build.bat">build.bat</a> and put it in <code>c:\play-2.0\framework</code></p>

<p>In the following commands I'll use the full path to the <code>play.bat</code> file as some of you will probably have a previous version of <code>play.bat</code> in your PATH and we want to ensure we are running the correct version.</p>

<p>Now lets go ahead and create a new project.</p>

<pre class="brush: bash">
c:
cd \
mkdir newproject
cd newproject
c:\play-2.0\play.bat new
</pre>

<p>The framework will now ask for an application name.  You can specify something else or just hit ENTER to accept the default <code>newproject</code>.  Once accepted a full Play! 2.0 project will be created in the current directory.</p>

<p>Now lets go ahead and run the new project via the new Play! console.</p>

<pre class="brush: bash">
c:\play-2.0\play.bat    #shows some [info] logs and then runs the console
</pre>

<p>Once the console is running and waiting for your command, type <code>run</code>.  Now open a browser to <code>http://127.0.0.1:9000</code> and say Hello!</p>

<p>You can also launch the console and auto run with <code>c:\play-2.0\play.bat run</code> as usual.  Hitting CTRL-D will just drop you back to the console.</p>

<p>I forked the project on github and had recompiled the framework to test the .bat files and I got some weird dependency issues.  I fixed it by deleting my <code>~/.iv2</code> contents which for me was located at <code>c:\Users\brian\.ivy2</code>.  This tip was taken from the <code>Play20/README.textile</code> repo so thanks to guillaume for that simple but useful comment, otherwise this wouldn't have gotten done.</p>

<p>Looks like this means I now have to start looking into <a href="http://www.scala-lang.org/">Scala</a> some more.</p>

<p>Congrats to the Play! team and also to the community as we get to reap the benefits of their hard work!</p>

<?$this->linkPost('ansi-colour-support-for-play-framework-2-preview', function($url, $title) {?>
   <p class="quote"><b>UPDATE:</b> to fix the ANSI control characters in the play output see <a href="<?=$url?>"><?=$title?></a></p>
<?});?>