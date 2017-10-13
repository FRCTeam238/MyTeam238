<?php
require_once '../main.inc.php';
$Security = new Secure;
$Security->requireAdminLogin();//lock it down ADMIN

$BuildPage = new BuildPage();
$BuildPage->printHeader('Admin');
?>
This is the administration page for <?php echo SITE_SHORTNAME ?>. From here, users (with appropriate permissions) can update system settings, view 
submitted data, and assist other users.
<br /><br />
Under Development

<?php
$BuildPage->printFooter();