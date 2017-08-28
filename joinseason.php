<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

$Data = new Data;
require(CLASSES_DIR.'registrant_types.php');

if($_POST){//incoming join attempt
    if($_POST['seasonrole'] != "Select..."){
        $result = $Data->joinSeason($_SESSION['_user']['id'], $_POST['seasonid'], Format::sanitizeName($_POST['pfname']), $_POST['seasonrole']);
        if($result){
            $_SESSION['statusCode'] =  1024;
            $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'User Joined Season as '.$_POST['seasonrole']);
            
            //Set new Session Vars
            if(!empty($_POST['pfname'])){
                $_SESSION['_user']['firstname'] = Format::sanitizeName($_POST['pfname']);
            }
            $_SESSION['reg_type'] = $_POST['seasonrole'];
            
            //Seal and redirect
            session_write_close();
            header("Location: index");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Join User to Season');
        }
    }    
}
else{
    if($Data->userHasProfileInActiveSeason($_SESSION['_user']['id'])){
        $_SESSION['statusCode'] =  1026;
        $Data->doLog(1026, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED Attempted Access Join Season Page - Already Complete');
        session_write_close();
        header("Location: index");
    }
}

$current_season = $Data->getCurrentlyActiveSeason();

$BuildPage = new BuildPage();
$BuildPage->printHeader('Join Season');
?>

<div class="alert alert-danger" role="alert">Please make your selection carefully. This can only be selected one time per season, and is not editable.</div>

<?php echo SITE_FULLNAME; ?> is now registering for the <?php echo $current_season->season_year ?> season (aka <?php echo $current_season->season_name ?>) !
<br /><Br />
<div class="col-md-6 col-md-push-3">
<form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="joinseason" name="joinseason">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Season Role</h3>
      </div>
      <div class="panel-body">
        <div class="form-group has-feedback">
            <label class="control-label" for="pfname">Preferred First Name</label><br /><em>This field is optional. If you don't specify a preferred name, we'll simply 
            use your legal first name as your preferred.</em>
            <input type="text" name="pfname" class="form-control" id="pfname" placeholder="Mike" autocomplete="off">                
        </div>
        <div class="form-group has-feedback">
            <label class="control-label" for="seasonrole">Your Role for <?php echo ($current_season->season_year - 1) ?> - <?php echo $current_season->season_year ?> Season</label><br />
            <select class="form-control" name="seasonrole" id="seasonrole" onchange="ChangeRole()">
                <option value="0">Select...</option>
                <option value="<?php echo RegistrantTypes::Student ?>"><?php echo RegistrantTypes::toString(RegistrantTypes::Student) ?></option>
                <option value="<?php echo RegistrantTypes::Mentor ?>"><?php echo RegistrantTypes::toString(RegistrantTypes::Mentor) ?></option>
                <option value="<?php echo RegistrantTypes::Parent ?>"><?php echo RegistrantTypes::toString(RegistrantTypes::Parent) ?></option>
                <option value="<?php echo RegistrantTypes::Alumni ?>"><?php echo RegistrantTypes::toString(RegistrantTypes::Alumni) ?></option>
                <option value="Sponsor">Sponsor</option>
             </select>
        </div>
        <div class="alert alert-info" role="alert" id="roledesc">Select a role...</div>
        <input type="hidden" value="<?php echo $current_season->season_id ?>" name="seasonid" />
        <button type="submit" class="btn btn-primary center-block" name="joinseasongo" id="joinseasongo">Begin Registration!</button>              
      </div>
      <br />
    </div>
</form>
</div>

<script>
    $( document ).ready(function() {
        $("#roledesc").hide();
        $("#joinseasongo").prop("disabled", true);
    });
    $("#joinseason").validate({
        rules: {
            pfname: {
                required: false,
                minlength: 3
            },
        },
        submitHandler: function(form) {
          if(document.getElementById("seasonrole").value == 0){
               alert('Please select a registration type before continuing!'); 
          }
          else{
              var selectedid = document.getElementById("seasonrole").value;
              var selectedtext = $("#seasonrole option[value='" + selectedid + "']").text();
                if (confirm('Are you sure you would like to register as a **' + selectedtext + '**?')){
                    form.submit(); 
                }
          }
        }
    });
    function ChangeRole() {
        $("#joinseasongo").prop("disabled", false);
        if(document.getElementById("seasonrole").value == <?php echo RegistrantTypes::Mentor ?>){
            $("#roledesc").html("We consider \"Mentors\" to be anyone who is post-high school age and assists the team, but <u><b>does not</b> have a student on the team</u>.");
            if($("#roledesc").is(":visible")){
                $("#roledesc").fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            }
            else{
                $( "#roledesc" ).show("medium");
            }
        }
        else if(document.getElementById("seasonrole").value == <?php echo RegistrantTypes::Parent ?>){
            $("#roledesc").html("Everyone who has a student on the team is classified as a \"Parent\", <u>even if they additionally participate as a mentor the team</u>.");
            if($("#roledesc").is(":visible")){
                $("#roledesc").fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            }
            else{
                $( "#roledesc" ).show("medium");
            }
        }
        else if(document.getElementById("seasonrole").value == "Sponsor"){
            $("#roledesc").html("We appreciate your support! However, there really isn't anything required of Sponsors in this system. If you need assistance, please contact the Team.");
            $("#joinseasongo").prop("disabled", true);
            if($("#roledesc").is(":visible")){
                $("#roledesc").fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            }
            else{
                $( "#roledesc" ).show("medium");
            }
        }
        else{
            $( "#roledesc" ).hide("fast");
            $("#roledesc").html("Select a role...");
        }
    }
</script>

<?php
$BuildPage->printFooter();