#!/bin/sh

now=`date +%Y%m%d%H%M%S`

cd /vol/www/nesbot.com
mkdir $now
cd $now
git clone git://github.com/briannesbitt/nesbot.com.git ./
composer install
php bundle.php

if [ ! -d vendor ];
then
    echo "No vendor directory !!"
    exit 1
fi

if [ ! -f posts.php ];
then
    echo "No posts.php file !!"
    exit 1
fi

if [ ! -f public/css/compiled.css ];
then
    echo "No compiled css file !!"
    exit 1
fi

if [ ! -f public/js/compiled.js ];
then
    echo "No compiled js file !!"
    exit 1
fi

cd ..
ln -s $now nextCurrent
mv -T nextCurrent current