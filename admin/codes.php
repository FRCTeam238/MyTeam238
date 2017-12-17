<?php
require_once '../main.inc.php';
require_once CLASSES_DIR.'search_statuscode.php';
$Security = new Secure;
$Security->requireAdminLogin();//lock it down ADMIN
$BuildPage = new BuildPage();
$BuildPage->printHeader('Admin');

if($_POST){
    $Data = new DataAdmin();
    $result = $Data->searchStatusCode($_POST['code']);
}
?>
The registration and administration portals use status codes to relay abbreviated information to users based on 
their actions. You can enter a code below to see the message being displayed to the user.<br />

<?php
if(isset($result)){
    if($result->code_id > 0){
        $error = $result->code_is_error ? "Yes" : "No";
        echo '
        <hr />
        <b>Code:</b> '.$result->code_id.'<br/>
        <b>Is Error:</b> '.$error.'<br/>
        <b>Message:</b> '.$result->code_message.'<br/><hr />
        '; 
    }
    else{
        echo '<hr /><span style="color:red;font-weight:bold;">The search returned no results.</span><br /><hr />';
    }
}
?>

<form name="search" method="post" action="<?php $_SERVER['PHP_SELF'] ?>">
    <label for="code">Status Code:</label>
    <input name="code" type="text" id="code" size="10" maxlength="4" autocomplete="off">&nbsp;&nbsp;
    <button type="submit" class="btn btn-success"> Search </button>
</form>

<?php
$BuildPage->printFooter();