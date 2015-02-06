<!DOCTYPE html>
<html>
<head>
   <title><?php
      $title = 'Consuming Knowledge';
      if (isset($post)) {
         $title = $post->title;
      }
      echo $title;?> -- Brian Nesbitt</title>
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
   <meta name="description" content="Developer who is always consuming knowledge. Startup enthusiast who doesn't enjoy being a drop in a bucket. Dividend stock investor. Lucky husband and father." />
   <link rel="shortcut icon" type="image/png" href="<?=$urlImg?>favicon.png" />
   <link rel="stylesheet" type="text/css" href="<?=$urlCss?>compiled.css" />
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js" type="text/javascript"></script>
   <script src="<?=$urlJs?>compiled.js" type="text/javascript"></script>
   <?$this->partial('gatracker.php', array('tracker' => $this->gaTracker()))?>
</head>
<body>

<div id="powered">
   <a href="http://slimframework.com/" target="_blank">powered by <img src="<?=$urlImg?>slim.png" alt="Slim Framework" width="40" height="20" /></a>
</div>

<div id="social">
   <a href="http://twitter.com/NesbittBrian"><img src="<?=$urlImg?>twitter.png" alt="Follow @NesbittBrian" width="32" height="32" /></a>
   <a href="http://feeds.feedburner.com/BrianNesbittsBlog"><img src="<?=$urlImg?>rss.png" alt="rss" width="32" height="32" /></a>
</div>

<div id="header">
   <h1><a href="/"><span>Consuming</span>Knowledge</a></h1>
</div>

<div id="page">
   <div id="menu">
      <h2>about me</h2>
      <div id="about">
         <ul>
            <li>3 yrs @ <a href="https://markido.com" target="_blank">Markido</a><br/><span class="sm">Co-Founder & CTO<br/></li>
            <li>11 yrs @ <a href="http://www.fuelindustries.com" target="_blank">Fuel Industries</a><br/><span class="sm">Co-Founder & CTO<br/><a href="http://list.canadianbusiness.com/rankings/profit100/2008/DisplayProfile.aspx?profile=87" target="_blank">100 employees, $8M+ in '07</a><br/></span></li>
            <li>2 yrs @ <a href="http://www.lockheedmartin.com/ca.html" target="_blank">Lockheed Martin</a></li>
            <li>Comp Sys B. Eng. @ <a href="http://www.carleton.ca/admissions/programs/computer-systems-engineering/" target="_blank">Carleton U</a></li>
         </ul>
      </div>
      <div id="links">
         <h2>contact</h2>
         <ul>
            <li><a href="http://twitter.com/NesbittBrian">twitter.com/NesbittBrian</a></li>
            <li><a href="mailto:brian@nesbot.com">brian@nesbot.com</a></li>
         </ul>
         <h2>what am i doing</h2>
         <ul>
            <li><a href="https://markido.com" title="Create better PowerPoint presentations in less time." target="_blank"><i>Engage</i></a></li>
            <li><a href="http://github.com/briannesbitt" target="_blank">github</a></li>
         </ul>
         <h2>what i play with</h2>
         <ul>
            <li><a href="http://sparkjava.com/" target="_blank">sparkjava.com</a></li>
            <li><a href="http://www.dropwizard.io/" target="_blank">dropwizard.io</a></li>
            <li><a href="http://slimframework.com/" target="_blank">slimframework.com</a></li>
            <li><a href="http://www.ottawasenators.com/" target="_blank">ottawasenators.com</a></li>
            <li><a href="http://deservefinancialfreedom.com" target="_blank">deservefinancialfreedom.com</a></li>
         </ul>
      </div>
   </div>

   <div id="content"><?$this->partial($childView, $this->getData())?></div>
</div>

</body>
</html>
