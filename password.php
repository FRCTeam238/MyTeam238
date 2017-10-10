<?php
require_once 'main.inc.php';

//Initialize
$showenteremail = false;
$showrequestprocessed = false;
$showupdateform = false;
$update_requirecurrentpassword = false;

if(isset($_SESSION['_user'])){//already logged in. must be changing password.
    $showupdateform = true;
    $update_requirecurrentpassword = true;
}

if($_POST){//incoming email address to reset or update password request
    $Data = new Data;
    if(isset($_POST['passwordreset'])){
        //start the reset
        $gen = md5(rand());
        $resetKey = substr($gen,strlen($gen) - 12,12);
        //this method will prep the user account for reset. a 0 return means user not found or failure
        $setupreset = $Data->doAllowAccountPwReset($_POST['email'], $resetKey, (time() + 3600));
        if($setupreset){
            //Reset setup, send email
            $Email = new Email;
            $emailsend = $Email->sendEmail($_POST['email'], 'pwreset', [$resetKey, $_POST['email']]);//0 is key, 1 is email
            if($emailsend){
                $showrequestprocessed = true;//done, show the done message
            }
            else{
                $_SESSION['statusCode'] =  1013;
                session_write_close();
                header("Location: login");
            }            
        }
        else{
            $_SESSION['statusCode'] =  1012;
            session_write_close();
            header("Location: login");
        }
    }
    elseif(isset($_POST['passwordupdate'])){
        //process the update with the new password
        if($_POST['email'] == 'usepasswordinstead'){//update a logged in user            
           $updateresult = $Data->doUpdateUserPassword_ViaCurrent($_SESSION['_user']['id'], $_POST['currentpassword'], $_POST['password_update']);
           if($updateresult){
                $_SESSION['statusCode'] =  1014;
                session_write_close();
                header("Location: logout");//logout so they can use the new password
            }
            else{
                $_SESSION['statusCode'] =  1023;
            }
        }
        else{
            $updateresult = $Data->doUpdateUserPassword($_POST['email'], $_POST['key'], $_POST['password_update']);
            if($updateresult){
                $_SESSION['statusCode'] =  1014;
                session_write_close();
                header("Location: login");
            }
            else{
                $_SESSION['statusCode'] =  1015;
                session_write_close();
                header("Location: login");
            }
        }
    }
}
elseif(isset($_GET['reset'])){//reset email link clicked
    if(isset($_GET['email']) && isset($_GET['key'])){
        $showupdateform = true;
    }
    else{
        header("Location: login");
    }
}
elseif(!isset($_SESSION['_user'])){
    $showenteremail = true;
}
$BuildPage = new BuildPage();
$BuildPage->printHeader('Forgotten Password');
$BuildPage->showCode();
?>
If you have forgotten the password you use to access your account, you can request it be reset below. If you've forgotten the email address 
associated with your account, please contact us for assistance.<br /><br />
<div class="col-md-6 col-md-push-3">    
    <?php
    if($showenteremail):
    ?>
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="passwordreset" name="passwordreset">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Password Reset</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                <label class="control-label" for="email">Email</label>
                <input type="text" name="email" class="form-control" id="email" placeholder="me@example.com" autocomplete="off">                
            </div>
          </div>
            <button type="submit" class="btn btn-primary center-block" name="passwordreset">Reset</button>
          <br />
        </div>
        
    </form>
    <?php
    elseif($showrequestprocessed):
    ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Password Reset</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                The reset request has been submitted. An email with reset instructions will be sent to the email address you specified. You can safely close this window.
            </div>
          </div>
    </div>
    <?php
    elseif($showupdateform):
    ?>
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="passwordupdate" name="passwordupdate">
        <div class="panel panel-warning">
          <div class="panel-heading">
            <h3 class="panel-title">Password Reset</h3>
          </div>
          <div class="panel-body">
            <?php
              if($update_requirecurrentpassword):
            ?>
            You can update your existing password by entering your current password and a new selection.<br /><br />
            <div class="form-group has-feedback">
                <label class="control-label" for="currentpassword">Current Password</label>
                <input type="password" name="currentpassword" class="form-control" id="currentpassword">
                <input type="hidden" name="email" value="usepasswordinstead">
                <input type="hidden" name="key" value="usepasswordinstead">
            </div>
            <?php
                else:
            ?>
            Your request has been accepted. To update your password, please select a new one below.<br /><br />
            <div class="form-group has-feedback">
                <label class="control-label" for="email">Email</label>
                <input type="text" name="email" class="form-control" id="email" value="<?php echo($_GET['email']); ?>" readonly="readonly">
                <input type="hidden" name="key" value="<?php echo($_GET['key']); ?>">
            </div>
            <?php
                endif;
            ?>
            <div class="form-group has-feedback">
                <label class="control-label" for="password_update">Password</label>
                <input type="password" name="password_update" class="form-control" id="password_update">                
            </div>
              <div class="form-group has-feedback">
                <label class="control-label" for="password_update2">Password (confirm)</label>
                <input type="password" name="password_update2" class="form-control" id="password_update2">                
            </div>
          </div>
            <button type="submit" class="btn btn-warning center-block" name="passwordupdate">Update Password</button>
            <br />
        </div>
    </form>
    <?php
    endif;
    ?>
</div>

<script>
$(document).ready(function () {
    $('#passwordreset').validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages:{
            email: "Account email address is required"
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    
    $('#passwordupdate').validate({
        rules: {
            <?php
                if(!$update_requirecurrentpassword):
            ?>
            email: {
                required: true,
                email: true
            },
            <?php
                else:
            ?>
            currentpassword: {
                required: true
            },
            <?php
                endif;
            ?>
            password_update: {
                required: true,
                minlength: 8
            },
            password_update2: {
                required: true,
                equalTo: "#password_update"
              }
        },
        messages:{
            email: "Email address is required",
            currentpassword: "Current password is required",
            password_create: "Please enter a password",
            password_create2: "The passwords you entered do not match"
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        submitHandler: function(form) {
            document.passwordupdate.password_update.value = window.btoa(document.passwordupdate.password_update.value);
            document.passwordupdate.password_update2.value = window.btoa(document.passwordupdate.password_update2.value);
            document.passwordupdate.currentpassword.value = window.btoa(document.passwordupdate.currentpassword.value);
            form.submit();
        }
    });
    
});
</script>

<?php
$BuildPage->printFooter();