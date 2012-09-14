<?/*A "quick" microbenchmark*/?>

<p>The other day I decided to do some microbenchmarking across several different languages.  I used an array of 1 M integers and applied the <a href="http://en.wikipedia.org/wiki/Quicksort">quicksort</a> sorting algorithm as the test code and also included the native language sort routines where applicable.</p>

<p>I wrote the runner using <a href="http://coffeescript.org/#cake">Cake</a> and <a href="http://coffeescript.org">Coffeescript</a> which compiles to Javascript and runs on <a href="http://nodejs.org/">node.js</a>.  Cake is a very simple build system and is similar to make/rake, as you probably already guessed.</p>

<p>Each respective test will run the sort algorithm 5 times (some only 3 because they take so long already it doesn't matter) and the minimum execution time is printed.  I ran each one a few times and took an eyeball average of those runs.  I am not that interested in a +/- of a few milliseconds when sorting 1 M integers.  I am way more interested in orders of magnitude.</p>

<h2>Results</h2>

<p><table class="bordered">
   <tr><th></th><th>Language</th><th>Version</th><th>Time in milliseconds</th></tr>
   <tr><td>1</td><td>C++</td><td>MS 16.00.30319.01 for 80x86</td><td>75</td></tr>
   <tr><td>2</td><td>C# Array.Sort()</td><td>.NET 3.5</td><td>100</td></tr>
   <tr><td>3</td><td>Java</td><td>1.6.0_23</td><td>105</td></tr>
   <tr><td>4</td><td>Groovy api (aka java)</td><td>1.8.5</td><td>110</td></tr>
   <tr><td>5</td><td>Java Arrays.sort()</td><td>1.6.0_23</td><td>121</td></tr>
   <tr><td>6</td><td>C#</td><td>.NET 3.5</td><td>126</td></tr>
   <tr><td>7</td><td>Scala</td><td>2.9.1.final</td><td>128</td></tr>
   <tr><td>8</td><td>Node.js (v8)</td><td>0.6.7</td><td>175</td></tr>
   <tr><td>9</td><td>Chrome JavaScript</td><td>16.0.912.77</td><td>182</td></tr>
   <tr><td>10</td><td>Ruby api array.sort!</td><td>1.9.2p290</td><td>250</td></tr>
   <tr><td>11</td><td>Firefox JavaScript</td><td>10.0</td><td>271</td></tr>
   <tr><td>12</td><td>IE JavaScript</td><td>9.0.8112.16421</td><td>307</td></tr>
   <tr><td>13</td><td>IE JavaScript api sort()</td><td>9.0.8112.16421</td><td>375</td></tr>
   <tr><td>14</td><td>Node.js (v8) api sort()</td><td>0.6.7</td><td>480</td></tr>
   <tr><td>15</td><td>Chrome JavaScript api sort()</td><td>16.0.912.77</td><td>520</td></tr>
   <tr><td>16</td><td>Python api sort()</td><td>3.1.3</td><td>814</td></tr>
   <tr><td>17</td><td>PHP api sort()</td><td>5.3.8</td><td>1441</td></tr>
   <tr><td>18</td><td>Firefox JavaScript api sort()</td><td>10.0</td><td>3490</td></tr>
   <tr><td>19</td><td>Ruby</td><td>1.9.2p290</td><td>3520</td></tr>
   <tr><td>20</td><td>Groovy</td><td>1.8.5</td><td>4100</td></tr>
   <tr><td>21</td><td>Python</td><td>3.1.3</td><td>8100</td></tr>
   <tr><td>22</td><td>PHP</td><td>5.3.8</td><td>9700</td></tr>
</table></p>

<h2>Things I will take away from this exercise</h2>

<p>Switching between too many languages during a day is tough and not very efficient.  I haven't done much coding in some of these languages, or not at least for awhile.  I didn't try to use the adopted paradigms in each language.  I did try to keep the overall structure the same for each language.  When you do a google search for quicksort some of the hits for ruby, python, javascript and scala show these "elegant" 1-3 liners of code that make me think I am looking at perl.  Needless to say, I tried a few but they ran quite a bit slower than my bloated version.  Thats not to say that some performance tuning could be done for each language's nuances, but as I mentioned, I am interested in the orders of magnitude comparison rather than a pure speed test.</p>

<p>It was interesting to see how easy some of the features were to find in some languages compared to others.  Command line parameters, random numbers, benchmarking code etc.  Lets take a look at command line parameters in each language.  They are all similar (some variation of an arg[sv] array) but yet different (array offset, parsing, brackets, semi-colon).  Its these subtle differences that make a lot of language context switching difficult.</p>

<p><pre>
C++                      int len = atoi(argv[1]);
C#                       var len = int.Parse(args[0]);
java:                    int len = Integer.parseInt(args[0]);
scala:                   val len = Integer.parseInt(args(0))
php:                     $len = $argv[1];
node.js (coffeescript):  len = process.argv[2]
groovy:                  len = args[0].toInteger()
python:                  alen = int(sys.argv[1])     # len is a reserved word
ruby:                    len = ARGV[0].to_i
</pre></p>

<p>A small but effective change included changing <code>pivot = arr[(left+right) / 2]</code> to <code>pivot = arr[(left+right) >> 1]</code>.  In some languages it generated a noticeable increase in performance.  As well it prevented a necessary cast/floor to an int preventing an array lookup of arr[3.5].</p>

<p>Pythons top google links are tied to v2 rather than v3.  Using the old print statement rather than the print() function is pretty frustrating as you view the examples from google and the code seems correct.  The error message was not at all helpful either.  I have seen this in other languages/frameworks as well.</p>

<p>Then we have the browser wars, aka the js engine war.  Chrome posted the best time for the quicksort.  For some reason I was expecting some overhead compared to node.js but the in browser version was pretty much identical in both test cases.  IE was the most consistent and by far had the best time using the Array API sort().  Firefox was really slow on the API call... no idea why.  It was slow enough that I actually got the warning saying the script was running too long.  During the algorithm Firefox was "Not Responding" as well.  I didn't bother running the code in older versions as that wasn't the goal of this exercise.</p>

<p>Consective runs in IE and FireFox produced consistent results.  However, this was not true for Chrome. Chrome's performance decreased by about double for the second run and a little more after that to what seemed to be a ceiling.  It probably has to do with GC since on a page refresh the time returned back to the fast time.</p>

<p>Groovy gets to piggy back off of the JVM for API calls.</p>

<p>Most of the API sorts were faster.  This is not surprising since the api would generally be running in the interpreters native language and are generally written in c++ so the sorts are running native code at that point.  I was surprised that the quicksort algorithm was faster than the API calls for Java and JavaScript.  In Java it is somewhat negligible and if you look at the source the Arrays sort() API is a "tuned" quicksort.  Without spending too much time on it, it <a href="http://code.google.com/p/v8/source/browse/trunk/src/array.js#776">appears that the v8 implementation</a> is doing a Quicksort written in js but makes extra calls to the required compare function among other things.  The compare is necessary as by default the method sorts the elements alphabetically which is obviously not what we wanted.  I assume this is where the overhead comes from, but that seems expensive as its almost 3x longer.</p>

<p>I was also surprised at how well all of the JavaScript engines did compared to the other main stream (server) languages.  The node.js v8 implementation was 10x - 25x faster than most of them and was less than 2x Java/C#.</p>

<p>You can view the code for each implementation on <a href="https://github.com/briannesbitt/QuicksortMicrobenchmark">github</a>.</p>

<p>Although I did have the idea before seeing this, the following blog post provided some good information <a href="http://stronglytypedblog.blogspot.com/2009/07/java-vs-scala-vs-groovy-performance.html">http://stronglytypedblog.blogspot.com/2009/07/java-vs-scala-vs-groovy-performance.html</a></p>

<?$this->linkPost("a-quick-microbenchmark-update-PHP-5-4", function ($url, $title) {?>
   <p class="quote">You can see the results with PHP 5.4 ... <a href="<?=$url?>"><?=$title?></a></p>
<?});?>