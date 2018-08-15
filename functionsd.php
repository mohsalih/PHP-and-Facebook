<?php

$g_array_dates = array();
$g_array_accounts = array();
$g_array_videos=array();

function ExtractFacebookDataD(){
	
// FlushDatabaseTables();
// GetManagedAccounts();
// MakeDatesList();
// GetPages();
// GetVideos();

}
function FlushDatabaseTables(){

	// FlushDatabaseTable("fb_page_messages");
	// FlushDatabaseTable("fb_page_fans");
	// FlushDatabaseTable("fb_page_engagements");
	// FlushDatabaseTable("fb_page_video_views");
	// FlushDatabaseTable("fb_page_video_view_times");
	// FlushDatabaseTable("fb_page_videos");
	// FlushDatabaseTable("fb_video_metrics");
	// FlushDatabaseTable("fb_video_views_by_country");
	// FlushDatabaseTable("fb_video_views_by_region");
	// FlushDatabaseTable("fb_video_views_by_reaction");
	// FlushDatabaseTable("fb_video_views_by_age_and_gender");
}

/*************************************************/
function FlushDatabaseTable($tablename){
/*************************
This function deletes all records from the specified database table.
**************************/
require('fb_conn.php');

// sql to delete a record
$sql = "DELETE FROM " . $tablename;

    if ($conn->query($sql) === TRUE) {
        echo "Record deleted from table $tablename\n";
    } else {
        echo "Error deleting from table $tablename: \n" . $conn->error;
    }

$conn->close();

}


function GetManagedAccounts(){
/***********************************************************************
Outcome: Populates the database table fb_accounts with account details for all the pages the DNI Facebook account manages. 

Process: 
Deletes records from table fb_accounts
Creates a file called facebook_accounts.json to store data
Calls Facebook API to get data in an array
Split the returned array into values and inserted into the database table fb_ccounts 
***********************************************************************/
echo "Running GetManagedAccounts, please wait ... \n";
FlushDatabaseTable("fb_accounts"); //Clears the database table fb_accounts

$table= 'facebook_accounts';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );

/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
************************************************************************/
$header = 0;
$ch=    curl_init();

$url=   'https://graph.facebook.com/v2.12/me/accounts?access_token='.$GLOBALS['g_access_token'];
curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);
// echo "$url\n";
/******************************************
3. Collect, display and decode the results.
*******************************************/
$results=   file_get_contents($filename);
$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'];
// $keys = array_keys($data_array);
$no_rows = count($data_array);

/*************************
4. Connect to the Database.
**************************/
require('fb_conn.php');  
/*********************************
Gets a time and date stamp
**********************************/
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');
/******************************************************
5. Insert the results into the database using a prepared statement and a foreach loop.
******************************************************/

for ($i=0;$i<$no_rows;$i++){
 
    $stmt1 = $conn->prepare("INSERT INTO fb_accounts 
     (access_token,
     category,
     category_list_id, 
     category_list_name,
     account_name, 
     account_id,
     datetimestamp) 
     VALUES (?, ?, ?, ?, ?, ?, ?)");

     $stmt1->bind_param("sssssss", 
        $access_token,
        $category,
        $category_list_id, 
        $category_list_name,
        $account_name,
        $account_id,
        $datetimestamp);

        //set parameters and execute
        $access_token  = $data_array[$i]['access_token'];   
        $category      = $data_array[$i]['category'];
        $category_list_id   = $data_array[$i]['category_list'][0]['id'];
        $category_list_name = $data_array[$i]['category_list'][0]['name'];
        $account_name = $data_array[$i]['name'];
        $account_id   = $data_array[$i]['id'];
        $datetimestamp = $myDateTimeStamp;
        
        $stmt1->execute();
}
       
     $stmt1->close();
     $conn->close();

}

