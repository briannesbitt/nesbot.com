<ul>
  <?foreach($posts as $post):?>
   <div class="post">
      <h1><a href="<?=$this->urlFor($post)?>"><?=$post->title?></a></h1>
      <p class="date"><?=$this->formatPosted($post)?></p>
   </div>
  <?endforeach;?>
</ul>
