<?php
/*********************************************************************
    dataread.class.php

    Description: Generates and controls data access

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
        $result = db_fetch_row(db_query($sql1));
        $settings = new site_settings();
        $settings->allow_new_accounts = $result[0];
        $settings->announcement = $result[2];
        $settings->site_email_enabled = $result[1];
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
        require(CLASSES_DIR.'season_profile.php');
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
        }
        else{
            return 0;         
        }
        return $season_profile;
    }
    
    function addRelationship_userSearch($season_id, $search_term){
        require(CLASSES_DIR.'relationship_search_user.php');
        //$user_search = new season_profile();
        
        if(filter_var($search_term, FILTER_VALIDATE_EMAIL)) { //use email
            $sql1 = "SELECT * FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON U.ID = UD.user_id "
                . "WHERE U.email = ".db_input($search_term).";";
        }
        else { //use last name
            $sql1 = "SELECT U.* FROM ".TABLE_USERS." U "
                . "JOIN ".TABLE_USERDETAILS." UD "
                . "ON U.ID = UD.user_id "
                . "WHERE UD.last_name LIKE ".db_input($search_term).";";
        }
        $returnValue = array();
        $query = db_query($sql1);
        if(db_num_rows($query)){
            while($row = db_fetch_array($query)){
                $searchuser = new relationship_search_user;
                $searchuser->user_id = $row['id'];
                $searchuser->user_firstname = $row['first_name'];
                $searchuser->user_lastname = $row['last_name'];
                
                //Check for Reg Type
                $sql2 = "SELECT UP.registration_type FROM ".TABLE_PROFILE." UP "
                . "WHERE UP.season_id = ".db_input($season_id)." "
                . "AND UP.user_id = ". db_input($row['id']).";";                
                $searchuser->user_reg_type = db_fetch_array(db_query($sql2));
                if(empty($searchuser->user_reg_type)){$searchuser->user_reg_type = 0;}
                array_push($returnValue, $searchuser);
            }
        }
        return $returnValue;
    }
    
    function checkIfRelationshipExists($from_id, $to_id, $require_accepted = FALSE){
        $sql1 = "SELECT * FROM ".TABLE_RELATIONSHIPS." UR "
                . "WHERE UR.user_id_from = ".db_input($from_id)." "
                . "AND UR.user_id_to = ". db_input($to_id);
                if($require_accepted){$sql .= "AND UR.accepted = 1;";}
        $sql1 .= ';';
        return db_num_rows(db_query($sql1));
    }
}