/****************************************************************************/
function GetPages(){
/* 
Outcome: Get a list of managed facebook accounts with access tokens from the database into an array array_accounts.
Process:
Selects from the database table fb_accounts.
Reads every column into variables.
Package those variables into an array array_accounts.
Assigns the array array_accounts to a global variable array g_array_dates.

GetPageMessages
GetPageFans
GetPageEngagements
GetPageVideoViews
GetPageVideoViewTimes
GetPageVideos
*/
/*************************
1. Read MySQL table () into an array array_acoounts.
**************************/
require('fb_conn.php');

$sql = "SELECT account_id, account_name, access_token FROM fb_accounts";
$result = $conn->query($sql);

//Get page messages
$array_accounts=array();
$i=0;
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

        $array_accounts[$i]['account_id']  =$row["account_id"];
        $array_accounts[$i]['account_name']=$row["account_name"];
        $array_accounts[$i]['access_token']=$row["access_token"];
        $i++;

        }
    }

$GLOBALS['g_array_accounts'] = $array_accounts;

echo "GetPages is completed \n";


}

function Step_Into_Account_Messages(){
/*
Outcome: Step through accounts and get all messages posted
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/
GetManagedAccounts();
GetPages();
FlushDatabaseTable("fb_page_messages"); 
  
    $array_accounts = $GLOBALS['g_array_dates'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        GetPageMessages($account_id,$account_access_token);

        }
echo "Function Step_Into_Account_Messages is completed\n";           
}

function Step_Into_Account_Videos(){
/*
Outcome: Step through accounts and get all messages posted
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/
GetManagedAccounts();
GetPages();
FlushDatabaseTable("fb_page_videos");    
    $array_accounts = $GLOBALS['g_array_dates'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];
        echo "$account_id-$account_name\n";

        GetPageVideos($account_id,$account_access_token);

        }
echo "Function Step_Into_Account_Videos is completed\n";           
}

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

function Step_Into_Account_Fans(){
/*
Outcome: Step through accounts and get all metrics for the fans
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/
GetManagedAccounts();
GetPages(); 
FlushDatabaseTable('fb_date_periods'); 
MakeDatesList();

    $array_accounts = $GLOBALS['g_array_accounts'];

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        Step_Into_Date_Periods($account_id,$account_access_token,"fans");

        }
echo "Function Step_Into_Account_Fans is completed\n";           
}


function Step_Into_Account_Engagements(){
/*
Outcome: Step through accounts and get all fans engagements with the pages
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/  
GetManagedAccounts();
GetPages(); 
FlushDatabaseTable('fb_date_periods'); 
MakeDatesList();

    $array_accounts = $GLOBALS['g_array_accounts'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        Step_Into_Date_Periods($account_id,$account_access_token,"engagements");

        }
echo "Completed Step_Into_Account_Engagements \n";           
}


function Step_Into_Account_Video_Views(){
/*
Outcome: Step through accounts and get all messages posted
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/
GetManagedAccounts();
GetPages(); 
FlushDatabaseTable('fb_date_periods'); 
MakeDatesList();

    $array_accounts = $GLOBALS['g_array_accounts'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        Step_Into_Date_Periods($account_id,$account_access_token,"video_views");

        }
echo "Completed Step_Into_Account_Video_Views\n";           
}


function Step_Into_Account_Video_View_Times(){
/*
Outcome: Step through accounts and get all messages posted
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Get all messages posted for the account.
Call the function GetPageMessages.
*/
GetManagedAccounts();
GetPages(); 
FlushDatabaseTable('fb_date_periods'); 
MakeDatesList();   
    $array_accounts = $GLOBALS['g_array_accounts'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        Step_Into_Date_Periods($account_id,$account_access_token,"video_view_times");

        }
echo "Completed Step_Into_Account_Video_View_Times\n";           
}

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

function GetPageFans($p_page_id,$p_page_access_token,$p_start_date,$p_end_date){

/***********************************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
**********************************************************************/
// echo "Running GetPageFans, please wait ... \n";
$table= 'facebook_page_fans';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url=   'https://graph.facebook.com/v2.12/'.$p_page_id.'/insights/page_fans?since='.$p_start_date.'&until='.$p_end_date.'&access_token='.$p_page_access_token;

// echo "GetPageFans $p_key - url: $url \n";

curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);

