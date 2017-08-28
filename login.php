<?php
require_once 'main.inc.php';
if(isset($_SESSION['_user'])){//already logged in. can't be here
    header("Location: index");
    exit;
}
$Data = new Data;
if($_POST){//incoming login or account create attempt    
    if(isset($_POST['accountlogin'])){
        
        if(isset($_POST['rememberme'])):
            setcookie("email", $_POST['email'], time()+2592000);//30 day expiration
        else:
            //don't want it saved. erase if they have it
            if(isset($_COOKIE['email'])):
                setcookie("email", "", -1);//delete cookie
            endif;
        endif;
        
        $currentseason = $Data->getCurrentlyActiveSeason();
        $_SESSION['current_season_id'] = $currentseason->season_id;
        
        $login_result = $Data->doAccountLogin($_SESSION['current_season_id'], $_POST['email'], $_POST['password']);
        if($login_result->account_found_valid){
            //Check Email Verified
            if(!$login_result->emailVerified){
                $_SESSION['statusCode'] =  1008;
            }            
            //Check Account Approved
            elseif(!$login_result->account_approved){
                $_SESSION['statusCode'] =  1009;
            }            
            //Check Force PW Required
            elseif($login_result->forcePwChange){
                $_SESSION['statusCode'] =  1010;
                session_write_close();
                header("Location: password");
            }
            else{
                $Security = new Secure;
                $_SESSION['_user']['id'] = $login_result->account_id;
                $_SESSION['_user']['email'] = $login_result->email_address;
                if(!empty($login_result->preferred_first_name)){ //use preferred name if they have one
                    $_SESSION['_user']['firstname'] = $login_result->preferred_first_name;
                }
                else{
                    $_SESSION['_user']['firstname'] = $login_result->first_name;    
                }                
                $_SESSION['_user']['lastname'] = $login_result->last_name;
                $_SESSION['reg_type'] = $login_result->registrant_type;//not under user array!
                $_SESSION['_user']['detail_complete'] = $login_result->profileComplete ? 1 : 0;
                $_SESSION['_user']['ip'] = $_SERVER['REMOTE_ADDR'];
                
                $Security->startNewSession();
                $Data->doLog(0, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Login Complete');
                session_write_close();
                header("Location: index");    
            }            
        }
        else{
            //Could not Log In
            $_SESSION['statusCode'] =  1007;
        }
        $_POST = array();//Clear at end of request
    }
    elseif(isset($_POST['accountcreate'])){
        $gen = md5(rand());
        $emailKey = substr($gen,strlen($gen) - 12,12);
        $createresult = $Data->doCreateAccount($_POST['email'], $_POST['password_create'], $emailKey);
        if($createresult[0]){ //comes back true if we're okay to continue
            $Email = new Email;
            //this email takes a parameter of [email key, user id]
            $sendresult = $Email->sendEmail($_POST['email'], 'createaccount', [$emailKey, $createresult[1]]);
            $_POST = array();//Clear at end of request
            if($sendresult){
                $_SESSION['statusCode'] =  1002;
                $Data->doLogEmailSent(1002, $createresult[1], Format::currentDateTime());
                $Data->doLog(1002, $createresult[1], $_SERVER['REQUEST_URI'], 'Account Created');
            }
            else{
                $_SESSION['statusCode'] =  1003;
            }
        }
        else{
            //Could not create
            $_POST = array();//Clear at end of request
            $_SESSION['statusCode'] =  1004;
        }
    }
}
elseif(isset($_GET['activate'])){//account activation
    if(isset($_GET['id']) && isset($_GET['key'])){
        if(isset($_GET['invitation'])){
            $invite = $Data->acceptInvitation($_GET['id'], $_GET['key']);
            if(strlen($invite)){
                //invite accepted, account made. prompt for new password.
                header("Location: password?reset&key=invitedacct&email=".$invite);
            }
            else{
                $_SESSION['statusCode'] =  1020;
            }
        }
        else{
            $activateresult = $Data->doActivateAccount($_GET['id'], $_GET['key']);
            if($activateresult){
                $_SESSION['statusCode'] =  1005;
            }
            else{
                $_SESSION['statusCode'] =  1006;
            }
        }        
    }
    else{
        $_SESSION['statusCode'] =  1006;
    }
}
$allowNewAccounts = $Data->allowNewRegistrations();
$BuildPage = new BuildPage();
$BuildPage->printHeader('Login');
?>
<h2>My Dashboard</h2>
Welcome to the <?php echo SITE_FULLNAME; ?> Registration and Membership site. Please login to your existing account or create a new one. If you were emailed a registration 
invitation, please use the personalized link to create your account, as it will expedite the registration process.<br /><br />
<div class="col-md-6">
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="accountlogin" name="accountlogin">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Account Login</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                <label class="control-label" for="email">Email</label>
                <input type="text" name="email" class="form-control" id="email" placeholder="me@example.com" autocomplete="off"<?php if(isset($_COOKIE['email'])):echo(' value="'.$_COOKIE['email'].'"');else:echo(' autofocus="autofocus"');endif; ?>>                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="password">Password</label>
                <input type="password" name="password" class="form-control" id="password">                
            </div>
            <div class="checkbox center-block">
                <label>
                    <input type="checkbox" name="rememberme"<?php if(isset($_COOKIE['email'])):echo(' checked="checked"');endif; ?>> Remember Me
                </label>
            </div>
            <button type="submit" class="btn btn-success center-block" name="accountlogin">Login</button>              
          </div>
          <br />
        </div>
    </form>
    <a href="forgotPassword" class="btn btn-info center-block" style="max-width: 175px;">Forgot Password?</a>
</div>
<?php
    if($allowNewAccounts):
?>
<div class="col-md-6">
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="accountcreate" name="accountcreate">
        <div class="panel panel-warning">
          <div class="panel-heading">
            <h3 class="panel-title">Create Account</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                <label class="control-label" for="email">Email</label>
                <input type="text" name="email" class="form-control" id="email" placeholder="me@example.com" autocomplete="off">                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="password_create">Password</label>
                <input type="password" name="password_create" class="form-control" id="password_create">                
            </div>
              <div class="form-group has-feedback">
                <label class="control-label" for="password_create2">Password (confirm)</label>
                <input type="password" name="password_create2" class="form-control" id="password_create2">                
            </div>
          </div>
            <button type="submit" class="btn btn-warning center-block" name="accountcreate">Create Account</button>
            <br />
        </div>
    </form>
</div>
<?php
    else:
?>
<div class="col-md-6">
    <div class="panel panel-warning">
        <div class="panel-heading">
            <h3 class="panel-title">Create Account</h3>
          </div>
        <div class="panel-body" style="color:darkred;">
            <b>We're not currently accepting new accounts. Please come back at a later time.</b>
        </div>
    </div>
</div>
<?php
    endif;
?>

<script>
$(document).ready(function () {
    $('#accountlogin').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            }
        },
        messages:{
            email: "Account email address is required",
            password: "Please enter your password"
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        submitHandler: function(form) {
            document.accountlogin.password.value = window.btoa(document.accountlogin.password.value);
            form.submit();
        }
    });
    
    $('#accountcreate').validate({
        rules: {
            email: {
                required: true,
                email: true
            },
            password_create: {
                required: true,
                minlength: 8
            },
            password_create2: {
                required: true,
                equalTo: "#password_create"
              }
        },
        messages:{
            email: "An email address is required to create an account",
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
            document.accountcreate.password_create.value = window.btoa(document.accountcreate.password_create.value);
            document.accountcreate.password_create2.value = window.btoa(document.accountcreate.password_create2.value);
            form.submit();
        }
    });
});
</script>

<?php
$BuildPage->printFooter();