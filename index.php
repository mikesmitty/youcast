<?php

?>
<html>
<head>
<title>YouCast</title>
    <link rel="stylesheet" type="text/css" href="extjs/resources/css/ext-all-neptune.css" />
    <link rel="stylesheet" type="text/css" href="layout.css" />

    <!-- <script type="text/javascript" src="extjs/ext-all-debug.js"></script> -->
    <script type="text/javascript" src="extjs/ext-all.js"></script>
    <script type="text/javascript" src="app/queue.js"></script>
    <script type="text/javascript" src="jwplayer/jwplayer.js"></script>
</head>
<body>
    <div class="wrapper">
        <div id="add"></div>
        <div id='mediaplayer'></div>
        <div id="nowplaying"></div>

        <script type="text/javascript">
          jwplayer('mediaplayer').setup({
            'flashplayer': './jwplayer/jwplayer.flash.swf',
            'id': 'player1',
            'type': 'sound',
            'width': '480',
            'height': '30',
            'autoplay': 'true',
            'volume': '60',
            'file': 'http://lan.mikesmitty.com:9000/youcast.mp3'
          });
        </script>

        <div class="push"></div>
    </div>
    <div id="queue" class="footer">
    </div>
</body>
</html>
