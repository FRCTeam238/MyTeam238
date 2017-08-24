<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

if($_POST){//incoming update attempt
    $Data = new Data;
    $gen = md5(rand());
    $newverifykey = substr($gen,strlen($gen) - 12,12);
    $result = $Data->doUpdateEmailAddress($_SESSION['_user']['id'], $_SESSION['_user']['email'], $_POST['currentpassword'], $_POST['newemail'], $newverifykey);
    if($result){
        $Email = new Email;
        //this email takes a parameter of [email key, user id]
        $sendresult = $Email->sendEmail($_SESSION['_user']['email'], 'createaccount', [$newverifykey, $_SESSION['_user']['id']]);
        $_POST = array();//Clear at end of request
        if($sendresult){
            $_SESSION['statusCode'] =  1027;
            $Data->doLogEmailSent(1027, $_SESSION['_user']['id'], Format::currentDateTime());
            $Data->doLog(1027, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Email on File Updated');
            session_write_close();
            header("Location: logout?silent=1");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Send Email, but Updated Email on File');
        }        
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Email on File');
    } 
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Update Email');
$BuildPage->showCode();

?>

If you have had a change in your email address since registering with us, you can update your address on file. This will become your 
new log-in email address, however we cannot automatically change any mailing list subscriptions you may have. You'll need to update 
those after completing your email change here.<br /><br />

<div class="alert alert-danger" role="alert"><b>WARNING:</b> When you initiate an email update, we will automatically log you out and send an email to the new address you specify. 
    You will need to click the link sent to the new email address before you're able to access the system to continue registration or system usage.</div>
<div class="col-md-6 col-md-push-3">
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="emailupdate" name="emailupdate">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Update Email</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                <b>Current Email Address</b>
                <input type="text" name="text" class="form-control" id="currentemail" value="<?php echo $_SESSION['_user']['email'] ?>" disabled="disabled" autocomplete="off">                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="newemail">New Email Address</label>
                <input type="text" name="newemail" class="form-control" id="newemail" placeholder="me@example.com" autocomplete="off">                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="newemail2">New Email Address (again)</label>
                <input type="text" name="newemail2" class="form-control" id="newemail" placeholder="me@example.com" autocomplete="off">                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="currentpassword">Current Password</label><br /><em>You must verify your identity to initiate an email change by entering your current password.</em>
                <input type="password" class="form-control" name="currentpassword">
            </div>
          </div>
            <button type="submit" class="btn btn-primary center-block" name="emailupdate">Change Email</button>
          <br />
        </div>
        
    </form>
</div>

<script>

$(document).ready(function () {
    $.validator.addMethod("new_email_not_same", function(value, element) {
   return $('#newemail').val() != $('#currentemail').val()
}, "New Email address must be different!");
    
    $('#emailupdate').validate({
        rules: {
            newemail: {
                required: true,
                email: true,
                new_email_not_same: true
            },
            newemail2:{
              required: true,
              equalTo: "#newemail"
            },
            currentpassword: {
                required: true
            }
        },
        messages:{
            newemail: "A new, and different, email address is required",
            newemail2: "New emails must match"
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        submitHandler: function(form) {
            document.emailupdate.currentpassword.value = window.btoa(document.emailupdate.currentpassword.value);
            form.submit();
        }
    });
});
</script>

<?php
$BuildPage->printFooter();