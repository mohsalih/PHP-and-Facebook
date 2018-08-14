<?php

function Step_Into_Account_Video_Metrics(){
/*
Outcome: Step through accounts and get all video mettircs of the pages
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function .
HERE
*/  
GetManagedAccounts();
GetPages();
GetVideos();
FlushDatabaseTable("fb_video_metrics");  
FlushDatabaseTable("fb_video_views_by_country");
FlushDatabaseTable("fb_video_views_by_region");
FlushDatabaseTable("fb_video_views_by_reaction");
FlushDatabaseTable("fb_video_views_by_age_and_gender");

    $array_videos = $GLOBALS['g_array_videos'];
    // var_dump($array_accounts);

    foreach ($array_videos as $key => $value) {

        $page_id  = $value["page_id"];
        $video_id = $value["video_id"];
        $page_access_token = $value["page_access_token"];
        // echo "$page_id-$video_id\n";

        GetVideoMetrics($page_id,$video_id,$page_access_token);

        }
echo "Function Step_Into_Account_Videos is completed\n";           
}


?>