/* 3. Collect, display and decode the results.*/
$results=   file_get_contents($filename);
$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'][0]['values'];
$no_rows = count($data_array);
// echo "NO OF ROWS $no_rows \n";
/* 4. Connect to the Database. */
require('fb_conn.php');  
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');
/***********************************************************************
5. Insert the results into the database using a prepared statement and a foreach loop.
**********************************************************************/
for ($i=0;$i<$no_rows;$i++){

    $stmt1 = $conn->prepare("INSERT INTO fb_page_fans 
     (page_id,
      end_time,
      page_fans,
      datetimestamp)
     VALUES (?, ?, ?, ?)");

     $stmt1->bind_param("isis", 
        $page_id,
        $end_time,
        $page_fans,
        $datetimestamp);

        $page_id       = $p_page_id;
        
        if (isset($data_array[$i]['end_time'])){
            $my_end_time = new DateTime($data_array[$i]['end_time']);
            $my_end_time->modify('-1 day');
            $end_time    = $my_end_time->format('Y-m-d H:i:s');
        }
        else{
            $end_time    = Null;
        }        
        // echo "Page Fans Count:" . $data_array[0]['values'][$i]['value'];
        if (isset($data_array[$i]['value'])){
            $page_fans     = $data_array[$i]['value'];
        }
        else{
            $page_fans     = 0;
        }
        $datetimestamp = $myDateTimeStamp;
      
        $stmt1->execute();
}
       
     $stmt1->close();
     $conn->close();

}
/********************************************************/
function GetPageEngagements($p_page_id,$p_page_access_token,$p_start_date,$p_end_date){
/*Get the numbers of page engagements per day and insert those into database*/


/***********************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
************************************************************/
// echo "Running GetPageEngagements, please wait ... \n";
$table= 'facebook_page_engagements';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url=   'https://graph.facebook.com/v2.12/'.$p_page_id.'/insights/page_engaged_users?since='.$p_start_date.'&until='.$p_end_date.'&access_token='.$p_page_access_token;

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
	
    $stmt1 = $conn->prepare("INSERT INTO fb_page_engagements 
     (page_id,
      end_time,
      page_engagements,
      datetimestamp)
     VALUES (?, ?, ?, ?)");

     $stmt1->bind_param("isis", 
        $page_id,
        $end_time,
        $page_engagements,
        $datetimestamp);
    
        $page_id       = $p_page_id;
        if (isset($data_array[0]['values'][$i]['end_time'])){
            $my_end_time = new DateTime($data_array[0]['values'][$i]['end_time']);
            $my_end_time->modify('-1 day');
            $end_time    = $my_end_time->format('Y-m-d H:i:s');
        }
        else{
            $end_time    = Null;
        }  
             
        if (isset($data_array[0]['values'][$i]['value'])){
            $page_engagements     = $data_array[0]['values'][$i]['value'];
        }
        else{
            $page_engagements     = 0;
        } 
        
        $datetimestamp = $myDateTimeStamp;
        
        $stmt1->execute();
         
} //For loop
       
     $stmt1->close();
     $conn->close();

}

/********************************************************/
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


