<ul>
  <?foreach($posts as $post):?>
   <div class="post">
      <div class="title"><a href="<?=$this->urlFor($post)?>"><?=$post->title?></a></div>
      <p class="date"><?=$this->formatPosted($post)?></p>
   </div>
  <?endforeach;?>
</ul>
