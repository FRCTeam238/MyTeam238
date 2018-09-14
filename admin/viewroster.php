<?php
require_once '../main.inc.php';
require_once CLASSES_DIR.'users_roster.php';
require_once CLASSES_DIR.'registrant_types.php';

$Security = new Secure;
$Security->requireAdminLogin("can_view_roster");//lock it down ADMIN
$BuildPage = new BuildPage();
$BuildPage->printHeader('Admin View Season Roster');
$Data = new DataAdmin();
$allusers = $Data->getSeasonRoster($_SESSION['current_season_id']);
?>
Review the full roster for the currently active season, including, where applicable,
the role the user has applied to. This is a read-only copy of the data.<br /><br />

<div class="row">
    <div class="col-md-8 col-md-push-2">
        <table class="table table-hover table-striped">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Approved</th>
            </tr>
            <?php
            foreach ($allusers as $key) {
                echo "<tr>";
                echo "<td>".$key->user_id."</td>";
                if(!empty($key->preferred_first_name)){
                    echo "<td>".$key->first_name." (".$key->preferred_first_name.") ".$key->last_name."</td>";
                }
                else{
                    echo "<td>".$key->first_name." ".$key->last_name."</td>";
                }
                
                if($key->user_reg_type != NULL){
                    echo "<td>".RegistrantTypes::toString($key->user_reg_type)."</td>";
                }
                else{
                    echo "<td><em>Unknown</em></td>";
                }
                
                if($key->account_approved){
                    echo '<td style="color:green;font-weight:bold;">Yes</td>';
                }
                else{
                    echo '<td style="color:red;">No</td>';
                }                
            }
            ?>
        </table>
    </div>
</div>

<?php
$BuildPage->printFooter();