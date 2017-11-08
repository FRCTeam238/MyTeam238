<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(FALSE, FALSE, TRUE);//lock it down

$Data = new Data;
if($_POST){
    $gen = md5(rand());
    $key = substr($gen,strlen($gen) - 10,10);
    $requester_name = $Data->getUsersNameFromEmail($_SESSION['_user']['email']);
    $insert = $Data->insertInvitation($_POST['to_email'], Format::sanitizeName($_POST['to_fullname'], TRUE), $_SESSION['_user']['id'], $requester_name, $key);
    if($insert){ //$insert is the ID of the inserted row
        $Email = new Email;
        //For params: 0 is invite id, 1 is invite key, 2 is users name, 3 is requester
        $email = $Email->sendEmail($_POST['to_email'], 'reginvite', [$insert, $key, Format::sanitizeName($_POST['to_fullname']), $requester_name]);
        if($email){
            $_SESSION['statusCode'] =  1021;
            $Data->doLogEmailSent(1021, $_SESSION['_user']['id'], Format::currentDateTime());
            $Data->doLog(1021, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Sent Invitation');
        }
        else{
            $_SESSION['statusCode'] =  1022;
            $Data->doLog(1022, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Send Invitation');
        }
    }
    else{
        $_SESSION['statusCode'] =  1022;
        $Data->doLog(1022, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Send Invitation');
    }
}
else{
    //get referral info to show on bottom of page
    $referrals = $Data->referredUsers($_SESSION['_user']['id']);
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Invitations');
?>

Sending an invitation helps gets your friends and colleagues involved with <?php echo SITE_SHORTNAME ?>. Someone who opens their 
account from one of your invitations has an expedited registration process, saving them time. Additionally, it will give you credit 
for the referral, visible below.<br /><br />

<div class="row">
    <div class="col-md-6 col-md-push-3">
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="sendinvite" name="sendinvite">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Send Invite</h3>
          </div>
          <div class="panel-body">
            <div class="form-group has-feedback">
                <label class="control-label" for="to_fullname">Invitee Full Name</label>
                <input type="text" name="to_fullname" class="form-control" id="fname" placeholder="Michael Phelps" autocomplete="off">                
            </div>
            <div class="form-group has-feedback">
                <label class="control-label" for="to_email">Invitee Email</label>
                <input type="text" name="to_email" class="form-control" id="lname" placeholder="me@example.com" autocomplete="off">                
            </div>        
            <button type="submit" class="btn btn-primary center-block" name="sendinvite">Send Invite</button>              
          </div>
          <br />
        </div>
    </form>
    </div>
</div>
<hr />
<div class="row">
    <div class="col-md-6 col-md-push-3">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">Your Referrals <span class="badge"><?php if(isset($referrals)){echo count($referrals);}else{echo 0;} ?></span></h3>
            </div>
            <div class="panel-body">
                <?php
                    if(isset($referrals)){
                        echo("<ol>");
                        foreach ($referrals as $key => $value){
                            echo("<li type=\"square\">" . $value[0] . " <em>(" . date("F Y", strtotime($value[1])) . ")</em></li>");
                            
                            
                            //print_r($key);print_r($value);exit;
                        }
                        echo("</ol>");
                    }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#sendinvite').validate({
        rules: {
            to_fullname: {
                required: true
            },
            to_email: {
                required: true,
                email: true
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