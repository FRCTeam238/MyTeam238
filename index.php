<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(FALSE);//lock it down

if(!$_SESSION['_user']['detail_complete']){
    $_SESSION['statusCode'] =  1016;
    session_write_close();
    header("Location: details");
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Home');
?>

<h2>My Team 238</h2>

<div class="alert alert-warning" role="alert">THIS IS TEMPORARY CONTENT THAT WILL BE REPLACED!</div>

PAGE CONTENT
<?php print_r($_SERVER);exit; ?>

<?php
$BuildPage->printFooter();