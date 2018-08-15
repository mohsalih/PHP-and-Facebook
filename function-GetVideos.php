<?php

function GetVideos(){
/* 
Get a list of videos with access tokens from the database into an array array_videos
*/
// echo "Running GetVideos, please wait .... \n";

/*************************
1. Read MySQL table () into an array array_acoounts.
**************************/
require('fb_conn.php');

$sql = "SELECT page_id, video_id, access_token FROM v_all_videos";
$result = $conn->query($sql);

$i=0;

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

        $array_videos[$i]['page_id']  =$row["page_id"];
        $array_videos[$i]['video_id'] =$row["video_id"];
        $array_videos[$i]['page_access_token']=$row["access_token"];
        $i++;
        }
    }

$GLOBALS['g_array_videos'] = $array_videos;

}


?>