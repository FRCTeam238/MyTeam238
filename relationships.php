<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE);//lock it down

require_once(CLASSES_DIR.'relationship_types.php');
require_once(CLASSES_DIR.'registrant_types.php');
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
else if($_POST && isset($_POST['addrelationship'])){//a user to relate to, and type, have been submitted
    if(!$Data->checkIfRelationshipExists($_SESSION['_user']['id'], $_POST['relationto_id'], FALSE)){
        if($Data->doAddRelationship($_SESSION['_user']['id'], $_POST['relationtype'], $_POST['relationto_id'])){
            $_SESSION['statusCode'] =  1033;
            $Data->doLog(1033, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Relationship Added');
            //try to send the heads up email (to the other party)
            $to = $Data->getUsersEmailFromId($_POST['relationto_id']);
            $to_name = $Data->getUsersNameFromEmail($to);
            $Email = new Email;
            $currentname = $_SESSION['_user']['firstname'] . " " . $_SESSION['_user']['lastname'];
            $sendresult = $Email->sendEmail($to, 'relationship', [$to_name, $currentname]);
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Add Relationship');
        }
    }
    else{ //relation already existed
        $_SESSION['statusCode'] =  1034;
        $Data->doLog(1034, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Add Relationship, Already Existed');
    }
}
else if($_POST && isset($_POST['confirmrelation'])){
    if($Data->doConfirmRelationship($_POST['user_id_from'], $_POST['user_id_to'])){
        $_SESSION['statusCode'] =  1035;
        $Data->doLog(1035, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Relationship Confirmed');
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Confirm Relationship');
    }
}
else if($_POST && isset($_POST['deleterelation'])){
    if($Data->doDeleteRelationship($_POST['user_id_from'], $_POST['user_id_to'])){
        $_SESSION['statusCode'] =  1035;
        $Data->doLog(1035, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Relationship Deleted');
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Delete Relationship');
    }
}

$all_relations = $Data->getRelationships($_SESSION['_user']['id'], FALSE);

$BuildPage = new BuildPage();
$BuildPage->printHeader('Relationships');
?>

Please use the relationship control options to link your <?php echo SITE_SHORTNAME ?> account with that of other users, like your child or parent. This will allow 
the appropriate permissions for the type of relationship established. All relationships must be verified by the linked party before being confirmed- for example, a Parent 
would need to select &QUOT;accept&QUOT; when a student makes a request to mark the relationship to their parent's account.
<br /><br />
<div class="col-md-12">
    <div class="col-md-5">
        <div class="panel panel-success">
            <div class="panel-heading">
              <h3 class="panel-title">Existing Relationships</h3>
            </div>
            <div class="panel-body">
                <table class="table">
                <?php
                $count = 1;
                foreach ($all_relations as $relation) {
                    $buttoncodedelete = '<form action="'.strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))).'" method="POST" id="deleterelation" name="deleterelation">'
                                    . '<input type="hidden" name="user_id_to" value="'.$relation->to_user_id.'" /><input type="hidden" name="user_id_from" value="'.$relation->from_user_id.'" />'
                                    . '<button type="submit" class="btn btn-danger center-block" name="deleterelation">Delete Relation</button></form>';
                    $buttoncodeconfirm = '<form action="'.strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))).'" method="POST" id="confirmrelation" name="confirmrelation">'
                                    . '<input type="hidden" name="user_id_to" value="'.$relation->to_user_id.'" /><input type="hidden" name="user_id_from" value="'.$relation->from_user_id.'" />'
                                    . '<button type="submit" class="btn btn-success center-block" name="confirmrelation">Confirm Relation</button></form>';
                    if($count > 1){echo('<hr />');}
                    echo('<tr');
                    if($count % 2 == 0){echo(' bgcolor="#FFFF9E"');}
                    echo('>');
                    echo('<td>Relationship From <b>'.$relation->from_first_name.' '.$relation->from_last_name.'</b><br />To <b>'.$relation->to_first_name.' '.$relation->to_last_name.'</b>: ' 
                            .RelationshipTypes::toString($relation->relation_type).'<br /><br />');
                    if($relation->accepted){
                        echo $buttoncodedelete;
                        
                    }
                    elseif($relation->from_user_id != $_SESSION['_user']['id']){ //don't show confirm button if you added the relation
                        echo $buttoncodeconfirm;
                        echo $buttoncodedelete;
                    }
                    else{
                        echo '<em>Pending Acceptance By Other User</em>';
                    }
                    echo('</td></tr>');
                    $count++;
                }
                if(count($all_relations) == 0){
                    echo 'No Relationships Found. You can add one to the right.';
                }
                ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Add Relationship</h3>
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
                                echo '<tr><td colspan="3">No matching users found. Please search with new terms.</td></tr>';
                            }
                        ?>
                    </table>
                <?php endif;                 
                if($show_user_selected): ?>
                <hr />
                <em>Pick a relationship type from the dropdown options, then select &quot;Add Relationship&quot; to initiate the request.</em>
                <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="addrelationship" name="addrelationship">
                   <div class="form-group has-feedback">
                        <label class="control-label" for="relationto">Relationship From</label>
                        <input type="text" name="relationto" class="form-control" id="relationto" value="<?php echo $_POST['usersname'] . ' (From Search)' ?>" disabled autocomplete="off">                
                        <input type="hidden" name="relationto_id" value="<?php echo $_POST['userid']; ?>" />
                    </div>
                    <div class="form-group has-feedback">
                        <label class="control-label" for="relationtype">Relation Type</label>
                        <select class="form-control" name="relationtype" id="relationtype" onchange="TypeChanged()">
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
                        <b>Relationship To</b>
                        <input type="text" name="text" class="form-control" id="relationfrom" value="<?php echo $_SESSION['_user']['firstname'] . ' ' . $_SESSION['_user']['lastname'] . ' (You)' ?>" disabled="disabled" autocomplete="off">                
                    </div>
                    <b>Verify that the following statement is accurate: </b><br />
                    <?php echo $_POST['usersname'] . ' is my ' ?><span id="relationtypetext"></span>.<br /><br />
                    <button type="submit" class="btn btn-primary center-block" name="addrelationship">Accurate - Add Relationship</button>
                  <br />
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<br /><br />
<a href="index" class="btn btn-warning " role="button">Go to My Account</a><br />
<script>

function TypeChanged(){
    $('#relationtypetext').text($("#relationtype option:selected").text());
}

$(document).ready(function () {
    $('#relationtypetext').text($("#relationtype option:selected").text());
    
    $('#usersearch').validate({
        rules: {
            searchname: {
                required: true,
                minlength: 2
            }
        },
        messages:{
            searchname: "You must insert a search term"
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