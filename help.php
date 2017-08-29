<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

$BuildPage = new BuildPage();
$BuildPage->printHeader('Account Help');
?>

<?php echo SITE_SHORTNAME ?> is here to help! We want you to have the best registration experience possible. If you encounter problems while interacting with 
the site, you can use the help methods below.
<br /><br />
<b>In-Person: </b>At any team meeting, talk with a Mentor or Coach<br />
<b>Email: </b>You can send your inquiry to web@frc238.org<br />
<br />
The following information is for debugging purposes, and may be requested by support engineers.<br /><br />

<?php $ip = $_SERVER['REMOTE_ADDR'];
$browser = $_SERVER['HTTP_USER_AGENT'];
$referrer = $_SERVER['HTTP_REFERER'];
 if ($referrer == "") {
  $referrer = "This page was accessed directly";
  }
echo "<b>Visitor IP address:</b><br/>" . $ip . "<br/>";
echo "<b>Browser (User Agent) Info:</b><br/>" . $browser . "<br/>";
echo "<b>Referrer:</b><br/>" . $referrer . "<br/>";
?>
<br /><br />
If you are no longer interested in being affiliated with <?php echo SITE_FULLNAME ?> you can deactivate your account by <a href="deactivate">clicking here</a>.<br /><br />
<a href="index" class="btn btn-info center-block" role="button">Return Home</a><br />
<?php
$BuildPage->printFooter();