/********************************************************/
function GetPageVideoViewTimes($p_page_id,$p_page_access_token,$p_start_date,$p_end_date){
/*Get the numbers of page video times per day and insert those into database*/    
/***********************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
************************************************************/
// echo "Running .., please wait ... \n";
$table= 'facebook_page_video_view_times';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url= 'https://graph.facebook.com/v2.12/'.$p_page_id.'/insights/page_video_view_time?since=' . $p_start_date .'&until='. $p_end_date .'&access_token='.$p_page_access_token;

// echo "video view times - $p_key - url: $url \n";

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

    $stmt1 = $conn->prepare("INSERT INTO fb_page_video_view_times 
     (page_id,
      end_time,
      page_video_view_time,
      datetimestamp)
     VALUES (?, ?, ?, ?)");

     $stmt1->bind_param("isis", 
        $page_id,
        $end_time,
        $page_video_view_time,
        $datetimestamp);

        $page_id       = $p_page_id;
        

        if (isset($data_array[0]['values'][$i]['end_time'])){
            $my_end_time = new DateTime($data_array[0]['values'][$i]['end_time']);
            $my_end_time->modify('-1 day');
            $end_time    = $my_end_time->format('Y-m-d H:i:s');
        }
        else{
            $end_time    = Null;
        }        

        if (isset($data_array[0]['values'][$i]['value'])){
            $page_video_view_time     = $data_array[0]['values'][$i]['value'];
        }
        else{
            $page_video_view_time     = 0;
        }
        
        $datetimestamp = $myDateTimeStamp;
        
        $stmt1->execute();
           // echo "$page_id-$end_time-$page_video_lengths\n";
         
} // For loop
       
     $stmt1->close();
     $conn->close();

}

