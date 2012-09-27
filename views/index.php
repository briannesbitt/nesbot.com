<?foreach($posts as $post):?>
<div class="post">
  <h1><a href="<?=$this->urlFor($post)?>"><?=$post->title?></a></h1>
  <div class="date"><?=$this->formatPosted($post)?></div>
</div>
<?endforeach;?>
