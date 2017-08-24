<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

if($_POST){//incoming sign attempt
    $Data = new Data;
    if($_POST['fname'] && $_POST['lname'] && $_POST['currentpassword']){
        $result = $Data->doSignBehaviorContract($_SESSION['_user']['id'], $_SESSION['_user']['email'], $_POST['currentpassword'], $_POST['fname'], $_POST['lname']);
        if($result){
            $_SESSION['statusCode'] =  1024;
            $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Signed Behavior Contract');
            session_write_close();
            header("Location: index");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Sign Behavior Contract');
        }
    }    
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Behavior Contract');
?>

<div class="alert alert-warning" role="alert">Please carefully review the following document and apply your digital signature if you agree to its contents.</div>

In order to maintain the best possible experience for all students and mentors of <?php echo SITE_SHORTNAME ?> we maintain a series of minimum 
standards and requirements that we expect everyone affiliated with the team to maintain to.
<br /><br />
Every member of <?php echo SITE_FULLNAME ?> has a duty and responsibility to represent himself/herself, the team and the school in the best manner possible. 
Because of the high visibility of all members in the school, when accepting a position on the team, every member must agree to abide by a higher 
standard of character, values and behavior than an average student. This applies to your behavior both in school and out. You are expected to avoid 
situations where you might be accused of wrong-doing. Being in the “wrong place at the wrong time” is not an excuse if you chose to be there in the first 
place.
<br /><br />
The following violations will result in dismissal/suspension from the team:
<br />
<ul>
    <li>Using illegal drugs or alcohol at any time</li>
    <li>Using tobacco (if under age 18) at any time</li>
    <li>Using tobacco while wearing a team shirt at any time</li>
    <li>Allowing yourself to be in a situation, in school or away from school, where you are accused of/arrested for an illegal activity</li>
    <li>Skipping classes or school</li>
    <li>Harassment (verbal/physical/sexual/etc…) of another student or team member, including via social media (cellular devices, Facebook, Twitter, etc…)</li>
    <li>Any act (either in school or away from school) in which the coaching staff or administration believes the member's conduct is detrimental to the team</li>
    <li>Missing 3 practices/meetings (without prior approval from the coaching staff)</li>
</ul>
<br />
The following violations will result in suspension from competition:
<br />
<ul>
    <li>Missing 2 practices (without prior approval from coaching staff)</li>
    <li>Tardiness to practice 3 or more times (without approval from coaching staff)</li>
    <li>Failure to bring necessary equipment to practice/meets</li>
</ul>
<br />
<?php echo SITE_SHORTNAME ?> follows the school's policy on academics and school attendance (the following policy is from September 2016, please contact Team Coaches 
for questions regarding any recent updates):
<br />
<em>Students shall maintain a minimum GPA of 2.5 (Simple/Unweighted). In the event that a GPA falls below 2.5, the student shall be removed from the team for a minimum 
    of one marking period and until his/her GPA reaches the 2.5 standard set for all students. If a student receives an "F", that student shall forfeit the team participation 
    for the remainder of the school year. Any student found guilty of plagiarism/cheating shall be removed from the team for the remainder of the year. Students who incur their 
    third unexcused tardy from/to school or class will be deemed ineligible for the next scheduled practice, contest, or activity." Upon third unexcused absence- "Suspension from 
    participation for (5) consecutive school days, no weekend play." Second offense - 10 consecutive days. Third offense - dismissal from all activities, clubs, or athletic teams 
    for the remainder of the school year.</em>
<br /><br />
By applying your digital signature below, you affirm that you have read this and fully understand the rules set forth on this Contract. You are also stating that you understand 
that violations of this contract could result in your being dismissed from the team or being suspended from competition at Coach discretion.
<br /><br />

<div class="col-md-6 col-md-push-3">
<form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="behaviorcontract" name="behaviorcontract">
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title">Sign Document</h3>
      </div>
      <div class="panel-body">
        <div class="form-group has-feedback">
            <label class="control-label" for="fname">Legal First Name</label>
            <input type="text" name="fname" class="form-control" id="fname" placeholder="Michael" autocomplete="off">                
        </div>
        <div class="form-group has-feedback">
            <label class="control-label" for="lname">Legal Last Name</label>
            <input type="text" name="lname" class="form-control" id="lname" placeholder="Phelps" autocomplete="off">                
        </div>
        <div class="form-group has-feedback">
            <label class="control-label" for="currentpassword">Current Password</label><br />
            <input type="password" class="form-control" name="currentpassword">
        </div>
        <button type="submit" class="btn btn-primary center-block" name="accountdetail">Sign</button>              
      </div>
      <br />
    </div>
</form>
</div>

<script>
$(document).ready(function () {
    $('#behaviorcontract').validate({
        rules: {
            fname: {
                required: true
            },
            lname: {
                required: true
            },
            currentpassword: {
                required: true
            }
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            element.closest('.form-group').removeClass('has-error').addClass('has-success');
        },
        submitHandler: function(form) {
            document.behaviorcontract.currentpassword.value = window.btoa(document.behaviorcontract.currentpassword.value);
            form.submit();
        }
    });
});
</script>

<?php
$BuildPage->printFooter();