<?php
require_once 'main.inc.php';
$Security = new Secure;
$Security->requireLogin();//lock it down

function MakeThumb($thumb_target = '', $width = 200,$height = 200,$SetFileName = false, $quality = 75)
{
    $thumb_img  =   imagecreatefromjpeg($thumb_target);
    list($w, $h) = getimagesize($thumb_target);

    if($w > $h) {
            $new_height =   $height;
            $new_width  =   floor($w * ($new_height / $h));
            $crop_x     =   ceil(($w - $h) / 2);
            $crop_y     =   0;
    }
    else {
            $new_width  =   $width;
            $new_height =   floor( $h * ( $new_width / $w ));
            $crop_x     =   0;
            $crop_y     =   ceil(($h - $w) / 2);
    }

    $tmp_img = imagecreatetruecolor($width,$height);
    imagecopyresampled($tmp_img, $thumb_img, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $w, $h);
    if($SetFileName == false) {
            header('Content-Type: image/jpeg');
            imagejpeg($tmp_img);
    }
    else{
        imagejpeg($tmp_img,$SetFileName,$quality);
    }
    imagedestroy($tmp_img);
}

$Data = new Data;
if($_POST){//incoming new photo attempt
    $target_dir = "images/profile/";    
    $uploadOk = 1;
    $imageFileType = pathinfo($_FILES["profpic"]["name"],PATHINFO_EXTENSION);
    $target_file = $target_dir . $_SESSION['_user']['id'] . "." . $imageFileType;//basename($_FILES["profpic"]["name"]);//echo($target_file);exit;
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["profpic"]["tmp_name"]);
        if($check === false) {
            $_SESSION['statusCode'] =  1029;
            $Data->doLog(1029, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Upload Prof Pic. Not an image.');
            goto endofprocess;
        }
    }
    // Check file size
    if ($_FILES["profpic"]["size"] > 100000) {
        $_SESSION['statusCode'] =  1029;
        $Data->doLog(1029, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Upload Prof Pic. Too big.');
        goto endofprocess;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png") {
        $_SESSION['statusCode'] =  1029;
        $Data->doLog(1029, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Upload Prof Pic. Wrong file type.');
        goto endofprocess;
    }
    if (!move_uploaded_file($_FILES["profpic"]["tmp_name"], $target_file)) {
        $_SESSION['statusCode'] =  1029;
        $Data->doLog(1029, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Upload Prof Pic. Unknown error.');
        goto endofprocess;
    }
    
    //At this point we have the file uploaded as the user ID. extension, for manipulation
    $gen = md5(rand());
    $img_key = substr($gen,strlen($gen) - 10,10);
    $target_file_final = $target_dir . $img_key . ".jpg";//always ends as jpg
    if($imageFileType == "png"){
        //PNG
        $image = imagecreatefrompng($target_file);
        imagejpeg($image, $target_file_final, 50);
        imagedestroy($image);
    }
    else{
        //JPG
        $jpgimage = imagecreatefromjpeg($target_file);
        imagejpeg($jpgimage, $target_file_final, 50);
        imagedestroy($jpgimage);
    }
    unlink($target_file);//delete the working file
    
   MakeThumb($target_file_final,200,200,$target_file_final);//convert to 200, the biggest size we really use
    
    //Save file name to DB
    $result = $Data->doSaveUserProfilePicKey($_SESSION['_user']['id'], $img_key);
    if($result){
        $_SESSION['statusCode'] =  1024;
        $Data->doLog(1024, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'Profile Pic Updated');
        session_write_close();
        header("Location: index");
    }
    else{
        $_SESSION['statusCode'] =  1025;
        $Data->doLog(1025, $_SESSION['_user']['id'], $_SERVER['REQUEST_URI'], 'FAILED to Update Profile Pic');
    }
    endofprocess:    
}

$BuildPage = new BuildPage();
$BuildPage->printHeader('Profile Picture');
$BuildPage->showCode();
?>

Your profile picture represents you within the <?php echo SITE_SHORTNAME ?> system. This picture can also be viewed by other users through the &quot;Yearbook&quot; feature.<br /><br />
<div>
    <div class="col-md-8">
        The picture you select must:
        <ul>
            <li>Show your face</li>
            <li>Refrain from containing any illegal activity</li>
            <li>Refrain from showing drug, tobacco, or alcohol use regardless of your age</li>
            <li>Do not upload copyrighted content, unless you have the rights to do so</li>
            <li>When possible, no other people should be visible in the photo</li>
            <li>File should be 200 x 200 pixels, or will be trimmed. A preview will be shown before uploading.</li>
            <li>File should be JPG or PNG extension, 1 MB or less</li>
        </ul>
    </div>
    <div class="col-md-4">
        <b>Current Picture:</b><br />
        <img src="<?php echo $Data->doGetUserProfilePicPath($_SESSION['_user']['id']) ?>" class="img-rounded" alt="Profile Picture" height="150" width="150">
        <br /><br />
    </div>
</div>
<hr />
<div class="col-md-6 col-md-push-3">
    <form action="<?php echo strtolower(ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME))); ?>" method="POST" id="profilepic" name="profilepic" enctype="multipart/form-data">
        <div class="panel panel-primary">
          <div class="panel-heading">
            <h3 class="panel-title">Update Profile Picture</h3>
          </div>
          <div class="panel-body">            
            <div class="form-group has-feedback">
                <input type="file" onchange="readURL(this);" class="btn btn-default" name="profpic" /><br />
                <div class="col-md-6">
                    <b id="preview_text">(Preview) Full Size</b><br />
                    <img id="preview" src="#" />
                </div>
                <div class="col-md-6">
                    <b id="preview_text2">(Preview) Small Size</b><br /><br /><br /><br />
                    <img id="preview2" src="#" class="center-block" /><br /><br /><br /><br />
                </div>                
            </div>
          </div>
            <button type="submit" class="btn btn-primary center-block" name="profilepic">Change Profile Picture</button>
          <br />
        </div>
        
    </form>
</div>

<script>
function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview')
                        .attr('src', e.target.result)
                        .width(200)
                        .height(200);
                    $('#preview2')
                        .attr('src', e.target.result)
                        .width(26)
                        .height(26);
                };
                $('#preview_text').show();
                $('#preview_text2').show();
                reader.readAsDataURL(input.files[0]);
            }
        }

$(document).ready(function () {
    $('#preview_text').hide();
    $('#preview_text2').hide();
    $('#profilepic').validate({
        rules: {
            profpic: {
                required: true
            }            
        },
        messages:{
            profpic: "Picture is Required"
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