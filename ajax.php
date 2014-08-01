<?php

switch($_REQUEST['action']) {
    case 'list':
        # List the current queue
        list_queue();
        json_out();
        break;
    case 'push':
        # Push a video onto the top of the queue
        push_video();
        break;
    case 'delete':
        # Delete a video from the queue
        delete_video();
        break;
    case 'nowplaying':
        # Grab the now playing info from Ices
        now_playing();
        json_out();
        break;
    case 'shift':
        # Shift a video off the bottom of the queue
        # This doesn't really make sense anymore. I think?
        break;
}

function json_out(){
    global $output_array;

    if(!empty($output_array)) print json_encode($output_array);
}

function list_queue(){
    global $output_array;
    require_once 'mysql.inc.php';

    $query = "SELECT tbl_queue_id,tbl_queue_slot,tbl_video_url,tbl_queue_requestor,tbl_video_ready,tbl_video_error FROM tbl_queue AS q INNER JOIN tbl_video AS v ON q.tbl_queue_video_id = v.tbl_video_id ORDER BY q.tbl_queue_slot ASC";
    $result = $db_conn->query($query);

    $i = 1;
    while($queue = $result->fetch_assoc()) {
        $work_array['order'] = $i;
        $work_array['queue_id'] = $queue['tbl_queue_id'];
        $work_array['url'] = $queue['tbl_video_url'];
        $work_array['requestor'] = $queue['tbl_queue_requestor'];
        $work_array['ready'] = ($queue['tbl_video_ready'] == 1) ? '&#x2713;' : '&#x2717;';
        $work_array['error'] = $queue['tbl_video_error'];
        $i++;

        $output_array['queue'][] = $work_array;
        unset($work_array);
    }
}

function push_video(){
    if(isset($_REQUEST['url'])) {
        global $db_conn;
        require_once 'mysql.inc.php';

        $url = $db_conn->real_escape_string($_REQUEST['url']);
        $query = "INSERT INTO tbl_video (tbl_video_url, tbl_video_ready, tbl_video_dl) VALUES ('$url', 0, 0)";
        $db_conn->query($query);

        $video_id = $db_conn->insert_id;
        $requestor = (isset($_REQUEST['requestor'])) ? $db_conn->real_escape_string($_REQUEST['requestor']) : '';
        $slot = fetch_last() + 1;
        $query = "INSERT INTO tbl_queue (tbl_queue_video_id, tbl_queue_requestor, tbl_queue_slot) VALUES ($video_id, '$requestor', $slot)";
        $db_conn->query($query);

        #`echo "php $dir/$cron" |at now`;
        #`echo "php $dir/$cron" |at now >> /nfsh/michaels/peacock-youcast.log`;
    }
}

function fetch_last() {
    global $db_conn;
    require_once 'mysql.inc.php';

    $query = "SELECT tbl_queue_slot FROM tbl_queue ORDER BY tbl_queue_slot DESC LIMIT 1";
    $result = $db_conn->query($query);
    $row = $result->fetch_assoc();
    
    if(isset($row['tbl_queue_slot'])) return $row['tbl_queue_slot'];
    else return 0;
}

function delete_video() {
    global $db_conn;
    require_once 'mysql.inc.php';
#print "I wanna be the very best, like no one ever was.\n";

    $row = (int) $db_conn->real_escape_string($_REQUEST['row']);
    if($row < 1) return 1;
#print "To catch them is my real test; to train them is my cause!\n";

    $query = "SELECT tbl_video_url,tbl_video_file FROM tbl_video WHERE tbl_video_id=(SELECT tbl_queue_video_id FROM tbl_queue WHERE tbl_queue_id=$row LIMIT 1) LIMIT 1";
    $result = $db_conn->query($query);
    if(!$result) return 2;

#print "I will travel across the land, searching far and wide.\n";
    $row_array = $result->fetch_assoc();
    if(empty($row_array['tbl_video_file'])) return 3;

    unlink("$dir/dl/{$row_array['tbl_video_file']}.m4a"); # This is bad. There should be validation here in case of ../ attempts
    unlink("$dir/dl/{$row_array['tbl_video_file']}.mp3"); # This is bad. There should be validation here in case of ../ attempts

    $video_id = $row_array['tbl_queue_video_id'];

    $query = "DELETE FROM tbl_queue WHERE tbl_queue_id=$row LIMIT 1";
    $db_conn->query($query) or die; # This might flag the desired error response to ExtJS. I should test that.
}

function now_playing() {
    global $output_array;
    require_once 'config.inc.php';

    if(is_readable($cue_file)) {
        $cue_content = file($cue_file);

        $output_array['metadata']['percent'] = $cue_content[4];
        $output_array['metadata']['number'] = $cue_content[5];
        $output_array['metadata']['user'] = $cue_content[6];
        $output_array['metadata']['title'] = $cue_content[7];
    }
    else {
        http_response_code(500);
    }
}
