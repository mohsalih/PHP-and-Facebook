<?php 

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


?>