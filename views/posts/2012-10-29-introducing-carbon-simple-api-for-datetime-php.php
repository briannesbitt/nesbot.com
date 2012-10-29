<?/*Introducing Carbon : A simple API extension for DateTime with PHP 5.3+*/?>

<p>Unofficially released last month, <a href="https://github.com/briannesbitt/Carbon">Carbon</a> is <code>A simple API extension for DateTime with PHP 5.3+</code>. Code examples are worth a thousand words so lets start with this:</p>

<pre><code class="php">
printf("Right now is %s", Carbon::now()->toDateTimeString());
printf("Right now in Vancouver is %s", Carbon::now('America/Vancouver'));  //implicit __toString()
$tomorrow = Carbon::now()->addDay();
$lastWeek = Carbon::now()->subWeek();
$nextSummerOlympics = Carbon::createFromDate(2012)->addYears(4);

$officialDate = Carbon::now()->toRFC2822String();

$howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

$noonTodayLondonTime = Carbon::createFromTime(12, 0, 0, 'Europe/London');

$worldWillEnd = Carbon::createFromDate(2012, 12, 21, 'GMT');

// comparisons are always done in UTC
if (Carbon::now()->gte($worldWillEnd)) {
   die();
}

if (Carbon::now()->isWeekend()) {
   echo 'Party!';
}

echo Carbon::now()->subMinutes(2)->diffForHumans(); // '2 minutes ago'

// ... but also does 'from now', 'after' and 'before'
// rolling up to seconds, minutes, hours, days, months, years

$daysSinceEpoch = Carbon::createFromTimeStamp(0)->diffInDays();
</code></pre>

<p>For full installation instructions, documentation and code examples you should visit the repo <a href="https://github.com/briannesbitt/Carbon">https://github.com/briannesbitt/Carbon</a> but with <a href="http://getcomposer.org/">composer</a> (or without, but why would you??) you should be up and running in seconds.  If you want to see the project history, its tracked <a href="https://github.com/briannesbitt/Carbon/blob/master/history.md">here</a>. Feedback is welcomed.</p>

<p>I hate reading a readme.md file that has code errors and/or sample output that is incorrect. I tried something new with this project and wrote a quick <a href="https://github.com/briannesbitt/Carbon/blob/master/readme.php">readme parser</a> that can lint sample source code or execute and inject the actual result into a generated readme.  I'll create another post about this, but for now if you want to read more you can continue reading this in the <a href="https://github.com/briannesbitt/Carbon#about-contributing">Contributing</a> section of the project readme.</p>

<h2>What's up next?</h2>

<p>There have been a few feature requests already submitted.  A few users have asked for an immutable implementation.  There are no definite plans, but I think a copy on write implementation would be pretty simple to layer on top.  This might get in there in the future if it gets mentioned a few more times.  The other feature, which was targeted at testability, was an implemention of the <a href="https://github.com/bebanjo/delorean">Delorean time travel API</a> that allows the mocking of <code>Time.now</code> in Ruby.  We went <a href="https://github.com/briannesbitt/Carbon/pull/1">back and forth</a> a bit on the implementation details and ended up closing it for lack of a satisfactory solution that wasn't confusing in the end.  The idea is a good one and some implementation to allow easy mocking of <code>Carbon::now()</code> will get added in the future.  As already mentioned feedback, ideas and code submissions are welcomed.</p>

<h2>Why the name Carbon?</h2>

<p>Read about <a href="http://en.wikipedia.org/wiki/Radiocarbon_dating">Carbon Dating</a>.</p>

<h2>Why require PHP 5.3?</h2>

<p>And finally just a quick note on the PHP 5.3 requirement. The implementation requires PHP 5.3+ since it makes use of <a href="http://www.php.net/manual/en/datetime.add.php">DateTime::add</a> which uses the <a href="http://www.php.net/manual/en/class.dateinterval.php">DateInterval</a> class and those were only introduced in 5.3 simple as that.  Its also more than 3 years old so I doubt this is much of an issue for anyone.</p>