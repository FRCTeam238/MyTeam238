<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

if($_POST){//incoming deactivate attempt
    $Data = new Data;
    if($_POST['agree']){
        $result = $Data->doDeactivateAccount($_SESSION['_user']['id'], $_SESSION['_user']['email'], $_POST['currentpassword']);
        if($result){
            $_SESSION['statusCode'] =  1028;
            $Data->doLog(1028, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Account Deactivated');
            session_write_close();
            header("Location: logout?silent=1");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Deactivate Account');
        }
    }
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Deactivate Account');
?>

<div class="alert alert-warning" role="alert">Please carefully review the following information before making a final decision regarding your account.</div>

<?php echo SITE_FULLNAME ?> understands that, from time to time, users wish to deactivate their account and end their relationship with <?php echo SITE_SHORTNAME ?>.
Electing to proceed with prevent future use of the currently logged-in user from accessing the electronic <?php echo SITE_SHORTNAME ?> resources, as the account will enter 
a new permanent state. Please note that this step is not necessary when your career, as a student, mentor, etc, comes to an end. Your account will automatically "go dormant" 
when unused, no need to proceed. You only need to continue if you wish to deactivate your account, not as a required step of ending time with the team.<br /><br />
<span style='color:red;'>We <b>highly recommend</b> you speak with Team Coaches or Administrators before doing this process.</span>
<br /><br />
<div class="col-md-6 col-md-push-3">
<form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="deactivateaccount" name="deactivateaccount">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Sign Document</h3>
      </div>
      <div class="panel-body">
        <label>
            <input type="checkbox" name="agree"> I wish to permanently deactivate my <?php echo SITE_FULLNAME ?> account.<br /><br />
            <span style="font-style:italic;font-size:11px;">If you do not wish to proceed, use the menu to return to the home page. Otherwise, 
            enter your current password to verify your identity.</span>
        </label>
        <div class="form-group has-feedback">
            <label class="control-label" for="currentpassword">Current Password</label><br />
            <input type="password" class="form-control" name="currentpassword">
        </div>
        <button type="submit" class="btn btn-primary center-block" name="deactivateaccount">Deactivate</button>              
      </div>
      <br />
    </div>
</form>
</div>

<script>
$(document).ready(function () {
    $('#deactivateaccount').validate({
        rules: {
            agree: {
                required: true
            },
            currentpassword: {
                required: true
            }
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        submitHandler: function(form) {
            if(confirm("Are you absolutely sure you wish to Deactivate?")){
                document.deactivateaccount.currentpassword.value = window.btoa(document.deactivateaccount.currentpassword.value);
                form.submit();
            }
        }
    });
});
</script>

<?php
$BuildPage->printFooter();