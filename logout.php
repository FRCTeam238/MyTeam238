<?php
require_once 'main.inc.php';
if(isset($_SESSION['_user'])){
$sql = "DELETE FROM ".TABLE_SESSIONS
	 . " WHERE user_id = ".db_input($_SESSION['_user']['id']).";";
    $query = db_query($sql);
    if(!$query){//didnt delete? who cares i guess?
    }
    $_SESSION['statusCode'] = 1011;
    $Data = new Data;
    $Data->doLog(1011, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Logout Complete');
    Secure::suspendThisSession();
    session_write_close();
    header("Location: login");
}
else{//not logged in. go away.
    header("Location: login");
}