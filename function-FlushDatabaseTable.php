<?php
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

?>