<div class="post">
    <h1><?=$post->title?></h1>
    <div class="date"><?=$this->formatPosted($post)?></div>

    <?$this->partial('share.php', array('t' => $post->title, 'u' => $this->urlFullFor($post), 'uid' => 'Top'))?>
    <?$this->renderPost($post)?>
    <?$this->partial('share.php', array('t' => $post->title, 'u' => $this->urlFullFor($post), 'uid' => 'Bottom'))?>

    <div id="post-nav">
        <span><?if($prev!=null) printf('<a href="%s"><- %s</a>', $this->urlFor($prev), $prev->title)?>&nbsp;</span>
        <span><a href="/">Home</a></span>
        <span><?if($next!=null) printf('<a href="%s">%s -></a>', $this->urlFor($next), $next->title)?>&nbsp;</span>
        <div class="c"></div>
    </div>
</div>

<?$this->partial('disqus.php')?>

<script src="<?=$urlJs?>highlight.nums.js"></script>
<script src="<?=$urlJs?>php.js"></script>
<script src="<?=$urlJs?>java.js"></script>
<script src="<?=$urlJs?>scala.js"></script>
<script src="<?=$urlJs?>bash.js"></script>
<script src="<?=$urlJs?>json.js"></script>
<script src="<?=$urlJs?>coffeescript.js"></script>
<script>
   hljs.LANGUAGES.php = php(hljs);
   hljs.LANGUAGES.java = java(hljs);
   hljs.LANGUAGES.scala = scala(hljs);
   hljs.LANGUAGES.bash = bash(hljs);
   hljs.LANGUAGES.json = json(hljs);
   hljs.LANGUAGES.coffeescript = coffeescript(hljs);
   hljs.lineNodes = true;
   hljs.initHighlightingOnLoad();
</script>