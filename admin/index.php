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
<table class="table text-center">
    <tr>
        <td><a href="http://www.frc238.org" target="_blank"><img src="../images/icons/website.png" width="128" height="128" alt="website" border="0"></a></td>
        <td><a href="http://www.surpasshosting.com/server-status.php" target="_blank"><img src="../images/icons/status.png" width="128" height="128" alt="status" border="0"></a></td> 
        <td><a href="codes"><img src="../images/icons/code.png" width="128" height="128" alt="codes" border="0"></a></td>        
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td style="border-top:0px;">Team Website</td>
        <td style="border-top:0px;">Server Status</td>
        <td style="border-top:0px;">Check Codes</td>
        <td style="border-top:0px;">&nbsp;</td>
    </tr>
    <tr>
        <td>
            <?php if($_SESSION['_admin']['can_approve_accounts'] == 1){echo('<a href="approveaccounts"><img src="../images/icons/approveaccounts.png" border="0" /></a>');}else{echo('<img src="../images/icons-inactive/approveaccounts.png" border="0" title="Restricted" />');} ?>
        </td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td style="border-top:0px;">Approve Accounts</td>
        <td style="border-top:0px;">&nbsp;</td>
        <td style="border-top:0px;">&nbsp;</td>
        <td style="border-top:0px;">&nbsp;</td>
    </tr>
</table>

<?php
$BuildPage->printFooter();