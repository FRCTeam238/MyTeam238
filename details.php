<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(FALSE);//lock it down

if($_SESSION['_user']['detail_complete']){//can't be here once profile is done
    header("Location: index");
}

if($_POST){//incoming update attempt
    $Data = new Data;
    if($_POST['ofage'] && !empty($_POST['fname']) && !empty($_POST['lname'])){
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
            <label>
                <input type="checkbox" name="ofage"> I am at least 13 years of age<br /><br />
                <span style="font-style:italic;font-size:11px;">We're not allow to collect information about those under the age of 13.</span>
            </label>
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
            },
            ofage: {
                required: true
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