<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE, TRUE);//lock it down

require_once(CLASSES_DIR.'registrant_types.php');
require_once(CLASSES_DIR.'relationship_types.php');
$show_search_results = false;
$show_user_selected = false;

$Data = new Data;
if($_POST && isset($_POST['usersearch'])){//searching for user
    $search_result = $Data->userSearch($_SESSION['current_season_id'], $_SESSION['_user']['id'], $_POST['searchname']);
    $show_search_results = true;
}
else if($_POST && isset($_POST['selectuser'])){//a user has been picked from the search
    $show_user_selected = true;
}
else if($_POST && isset($_POST['addemercontact'])){//a user to make emer contact
    if($Data->doAddEmergencyContact_ById($_SESSION['_user']['id'], $_SESSION['current_season_id'], $_POST['contact_id'])){
        $_SESSION['statusCode'] =  1024;
        $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Emer Contact Added (id)');
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Add Emer Contact (id)');
    }
}
else if($_POST && isset($_POST['manualcontact'])){
    $realphone = preg_replace("/[^0-9]/","",$_POST['cellphone']);
    if($Data->doAddEmergencyContact_Manual($_SESSION['_user']['id'], $_SESSION['current_season_id'], $_POST['fname'], $_POST['lname'], $_POST['relationtype'], $realphone)){
        $_SESSION['statusCode'] =  1024;
        $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Emer Contact Added (manual)');
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Add Emer Contact (manual)');
    }
}

$current_emer_contact = $Data->getEmergencyContact($_SESSION['_user']['id'], $_SESSION['current_season_id']);

$BuildPage = new BuildPage();
$BuildPage->printHeader('Emergency Contact');
?>

In the unlikely event of an emergency while participating in <?php echo SITE_SHORTNAME ?> activities, we'll need to know what action you wish us to take. If your emergency
contact is already affiliated with us, you can simply search for, and select, that user. If they are not affiliated, you can manually add their contact information. <span style='color:red;'>All 
    emergency contacts must be 18 years or older.</span>
<br /><br />
<div class="col-md-12">
    <div class="col-md-4">
        <div class="panel panel-success">
            <div class="panel-heading">
              <h3 class="panel-title">Current Emer. Contact</h3>
            </div>
            <div class="panel-body">
                Your current emergency contact is:<br /><b>
                <?php
                    echo $current_emer_contact[0] . ' ' . $current_emer_contact[1];
                    ?></b>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Add Emer. Contact</h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="usersearch" name="usersearch">
                   <div class="form-group has-feedback">
                       <b>Search for User (Email or Last Name)</b><br /><em>To begin (or restart) a search, simply enter new terms and select &quot;Search&quot;</em>
                        <input type="text" name="searchname" class="form-control" id="searchname" placeholder="abc@email.com or Smith"
                               <?php if(isset($_POST['searchname'])){echo ' value ="'.$_POST['searchname'].'" ';} ?> autocomplete="off">                
                    </div>
                    <button type="submit" class="btn btn-primary center-block" name="usersearch">Search</button>
                  <br />
                </form>
                <?php if($show_search_results): ?>
                    <em>Based on your search, indicate the matching user by pressing the matching &quot;Select&quot; button.</em>
                    <table class="table">
                        <tr>
                            <th>Name</th>
                            <th>User Type</th>
                            <th>Select</th>
                        </tr>
                        <?php
                        $counter = 1;
                            foreach ($search_result as $user){
                                $buttoncode = '<form action="'.strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))).'" method="POST" id="selectuser" name="selectuser">'
                                    . '<input type="hidden" name="userid" value="'.$user->user_id.'" /><input type="hidden" name="usersname" value="'.$user->user_firstname.' '.$user->user_lastname.'" />'
                                        . '<button type="submit" class="btn btn-primary center-block" name="selectuser">Select</button></form>';
                                echo('<tr');
                                if($counter % 2 == 0){echo(' bgcolor="#FFFF9E"');}
                                echo('><td>'.$user->user_firstname.' '.$user->user_lastname.'</td><td>'. RegistrantTypes::toString($user->user_reg_type).'</td><td>'.$buttoncode.'</td></tr>');
                                $counter++;
                            }
                            if(count($search_result) == 0){
                                echo '<tr><td colspan="3">No matching users found. Please search with new terms. '; 
                            }
                            echo 'Is your contact missing and/or not registered with '.SITE_SHORTNAME.'? You can manually add them instead. Only manually add a contact if you\'re sure they don\'t have an account!</td></tr>';
                        ?>
                    </table>
                    <hr />
                    <b>Manually Add Contact</b>
                    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="manualcontact" name="manualcontact">
                        <div class="form-group has-feedback">
                            <div class="form-group has-feedback">
                                <label class="control-label" for="fname">First Name</label>
                                <input type="text" name="fname" class="form-control" id="fname" placeholder="Michael" autocomplete="off">                
                            </div>
                            <div class="form-group has-feedback">
                                <label class="control-label" for="lname">Last Name</label>
                                <input type="text" name="lname" class="form-control" id="lname" placeholder="Phelps" autocomplete="off">                
                            </div>
                            <div class="form-group has-feedback">
                                <label class="control-label" for="relationtype">Relation Type</label>
                                <select class="form-control" name="relationtype" id="relationtype">
                                    <?php 
                                        $fullClass = new ReflectionClass('RelationshipTypes');
                                        $allRelationTypes = $fullClass->getConstants ();                            
                                        foreach ($allRelationTypes as $key => $value) {
                                            echo('<option value="'.$value.'">'.$key.'</option>');
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group has-feedback">
                                <label class="control-label" for="cellphone">Cell Phone Number</label>
                                <input type="text" name="cellphone" class="form-control" id="cellphone" placeholder="123-456-7890" autocomplete="off">                
                            </div>
                         </div>
                         <button type="submit" class="btn btn-primary center-block" name="manualcontact">Add Manually</button>
                       <br />
                    </form>
                <?php endif;                 
                if($show_user_selected): ?>
                <hr />
                <em>Please review the selected contact and make sure this is the person you wish to make your Emergency Contact.</em>
                <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="addemercontact" name="addemercontact">
                   <div class="form-group has-feedback">
                        <label class="control-label" for="contactname">Contact Name</label>
                        <input type="text" name="contactname" class="form-control" id="relationto" value="<?php echo $_POST['usersname'] . ' (From Search)' ?>" disabled autocomplete="off">                
                        <input type="hidden" name="contact_id" value="<?php echo $_POST['userid']; ?>" />
                    </div>
                    <button type="submit" class="btn btn-primary center-block" name="addemercontact">Confirm - Make Contact</button>
                  <br />
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function () {
    $('#addemercontact').validate({
        submitHandler: function(form) {
            if(confirm("Are you sure you would like to make this your emergency contact?")){
                form.submit();
            }
        }
    });
    $('#usersearch').validate({
        rules: {
            searchname: {
                required: true,
                minlength: 2
            }
        },
        messages:{
            searchname: "Please enter a search criteria"
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    $('#manualcontact').validate({
        rules: {
            cellphone: {
                required: true,
                phoneUS: true
            },
            fname: {
                required: true,
                minlength: 2
            },
            lname: {
                required: true,
                minlength: 2
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
unset($_POST);//clear post (can't refresh and resubmit)