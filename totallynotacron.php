<?php

chdir('dl');

if(isset($argv[1])) {
    $video = escapeshellcmd($argv[1]);
    $file_name = sha1($video).'m4a';

    require_once 'mysql.inc.php';
    $video_id = $db_conn->real_escape_string($argv[2]);

    $query = "UPDATE tbl_video SET tbl_video_file='$file_name' WHERE tbl_video_id=$video_id";
    $db_conn->query($query);

#print "$file_name\n";
    #exec("$dir/youtube-dl --add-metadata --no-mtime --id -f bestaudio -x $video", $output, $return);
    #exec("$dir/youtube-dl --add-metadata --no-mtime --id --audio-format mp3 -x $video", $output, $return);
    exec("$dir/youtube-dl $dl_opts $video", $output, $return);

/*
print(date('c')."\n");
print("Command: $dir/youtube-dl $dl_opts $video END\n");
print_r($output, true);
*/

    if($return == 0) {
        $query = "UPDATE tbl_video SET tbl_video_ready=1 WHERE tbl_video_id=$video_id";
    }
    else {
        $query = "UPDATE tbl_video SET tbl_video_error='ERRAWR' WHERE tbl_video_id=$video_id";
    }
    $db_conn->query($query);
}
else {
    require_once 'mysql.inc.php';

    $query = "SELECT tbl_queue_video_id,tbl_video_url,tbl_queue_id FROM tbl_queue AS q INNER JOIN tbl_video AS v ON q.tbl_queue_video_id = v.tbl_video_id WHERE v.tbl_video_ready<>1 AND v.tbl_video_dl=0 AND v.tbl_video_error IS NULL ORDER BY q.tbl_queue_slot ASC LIMIT 3";
    $result = $db_conn->query($query);

    while($row = $result->fetch_assoc()) {
        if(preg_match('/^https?:\/\/(www\.)?youtu(be\.com|\.be)/', $row['tbl_video_url'])) {
            `echo "php $dir/$cron {$row['tbl_video_url']} {$row['tbl_queue_video_id']}" |at now`;
            $query = "UPDATE tbl_video SET tbl_video_dl=1 WHERE tbl_video_id={$row['tbl_queue_video_id']}";
            $db_conn->query($query);
        }
    }
}
