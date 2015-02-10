<?php
require 'vendor/autoload.php';

// generate posts
passthru(sprintf("php %s/genposts.php", __DIR__), $out);

// compile less => css
$lessInput = __DIR__.'/public/css/main.less';
$cssOutput = __DIR__.'/public/css/compiled.css';
$lessCacheFile = __DIR__.'/less.cache';

if (!file_exists($cssOutput)) {
   @unlink($lessCacheFile);
}

if (file_exists($lessCacheFile)) {
   $cache = unserialize(file_get_contents($lessCacheFile));
} else {
   $cache = __DIR__.'/public/css/main.less';
}

$less = new lessc;
$less->setFormatter("compressed");
$newCache = $less->cachedCompile($cache);

if (!is_array($cache) || $newCache["updated"] > $cache["updated"]) {
   if (file_put_contents($cssOutput, $newCache['compiled']) !== false) {
      gzip($cssOutput);
      file_put_contents($lessCacheFile, serialize($newCache));
   }
}

// minify js
$jsDir = __DIR__.'/public/js/';
$jsOutput = 'compiled.js';

$js = '';

// for this site, the order of including doesn't matter
if (file_exists($jsDir)) {
   foreach (scandir($jsDir) as $file) {
      if (substr($file, -3) === '.js' && $file !== $jsOutput) {
         $js .= \JShrink\Minifier::minify(file_get_contents($jsDir.$file));
      }
   }
}

file_put_contents($jsDir.$jsOutput, $js);
gzip($jsDir.$jsOutput);


function gzip($inFile, $outFile = null)
{
   file_put_contents(($outFile === null) ? $inFile.'.gz' : $outFile, gzencode(file_get_contents($inFile), 9));
}
