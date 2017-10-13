<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(FALSE);//lock it down

if($_SESSION['_user']['detail_complete']){//can't be here once profile is done
    $_SESSION['statusCode'] =  1026;
    session_write_close();
    header("Location: index");
}

if($_POST){//incoming update attempt
    $Data = new Data;
    //Make sure they're 13yrs +    
    $yearmustbeunder = date("Y") - 12;
    $birthyear = substr($_POST['dob'], 0, 4);
    $age = date_diff(date_create($_POST['dob']), date_create('today'))->y;
    if($birthyear < $yearmustbeunder && $age >= 13 && !empty($_POST['fname']) && !empty($_POST['lname'])){
        $result = $Data->doUpdateUserDetails($_SESSION['_user']['id'], Format::sanitizeName($_POST['fname']), Format::sanitizeName($_POST['lname']), $_POST['dob']);
        if($result){
            $_SESSION['statusCode'] =  1018;
            $_SESSION['_user']['detail_complete'] = 1;
            $Data->doLog(1018, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Updated User Details');
            
            //Set new Session Vars
            $_SESSION['_user']['firstname'] = Format::sanitizeName($_POST['fname']);
            $_SESSION['_user']['lastname'] = Format::sanitizeName($_POST['lname']);
            
            session_write_close();
            header("Location: index");
        }
        else{
            $_SESSION['statusCode'] =  1019;
            $Data->doLog(1019, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Updated User Details');
        }
    }
    else{
        //not of age or missing data
        $_SESSION['statusCode'] =  1037;
        $Data->doLog(1037, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Set Details, Not of Age');
    }
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('User Detail');
?>

Welcome to <?php echo SITE_SHORTNAME ?>! We see that you're new here, and in order to continue, you must complete 
your account detail. Your account detail will collect your standard identifying information. It cannot be edited once submitted.<br /><br />
<div class="alert alert-warning" role="alert"><b>Warning:</b> This account detail is a one-time completion, please check your entries carefully.
You'll have the opportunity at a later time to specify additional information (such as your "preferred name" or your shirt size). <br /><br />
<b><em>Enter information on this page exactly as it appears on your government issued identification.</em></b></div>

<div class="col-md-6 col-md-push-3">
<form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="accountdetail" name="accountdetail">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Account Detail</h3>
      </div>
      <div class="panel-body">
        <div class="form-group has-feedback">
            <label class="control-label" for="fname">Legal First Name</label>
            <input type="text" name="fname" class="form-control" id="fname" placeholder="Michael" autocomplete="off">                
        </div>
        <div class="form-group has-feedback">
            <label class="control-label" for="lname">Legal Last Name</label>
            <input type="text" name="lname" class="form-control" id="lname" placeholder="Phelps" autocomplete="off">                
        </div>
        <div class="form-group has-feedback">
            
            <label class="control-label" for="dob">Legal Date of Birth</label><br />
            <input type="date" class="form-control" name="dob" placeholder="Date of Birth"><br />
            <div class="alert alert-warning" role="alert">We use this information to verify your age. Unless you are a student, your 
                Date of Birth will not be visible to anyone on the <?php echo SITE_SHORTNAME ?> Registration System, or any Team Coaches/Administrators.
                We're not allow to collect information about those under the age of 13.</div>
        </div>
        <button type="submit" class="btn btn-primary center-block" name="accountdetail">Finalize Details</button>              
      </div>
      <br />
    </div>
</form>
</div>

<script>
$(document).ready(function () {
    $('#accountdetail').validate({
        rules: {
            fname: {
                required: true,
                minlength: 3
            },
            lname: {
                required: true,
                minlength: 3
            },
            dob: {
                required: true,
                dateISO: true
            }
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
});
</script>

<?php
$BuildPage->printFooter();