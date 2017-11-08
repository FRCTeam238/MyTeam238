<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(FALSE);//lock it down

if(!$_SESSION['_user']['detail_complete']){
    $_SESSION['statusCode'] =  1016;
    session_write_close();
    header("Location: details");
}
$Data = new Data();
$current_season = $Data->getCurrentlyActiveSeason();
require_once(CLASSES_DIR.'index_status.php');
require_once(CLASSES_DIR.'registrant_types.php');
$index_status = $Data->getIndexStatus($_SESSION['_user']['id'], $_SESSION['current_season_id'], $_SESSION['reg_type']);

$BuildPage = new BuildPage();
$BuildPage->printHeader('Home');
?>
Your <?php echo SITE_SHORTNAME ?> account is the center of your digital relationship with <?php echo SITE_FULLNAME ?>. Each season, you'll be asked to update and enter 
account information in addition to some features and functionality that is available throughout the season. In addition to the options listed below, some user data and 
configurations can be manipulated using the menu on the upper right, accessed by clicking on your name.
<br /><br />

<?php if(!$index_status->account_approved && $index_status->join_season): ?>
<div class="alert alert-danger" role="alert">
    <img src="<?php echo SITE_URL ?>images/warning.png" width="50" height="50" salt="announcement" /><b>Heads Up:</b>
    You've completed as many steps as you can do without your account being processed.<br />A staff member will process these pending 
    account soon, and then you'll be able to proceed.
</div>
<?php endif; ?>

<div class="col-md-12">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h2 class="panel-title"><?php echo $current_season->season_year ?> Season (<?php echo $current_season->season_name ?>)</h2>
        </div>
        <div class="panel-body">
          <div class="progress">
              <div class="progress-bar
                   <?php 
                   if($index_status->percent_complete <= 20){
                       echo 'progress-bar-danger ';
                   }
                   else if($index_status->percent_complete > 20 && $index_status->percent_complete < 100){
                       echo 'progress-bar-warning ';
                   }
                   else{ //100%
                       echo 'progress-bar-success ';
                   }
                   ?>
                   progress-bar-striped active" role="progressbar" style="width: <?php echo $index_status->percent_complete ?>%;min-width: 2em;">
                  <?php echo $index_status->percent_complete ?>%
              </div>
          </div>
            <table class="table table-striped table-hover">
                <tr>
                    <th>Required Element</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <tr>
                    <td>Join Season</td>
                    <td><?php if($index_status->join_season){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Joined';}
                                else{echo '<span class="glyphicon glyphicon-record" style="color:red;" aria-hidden="true"></span> To-Do';} ?></td>
                    <td><?php if(!$index_status->join_season){echo '<a href="joinseason" class="btn btn-warning center-block" role="button">Begin</a>';}
                                else{echo 'Joined As '.RegistrantTypes::toString($_SESSION['reg_type']);} ?></td>
                </tr>
                <?php if(!$index_status->join_season || ($index_status->join_season && $_SESSION['reg_type'] != RegistrantTypes::Alumni)): ?>
                <tr>
                    <td>Behavior Contract</td>
                    <td><?php if($index_status->behavior_contract){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Signed';}
                                else{echo '<span class="glyphicon glyphicon-record" style="color:red;" aria-hidden="true"></span> To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season || !$index_status->account_approved){
                            echo 'Not Available Yet';
                        }
                        else{
                            if(!$index_status->behavior_contract){
                                echo '<a href="behavior" class="btn btn-warning center-block" role="button">Begin</a>';
                            }
                            else{
                                echo '<a href="behavior" class="btn btn-default center-block" role="button">Review</a>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Season Profile</td>
                    <td><?php if($index_status->season_profile){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Completed';}
                                else{echo '<span class="glyphicon glyphicon-record" style="color:red;" aria-hidden="true"></span> To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season || !$index_status->account_approved){
                            echo 'Not Available Yet';
                        }
                        else{
                            if(!$index_status->season_profile){
                                echo '<a href="profile" class="btn btn-warning center-block" role="button">Begin</a>';
                            }
                            else{
                                echo '<a href="profile" class="btn btn-default center-block" role="button">Review / Edit</a>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php if(!$index_status->join_season || ($index_status->join_season && $_SESSION['reg_type'] != RegistrantTypes::Alumni)): ?>
                <tr>
                    <td>Emergency Contact</td>
                    <td><?php if($index_status->emergency_contact){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Selected';}
                                else{echo '<span class="glyphicon glyphicon-record" style="color:red;" aria-hidden="true"></span> To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season || !$index_status->account_approved){
                            echo 'Not Available Yet';
                        }
                        else{
                            if(!$index_status->emergency_contact){
                                echo '<a href="emergency" class="btn btn-warning center-block" role="button">Begin</a>';
                            }
                            else{
                                echo '<a href="emergency" class="btn btn-default center-block" role="button">View or Edit</a>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><?php if($_SESSION['reg_type'] > 0){echo RegistrantTypes::toString($_SESSION['reg_type']);} ?> Specifics</td>
                    <td><?php if($index_status->registrant_specific){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Completed';}
                                else{echo '<span class="glyphicon glyphicon-record" style="color:red;" aria-hidden="true"></span> To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season || !$index_status->account_approved){
                            echo 'Not Available Yet';
                        }
                        else{
                            if($_SESSION['reg_type'] > 0){
                                $page_url_name = "";
                                $button_text = "";
                                $button_type = "warning";
                                if($_SESSION['reg_type'] == RegistrantTypes::Student){
                                    $page_url_name = "profilestudent";
                                }
                                else if($_SESSION['reg_type'] == RegistrantTypes::Mentor || $_SESSION['reg_type'] == RegistrantTypes::Parent){
                                    $page_url_name = "profileadult";
                                }
                                else if($_SESSION['reg_type'] == RegistrantTypes::Alumni){
                                    $page_url_name = "profilealum";
                                }
                                if(!$index_status->registrant_specific){
                                    $button_text = "Begin";
                                }
                                else{
                                    $button_text = "Review";
                                    $button_type = "default";
                                }
                                
                                echo '<a href="'.$page_url_name.'" class="btn btn-'.$button_type.' center-block" role="button">'.$button_text.'</a>';
                            }
                            else{
                                echo 'Not Available Yet';
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <em>Once all elements have reached their minimum level of completion, and the progress bar shows 100%, you'll be ready to participate in the <?php echo $current_season->season_year ?> season!</em>
        </div>
    </div>
</div>
<br /><br />
<div class="col-md-12">
    <div class="col-md-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">Team Activities and Product Orders</h3>
            </div>
            <div class="panel-body">
                Actions Coming Soon
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">User Engagement</h3>
            </div>
            <div class="panel-body">
                <?php if($index_status->account_approved): ?>
                <a href="relationships" class="btn btn-default center-block" role="button">Manage Relationships</a>
                <em>Link Your Child or Parent, required for some site activities</em>
                <hr />
                <a href="invites" class="btn btn-default center-block" role="button">Invite Friends to <?php echo SITE_SHORTNAME ?></a>
                <em>Invite your friends to register and join the team, and collect referral credits</em>
                <?php else: ?>
                Not Available Yet
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?php
$BuildPage->printFooter();