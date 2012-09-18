<?php
class BlogView extends MasterView
{
   private $posts;

   public function __construct($app, $masterTemplate = 'template.php', IPosts $posts)
   {
      parent::__construct($app, $masterTemplate);
      $this->posts = $posts;
   }

   public function renderPost(Post $post)
   {
      $this->partial(sprintf('posts/%s-%s.php', $post->posted->format('Y-n-j'), $post->slug));
   }

   public function formatPosted(Post $post)
   {
      return $post->posted->format('M j, Y');
   }

   public function withPost($slug, $callable)
   {
      $callable($this->posts->findBySlug($slug));
   }

   public function linkPost($slug, $callable)
   {
      $post = $this->posts->findBySlug($slug);
      $callable($this->urlFullFor($post), $post->title);
   }

   public function followUpTo($slug)
   {
      $this->linkPost($slug, function ($url, $title) {
         printf('<p class="quote">This is a follow up to <a href="%s">%s</a>... you may want to go and read that first.</p>', $url, $title);
      });
   }

   public function urlFor(Post $post)
   {
      return $this->app->urlFor('post', array('slug' => $post->slug, 'year' => $post->posted->year, 'month' => $post->posted->month, 'day' => $post->posted->day));
   }
   public function urlFullFor(Post $post)
   {
      return $this->urlBase() . $this->urlFor($post);
   }

   public function gaTracker()
   {
      return $this->env['GATRACKER'];
   }
}
