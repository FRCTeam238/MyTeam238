<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE, TRUE, TRUE);//lock it down
$readonly = false;
$Data = new Data;
require_once CLASSES_DIR.'registrant_types.php';
require_once CLASSES_DIR.'season_profile_student.php';

if(!isset($_SESSION['reg_type']) || $_SESSION['reg_type'] != RegistrantTypes::Student){
    $_SESSION['statusCode'] =  1026;
    $Data->doLog(1026, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Attempted to View Ineligible Page');
    session_write_close();
    header("Location: index");
}

if($_POST){//incoming profile info attempt
    $result = $Data->doUpdateSeasonProfile_Student($_SESSION['_user']['id'], $_SESSION['current_season_id'], $_POST['grade'], $_POST['student_id']);
    if($result){
        $_SESSION['statusCode'] =  1024;
        $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Season Student Profile Updated');
        session_write_close();
        header("Location: index");
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Season Student Profile');
    }
}
else{
    $season_profile_student = $Data->getCurrentSeasonProfile_Student($_SESSION['_user']['id'], $_SESSION['current_season_id']);
    if($season_profile_student && $season_profile_student->isProfileComplete()){
        $readonly = true;
    }
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Student Profile');
?>

Each season you will be asked to answer some questions that are specific to your registration type.<br /><br />
<?php if($readonly): ?>
    <div class="alert alert-success" role="alert">You've already submitted this information. The contents are available for review below.</div><br />
<?php endif; ?>

<div class="col-md-push-3 col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Student Profile</h3>
        </div>
        <div class="panel-body">
        <?php if(!$readonly): ?>
        <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="studentprofile" name="studentprofile">
        <?php endif; ?>
            <div class="col-md-12">
                <div class="form-group has-feedback">
                    <label class="control-label" for="grade">Grade Level</label><br />
                    <select class="form-control" name="grade" id="grade"
                        <?php if($readonly){echo ' disabled';} ?>
                    >
                        <option value="8" <?php if($readonly && $season_profile_student->grade_level == "8"){echo ' selected';} ?>>8th Grade</option>
                        <option value="9" <?php if($readonly && $season_profile_student->grade_level == "9"){echo ' selected';}if(!$readonly){echo ' selected';} ?>>Freshman (9th Grade)</option>
                        <option value="10" <?php if($readonly && $season_profile_student->grade_level == "10"){echo ' selected';} ?>>Sophomore (10th Grade)</option>
                        <option value="11" <?php if($readonly && $season_profile_student->grade_level == "11"){echo ' selected';} ?>>Junior (11th Grade)</option>
                        <option value="12" <?php if($readonly && $season_profile_student->grade_level == "12"){echo ' selected';} ?>>Senior (12th Grade)</option>
                    </select>
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="student_id">MSD Student ID</label>
                    <input type="text" name="student_id" class="form-control" id="student_id" placeholder="000000" autocomplete="off"
                        <?php if($readonly){echo ' value="'.$season_profile_student->msd_school_id.'" disabled';} ?>
                    >                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="permission">Permission Slip Status</label>
                    <input type="text" name="permission" class="form-control" id="permission"
                        <?php 
                            if($season_profile_student && $season_profile_student->permission_slip_signed){
                                echo 'value="Signed by '.$season_profile_student->permission_slip_signed_who.'"';
                            }
                            else{
                                echo 'value="Not Signed"';
                            }
                        ?>
                    disabled>
                    <em>Permission slip status can be edited by a Parent or Legal Guardian</em>
                </div>
            </div>
        <?php if(!$readonly): ?>
            <div class="col-md-12"><br />
                <button type="submit" class="btn btn-primary center-block" name="studentprofile">Update Profile</button>
            </div>
        </form>
        <?php endif; ?>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('#studentprofile').validate({
        rules: {
            student_id: {
                required: true,
                number: true
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