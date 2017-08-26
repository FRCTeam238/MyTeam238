<?php
/*********************************************************************
    data.class.php

    Description: Generates and controls data access

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class Data {
    
    function doLog($status_code, $user_id, $url, $message){
        $sql1 = "INSERT INTO `".TABLE_LOG."`(`status_id`, `user_id`, `url`, `message`) "
                . "VALUES (".$status_code.",".$user_id.",'".$url."','".$message."');";
        return db_query($sql1);
    }
    
    function doLogEmailSent($status_code, $user_id, $sent_when, $user_profile_id = NULL){
        $sql1 = "INSERT INTO `".TABLE_LOG_EMAIL."`(`status_id`, `user_id`, `sent`, `user_profile_id`) "
                . "VALUES (".$status_code.",".$user_id.",'".$sent_when."',".$user_profile_id.");";
        if($user_profile_id == NULL){
            $sql1 = substr($sql1, 0, -2);
            $sql1 .= "NULL);";
        }
        return db_query($sql1);
    }
    
    static function doGetUserProfilePicPath($user_id){
        $prof_pic_path = SITE_URL.'images/profile/';
        $sql1 = "SELECT UD.profile_pic_key FROM ".TABLE_USERDETAILS." UD "
                . "WHERE UD.user_id = ".db_input($user_id)." "
                . "AND UD.profile_pic_key IS NOT NULL "
                . "AND UD.is_deleted = 0;";
        if(db_num_rows(db_query($sql1))){
            $prof_pic_path .= db_fetch_row(db_query($sql1))[0] . '.jpg';
        }
        else{
            $prof_pic_path .= 'default.jpg';
        }
        return $prof_pic_path;
    }
    
    function doSaveUserProfilePicKey($user_id, $pic_key){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "SET UD.profile_pic_key = ". db_input($pic_key)." "
                    . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function doCreateAccount($email, $password, $emailKey){
        //User creating a new account
        $now = Format::currentDateTime();
        $sql1 = "SELECT * FROM ".TABLE_USERS." U WHERE U.email = ".db_input($email)."";
        if(db_num_rows(db_query($sql1))){//email in use
            $_SESSION['statusCode'] =  1001;
            return 0;
        }
        $sql2 = "START TRANSACTION;";
        $result2 = db_query($sql2);
        $sql3 = "INSERT INTO `".TABLE_USERS."`(email, password) VALUES (".db_input($email).", ".db_input($password).");";
        $result3 = db_query($sql3);
        $sql4 = "SELECT LAST_INSERT_ID();";
        $result4 = db_query($sql4);
        $insert_id = db_fetch_row($result4)[0];
        $sql5 = "INSERT INTO `".TABLE_USERDETAILS."` (user_id, emailKey, createdOn, registrationIP) VALUES (".$insert_id.", '".$emailKey."', '".$now."','".$_SERVER['REMOTE_ADDR']."');";
        $result5 = db_query($sql5);
        $sql6 = "COMMIT;";
        $result6 = db_query($sql6);
        $sql7 = "ROLLBACK;";
        
        //return an array of [result, user_id]
        if($result2 && $result3 && $result4 && $result5 && $result6){ //query passes
            return [1, $insert_id];
        }
        else{
            db_query($sql7);
            return [0, 0];
        }
    }
    
    function doActivateAccount($user_id, $emailKey){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.ID = ".db_input($user_id)." "
                . "AND UD.emailKey = ". db_input($emailKey)." "
                . "and UD.emailVerified = 0;";
        if(db_num_rows(db_query($sql1))){
            //mark activated
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "SET emailVerified = 1 "
                    . "WHERE UD.user_id = ". db_input($user_id);
            return db_query($sql2);
        }
        else{
            return 0;
        }
    }
    
    function doApproveAccount($user_id){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                . "SET account_approved = 1 "
                . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function allowNewRegistrations(){
        $sql1 = "SELECT * FROM ".TABLE_CONFIG." S "
                . "WHERE S.allow_new_accounts = 1 "
                . "LIMIT 1;";
        return db_num_rows(db_query($sql1));
    }
    
    function doAccountLogin($email, $password){
        require(CLASSES_DIR.'login_info.php');
        $login_info = new login_info();        
        $sql1 = "SELECT U.*, UD.* FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($email)." "
                . "AND U.password = ". db_input($password)." "
                . "AND UD.is_deleted = 0;";
        if(db_num_rows(db_query($sql1))){
            //account exists
            $login_holding = db_fetch_array(db_query($sql1));
            $login_info->account_found_valid = 1;
            $login_info->email_address = $login_holding['email'];
            $login_info->account_id = $login_holding['user_id'];
            $login_info->emailVerified = $login_holding['emailVerified'];
            $login_info->first_name = $login_holding['first_name'];
            $login_info->last_name = $login_holding['last_name'];
            $login_info->account_approved = $login_holding['account_approved'];
            $login_info->dob = $login_holding['dob'];
            $login_info->forcePwChange = $login_holding['forcePwChange'];
            $login_info->isProfileComplete();
            
            //Now we want some basic info from their season profile, if they've started it
            $login_info->preferred_first_name = "";//defaults, only change if they have a profile
            $login_info->registrant_type = 0;
            if($this->userHasProfileInActiveSeason($login_holding['user_id'])){
                
            }
        }
        else{
            $login_info->account_found_valid = 0;            
        }
        return $login_info;
    }
    
    static function getUsersNameFromEmail($email){
        $name = '';
        $sql = "SELECT `first_name`, `last_name`"
                 . " FROM ".TABLE_USERDETAILS." UD "
                 . " JOIN ".TABLE_USERS." U ON UD.user_id = U.id "
                 . " WHERE U.email = ".db_input($email);
        $row = db_fetch_row(db_query($sql));
        $name = "";
        if(!empty($row[0])){
            $name .= $row[0];
                if(!empty($row[1])){
                    $name .= " " . $row[1];            
                }
        }
        return $name;
    }
    
    function doAllowAccountPwReset($user_email, $resetKey, $expirationDateTimeUnix){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($user_email)." "
                . "AND UD.is_deleted = 0;";
        if(!db_num_rows(db_query($sql1))){
            //user not found
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD "
                    . "JOIN ".TABLE_USERS." U ON UD.user_id = U.id "
                    . "SET pwResetKey = '".$resetKey."', "
                    . "pwResetExpiration = FROM_UNIXTIME(".$expirationDateTimeUnix.") "
                    . "WHERE U.email = ". db_input($user_email);
            return db_query($sql2);
        }
    }
    
    function doUpdateUserPassword($user_email, $resetKey, $newPassword){
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UD.user_id = U.id "
                . "WHERE U.email = ".db_input($user_email)." "
                . "AND UD.is_deleted = 0 "
                . "AND UD.pwResetKey = ".db_input($resetKey)." "
                . "AND UD.pwResetExpiration >= NOW();";
        if(!db_num_rows(db_query($sql1))){
            //user not found, resetkey expired or wrong
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERDETAILS." UD, ".TABLE_USERS." U "
                    . "SET U.password = ". db_input($newPassword).", "
                    . "UD.pwResetKey = NULL, "
                    . "UD.pwResetExpiration = NULL "
                    . "WHERE U.email = ". db_input($user_email);
            return db_query($sql2);
        }
    }
    
    function doUpdateUserPassword_ViaCurrent($user_id, $user_currentpassword, $user_newpassword){//print_r($_POST);exit;
        //should only do update if current password was correct
        $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "WHERE U.id = ".$user_id." "
                . "AND U.password = ". db_input($user_currentpassword).";";
        if(!db_num_rows(db_query($sql1))){
            //user not found, or password wrong
            return 0;
        }
        else{
            $sql2 = "UPDATE ".TABLE_USERS." U "
                    . "SET U.password = ". db_input($user_newpassword)." "
                    . "WHERE U.id = ".$user_id.";";
            return db_query($sql2);
        }
    }
    
    function doUpdateUserDetails($user_id, $fname, $lname, $dob){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." UD "
                . "SET UD.first_name = ". db_input($fname).", "
                . "UD.last_name = ". db_input($lname).", "
                . "UD.dob = ". db_input($dob)." "
                . "WHERE UD.user_id = ". db_input($user_id);
        return db_query($sql1);
    }
    
    function acceptInvitation($invite_id, $invite_key){
        $sql1 = "SELECT * FROM ".TABLE_INVITES." I "
                . "WHERE I.id = ". db_input($invite_id)." "
                . "AND I.key = ". db_input($invite_key)." "
                . "AND I.used = 0;";
        $row = db_fetch_row(db_query($sql1));
        $create = $this->doCreateAccount($row[1], "temppass", "invited");
        if($create){
            //Now get the id we just inserted
            $sql2 = "SELECT U.id FROM ".TABLE_USERS." U "
                . "WHERE U.email = ". db_input($row[1]).";";
            $new_id = db_fetch_row(db_query($sql2))[0];
            $activate = $this->doActivateAccount($new_id, "invited");
            if($activate){
                $approved = $this->doApproveAccount($new_id); 
                $pwreset = $this->doAllowAccountPwReset($row[1], "invitedacct", (time() + 900));//you get 15 mins to set password
                if($approved && $pwreset){
                    //flag the invite as used
                    $sql2 = "UPDATE ".TABLE_INVITES." I "
                    . "SET used = 1 "
                    . "WHERE I.id = ". db_input($invite_id);
                    db_query($sql2);
                    //regardless, if we make it here, count as all good and return the email address
                    return $row[1];
                }
                else{
                    return 0;
                }
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }
    
    function insertInvitation($to_email, $to_name, $requester_id, $requester_name, $key){
        $sql1 = "INSERT INTO `".TABLE_INVITES."`(`email_invited`, `name_invited`, `requester`, `requester_user_id`, `key`) "
                . "VALUES (".db_input($to_email).",".db_input($to_name).",".db_input($requester_name).",".db_input($requester_id).",".db_input($key).")";
        db_query($sql1);
        return db_fetch_row(db_query("SELECT LAST_INSERT_ID();"))[0];
    }
    
    function referredUsers($referred_by_id){
        $sql1 = "SELECT * FROM ".TABLE_INVITES." I "
                . "WHERE I.requester_user_id = ". db_input($referred_by_id)." "
                . "AND I.used = 1;";
        $query = db_query($sql1);
        $referred_users = array();
        if(db_num_rows($query)){//at least 1 event
            while($row = db_fetch_array($query)){
                array_push($referred_users, array($row['name_invited'], $row['server_stamp']));
            }
        }
        return $referred_users;
    }
    
    function doSignBehaviorContract($user_id, $email, $password, $fname, $lname){
        $verify = $this->doAccountLogin($email, $password);
        if($verify->account_found_valid){
            //at this point, the password was valid
            $fullname = $this->getUsersNameFromEmail($email);
            if(strtolower($fullname) == strtolower($fname . " " . $lname)){
                $sql1 = "UPDATE ".TABLE_PROFILE." UP "
                . "SET UP.behavior_contract = 1 "
                . "WHERE UP.user_id = ". db_input($user_id);
                return db_query($sql1);
            }
        }        
        return 0;
    }
    
    function getCurrentlyActiveSeason(){
        $sql1 = "SELECT * FROM ".TABLE_SEASONS." s "
                . "WHERE s.is_active = 1 "
                . "ORDER BY year DESC "
                . "LIMIT 1;";
        require(CLASSES_DIR.'season_info.php');
        $season_info = new season_info();
        if(db_num_rows(db_query($sql1))){
            //season exists
            $season_hold = db_fetch_row(db_query($sql1));
            $season_info->season_id = $season_hold[0];
            $season_info->season_year = $season_hold[1];
            $season_info->season_name = $season_hold[2];
            $season_info->season_active = $season_hold[3];
        }
        return $season_info;
    }
    
    function userHasProfileInActiveSeason($user_id){
        $sql1 = "SELECT * FROM ".TABLE_PROFILE." p "
                . "WHERE p.user_id = ". db_input($user_id)." "
                . "AND p.season_id = ("
                    . "SELECT s.id FROM ".TABLE_SEASONS." s "
                    . "WHERE s.is_active = 1 "
                    . "ORDER BY s.year DESC "
                    . "LIMIT 1"
                . ")";
        return db_num_rows(db_query($sql1));
    }
    
    function joinSeason($user_id, $season_id, $pref_name, $seasonrole){
        require_once(CLASSES_DIR.'registrant_types.php');
        $seasonrole = strtoupper($seasonrole);
        if(!$this->userHasProfileInActiveSeason($user_id) && $seasonrole != "Sponsor"){
            $sql1 = "INSERT INTO ".TABLE_PROFILE." (`season_id`, `user_id`, `registration_type`, `preferred_first_name`, `profile_started`) "
                . "VALUES (". db_input($season_id).", ". db_input($user_id).", ". db_input($seasonrole).",". db_input($pref_name).", '".Format::currentDateTime()."')";
            if(db_query($sql1)){
                $sql2 = "SELECT LAST_INSERT_ID();";
                $result2 = db_query($sql2);
                $insert_id = db_fetch_row($result2)[0];                
                if($seasonrole == RegistrantTypes::Mentor || $seasonrole == RegistrantTypes::Parent){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_STUDENT." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                else if($seasonrole == RegistrantTypes::Student){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_MENTORPARENT." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                else if($seasonrole == RegistrantTypes::Alumni){
                    $sql2 = "INSERT INTO ".TABLE_PROFILE_ALUMNI." (`user_profile_id`) "
                            . "VALUES (".$insert_id.")";
                }
                return db_query($sql2);
            }
        }
        else{
            return 0;
        }
    }
    
    function doUpdateEmailAddress($user_id, $email, $password, $new_email, $newverifykey){
        $verify = $this->doAccountLogin($email, $password);
        if($verify->account_found_valid){
            $sql1 = "UPDATE ".TABLE_USERS." u "
                    . "SET u.email = ".db_input($new_email)." "
                    . "WHERE u.email = ".db_input($email)." "
                    . "AND u.id = ".db_input($user_id).";";
            $result1 = db_query($sql1);
            $sql2 = "UPDATE ".TABLE_USERDETAILS." ud "
                    . "SET ud.emailVerified = 0, "
                    . "ud.emailKey = ".db_input($newverifykey)." "
                    . "WHERE ud.user_id = ".db_input($user_id).";";
            $result2 = db_query($sql2);
            if($result1 && $result2){
                return 1;
            }
        }
        return 0;
    }
    
    function doDeactivateAccount($user_id, $email, $password){
        $verify = $this->doAccountLogin($email, $password);
        if($verify->account_found_valid){
            $sql1 = "UPDATE ".TABLE_USERDETAILS." ud "
                    . "SET ud.is_deleted = 1, forcePwChange = 1 "
                    . "WHERE ud.user_id = ".db_input($user_id)." "
                    . "LIMIT 1;";
            return db_query($sql1);
        }
        return 0;
    }
    
    /*
    function getPendingInvites(){

            $now = Format::currentDateTime();
            $sql = "SELECT t.createdBy, e.eventName, s.sportName, s.sportGender, ti.key, ti.invitationTime, et.tentNumAssignmentInProgress, e.event_id, t.tent_id "
                     . "FROM ".TABLE_EVENTS." e, ".TABLE_TENTS." t, ".TABLE_SPORTS." s, ".TABLE_TENTS_INVITATIONS." ti, ".TABLE_EVENTS_TIME." et "
                     . "WHERE e.event_id = t.event_id "
                     . "AND t.tent_id = ti.tent_id "
                     . "AND e.sport_id = s.sport_id "
                     . "AND et.event_id = e.event_id "
                     . "AND t.tentOrder = 0 "//tent does not have number assigned
                     . "AND ti.isActive = 1 "//invitation has not been rejected by creator
                     . "AND et.tentingEnd > '".$now."' "//cant accept invite if tenting is over
                     . "AND ti.KC_ID = ".db_input($_SESSION['_user']['KC_ID']).";";

            $query = db_query($sql);
            $resultArray = array();
            $html = '';
            if(db_num_rows($query)){//at least 1 event
                    while($row = db_fetch_array($query)){
                            $createdBy = $row['createdBy'];
                            $eventName = $row['eventName'];
                            $sport = $row['sportName'];
                            $sportGender = $row['sportGender'];
                            $invitationTime = $row['invitationTime'];
                            $invitationKey = $row['key'];
                            $distroInProgress = $row['tentNumAssignmentInProgress'];
                            $eventId = $row['event_id'];
                            $tentId = $row['tent_id'];
                            if($sportGender == 'M'){$sportGender = "Men's";}else{$sportGender = "Women's";}
                            $title = $sportGender . ' ' . $sport . ' :: ' . $eventName;
                            array_push($resultArray,array("title"=>$title, "createdBy"=>$createdBy, "key"=>$invitationKey, "invitationTime" =>$invitationTime, "distroInProgress"=>$distroInProgress, "eventId"=>$eventId, "tentId"=>$tentId));
                    }

            //run through each result array. if in a tent for that event, ignore the other invitations	
                    foreach($resultArray as $key => $value){
                            $sql = "SELECT tg.tent_id "
                                     . "FROM ".TABLE_TENTS_GROUPS." tg, ".TABLE_TENTS." t "
                                     . "WHERE tg.tent_id = t.tent_id "
                                     . "AND t.event_id = '".$value['eventId']."' "
                                     . "AND tg.KC_ID = ".db_input($_SESSION['_user']['KC_ID']).";";
                            if(db_num_rows(db_query($sql))){
                                    unset($resultArray[$key]);
                            }
                    }
                    //format a response
                    if(count($resultArray)){
                            $html = '<hr /><h3>Pending Invitations</h3>
                            The following invitations are outstanding. Please click the button to accept an invitation.<br />&nbsp;
                            <table width="875" class="clean center">
                            <tr><th width="350">Event</th><th width="175">Invited By</th><th width="175">Invited On</th><th width="175">Details</th></tr>
                            ';

                            $rowNum = 0;
                            foreach($resultArray as $key => $value){
                                    $html .= '<tr';
                                    if($rowNum % 2 == 0){$html .= ' class="alt"';}
                                    $html .= '><td>'.$value['title'].'</td><td>'.Format::getUsersName($value['createdBy']).'</td><td>'.Format::displayDateTimeFromDB($value['invitationTime']).'</td><td>';
                                    if(!$value['distroInProgress']){$html .= '<a href="'.SITE_URL.'invitation.php?k='.$value['key'].'"><input type="button" id="submit" value="&nbsp;&nbsp;&nbsp;Accept Invitation" style="width: 170px;" /></a>';}else{$html .= '<span class="error"><i>You cannot accept this invitation right now. Tent order distribution is in progress.</i></span>';}
                                    $html .= '</td></tr>';
                                    $rowNum++;
                            }
                            $html .= '</table>';
                    }	
            }		

            return $html;
    }

    function getUpcomingEvents(){

            $now = Format::currentDateTime();

            $sql = "SELECT e.event_id, e.eventName, s.sportName, s.sportGender, et.eventStart, et.tentingStart "
                     . "FROM ".TABLE_EVENTS." e, ".TABLE_EVENTS_TIME." et, ".TABLE_SPORTS." s "
                     . "WHERE et.event_id = e.event_id "
                     . "AND e.sport_id = s.sport_id "
                     . "AND et.showOnlineStart < '".$now."' "
                     . "AND et.eventStart >= '".$now."' "
                     . "ORDER BY et.eventStart DESC;";

            $query = db_query($sql);
            $resultArray = array();
            if(db_num_rows($query)){//at least 1 event
                    while($row = db_fetch_array($query)){
                            $eventID = $row['event_id'];
                            $eventName = $row['eventName'];
                            $sport = $row['sportName'];
                            $sportGender = $row['sportGender'];
                            $eventStart = $row['eventStart'];
                            $eventTentStart = $row['tentingStart'];
                            if($sportGender == 'M'){$sportGender = "Men's";}else{$sportGender = "Women's";}
                            $title = $sportGender . ' ' . $sport . ' :: ' . $eventName;
                            $resultArray[$eventID] = array("title"=>$title, "tentStart"=>$eventTentStart, "eventStart"=>$eventStart);
                    }
            }
            $html = '<hr /><h3>Upcoming Tenting Events</h3>';
            if(!count($resultArray)){
                    $html .= '<span class="textCenter error">No Upcoming Events</span>';
                    }
                    else{
                            $html .= '
                            The following events are coming up soon. Click on the details button for full information or to tent for the event, if you are not already.<br />&nbsp;
                            <table width="875" class="clean center">
                            <tr><th width="350">Event Title</th><th width="175">Event Begins</th><th width="175">Tenting Begins</th><th width="175">Details</th></tr>
                            ';

                            $rowNum = 0;
                            foreach($resultArray as $key => $value){
                                    $html .= '<tr';
                                    if($rowNum % 2 == 0){$html .= ' class="alt"';}
                                    $html .= '><td>'.$value['title'].'</td><td>'.Format::displayDateTimeFromDB($value['eventStart']).'</td><td>'.Format::displayDateTimeFromDB($value['tentStart']).'</td><td><a href="'.SITE_URL.'event.php?e='.$key.'"><input type="button" id="forward" value="View Details" style="width: 170px;" /></a></td></tr>';
                                    $rowNum++;
                            }

                            $html .= '</table>';
                    }
            $html .= '<br />';

            return $html;
    }
     * 
     */
}