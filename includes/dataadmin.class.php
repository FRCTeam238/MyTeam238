<?php
/*********************************************************************
    dataadmin.class.php

    Description: Generates and controls data access (admin specific access)

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class DataAdmin extends Data {
    
    function doAdminLogin($user_id){
        require_once(CLASSES_DIR.'login_admin.php');
        $login_admin = new login_admin();
        $sql1 = "SELECT AR.* "
                . "FROM ".TABLE_ADMIN_ROLES." AR "
                . "WHERE AR.user_id = ".db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            //account exists
            $admin_holding = db_fetch_array(db_query($sql1));
            $login_admin->account_id = $user_id;
            $login_admin->is_admin = 1;
            
            /*
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
            */
        }
        else{
            $login_admin->is_admin = 0;
        }
        return $login_admin;
    }
}