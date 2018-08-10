<?php
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

function Step_Into_Accounts(){
/*
Outcome: Step through accounts 
Process:
Read the global variable array g_array_dates.
Read each elements of the array.
Display the contents of each element value.
*/   
    $array_accounts = $GLOBALS['g_array_dates'];
    // var_dump($array_accounts);

    foreach ($array_accounts as $key => $value) {

        $account_id  = $value["account_id"];
        $account_name = $value["account_name"];
        $account_access_token = $value["access_token"];

        echo "Account: $account_id\n";

        }
echo "Function Step_Into_Accounts is completed\n";           
}
?>