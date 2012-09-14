<div class="post">
    <div class="title"><?=$post->title?></div>
    <div class="date"><?=$this->formatPosted($post)?></div>

    <?$this->partial('share.php', ['t' => $post->title, 'u' => $this->urlFullFor($post), 'uid' => 'Top'])?>
    <?$this->renderPost($post)?>
    <?$this->partial('share.php', ['t' => $post->title, 'u' => $this->urlFullFor($post), 'uid' => 'Bottom'])?>

    <div id="post-nav">
        <span><?if($prev!=null) printf('<a href="%s"><- %s</a>', $this->urlFor($prev), $prev->title)?>&nbsp;</span>
        <span><a href="/">Home</a></span>
        <span><?if($next!=null) printf('<a href="%s">%s -></a>', $this->urlFor($next), $next->title)?>&nbsp;</span>
        <div class="c"></div>
    </div>
</div>

<?$this->partial('disqus.php')?>