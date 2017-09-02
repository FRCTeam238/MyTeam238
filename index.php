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
$index_status = $Data->getIndexStatus($_SESSION['_user']['id'], $_SESSION['current_season_id']);

$BuildPage = new BuildPage();
$BuildPage->printHeader('Home');
?>
Your <?php echo SITE_SHORTNAME ?> account is the center of your digital relationship with <?php echo SITE_FULLNAME ?>. Each season, you'll be asked to update and enter 
account information in addition to some features and functionality that is available throughout the season. In addition to the options listed below, some user data and 
configurations can be manipulated using the menu on the upper right, accessed by clicking on your name.
<br /><br />
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
                    <td><?php if($index_status->join_season){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Joined';}else{echo 'To-Do';} ?></td>
                    <td><?php if(!$index_status->join_season){echo '<a href="joinseason" class="btn btn-warning center-block" role="button">Begin</a>';}else{echo '---';} ?></td>
                </tr>
                <tr>
                    <td>Behavior Contract</td>
                    <td><?php if($index_status->behavior_contract){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Signed';}else{echo 'To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season){
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
                <tr>
                    <td>Season Profile</td>
                    <td><?php if($index_status->season_profile){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Completed';}else{echo 'To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season){
                            echo 'Not Available Yet';
                        }
                        else{
                            if(!$index_status->season_profile){
                                echo '<a href="profile" class="btn btn-warning center-block" role="button">Begin</a>';
                            }
                            else{
                                echo '<a href="profile" class="btn btn-default center-block" role="button">Review</a>';
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Emergency Contact</td>
                    <td><?php if($index_status->emergency_contact){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Selected';}else{echo 'To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season){
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
                <tr>
                    <td>Registrant Type Specifics</td>
                    <td><?php if($index_status->registrant_specific){echo '<span class="glyphicon glyphicon-ok" style="color:green;" aria-hidden="true"></span> Completed';}else{echo 'To-Do';} ?></td>
                    <td>
                        <?php
                        if(!$index_status->join_season){
                            echo 'Not Available Yet';
                        }
                        else{
                            if(!$index_status->registrant_specific){
                                echo '<a href="#" class="btn btn-warning center-block" role="button">Begin</a>';
                            }
                            else{
                                echo '<a href="#" class="btn btn-default center-block" role="button">Review</a>';
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
                <a href="relationships" class="btn btn-default center-block" role="button">Add Relationships</a>
                <em>Link Your Child or Parent, required for some site activities</em>
                <hr />
                <a href="invites" class="btn btn-default center-block" role="button">Invite Friends to <?php echo SITE_SHORTNAME ?></a>
                <em>Invite your friends to register and join the team, and collect referral credits</em>
            </div>
        </div>
    </div>
</div>



<?php
$BuildPage->printFooter();