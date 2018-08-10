<?php
/*******************************************************/
function GetPageMessages($p_page_id,$p_page_access_token){
/*Get a list of messages posted by the page into facebook and insert those into database*/   

/* 1. Create a file that will be used to store the results from the call to the Facebook Graph API.*/
$table= 'facebook_page_messages';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );

/* 2. Create and execute the API call using the PHP cURL library functions. */
$header = 0;
$ch=    curl_init();

$url=   'https://graph.facebook.com/v2.12/'.$p_page_id.'/posts?fields=created_time,message,id,insights.metric(post_reactions_by_type_total).period(lifetime).as(post_reactions_by_type_total)&access_token='.$p_page_access_token;

// echo "url: $url \n";

curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);

/* 3. Collect, display and decode the results.*/
$results=   file_get_contents($filename);
$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'];
$no_rows = count($data_array);

// /* 4. Connect to the Database. */
require('fb_conn.php');  
$conn->set_charset("utf8");

// Gets a time and date stamp
// *********************************
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');

/* 5. Insert the results into the database using a prepared statement and a foreach loop. */

for ($i=0;$i<$no_rows;$i++){

    $stmt1 = $conn->prepare("INSERT INTO fb_page_messages 
     (created_time,
     message,
     id, 
     likee,
     love,
     wow,
     haha,
     sorry,
     anger,
     datetimestamp) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

     $stmt1->bind_param("ssssssssss", 
        $created_time,
        $message,
        $id,
        $likee,
        $love,
        $wow,
        $haha,
        $sorry,
        $anger, 
        $datetimestamp);

        $created_time  = $data_array[$i]['created_time'];
        if (isset($data_array[$i]['message'])){
            $message       = $data_array[$i]['message'];
        }
        else{
            $message       =Null;
        }

        $id            = $data_array[$i]['id'];
        $likee = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['like'];
        $love = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['love'];
        $wow = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['wow'];
        $haha = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['haha'];
        $sorry = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['sorry'];
        $anger = $data_array[$i]['post_reactions_by_type_total']['data'][0]['values'][0]['value']['anger'];
        
        $datetimestamp = $myDateTimeStamp;
        
        $stmt1->execute();
         
}
       
     $stmt1->close();
     $conn->close();
    
}
?>