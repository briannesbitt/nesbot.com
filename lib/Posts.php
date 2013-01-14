<?php

interface IPosts
{
   public function findAll();
   public function findBySlug($slug);
   public function next(Post $post);
   public function prev(Post $post);
}

class Posts implements IPosts
{
   // [#] => Post
   private $posts;

   // [slug] => #
   private $postsOrder;

   public function __construct(array $posts, array $postsOrder)
   {
      $this->posts = $posts;
      $this->postsOrder = $postsOrder;
   }

   public function findAll()
   {
      return $this->posts;
   }

   public function findBySlug($slug)
   {
      if (!array_key_exists($slug, $this->postsOrder)) {
         return null;
      }

      $i = $this->postsOrder[$slug];

      return array_key_exists($i, $this->posts) ? $this->posts[$i] : null;
   }

   public function next(Post $post)
   {
      if (!array_key_exists($post->slug, $this->postsOrder)) {
         return null;
      }

      $i = $this->postsOrder[$post->slug];

      return (++$i < count($this->posts)) ? $this->posts[$i] : null;
   }

   public function prev(Post $post)
   {
      if (!array_key_exists($post->slug, $this->postsOrder)) {
         return null;
      }

      $i = $this->postsOrder[$post->slug];

      return (--$i >= 0) ? $this->posts[$i] : null;
   }
}
