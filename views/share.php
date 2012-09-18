<?php
$t = urlencode($t);
$u = urlencode($u);
?>

<p class="share">
   <a id="share-twitter-<?=$uid?>" href="http://twitter.com/home?status=<?=$t?>%20<?=$u?>%20@NesbittBrian" target="_blank"><img src="<?=$urlImg?>share/tweet.png" width="55" height="20" alt="Tweet this" title="Tweet this" /></a>
   <a id="share-fb-<?=$uid?>" href="http://www.facebook.com/sharer.php?u=<?=$u?>&t=<?=$t?>" target="_blank"><img src="<?=$urlImg?>share/fb.png")" width="63" height="20" alt="Share on facebook" title="Share on Facebook" /></a>
   <a id="share-hn-<?=$uid?>" href="http://news.ycombinator.com/submitlink?u=<?=$u?>&t=<?=$t?>" target="_blank"><img src="<?=$urlImg?>share/hn.png")" width="20" height="20" alt="Share on Hacker News" title="Share on Hacker News" /></a>
   <a id="share-dz-<?=$uid?>" href="http://www.dzone.com/links/add.html?url=<?=$u?>&title=<?=$t?>" target="_blank"><img src="<?=$urlImg?>share/dz.png")" width="21" height="20" alt="Share on DZone" title="Share on DZone" /></a>
</p>

<script type="text/javascript">
$(document).ready(function()
{
   $("#share-twitter-<?=$uid?>").click( function() {gaTrackEvent("SocialLinks", "Twitter<?=$uid?>"); } );
   $("#share-fb-<?=$uid?>").click( function() {gaTrackEvent("SocialLinks", "Facebook<?=$uid?>"); } );
   $("#share-hn-<?=$uid?>").click( function() {gaTrackEvent("SocialLinks", "HackerNews<?=$uid?>"); } );
   $("#share-dz-<?=$uid?>").click( function() {gaTrackEvent("SocialLinks", "DZone<?=$uid?>"); } );
});
</script>