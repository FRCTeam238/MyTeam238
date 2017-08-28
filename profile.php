<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE, TRUE);//lock it down

if($_POST){//incoming profile info attempt
    $Data = new Data;
    $realphone = preg_replace("/[^0-9]/","",$_POST['cellphone']);
    if(empty($_POST['address2'])){ $_POST['address2'] = NULL; }
    if(!empty($_POST['address1']) && !empty($_POST['city']) && !empty($_POST['zip'])){
        $result = $Data->doUpdateSeasonProfile($_SESSION['_user']['id'], $_SESSION['current_season_id'], $realphone, 
                                                $_POST['gender'], $_POST['shirt'], $_POST['bio'], $_POST['address1'], $_POST['address2'], $_POST['city'], $_POST['state'], $_POST['zip']);
        if($result){
            $_SESSION['statusCode'] =  1024;
            $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Season Profile Updated');            
            session_write_close();
            header("Location: index");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Season Profile');
        }
    }    
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Season Profile');
?>

Each season you have the opportunity to enter certain identifying information, which may have changed from the previous season. In a later step, you'll be 
asked to complete additional profile information specific to your registration type.
<br /><br />

<div class="col-md-12">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Season Profile</h3>
        </div>
        <div class="panel-body">
        <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="seasonprofile" name="seasonprofile">
            <div class="col-md-6">      
                <div class="form-group has-feedback">
                    <label class="control-label" for="address1">Address (Line 1)</label>
                    <input type="text" name="address1" class="form-control" id="address1" placeholder="123 Spring Street" autocomplete="off">                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="address2">Address (Line 2)</label>
                    <input type="text" name="address2" class="form-control" id="address2" placeholder="Unit 123" autocomplete="off">                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="city">City</label>
                    <input type="text" name="city" class="form-control" id="city" value="Manchester" autocomplete="off">                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="state2">State</label>
                    <input type="text" name="state2" class="form-control" id="state2" value="NH" disabled="disabled">
                    <input type="hidden" name="state" class="form-control" id="state" value="NH">
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="zip">Zip</label>
                    <input type="number" name="zip" class="form-control" id="zip" placeholder="03101" autocomplete="off">                
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group has-feedback">
                    <label class="control-label" for="cellphone">Cell Phone Number</label>
                    <input type="text" name="cellphone" class="form-control" id="cellphone" placeholder="123-456-7890" autocomplete="off">                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="gender">Gender</label><br />
                    <select class="form-control" name="gender" id="gender">
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                        <option value="O">Other</option>
                    </select>
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="shirt">Shirt Size</label><br />
                    <span id="shirtwarning"><em>This shirt size (2XL+) may incur additional fees per product ordered or provided.</em></span>
                    <select class="form-control" name="shirt" id="shirt" onchange="ChangeSize()">
                        <option value="S">Small</option>
                        <option value="M">Medium</option>
                        <option value="L" selected>Large</option>
                        <option value="XL">X-Large</option>
                        <option value="2XL">2X-Large</option>
                        <option value="3XL">3X-Large</option>
                    </select>
                </div>        
                <div class="form-group has-feedback">
                    <label class="control-label" for="bio">Biography</label><br /><em>Your biography is used on the &quot;Yearbook&quot; page.
                        <span id="bio_morebutton"><a href="#" onclick="showbiomore()">(more)</a></span> <span id="bio_more">It shows alongside your profile picture, to help 
                                        others in the organization know who you are, and learn about you. This biography will not be publicly visible.</span></em>
                    <textarea name="bio" class="form-control" id="bio" lines="4" onkeyup="countChar(this)" maxlength="500"></textarea><br />
                    <span style="font-size:11px;"><span id="charNum">500</span> <em>characters remaining</em></span>
                </div>
            </div>
            <div class="col-md-12"><br />
                <button type="submit" class="btn btn-primary center-block" name="seasonprofile">Update Profile</button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
function countChar(val) {
  var len = val.value.length;
  if (len >= 500) {
    val.value = val.value.substring(0, 500);
  } else {
    $('#charNum').text(500 - len);
  }
};
function showbiomore(){
    $("#bio_more" ).show("fast");
    $("#bio_morebutton").hide();
}
function ChangeSize() {
    if(document.getElementById("shirt").value == "2XL" || document.getElementById("shirt").value == "3XL"){
        $( "#shirtwarning" ).show("medium");
    }
    else{
        $( "#shirtwarning" ).hide("medium");
    }
}
$(document).ready(function () {
    $( "#shirtwarning" ).hide();
    $( "#bio_more" ).hide();
    $('#seasonprofile').validate({
        rules: {
            cellphone: {
                required: true,
                phoneUS: true
            },
            address1: {
                required: true,
                minlength: 8
            },
            city: {
                required: true,
                minlength: 3
            },
            zip: {
                required: true,
                number: true,
                minlength: 5,
                maxlength: 5
            },
            bio: {
                maxlength: 500
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