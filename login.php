<?php
require_once 'main.inc.php';
if(isset($_SESSION['_user'])){//already logged in. can't be here
    header("Location: index");
    exit;
}
$Data = new Data;
$site_settings = $Data->getSiteSettings();
if($_POST){//incoming login or account create attempt    
    if(isset($_POST['accountlogin'])){
        if(isset($_SESSION['login_locked_until']) && !TESTMODE){
            if(strtotime(Format::currentDateTime()) < $_SESSION['login_locked_until']){
                //still locked out
                $_SESSION['statusCode'] =  1032;
                $spamblock = true;
            }
            else{
                unset($_SESSION['login_locked_until']);//they can try again now
            }
        }        
        if(isset($_SESSION['last_login_attempt'])){ //we set this flag to catch repeat rapid login attempts
            $timeFirst  = strtotime($_SESSION['last_login_attempt']);
            $timeSecond = strtotime(Format::currentDateTime());
            $differenceInSeconds = $timeSecond - $timeFirst;
            if($differenceInSeconds < 5){
                //User is trying too fast. Needs to wait between attempts.
                $_SESSION['statusCode'] =  1031;
                $spamblock = true;
            }
        }
        if(!isset($spamblock)){
            $_SESSION['last_login_attempt'] = Format::currentDateTime();
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
                        $_SESSION['_user']['preferredfirstname'] = $login_result->preferred_first_name;
                    }
                    else{
                        $_SESSION['_user']['preferredfirstname'] = $login_result->first_name;
                    }
                    $_SESSION['_user']['firstname'] = $login_result->first_name;
                    $_SESSION['_user']['lastname'] = $login_result->last_name;
                    $_SESSION['reg_type'] = $login_result->registrant_type;//not under user array!
                    $_SESSION['_user']['detail_complete'] = $login_result->profileComplete ? 1 : 0;
                    $_SESSION['_user']['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['_user']['approved_account'] = 0;
                    if($login_result->account_approved){
                        $_SESSION['_user']['approved_account'] = 1;
                    }

                    //ADDITIONAL FIELDS THAT NEED TO BE DETERMINED ON LOGIN (default state)
                    $_SESSION['season_signed_behavior'] = 0;
                    $_SESSION['season_profile_complete'] = 0;

                    if($Data->userHasProfileInActiveSeason($currentseason->season_id, $_SESSION['_user']['id'])){
                        $profile = $Data->getCurrentSeasonProfile($_SESSION['_user']['id'], $_SESSION['current_season_id']);
                        $profile->isProfileComplete();
                        $_SESSION['season_signed_behavior'] = $profile->behavior_contract;
                        $_SESSION['season_profile_complete'] = $profile->season_profile_complete;
                    }
                    
                    //CHECK FOR ADMIN PERMISSIONS AND LOAD THEM UP, IF APPLICABLE
                    $DataAdmin = new DataAdmin();
                    $admin_login = $DataAdmin->doAdminLogin($login_result->account_id);
                    if($admin_login->is_admin){
                        $_SESSION['_admin']['is_admin'] = 1;//can see admin basic info
                        $_SESSION['_admin']['can_approve_accounts'] = $admin_login->can_approve_accounts;
                        $_SESSION['_admin']['can_view_profiles'] = $admin_login->can_view_profiles;
                    }

                    //READY FOR SESSION and LOGIN
                    $Security->startNewSession();
                    unset($_SESSION['last_login_attempt']);//delete the tracking of time, since they were successful
                    unset($_SESSION['login_failures']);
                    $Data->doLog(0, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Login Complete');
                    session_write_close();
                    header("Location: index");    
                }
            }
            else{
                //Could not Log In
                $_SESSION['statusCode'] =  1007;
            }
        }
        $_POST = array();//Clear at end of request
        //If we reach here, we have a login attempt, but it was not successful. Add to failure count.
        if(!isset($_SESSION['login_failures'])){
            $_SESSION['login_failures'] = 1;
        }
        elseif(!isset($_SESSION['login_locked_until'])){
            $_SESSION['login_failures']++;
        }
        if($_SESSION['login_failures'] >= 5){
            //Stop them for a while
            $_SESSION['statusCode'] =  1032;
            $_SESSION['login_locked_until'] = strtotime("+10 minutes", strtotime(Format::currentDateTime()));
            unset($_SESSION['login_failures']);
        }
    }
    elseif(isset($_POST['accountcreate'])){
        if(($site_settings->new_accounts_access_code != NULL) && isset($_POST['accesscode']) && (strlen(trim($_POST['accesscode'])) > 0)){//system is allowing access codes
            if(!isset($_POST['accesscode']) || strtoupper($_POST['accesscode']) != strtoupper($site_settings->new_accounts_access_code)){
                $_SESSION['statusCode'] =  1036;
                $skip_processing = true;
            }
            else{
                $access_code_used = 1;
            }
        }
        if(!isset($skip_processing)){
            $gen = md5(rand());
            $emailKey = substr($gen,strlen($gen) - 12,12);
            if(!isset($access_code_used)){$access_code_used = 0;}
            $createresult = $Data->doCreateAccount($_POST['email'], $_POST['password_create'], $emailKey, $access_code_used);
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
$allowNewAccounts = $site_settings->allow_new_accounts;
$BuildPage = new BuildPage();
$BuildPage->printHeader('Login');
if(isset($_SESSION['login_locked_until'])){
    echo '<!--Locked Until: ' . $_SESSION['login_locked_until'] . '-->';
}
?>
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
                <input type="password" name="password" class="form-control" id="password" autofocus>                
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
    <a href="password" class="btn btn-info center-block" style="max-width: 175px;">Forgot Password?</a>
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
                <?php if($site_settings->new_accounts_access_code): ?>
                <div class="form-group has-feedback">
                    <label class="control-label" for="accesscode">Access Code</label><br />
                    <em>Current system configuration allows the use of Access Codes for expedited registration. If you do not 
                        have an access code, simply leave this box blank to proceed with registration.</em>
                    <input type="text" name="accesscode" class="form-control" id="accesscode" autocomplete="off" placeholder="Optional">                
                </div>
                <?php endif; ?>
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
            password: "Please enter your password",
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
            password_create: "Please enter a password. It needs to be at least 8 characters.",
            password_create2: "The passwords you entered do not seem to match."
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