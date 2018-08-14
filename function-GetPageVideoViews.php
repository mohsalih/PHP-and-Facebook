<?php

function GetPageVideoViews($p_page_id,$p_page_access_token,$p_start_date,$p_end_date){

/*Get the numbers of page video views per day and insert those into database*/

/***********************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
************************************************************/
// echo "Running GetPageVideoViews, please wait ... \n";
$table= 'facebook_page_video_views';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url=   'https://graph.facebook.com/v2.12/'.$p_page_id.'/insights/page_video_views?since='.$p_start_date.'&until='.$p_end_date.'&access_token='.$p_page_access_token;

// echo "$p_key - url: $url \n";

curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);

/* 3. Collect, display and decode the results.*/
$results=   file_get_contents($filename);
$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'];
$no_rows = count($data_array[0]['values']);

/* 4. Connect to the Database. */
require('fb_conn.php');  
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');

/* 5. Insert the results into the database using a prepared statement and a foreach loop.*/
for ($i=0;$i<$no_rows;$i++){

    $stmt1 = $conn->prepare("INSERT INTO fb_page_video_views 
     (page_id,
      end_time,
      page_video_views,
      datetimestamp)
     VALUES (?, ?, ?, ?)");

     $stmt1->bind_param("isis", 
        $page_id,
        $end_time,
        $page_video_views,
        $datetimestamp);

        $page_id       = $p_page_id;
        

        if (isset($data_array[0]['values'][$i]['end_time'])){
            $my_end_time = new DateTime($data_array[0]['values'][$i]['end_time']);
            $my_end_time->modify('0 day');
            $end_time    = $my_end_time->format('Y-m-d H:i:s');
        }
        else{
            $end_time    = Null;
        }        
       
        if (isset($data_array[0]['values'][$i]['value'])){
            $page_video_views     = $data_array[0]['values'][$i]['value'];
        }
        else{
            $page_video_views     = 0;
        }
        
        $datetimestamp = $myDateTimeStamp;
        
        $stmt1->execute();
         
}//For loop 
	    $stmt1->close();
	    $conn->close();
}



?>