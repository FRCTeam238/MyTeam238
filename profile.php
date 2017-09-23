<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin(TRUE, TRUE);//lock it down
$readonly = false;
$Data = new Data;

if($_POST){//incoming profile info attempt
    $realphone = preg_replace("/[^0-9]/","",$_POST['cellphone']);
    if(empty($_POST['address2'])){ $_POST['address2'] = NULL; }
    if(!empty($_POST['address1']) && !empty($_POST['city']) && !empty($_POST['zip'])){
        $result = $Data->doUpdateSeasonProfile($_SESSION['_user']['id'], $_SESSION['current_season_id'], $realphone, 
                                                $_POST['gender'], $_POST['shirt'], trim($_POST['bio']), strtoupper($_POST['address1']), strtoupper($_POST['address2']), strtoupper($_POST['city']),
                                                $_POST['state'], $_POST['zip']);
        if($result){
            $_SESSION['statusCode'] =  1024;
            $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Season Profile Updated');
            $_SESSION['season_profile_complete'] = true;
            session_write_close();
            header("Location: index");
        }
        else{
            $_SESSION['statusCode'] =  1025;
            $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Season Profile');
        }
    }    
}

$season_profile = $Data->getCurrentSeasonProfile($_SESSION['_user']['id'], $_SESSION['current_season_id']);
if($season_profile->isProfileComplete()){
    $editMode = true;
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Season Profile');
?>

Each season you have the opportunity to enter certain identifying information, which may have changed from the previous season. In a later step, you'll be 
asked to complete additional profile information specific to your registration type.<br /><br />
<?php if($season_profile->isProfileComplete()): ?>
    <div class="alert alert-success" role="alert">You've already submitted this information. The contents are editible below, if necessary.</div><br />
<?php endif; ?>
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
                    <input type="text" name="address1" class="form-control" id="address1" placeholder="123 Spring Street" autocomplete="off"
                        <?php if($editMode){echo ' value="'.$season_profile->address_1.'"';} ?>
                    >                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="address2">Address (Line 2)</label>
                    <input type="text" name="address2" class="form-control" id="address2" autocomplete="off"
                        <?php if($editMode){echo ' value="'.$season_profile->address_2.'"';}else{echo ' placeholder="Unit 123"';} ?>
                    >                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="city">City</label>
                    <input type="text" name="city" class="form-control" id="city" placeholder="Manchester" autocomplete="off"
                        <?php if($editMode){echo ' value="'.$season_profile->address_city.'"';} ?>
                    >                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="state2">State</label>
                    <input type="text" name="state2" class="form-control" id="state2" value="NH" disabled="disabled">
                    <input type="hidden" name="state" class="form-control" id="state"
                        <?php if($editMode){echo ' value="'.$season_profile->address_state.'"';} ?>
                    >
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="zip">Zip</label>
                    <input type="number" name="zip" class="form-control" id="zip" placeholder="03101" autocomplete="off"
                        <?php if($editMode){echo ' value="'.$season_profile->address_zip.'"';} ?>
                    >                
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group has-feedback">
                    <label class="control-label" for="cellphone">Cell Phone Number</label>
                    <input type="text" name="cellphone" class="form-control" id="cellphone" placeholder="123-456-7890" autocomplete="off"
                        <?php if($editMode){echo ' value="'.Format::phoneNumberDisplay($season_profile->cell_phone).'"';} ?>
                    >                
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="gender">Gender</label><br />
                    <select class="form-control" name="gender" id="gender"
                        <?php if($editMode){echo ' ';} ?>
                    >
                        <option value="M" <?php if($season_profile->gender == "M"){echo ' selected';} ?>>Male</option>
                        <option value="F" <?php if($season_profile->gender == "F"){echo ' selected';} ?>>Female</option>
                        <option value="O" <?php if($season_profile->gender == "O"){echo ' selected';} ?>>Other</option>
                    </select>
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="shirt">Shirt Size</label><br />
                    <span id="shirtwarning"><em>This shirt size (2XL/3XL) may incur additional fees per product ordered or provided.</em></span>
                    <select class="form-control" name="shirt" id="shirt" onchange="ChangeSize()"
                        <?php if($editMode){echo ' ';} ?>
                    >
                        <option value="S" <?php if($season_profile->shirt_size == "S"){echo ' selected';} ?>>Small</option>
                        <option value="M" <?php if($season_profile->shirt_size == "M"){echo ' selected';} ?>>Medium</option>
                        <option value="L" <?php if($season_profile->shirt_size == "L"){echo ' selected';} if(!$readonly){echo ' selected';} ?>>Large</option>
                        <option value="XL" <?php if($season_profile->shirt_size == "XL"){echo ' selected';} ?>>X-Large</option>
                        <option value="2XL" <?php if($season_profile->shirt_size == "2XL"){echo ' selected';} ?>>2X-Large</option>
                        <option value="3XL" <?php if($season_profile->shirt_size == "3XL"){echo ' selected';} ?>>3X-Large</option>
                    </select>
                </div>
                <div class="form-group has-feedback">
                    <label class="control-label" for="bio">Biography</label><br /><em>Your biography is used on the &quot;Yearbook&quot; page.
                        <span id="bio_morebutton"><a href="#" onclick="showbiomore()">(more)</a></span> <span id="bio_more">It shows alongside your profile picture, to help 
                                        others in the organization know who you are, and learn about you. This biography will not be publicly visible.</span></em>
                    <textarea name="bio" class="form-control" id="bio" lines="4" onkeyup="countChar(this)" maxlength="500"><?php if($editMode){echo $season_profile->biography;}else{echo '';} ?></textarea><br />
                    <span style="font-size:11px;"><span id="charNum">500</span> <em>characters remaining</em></span>
                </div>
            </div>
            <div class="col-md-12"><br />
                <button type="submit" class="btn btn-primary center-block" name="seasonprofile">Update Profile</button>
            </div>
        </form>
        </div>
        <?php if($editMode): ?>
        <a href="index" class="btn btn-danger center-block" style="max-width:200px;">Don't Edit - Return Home</a><br />
        <?php endif; ?>
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
    $("#shirtwarning" ).hide("fast");
    
    if(document.getElementById("shirt").value === "S" || document.getElementById("shirt").value === "M" || 
        document.getElementById("shirt").value === "L" || document.getElementById("shirt").value === "XL"){
    $( "#shirtwarning" ).hide("medium");
    }
    else{
        $( "#shirtwarning" ).show("medium");
    }
}
$(document).ready(function () {
    jQuery.validator.addMethod('phoneUS', function(phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, ''); 
        return this.optional(element) || phone_number.length > 9 &&
            phone_number.match(/^(1-?)?(\([2-9]\d{2}\)|[2-9]\d{2})-?[2-9]\d{2}-?\d{4}$/);
    }, 'Please enter a valid phone number.');
    
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