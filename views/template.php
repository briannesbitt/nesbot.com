<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>

   <?$this->partial('gatracker.php', array('tracker' => $this->gaTracker()))?>
</head>
<body>

<div id="powered">
   <a href="http://slimframework.com/" target="_blank">powered by <img src="<?=$urlImg?>slim.png" alt="Slim Framework" width="40" height="20" align="absmiddle" /></a>
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
            <li>2 yrs @ Contracting</li>
            <li>10 yrs @ <a href="http://www.fuelindustries.com" target="_blank">Fuel Industries</a><br/><span class="sm">Co-Founder & CTO<br/><a href="http://list.canadianbusiness.com/rankings/profit100/2008/DisplayProfile.aspx?profile=87" target="_blank">100 employees, $8M+ in '07</a><br/></span></li>
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
            <li><a href="http://help.slimframework.com" target="_blank">help.slimframework.com</a></li>
            <li><a href="http://deservefinancialfreedom.com" target="_blank">deservefinancialfreedom.com</a></li>
            <li><a href="http://withoutafather.com" target="_blank">withoutafather.com</a></li>
            <li><a href="http://github.com/briannesbitt" target="_blank">github</a></li>
            <li><a href="http://mogade.com" target="_blank">mogade.com</a></li>
            <li><a href="http://hockey.nesbot.com" target="_blank">sidney crosby art ross watch</a></li>
            <li><a href="http://www.ottawatravellers.ca" target="_blank">hockey</a></li>
         </ul>
         <h2>what i play with</h2>
         <ul>
            <li><a href="http://slimframework.com/" target="_blank">slimframework.com</a></li>
            <li><a href="http://mongodb.org/" target="_blank">mongodb.org</a></li>
            <li><a href="http://www.thediv-net.com/" target="_blank">thediv-net.com</a></li>
            <li><a href="http://www.ottawasenators.com/" target="_blank">ottawasenators.com</a></li>
         </ul>
      </div>
   </div>

   <div id="content"><?$this->partial($childView, $this->getData())?></div>
</div>

</body>
</html>
