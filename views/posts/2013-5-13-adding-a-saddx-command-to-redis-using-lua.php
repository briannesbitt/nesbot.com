<?/*Adding a SADDX command to Redis using Lua*/?>

<p>A popular use case for Redis is as an in-memory cache.  Most see it as an advanced memcached on steroids.  The <b>advanced</b> part is right there in its description on <a href="http://redis.io/">redis.io</a> : <b>advanced key-value store</b> that can contain strings, hashes, lists, sets and sorted sets.  If you don't know about the data structure server called Redis, then read about it at <a href="http://redis.io/">redis.io</a>.</p>

<h2>Why add if exists?</h2>

<p>An add if exists pattern is useful when using Redis as a memory cache server.  Suppose you have a blog that has posts and those posts have tags.  One day you introduce a new Redis memory cache into production.  When a post is read you neatly get all of the tags associated with the blog post. You of course are caching those tags using a Redis set.  The cache is empty so you grab the tags from the database and perform <code>SADD</code>'s to populate the cache. Good Work! But consider what happens when the first action is adding a new tag to an existing post.  You write the new tag to the database and <code>SADD</code> it into the cache.  Now the cached set just has the single tag in it and is missing any previous tags.  Now when a post is viewed the cached set exists and your incomplete list of tags is shown. Zoiks !</p>

<h2>Solutions</h2>

<p>There are a few ways to solve this, but the easiest is to only <code>SADD</code> the new value if the set already exists.  To accompany this, on reads when the cache is empty you repopulate the full list from your permanent datastore and everything works as expected.</p>

<h2>Atomicity</h2>

<p>Knowing that we should be checking for the set first, lets look at a potential implementation.</p>

<pre><code>
key = "post:11:tags"  // Lets assume the post id is 11
tag = "code"

if (redis.exists(key)) {
   redis.sadd(key, tag)
}
</code></pre>

<p>Another option would be to use <code>SCARD</code> and check for a <code>> 0</code> result.  The real issue though is there is a race condition.  Any number of things can occur between the check for existence and the <code>SADD</code>.  Another client could delete the set, you could hit a memory limit and Redis expires the key or an admin trying to be funny executes a <code>FLUSHALL</code> at the wrong time.  Any of those (and others) would cause the issue illustrated above to occur.  This is because the <code>EXISTS</code> and <code>SADD</code> when executed by a client do not run in an atomic way, other commands can get executed in between.  Redis does have the concept of transactions, but the result of one command in the transaction can not be used to influence another command in the same transaction.</p>

<p>The closest commands Redis has at the moment are <code>LPUSHX</code> and <code>RPUSHX</code>, but both only operate on lists so they don't currently help us.  There is also a <code>WATCH</code> command but if you are using Redis 2.6+ a simple Lua solution is much easier to understand.</p>

<h2>Enter SADDX and Lua</h2>

<p>According to the Redis documentation each Lua script command is executed atomically.  That is to say no other script or Redis command will be executed while a script is being executed.  This will protect us against our race condition. Yeah!</p>

<p>I started with the following Lua script.</p>

<pre><code>
if redis.call('type', KEYS[1]) == 'set' then
   return redis.call('sadd', KEYS[1], ARGV[1])
else
   return nil
end
</code></pre>

<p>I changed the check to use the <code>TYPE</code> command to check for the existence and type of the key. This script would support the following usage:</p>

<pre><code>
    EVALSHA 1c3bc2f2cae54a34f52206df01a549a07f240115 1 post:11:tags code
</code></pre>

<p>This however didn't work as expected and since this is also my first attempt with any Lua scripting the answer wasn't obvious for me at first.  After a bit I found the <code>type</code> function so I could determine what the <code>redis.call</code> was returing since it apparently wasn't a string.  Turns out it was of type <code>table</code>, which in Lua is an aggregate data type used for storing any type of collection (lists, sets, arrays, hashes etc.).  If you look at the <a href="http://redis.io/commands/type">TYPE</a> command its return value is indicated as a <code>status reply</code>.  The <a href="http://redis.io/commands/eval">EVAL</a> command documentation has a section on converting from Redis response types to Lua types and it indicates a status reply gets converted to a Lua table with a single <code>ok</code> field containing the status. Ah ha!</p>

<p>Now we can alter our Lua script to get to a working implementation.</p>

<pre><code>
if redis.call('type', KEYS[1]).ok == 'set' then
   return redis.call('sadd', KEYS[1], ARGV[1])
else
   return nil
end
</code></pre>

<p>We have successfully created an atomic <code>SADDX</code> command using Lua and can safely add if exists our tags.</p>

<p>For completeness sake, the <code>SCARD</code> command returns an integer so its implementation would look like:</p>

<pre><code>
if redis.call('scard', KEYS[1]) > 0 then
   return redis.call('sadd', KEYS[1], ARGV[1])
else
   return nil
end
</code></pre>

<h2>Closing notes</h2>

<p>As both have a <code>O(1)</code> time complexity (they'll each run at a consistent speed no matter how many keys or set members) you would just have to benchmark each using a simple scenario to determine which is faster.</p>

<p>This is generally a fire and forget type statement, so for either you could safely remove the else portion.  It seems the script returns a <code>nil</code> anyway, but I would favour leaving the return of the SADD command.  This allows you to check the return if required where a <code>nil</code> (or <code>null</code>, depending on your client language of choice) is returned when the set doesn't exist, a 0 if the value already exists in the set and a 1 if the value was added.</p>

<p>This leaves our final implementation as:</p>

<pre><code>
if redis.call('scard', KEYS[1]) > 0 then
   return redis.call('sadd', KEYS[1], ARGV[1])
end
</code></pre>