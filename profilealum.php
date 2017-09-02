<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE, TRUE);//lock it down
$readonly = false;
$Data = new Data;
require_once CLASSES_DIR.'registrant_types.php';

if(!isset($_SESSION['reg_type']) || $_SESSION['reg_type'] != RegistrantTypes::Alumni){
    $_SESSION['statusCode'] =  1026;
    $Data->doLog(1026, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Attempted to View Ineligible Page');
    session_write_close();
    header("Location: index");
}

if($_POST){//incoming profile info attempt    
    
    $result = $Data->doUpdateSeasonProfile_Alumni($_SESSION['_user']['id'], $_SESSION['current_season_id'], $_POST['grad_year']);
    if($result){
        $_SESSION['statusCode'] =  1024;
        $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Season Profile Alumni Updated');
        session_write_close();
        header("Location: index");
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Season Alumni Profile');
    }
}
else{
    $season_profile_alumni = $Data->getCurrentSeasonProfile_Alumni($_SESSION['_user']['id'], $_SESSION['current_season_id']);
    if($season_profile_alumni && $season_profile_alumni->isProfileComplete()){
        $readonly = true;
    }
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Alumni Profile');
?>

Each season you will be asked to answer some questions that are specific to your registration type.<br /><br />
<?php if($readonly): ?>
    <div class="alert alert-success" role="alert">You've already submitted this information. The contents are available for review below.</div><br />
<?php endif; ?>

<div class="col-md-push-3 col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Alumni Profile</h3>
        </div>
        <div class="panel-body">
        <?php if(!$readonly): ?>
        <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="alumprofile" name="alumprofile">
        <?php endif; ?>
            <div class="col-md-12">      
                <div class="form-group has-feedback">
                    <label class="control-label" for="grad_year">Graduation Year</label>
                    <input type="text" name="grad_year" class="form-control" id="grad_year" placeholder="<?php echo date("Y",strtotime("-1 year")); ?>" autocomplete="off"
                        <?php if($readonly){echo ' value="'.$season_profile_alumni->graduation_year.'" disabled';} ?>
                    >                
                </div>
            </div>
        <?php if(!$readonly): ?>
            <div class="col-md-12"><br />
                <button type="submit" class="btn btn-primary center-block" name="alumprofile">Update Profile</button>
            </div>
        </form>
        <?php endif; ?>
        </div>
    </div>
    <?php if($readonly): ?>
    <a href="index" class="btn btn-info center-block" role="button">Return Home</a><br />
    <?php endif; ?> 
</div>

<script>
$(document).ready(function () {
    $('#alumprofile').validate({
        rules: {
            grad_year: {
                required: true,
                number: true,
                min: 1992,
                max: <?php echo date("Y") ?>
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