<?php

function GetPageVideos($p_page_id,$p_page_access_token){
/*Get a list of all videos on all pages and insert those into database*/   
/***********************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
************************************************************/
$table= 'facebook_page_videos';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url= 'https://graph.facebook.com/v2.12/'.$p_page_id.'/videos?access_token='.$p_page_access_token;

echo "page videos- url: $url \n";

curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);

/* 3. Collect, display and decode the results.*/
$results=   file_get_contents($filename);

// echo "$results\n"; exit;

$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'];
$no_rows = count($data_array);
echo "$no_rows\n";
/* 4. Connect to the Database. */
require('fb_conn.php');
$conn->set_charset("utf8");  
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');

/* 5. Insert the results into the database using a prepared statement and a foreach loop.*/

for ($i=0;$i<$no_rows;$i++){

    $stmt1 = $conn->prepare("INSERT INTO fb_page_videos
                            (page_id,
                            video_id,
                            updated_time,
                            datetimestamp,
                            video_description)
                            VALUES (?, ?, ?, ?, ?)");

                         $stmt1->bind_param("sssss", 
                            $page_id,
                            $video_id,
                            $updated_time,
                            $myDateTimeStamp,
                            $video_description);

        $page_id  = $p_page_id;
        $video_id = $data_array[$i]['id'];
        $my_updated_time = new DateTime($data_array[$i]['updated_time']);
        $my_updated_time->modify('-1 day');
        $updated_time    = $my_updated_time->format('Y-m-d H:i:s');

        if (isset($data_array[$i]['description'])){
            $video_description    = $data_array[$i]['description'];
        }
        else{
        $video_description    = Null;
        }
        
    $stmt1->execute();
    $stmt1->close();
 
}//For loop

$conn->close();


}



?>