<?/*ANSI colour support in Windows for the Play! Framework 2.0 preview*/?>

<?$this->followUpTo('windows-support-for-play-framework-2-preview')?>

<p>As pointed out today the Windows version outputs various ANSI color codes rather than changing the color of the text.  There aren't many so they don't really get in the way, we just don't get color.</p>

<p>For example when creating a new project in a directory that contains files you should get an error message in red:</p>
<p><code style="color:red">The directory is not empty, cannot create a new application here.</code></p>

<p>Well on windows at the moment you get:</p>
<p><code>[31mThe directory is not empty, cannot create a new application here.[0m</code></p>

<p>The fix comes via <a href="http://adoxa.110mb.com/ansicon/index.html">Ansicon</a>, a clever C app that "provides ANSI escape sequence recognition for Windows console programs (both 32- (x86) and 64-bit (x64)). It is basically the Windows equivalent of ANSI.SYS".</p>

<p>Here we go with the instructions. First, you are going to have to re-download the <code>play.bat</code> file as I have pushed an update to integrate Ansicon. I augmented the batch file with a registry check to see which OS bitness is installed which is needed later.</p>

<p>Download <a href="https://github.com/briannesbitt/Play20/blob/windows-support/play.bat">play.bat</a> and put it in <code>c:\play-2.0</code></p>

<p>Now for Ansicon.  They provide seperate binaries for 32 and 64 bit.</p>

<p>Download <a href="<?=$urlBase?>/downloads/playAnsicon.zip">playAnsicon.zip</a> and unzip it to <code>c:\play-2.0</code>.  This should give you the following directories <code>c:\play-2.0\ansicon\x86</code> and <code>c:\play-2.0\ansicon\x64</code> each with some dll's and their respective <code>ansicon.exe</code>.</p>

<p>That should be it.  Now when you run the <code>play.bat</code> script you should see some glorious <span style="color:blue">c</span><span style="color:red">o</span><span style="color:green">l</span><span style="color:red">o</span><span style="color:blue">u</span><span style="color:green">r</span>!</p>

<p>Someone on the playframework group mentioned that the same issue occurs with Jenkins.  This fix should be able to work with it as well.  Basically I found you can ansicon 2 ways.  If you run <code>ansicon.exe -p</code> from the cmd prompt, then any commands after that should interpret ANSI colour.  I use <a href="http://sourceforge.net/projects/console/">console2</a> as a Windows console replacement, mostly for the tabs, and I found this initial method didn't work because of the way cmd gets wrapped by the parent application.  The other way you can run ansicon is by piping output to it and have it echo it while interpreting ANSI escape commands along the way, <code>myFunCommandThatDoesSomething | ansicon.exe -t</code>.</p>

<p>I hope at some point this can get pulled into the master trunk for us Windows folk.</p>