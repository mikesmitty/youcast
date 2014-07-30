#!/bin/bash

function gen_playlist() {
    cd /var/www/youcast/
    /bin/ls -1rt /var/www/youcast/dl/*.mp3 > /var/www/youcast/youcast.m3u
    #/usr/bin/killall -HUP ezstream 2>&1 /dev/null
}

gen_playlist
/bin/sleep 30
gen_playlist
