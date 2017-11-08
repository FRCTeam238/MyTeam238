<?php
/*********************************************************************
    dataread.class.php

    Description: Generates and controls data access (read access, no write)

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class DataRead {
    
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
    
    function getSiteSettings(){
        require_once(CLASSES_DIR.'site_settings.php');
        $sql1 = "SELECT * FROM ".TABLE_CONFIG." S "
                . "LIMIT 1;";
        $result = db_fetch_array(db_query($sql1));
        $settings = new site_settings();
        $settings->allow_new_accounts = $result['allow_new_accounts'];
        $settings->new_accounts_access_code = $result['new_accounts_access_code'];
        $settings->site_email_enabled = $result['site_email_enabled'];
        $settings->announcement = $result['announcement'];
        return $settings;
    }
    
    function doAccountLogin($season_id, $email, $password){
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
            if($this->userHasProfileInActiveSeason($season_id, $login_holding['user_id'])){
                $current_profile = $this->getCurrentSeasonProfile($login_info->account_id, $season_id);
                $login_info->preferred_first_name = $current_profile->preferred_first_name;
                $login_info->registrant_type = $current_profile->registration_type;
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
    
    static function getUsersEmailFromId($user_id){
        $sql = "SELECT `email` "
                 . " FROM ".TABLE_USERS." U "
                 . " WHERE U.id = ".db_input($user_id);
        $row = db_fetch_row(db_query($sql));
        return $row[0];
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
    
    function userHasProfileInActiveSeason($season_id, $user_id){
        $sql1 = "SELECT * FROM ".TABLE_PROFILE." p "
                . "WHERE p.user_id = ". db_input($user_id)." "
                . "AND p.season_id = ". db_input($season_id).";";
        return db_num_rows(db_query($sql1));
    }
        
    function getCurrentSeasonProfile($user_id, $season_id){
        require_once(CLASSES_DIR.'season_profile.php');
        $season_profile = new season_profile();        
        $sql1 = "SELECT UP.* FROM ".TABLE_PROFILE." UP "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            $season_holding = db_fetch_array(db_query($sql1));
            $season_profile->id = $season_holding['id'];
            $season_profile->registration_type = $season_holding['registration_type'];
            $season_profile->preferred_first_name = $season_holding['preferred_first_name'];
            $season_profile->profile_started = $season_holding['profile_started'];
            $season_profile->behavior_contract = $season_holding['behavior_contract'];
            $season_profile->cell_phone = $season_holding['cell_phone'];
            $season_profile->gender = $season_holding['gender'];
            $season_profile->shirt_size = $season_holding['shirt_size'];
            $season_profile->address_1 = $season_holding['address_1'];
            $season_profile->address_2 = $season_holding['address_2'];
            $season_profile->address_city = $season_holding['address_city'];
            $season_profile->address_state = $season_holding['address_state'];
            $season_profile->address_zip = $season_holding['address_zip'];
            $season_profile->emergency_contact_id = $season_holding['emergency_contact_id'];
            $season_profile->emergency_contact_user_id = $season_holding['emergency_contact_user_id'];
            $season_profile->biography = $season_holding['biography'];
            
            $sql2 = "SELECT UD.account_approved FROM ".TABLE_USERDETAILS." UD "
                . "WHERE UD.user_id = ". db_input($user_id).";";
            if(db_num_rows(db_query($sql2))){
                $season_holding2 = db_fetch_array(db_query($sql2));
                $season_profile->account_approved = $season_holding2['account_approved'];
            }
        }
        else{
            return 0;         
        }
        return $season_profile;
    }
    
    function userSearch($season_id, $current_user_id, $search_term){
        require(CLASSES_DIR.'search_user.php');
        
        if(filter_var($search_term, FILTER_VALIDATE_EMAIL)) { //use email
            $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON U.ID = UD.user_id "
                . "WHERE U.email = ".db_input($search_term)." "
                . "AND UD.user_id != ". db_input($current_user_id)." "
                . "AND UD.is_deleted = 0;";
        }
        else { //use last name
            $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON U.ID = UD.user_id "
                . "WHERE UD.last_name LIKE ".db_input($search_term)." "
                . "AND UD.user_id != ". db_input($current_user_id)." "
                . "AND UD.is_deleted = 0;";
        }
        $returnValue = array();
        $query = db_query($sql1);
        if(db_num_rows($query)){
            while($row = db_fetch_array($query)){
                $searchuser = new search_user;
                $searchuser->user_id = $row['id'];
                $searchuser->user_firstname = $row['first_name'];
                $searchuser->user_lastname = $row['last_name'];
                
                //Check for Reg Type
                $sql2 = "SELECT UP.registration_type FROM ".TABLE_PROFILE." UP "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($row['id']).";";                
                $searchuser->user_reg_type = db_fetch_array(db_query($sql2))['registration_type'];
                if(empty($searchuser->user_reg_type)){$searchuser->user_reg_type = 0;}
                array_push($returnValue, $searchuser);
            }
        }
        return $returnValue;
    }
    
    function checkIfRelationshipExists($user_1, $user_2, $require_accepted = FALSE){ //there are two checks here, because the relation could have been entered in either "direction"
        $sql1 = "SELECT * FROM ".TABLE_RELATIONSHIPS." UR "
                . "WHERE UR.user_id_from = ".db_input($user_1)." "
                . "AND UR.user_id_to = ". db_input($user_2)." ";
                if($require_accepted){$sql .= "AND UR.accepted = 1 ";}
        $sql1 .= 'AND UR.is_deleted = 0;';
        $sql2 = "SELECT * FROM ".TABLE_RELATIONSHIPS." UR "
                . "WHERE UR.user_id_from = ".db_input($user_2)." "
                . "AND UR.user_id_to = ". db_input($user_1)." ";
                if($require_accepted){$sql .= "AND UR.accepted = 1 ";}
        $sql2 .= 'AND UR.is_deleted = 0;';
        return db_num_rows(db_query($sql1)) + db_num_rows(db_query($sql2));
    }
    
    function getRelationships($user_id, $accepted_only){
        require(CLASSES_DIR.'user_relationship.php');
        
        $sql1 = "SELECT UD1.user_id AS from_user_id, UD1.first_name AS from_firstname, UD1.last_name AS from_lastname, UD2.user_id AS to_user_id, UD2.first_name AS to_firstname, UD2.last_name AS to_lastname, "
                . "UR.* FROM ".TABLE_RELATIONSHIPS." UR "
                . "INNER JOIN ".TABLE_USERDETAILS." UD1 "
                . "ON UR.user_id_from = UD1.user_id "
                . "LEFT JOIN ".TABLE_USERDETAILS." UD2 "
                . "ON UR.user_id_to = UD2.user_id "
                . "WHERE (UR.user_id_from = ".db_input($user_id)." "
                . "OR UR.user_id_to = ". db_input($user_id).") "
                . "AND UR.is_deleted = 0 ";
            if($accepted_only){ $sql1 .= "AND UR.accepted = 1;";}
        
        $returnValue = array();
        $query = db_query($sql1);
        if(db_num_rows($query)){
            while($row = db_fetch_array($query)){
                $relation = new user_relationship();                                
                $relation->from_user_id = $row['from_user_id'];
                $relation->from_first_name = $row['from_firstname'];
                $relation->from_last_name = $row['from_lastname'];
                $relation->to_first_name = $row['to_firstname'];
                $relation->to_last_name = $row['to_lastname'];
                $relation->to_user_id = $row['to_user_id'];                
                $relation->relation_type = $row['relationship'];
                $relation->accepted = $row['accepted'];
                
                array_push($returnValue, $relation);
            }
        }
        return $returnValue;
    }
    
    function getEmergencyContact($user_id, $season_id){
        $sql1 = "SELECT UD.first_name, UD.last_name FROM ".TABLE_PROFILE." UP "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON UP.emergency_contact_user_id = UD.user_id "
                . "WHERE UP.user_id = ".db_input($user_id)." "
                . "AND UP.season_id = ". db_input($season_id).";";
        if(db_num_rows(db_query($sql1))){
            return db_fetch_row(db_query($sql1));
        }
        else{
            $sql2 = "SELECT EC.first_name, EC.last_name FROM ".TABLE_EMER_CONTACTS." EC "
                . "JOIN ".TABLE_PROFILE." UP ON EC.id = UP.emergency_contact_id "
                . "WHERE UP.user_id = ".db_input($user_id)." "
                . "AND UP.season_id = ". db_input($season_id).";";
            if(db_num_rows(db_query($sql2))){
                return db_fetch_row(db_query($sql2));
            }
        }
    }
    
    function getIndexStatus($user_id, $season_id, $reg_type){
        $returnValue = new index_status();
        if($this->userHasProfileInActiveSeason($season_id, $user_id)){
            $returnValue->join_season = 1;
            $curr_profile = $this->getCurrentSeasonProfile($user_id, $season_id);
            $curr_profile->isProfileComplete();
            $returnValue->account_approved = $curr_profile->account_approved;
            if($curr_profile->behavior_contract){
                $returnValue->behavior_contract = 1;
            }
            if($curr_profile->emergency_contact_id || $curr_profile->emergency_contact_user_id){
                $returnValue->emergency_contact = 1;
            }
            if($curr_profile->season_profile_complete){
                $returnValue->season_profile = 1;
            }
            
            if($curr_profile->registration_type == RegistrantTypes::Student){
                $reg_specific = $this->getCurrentSeasonProfile_Student($user_id, $season_id);
                if($reg_specific){
                    $returnValue->registrant_specific = $reg_specific->isProfileComplete();
                }
            }
            if($curr_profile->registration_type == RegistrantTypes::Parent || $curr_profile->registration_type == RegistrantTypes::Mentor){
                $reg_specific = $this->getCurrentSeasonProfile_Adult($user_id, $season_id);
                if($reg_specific){
                    $returnValue->registrant_specific = $reg_specific->isProfileComplete();
                }
            }
            if($curr_profile->registration_type == RegistrantTypes::Alumni){
                $reg_specific = $this->getCurrentSeasonProfile_Alumni($user_id, $season_id);
                if($reg_specific){
                    $returnValue->registrant_specific = $reg_specific->isProfileComplete();
                }
            }
        }
        
        $returnValue->percentComplete($reg_type);//update percentage
        return $returnValue;
    }
    
    function getCurrentSeasonProfile_Student($user_id, $season_id){
        require_once(CLASSES_DIR.'season_profile_student.php');
        $season_profile_student = new season_profile_student();        
        $sql1 = "SELECT UPS.* FROM ".TABLE_PROFILE_STUDENT." UPS "
                . "JOIN ".TABLE_PROFILE." UP ON UPS.user_profile_id = UP.id "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            $season_holding = db_fetch_array(db_query($sql1));
            $season_profile_student->grade_level = $season_holding['grade_level'];
            $season_profile_student->msd_school_id = $season_holding['msd_student_id'];
            $season_profile_student->permission_slip_signed = $season_holding['permission_slip_signed'];
            $season_profile_student->permission_slip_signed_who = $season_holding['permission_slip_signed_who'];
            $season_profile_student->permission_slip_signed_when = $season_holding['permission_slip_signed_when'];
        }
        else{
            return 0;
        }
        return $season_profile_student;
    }
    
    function getCurrentSeasonProfile_Adult($user_id, $season_id){
        require_once(CLASSES_DIR.'season_profile_adult.php');
        $season_profile_adult = new season_profile_adult();        
        $sql1 = "SELECT UPA.* FROM ".TABLE_PROFILE_MENTORPARENT." UPA "
                . "JOIN ".TABLE_PROFILE." UP ON UPA.user_profile_id = UP.id "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            $season_holding = db_fetch_array(db_query($sql1));
            $season_profile_adult->employer = $season_holding['employer'];
            $season_profile_adult->profession = $season_holding['profession'];
        }
        else{
            return 0;
        }
        return $season_profile_adult;
    }
    
    function getCurrentSeasonProfile_Alumni($user_id, $season_id){
        require_once(CLASSES_DIR.'season_profile_alumni.php');
        $season_profile_alumni = new season_profile_alumni();        
        $sql1 = "SELECT UPAL.* FROM ".TABLE_PROFILE_ALUMNI." UPAL "
                . "JOIN ".TABLE_PROFILE." UP ON UPAL.user_profile_id = UP.id "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            $season_holding = db_fetch_array(db_query($sql1));
            $season_profile_alumni->graduation_year = $season_holding['graduation_year'];
        }
        else{
            return 0;
        }
        return $season_profile_alumni;
    }
}