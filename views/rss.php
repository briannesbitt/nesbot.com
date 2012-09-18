<?echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">

<channel>
   <title>Brian Nesbitt's  Blog</title>
   <link>http://nesbot.com</link>
   <description>Developer who is always consuming knowledge. Startup enthusiast who doesn't enjoy being a drop in a bucket. Dividend stock investor. Lucky husband and father.</description>
   <language>en-us</language>
   <managingEditor>brian@nesbot.com (Brian Nesbitt)</managingEditor>
   <image>
      <url><?=$urlFullImg?>logo.gif</url>
      <title>Brian Nesbitt's  Blog</title>
      <link>http://nesbot.com</link>
   </image>
   <atom:link href="<?=$urlBase?>/rss" rel="self" type="application/rss+xml" />

   <?foreach($posts as $post):?>
   <item>
      <title><?=$post->title?></title>
      <link><?=$this->urlFullFor($post)?></link>
      <author>brian@nesbot.com (Brian Nesbitt)</author>
      <pubDate><?=$post->posted->toRFC822String()?></pubDate>
      <guid><?=$this->urlFullFor($post)?></guid>
      <description><![CDATA[<?$this->renderPost($post)?>]]></description>
   </item>
   <?endforeach;?>
</channel>
</rss>
