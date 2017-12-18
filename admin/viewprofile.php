<?php
require_once '../main.inc.php';
require_once CLASSES_DIR.'season_profile.php';
require_once CLASSES_DIR.'login_info.php';
require_once CLASSES_DIR.'registrant_types.php';
$Security = new Secure;
$Security->requireAdminLogin();//lock it down ADMIN
$BuildPage = new BuildPage();
$BuildPage->printHeader('Admin View Season Profile');
$Data = new DataAdmin();

if($_POST){
    if(isset($_POST['inputFirst']) || isset($_POST['inputLast'])){
        //Doing a Search        
        $_POST['inputFirst'] = isset($_POST['inputFirst']) ? $_POST['inputFirst'] : "";
        $_POST['inputLast'] = isset($_POST['inputLast']) ? $_POST['inputLast'] : "";
        if(strlen($_POST['inputFirst']) < 3 && strlen($_POST['inputLast']) < 3){
            $error = true;
        }
        else{
            $searchResults = $Data->doSearchUsers($_POST['inputFirst'], $_POST['inputLast']);
        }        
    }
    if(isset($_POST['userid'])){
        //Get an Individual User
        $userdata = $Data->getUserProfile($_POST['userid']);
        $user_login_info = $userdata[0];
        $user_profile = $userdata[1];
    }
}
?>
Review the full profile for an individual by entering some search criteria and selecting the matching user.
You need not enter the full first or last name, just a minimum of 3 characters in an individual box. 
All matching results will be returned.<br />
<?php

if(isset($searchResults)){
    if(count($searchResults) > 0){
        echo('<hr /><table class="table table-striped table-hover"><tr><th>Name</th><th>Email</th><th>View</th></tr>');
        foreach ($searchResults as $search){
            echo('<tr><td style="vertical-align:middle;">'.$search->first_name.' '.$search->last_name.'</td><td style="vertical-align:middle;">'.$search->email.'</td>');
            echo('<td><form action="'.strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))).'" method="POST" id="viewprofile" name="viewprofile">');
            echo('<input type="hidden" name="userid" value="'.$search->account_id.'"><button type="submit" class="btn btn-success">View</button></form>');
            echo('</td></tr>');
        }
        echo('</table>');
    }
    else{
        echo('<hr /><span style="color:red;font-weight:bold;">No Matching Results!</span>');
    }
}
else if(isset($error)){
    echo ('Invalid Search Criteria!');
}

if(isset($user_login_info)):
?>
<br />
<div class="row">
    <div class="col-md-8 col-md-push-2">
        <table class="table table-striped table-hover">
            <tr><th>Item</th><th>Value</th></tr>
            <tr>
                <td>Account ID</td>
                <td><?php echo isset($user_profile->id) ? $user_profile->id : "Unavailable"; ?></td>
            </tr>
            <tr>
                <td>Name</td>
                <td><?php echo $user_login_info->first_name; ?> (<?php echo $user_profile->preferred_first_name; ?>) <?php echo $user_login_info->last_name; ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td><?php echo $user_login_info->email_address; ?></td>
            </tr>
            <tr>
                <td>Account Approved</td>
                <td>
                    <?php 
                        if($user_login_info->account_approved){
                            echo '<span style="color:green;">Yes</span>';
                        }
                        else{
                            echo '<span style="color:red;">No</span>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>
                    <?php 
                    if(isset($user_profile) && $user_profile->registration_type == RegistrantTypes::Student){
                        $age = floor((time() - strtotime($user_login_info->dob))/31556926);
                        echo date("F j, Y", strtotime($user_login_info->dob)) . ' (Age '.$age.')';
                    }
                    else{
                        echo 'Hidden';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Registration Type</td>
                <td><?php echo isset($user_profile->registration_type) ? RegistrantTypes::toString($user_profile->registration_type) : ""; ?></td>
            </tr>
            <tr>
                <td>Profile Started</td>
                <td>
                    <?php 
                        echo isset($user_profile->profile_started) ? date("F j, Y, g:i a", strtotime($user_profile->profile_started)) : "";
                    ?>
                </td>
            </tr>
            <tr>
                <td>Behavior Contract Signed</td>
                <td>
                    <?php 
                        if(isset($user_profile) && $user_profile->behavior_contract){
                            echo '<span style="color:green;">Yes</span>';
                        }
                        else{
                            echo '<span style="color:red;">No</span>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Cell Phone</td>
                <td><?php echo $user_profile->cell_phone; ?></td>
            </tr>
            <tr>
                <td>Gender</td>
                <td><?php echo $user_profile->gender; ?></td>
            </tr>
            <tr>
                <td>Shirt Size</td>
                <td><?php echo $user_profile->shirt_size; ?></td>
            </tr>
            <tr>
                <td>Address</td>
                <td><?php echo $user_profile->address_1; ?><br /><?php echo $user_profile->address_2; ?><br />
                    <?php echo $user_profile->address_city; ?>,<?php echo $user_profile->address_state; ?>&nbsp;
                    <?php echo $user_profile->address_zip; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php
endif;
?>
<hr />
New Search:<br />
<div class="row">
    <div class="col-md-6 col-md-push-3">
        <form class="form-horizontal" action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))) ?>" method="POST" name="doSearch">
          <div class="form-group">
            <label for="inputFirst" class="col-sm-3 control-label">First Name</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="inputFirst" id="inputFirst" placeholder="First" minlength="3" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <label for="inputLast" class="col-sm-3 control-label">Last Name</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="inputLast" id="inputLast" placeholder="Last" minlength="3" autocomplete="off">
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-5 col-sm-10">
              <button type="submit" class="btn btn-primary"> Search </button>
            </div>
          </div>
        </form>
    </div>
</div>

<?php
$BuildPage->printFooter();