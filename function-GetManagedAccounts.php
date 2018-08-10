<?php
function GetManagedAccounts(){
/***********************************************************************
Outcome: Populates the database table fb_accounts with account details for all the pages the parent Facebook account manages. 

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

?>