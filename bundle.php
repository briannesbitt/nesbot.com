<?php

// generate posts
passthru(sprintf("php %s/genposts.php", __DIR__), $out);
