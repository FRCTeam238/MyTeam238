<?php
require_once '../main.inc.php';
require_once CLASSES_DIR.'search_statuscode.php';
$Security = new Secure;
$Security->requireAdminLogin();//lock it down ADMIN
$BuildPage = new BuildPage();
$BuildPage->printHeader('Admin Approve Accounts');

$Data = new DataAdmin();
if($_POST){
    //Approve a user on request
    $actapproved = $Data->doApproveAccount($_POST['userid']);
    unset($_POST);
}
$pending = $Data->getAccountsPendingApproval();

?>
The following accounts are pending approval. An account requires approval if it was created without using 
an Access Code, and is triggered after the user selects their Role type. Once an account is approved, 
it doesn't require approval again in the future, and can access an expanded set of site features.<br /><br />
The following accounts are awaiting approval:<br /><br />

<?php
if(isset($actapproved) && $actapproved){
    echo('<span style="font-weight:bold;color:green;">Account Approved</span><hsr /><');
}

if(count($pending) > 0){
    echo('<table class="table table-striped table-hover"><tr><th>Name</th><th>Email</th><th>Role Selected</th><th>Approve?</th></tr>');
    foreach ($pending as $pend){
        echo('<tr><td style="vertical-align:middle;">'.$pend->first_name.' '.$pend->last_name.'</td><td style="vertical-align:middle;">'.$pend->email.'</td><td style="vertical-align:middle;">'.$pend->reg_type.'</td><td>');
        echo('<form action="'.strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))).'" method="POST" id="approveact" name="approveact">');
        echo('<input type="hidden" name="userid" value="'.$pend->user_id.'"><button type="submit" class="btn btn-success">Approve</button></form>');
        echo('</td></tr>');
    }
    echo('</table>');
}
else{
    echo('<span style="font-weight:bold;color:green;">There are no accounts pending approval- Thanks for checking though!</span>');
}

?>

<?php
$BuildPage->printFooter();