/********************************************************/
function GetPageVideos($p_page_id,$p_page_access_token){
/*Get a list of all vides on all pages and insert those into database*/   
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

/*************************************************/
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

function GetVideoMetrics($p_page_id,$p_video_id,$p_page_access_token){
/***********************************************************
1. Create a file that will be used to store the results from the call to the Facebook Graph API.
************************************************************/
$table= 'facebook_video_metrics';
$filename=  ($table . '.json');
$fp=    fopen($filename, 'w' );
/***********************************************************************
2. Create and execute the API call using the PHP cURL library functions.
***********************************************************************/
$header = 0;
$ch=    curl_init();

$url= 'https://graph.facebook.com/v2.12/'.$p_video_id.'/video_insights?total_video_views,total_video_views_unique,total_video_views_autoplayed,total_video_views_clicked_to_play,total_video_views_organic,total_video_views_organic_unique,total_video_views_paid,total_video_views_paid_unique,total_video_views_sound_on&access_token='.$p_page_access_token;

// echo "$p_key-$p_page_id-$p_video_id \n";
// echo "$url \n";

curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);
/******************************************
3. Collect, display and decode the results.
*******************************************/
$results=   file_get_contents($filename);
$fb_array=  json_decode($results, true);
$data_array = $fb_array['data'];
$no_rows = count($data_array);
// echo "ROWS: $no_rows \n";
/* 4. Connect to the Database. */
require('fb_conn.php');  
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');

/* 5. Insert the results into the database using a prepared statement and a foreach loop.*/

if ($no_rows>0){

for ($i=0;$i<$no_rows;$i++){

    $stmt1 = $conn->prepare("INSERT INTO fb_video_metrics
                            (metric_id,
                            page_id,
                            video_id,
                            metric_name,
                            metric_period,
                            metric_title,
                            metric_description,
                            metric_value,
                            datetimestamp)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

                         $stmt1->bind_param("sssssssss",
                            $metric_id, 
                            $page_id,
                            $video_id,
                            $metric_name,
                            $metric_period,
                            $metric_title,
                            $metric_description,
                            $metric_value,
                            $myDateTimeStamp);

        $metric_id          = $data_array[$i]['id'];
        $page_id            = $p_page_id;
        $video_id           = $p_video_id;
        $metric_name        = $data_array[$i]['name'];
        // echo "$i- metric_name- $metric_name\n";
        $metric_period      = $data_array[$i]['period'];
        $metric_title       = $data_array[$i]['title'];
        $metric_description = $data_array[$i]['description'];
        $metric_value       = $data_array[$i]['values'][0]['value'];

        if  (is_array($metric_value)<> True){
            $stmt1->execute();
            $stmt1->close();
        }else {
            GetMoreVideoMetrics($p_page_id,$p_video_id,$p_page_access_token,$metric_name,$metric_value,$metric_id,$metric_period,$metric_title,$metric_description);
        } 
} // For loop

$conn->close();


}   
/******************************************************************/
}


function GetMoreVideoMetrics($p_page_id,$p_video_id,$p_page_access_token,$p_metric_name,$p_metric_value,$p_metric_id,$p_metric_period,$p_metric_title,$p_metric_description){
 

 /* 4. Connect to the Database. */
require('fb_conn.php');  
$datetimestamp = new DateTime();
$myDateTimeStamp=   $datetimestamp->format('Y-m-d H:i:s');

     if ($p_metric_name=='total_video_view_time_by_country_id'){

		foreach ($p_metric_value as $country => $views) {

	    $stmt1 = $conn->prepare("INSERT INTO fb_video_views_by_country
	                            (metric_id,
	                            page_id,
	                            video_id,
	                            metric_name,
	                            metric_period,
	                            metric_title,
	                            metric_description,
	                            view_country,
	                            view_value,
	                            datetimestamp)
	                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	                         $stmt1->bind_param("ssssssssss",
	                            $p_metric_id, 
	                            $p_page_id,
	                            $p_video_id,
	                            $p_metric_name,
	                            $p_metric_period,
	                            $p_metric_title,
	                            $p_metric_description,
	                            $country,
	                            $views,
	                            $myDateTimeStamp);
        $stmt1->execute();
        $stmt1->close(); 
    	}
    }elseif ($p_metric_name=='total_video_view_time_by_region_id') {

		foreach ($p_metric_value as $region => $views) {

	    $stmt1 = $conn->prepare("INSERT INTO fb_video_views_by_region
	                            (metric_id,
	                            page_id,
	                            video_id,
	                            metric_name,
	                            metric_period,
	                            metric_title,
	                            metric_description,
	                            view_region,
	                            view_value,
	                            datetimestamp)
	                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	                         $stmt1->bind_param("ssssssssss",
	                            $p_metric_id, 
	                            $p_page_id,
	                            $p_video_id,
	                            $p_metric_name,
	                            $p_metric_period,
	                            $p_metric_title,
	                            $p_metric_description,
	                            $region,
	                            $views,
	                            $myDateTimeStamp);
        $stmt1->execute();
        $stmt1->close();
        } 
	}elseif ($p_metric_name=='total_video_reactions_by_type_total') {

		foreach ($p_metric_value as $reaction => $reaction_value) {

	    $stmt1 = $conn->prepare("INSERT INTO fb_video_views_by_reaction
	                            (metric_id,
	                            page_id,
	                            video_id,
	                            metric_name,
	                            metric_period,
	                            metric_title,
	                            metric_description,
	                            view_reaction,
	                            view_value,
	                            datetimestamp)
	                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	                         $stmt1->bind_param("ssssssssss",
	                            $p_metric_id, 
	                            $p_page_id,
	                            $p_video_id,
	                            $p_metric_name,
	                            $p_metric_period,
	                            $p_metric_title,
	                            $p_metric_description,
	                            $reaction,
	                            $reaction_value,
	                            $myDateTimeStamp);
	    $stmt1->execute();
	    $stmt1->close(); 
    	}
   	}elseif ($p_metric_name=='total_video_view_time_by_age_bucket_and_gender') {
   		
		foreach ($p_metric_value as $age_and_gender => $age_and_gender_value) {

	    $stmt1 = $conn->prepare("INSERT INTO fb_video_views_by_age_and_gender
	                            (metric_id,
	                            page_id,
	                            video_id,
	                            metric_name,
	                            metric_period,
	                            metric_title,
	                            metric_description,
	                            age_and_gender,
	                            view_value,
	                            datetimestamp)
	                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

	                         $stmt1->bind_param("ssssssssss",                       	
	                            $p_metric_id, 
	                            $p_page_id,
	                            $p_video_id,
	                            $p_metric_name,
	                            $p_metric_period,
	                            $p_metric_title,
	                            $p_metric_description,
	                            $age_and_gender,
	                            $age_and_gender_value,
	                            $myDateTimeStamp);
	    $stmt1->execute();
	    $stmt1->close();    
    	}
   	}         
        $conn->close(); 
}

function DateThis($p_date_string, $p_period){
    $date_d = date('Y-m-d',strtotime($p_period,strtotime($p_date_string)));
    return $date_d;
}



function MakeDatesList(){
/*
Outcome: Creates a list of start and end dates slicing periods with 93 days max as the requirement for Facebook API calls for metrics that have start and end dates. 

Process: 
Creates an array of periods of start and end dates based on the provided utlimate start and end dates of the requested time frame.
Saves all these sliced dates of start and end dates into an array.
Sends that array of dates to the function AddDatesToDB.
*/

        $my_dates = array();
        $s_date = $GLOBALS['g_date_start']; 
        $e_date = $GLOBALS['g_date_end'];
        $t_date =  date("Y-m-d");

        $i=0;

        do {
            $s_p_date = $s_date;
            if ($s_p_date>$t_date){
               $my_dates[$i]['start'] = $t_date;  
            }else{
               $my_dates[$i]['start'] = $s_date;
            }
            
            $s_p_date = DateThis($s_date, '3 month');
            $e_p_date = DateThis($s_p_date, '0 day'); //SOS

            if ($e_p_date>$e_date) {
                $e_p_date=$e_date;
                $my_dates[$i]['end']   = $e_p_date;
                // echo "1 \n";
                // break;
            }elseif($e_p_date>$t_date){
                $e_p_date=$t_date;
                $my_dates[$i]['end']   = $e_p_date;
                // echo "2 \n";
                // break;
            }


            $my_dates[$i]['end']   = $e_p_date;
            $s_date=$s_p_date;
            $i++;
        }while ($e_p_date<$e_date or $s_p_date<=$e_date);

        $rows=count($my_dates);
        AddDatesToDB($my_dates,$rows,'start','end');
        echo "Completed function MakeDatesListDates\n";   
}


function AddDatesToDB($p_array,$p_rows,$p_start_date,$p_end_date){
/*
Outcome: Inserts the array of dates into the database table fb_date_periods.

Process:
Deletes records from table fb_date_periods.
Inserts a fresh array of dates obtained from the array into the database table fb_date_periods.  
*/    
require('fb_conn.php');  

    // FlushDatabaseTable("fb_date_periods");

    for ($i=0;$i<$p_rows;$i++){
     
        $stmt1 = $conn->prepare("INSERT INTO fb_date_periods 
         (seq,
         start_date,
         end_date) 
         VALUES (?, ?, ?)");

         $stmt1->bind_param("sss", 
            $seq,
            $start_date,
            $end_date);

            //set parameters and execute
            $seq        = $i;   
            $start_date = $p_array[$i][$p_start_date];
            $end_date   = $p_array[$i][$p_end_date];
            
            $stmt1->execute();
    }
           
         $stmt1->close();
         $conn->close();
}

function Step_Into_Date_Periods($p_page_id,$p_page_access_token,$p_facebook_object){
        
require('fb_conn.php'); 

$sql = "SELECT seq, start_date, end_date FROM fb_date_periods";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

        $v_start_date = $row["start_date"];
        $v_end_date   = $row["end_date"];

        if ($p_facebook_object=='fans'){
            GetPageFans($p_page_id,$p_page_access_token,$v_start_date,$v_end_date); 
        }elseif($p_facebook_object=='engagements'){
            GetPageEngagements($p_page_id,$p_page_access_token,$v_start_date,$v_end_date);
        }elseif($p_facebook_object=='video_views'){
            GetPageVideoViews($p_page_id,$p_page_access_token,$v_start_date,$v_end_date);
        }elseif($p_facebook_object=='video_view_times'){
            GetPageVideoViewTimes($p_page_id,$p_page_access_token,$v_start_date,$v_end_date);
        }






        

        }
    }
}





?>