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
    case 'shift':
        # Shift a video off the bottom of the queue
        break;
}

function json_out(){
    global $output_array;

    print json_encode($output_array);
}

function list_queue(){
    global $output_array;
    require_once 'mysql.inc.php';

    $query = "SELECT tbl_queue_slot,tbl_video_url,tbl_queue_requestor,tbl_video_ready,tbl_video_error FROM tbl_queue AS q INNER JOIN tbl_video AS v ON q.tbl_queue_video_id = v.tbl_video_id ORDER BY q.tbl_queue_slot ASC";
    $result = $db_conn->query($query);

    while($queue = $result->fetch_assoc()) {
        $work_array['slot'] = $queue['tbl_queue_slot'];
        $work_array['url'] = $queue['tbl_video_url'];
        $work_array['requestor'] = $queue['tbl_queue_requestor'];
        $work_array['ready'] = $queue['tbl_video_ready'];
        $work_array['error'] = $queue['tbl_video_error'];

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
