<?php

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


?>