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
   private $posts = array();

   // [slug] => #
   private $order = array();

   public function add(Post $post)
   {
      $next = count($this->posts);
      $this->posts[$next] = $post;
      $this->order[$post->slug] = $next;
   }

   public function findAll()
   {
      return $this->posts;
   }

   public function findBySlug($slug)
   {
      if (!array_key_exists($slug, $this->order)) {
         return null;
      }

      $i = $this->order[$slug];

      return array_key_exists($i, $this->posts) ? $this->posts[$i] : null;
   }

   public function next(Post $post)
   {
      if (!array_key_exists($post->slug, $this->order)) {
         return null;
      }

      $i = $this->order[$post->slug];

      return (++$i < count($this->posts)) ? $this->posts[$i] : null;
   }

   public function prev(Post $post)
   {
      if (!array_key_exists($post->slug, $this->order)) {
         return null;
      }

      $i = $this->order[$post->slug];

      return (--$i >= 0) ? $this->posts[$i] : null;
   }
}
