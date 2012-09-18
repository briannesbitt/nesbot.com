<?if(isset($tracker)):?>
   <script type="text/javascript">
     var gaEnabled = true;
     var _gaq = _gaq || [];
     _gaq.push(['_setAccount', '<?=$tracker?>']);
     _gaq.push(['_trackPageview']);

     (function() {
       var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
       ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
       var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
     })();

   </script>
<?else:?>
   <script type="text/javascript">
   var gaEnabled = false;
   </script>
<?endif;?>

<script type="text/javascript">
function gaTrackEvent(category, action)
{
   if (gaEnabled) {
      _gat._getTrackerByName()._trackEvent(category, action);
   }
}
</script>
