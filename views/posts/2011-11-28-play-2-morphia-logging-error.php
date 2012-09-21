<?/*Using morphia with Play 2.0 and the sl4j logging error*/?>

<p>I am in the middle of another fun side project using <a href="http://playframework.org/2.0">Play 2.0</a> and Scala.  I am (strangely?!?) using <a href="http://code.google.com/p/morphia/">morphia</a> to access <a href="http://mongodb.org">mongoDB</a> as the datastore with my own Scala based wrapper.  Yes I looked at <a href="http://api.mongodb.org/scala/casbah/current/">casbah</a>, <a href="https://github.com/foursquare/rogue">rogue</a>, <a href="https://github.com/novus/salat">salat</a> but came back to morphia in the end since I had used it before.   I'll probably go back to some of those other Scala specific libraries, probaly salat, but it just wasn't my focus this time around.</p>

<p>I came across an issue that boiled down to a logging change from Play 1.X to 2.0.  Its an easy fix actually but the exception was weird (and got worse with subsequent page loads) enough that I'll post it here and hope I can save someone time.</p>

<p>The top of the exception stack that you get looks like this:</p>

<pre><code class="bash">
Caused by: java.lang.IllegalArgumentException: can't parse argument number
   at java.text.MessageFormat.makeFormat(MessageFormat.java:1339) ~[na:1.6.0_23]
   at java.text.MessageFormat.applyPattern(MessageFormat.java:458) ~[na:1.6.0_23]
   at java.text.MessageFormat.<init>(MessageFormat.java:350) ~[na:1.6.0_23]
   at java.text.MessageFormat.format(MessageFormat.java:811) ~[na:1.6.0_23]
   at org.slf4j.bridge.SLF4JBridgeHandler.getMessageI18N(SLF4JBridgeHandler.java:251) ~[jul-to-slf4j.jar:na]
   at org.slf4j.bridge.SLF4JBridgeHandler.callLocationAwareLogger(SLF4JBridgeHandler.java:209) ~[jul-to-slf4j.jar:na]
   at org.slf4j.bridge.SLF4JBridgeHandler.publish(SLF4JBridgeHandler.java:285) ~[jul-to-slf4j.jar:na]
   at java.util.logging.Logger.log(Logger.java:481) ~[na:1.6.0_23]
   at java.util.logging.Logger.doLog(Logger.java:503) ~[na:1.6.0_23]
   at java.util.logging.Logger.logp(Logger.java:672) ~[na:1.6.0_23]
   at com.google.code.morphia.logging.jdk.JDKLogger.log(JDKLogger.java:107) ~[na:na]
   at com.google.code.morphia.logging.jdk.JDKLogger.debug(JDKLogger.java:38) ~[na:na]
   at com.google.code.morphia.mapping.MappedClass.<init>(MappedClass.java:113) ~[na:na]
   at com.google.code.morphia.mapping.Mapper.getMappedClass(Mapper.java:200) ~[na:na]
   at com.google.code.morphia.mapping.Mapper.getCollectionName(Mapper.java:208) ~[na:na]
   at com.google.code.morphia.DatastoreImpl.getCollection(DatastoreImpl.java:546) ~[na:na]
   at com.google.code.morphia.DatastoreImpl.createQuery(DatastoreImpl.java:374) ~[na:na]
   at com.google.code.morphia.DatastoreImpl.find(DatastoreImpl.java:395) ~[na:na]
</code></pre>

<p>Then on the next page reload you start seeing exceptions like this:</p>

<pre><code class="bash">
NoClassDefFoundError: Could not initialize class models.Player$
</code></pre>

<p>Play 2.0 uses slf4j bridge <a href="https://github.com/playframework/Play20/blob/master/framework/play/src/main/scala/play/api/Logger.scala#L166">SLF4JBridgeHandler.install()</a> as it configures the root logger.  This causes morphia to throw errors when the logger is in DEBUG mode.  The last stack trace in morphia code is a call to <a href="http://code.google.com/p/morphia/source/browse/trunk/morphia/src/main/java/com/google/code/morphia/mapping/MappedClass.java#111">log.debug("MappedClass done: " + toString());</a>.  There is a <a href="http://code.google.com/p/morphia/wiki/SLF4JExtension">sl4fj morphia extension</a> that you need to include in your classpath and then ensure you call to register the logger extension at startup and the error no longer happens.  You can put it in a static {} block if you want or the <a href="https://github.com/playframework/Play20/wiki/ScalaGlobal">Global object beforeStart()</a>.</p>

<p>For java the code is:</p>

<pre><code class="java">
MorphiaLoggerFactory.registerLogger(SLF4JLoggerImplFactory.class);
</code></pre>

<p>For Scala the code is:</p>

<pre><code class="scala">
MorphiaLoggerFactory.registerLogger(classOf[SLF4JLogrImplFactory]);
</code></pre>

<p>Depending on where you register the new logger extension you might still see the exception.  The other solution (as provided by <a href="http://software-lgl.blogspot.com/">Green Luo</a> who manages the morphia Play module) is to call the <code>init()</code> which sets the internal <a href="http://code.google.com/p/morphia/source/browse/trunk/morphia/src/main/java/com/google/code/morphia/logging/MorphiaLoggerFactory.java#56">loggerFactory to null</a>.</p>

<pre><code class="java">
MorphiaLoggerFactory.init();
MorphiaLoggerFactory.registerLogger(SLF4JLoggerImplFactory.class);
</code></pre>

<?$this->linkPost('now-running-on-play-2-beta', function ($url, $title) {?>
   <p>Hopefully I'll launch this side project tonight/tomorrow and I'll post about it like I did about <a href="<?=$url?>">moving my blog to Play 2.0</a>.</p>
<?});?>

<p>For reference I have also posted responses on the morphia group as it has been asked there as well and an invalid bug was logged.</p>

<p>
   <a href="https://groups.google.com/d/topic/morphia/Ad8x1BYqD3w/discussion">https://groups.google.com/d/topic/morphia/Ad8x1BYqD3w/discussion</a><br/>
   <a href="http://code.google.com/p/morphia/issues/detail?id=328">http://code.google.com/p/morphia/issues/detail?id=328</a>
</p>