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
            
            $login_admin->can_approve_accounts = $admin_holding['can_approve_accounts'];
            $login_admin->can_view_profiles = $admin_holding['can_view_profiles'];
            $login_admin->can_view_roster = $admin_holding['can_view_roster'];
            
        }
        else{
            $login_admin->is_admin = 0;
        }
        return $login_admin;
    }
    
    function searchStatusCode($search){
        require_once(CLASSES_DIR.'search_statuscode.php');
        $return = new search_statuscode();
        $sql1 = "SELECT C.* "
                . "FROM ".TABLE_CODES." C "
                . "WHERE C.id = '".db_input($search)."';";
        if(db_num_rows(db_query($sql1))){
            $holding = db_fetch_array(db_query($sql1));
            $return->code_id = $holding['id'];
            $return->code_is_error = $holding['isError'];
            $return->code_message = $holding['message'];
        }
        return $return;
    }
    
    function getAccountsPendingApproval(){
        require_once(CLASSES_DIR.'approve_accounts.php');
        $return = null;
        $sql0 = "SELECT S.id "
                . "FROM ".TABLE_SEASONS." S "
                . "WHERE S.is_active = 1";
        $sid = db_fetch_row(db_query($sql0))[0];
        $sql1 = "SELECT UD.first_name, UD.last_name, U.email, U.id, UP.registration_type "
                . "FROM ".TABLE_USERDETAILS." UD "
                . "JOIN ".TABLE_USERS." U on UD.user_id = U.id "
                . "JOIN ".TABLE_PROFILE." UP on UP.user_id = U.id "
                . "WHERE UD.account_approved = 0 "
                . "AND UP.season_id = ".$sid.";";
        if(db_num_rows(db_query($sql1))){
            $return = array();
            $query = db_query($sql1);
            while($row = db_fetch_array($query)){
                $newReturn = new approve_accounts();
                $newReturn->user_id = $row['id'];
                $newReturn->first_name = $row['first_name'];
                $newReturn->last_name = $row['last_name'];
                $newReturn->email = $row['email'];
                $newReturn->reg_type = $row['registration_type'];
                array_push($return, $newReturn);
            }
        }
        return $return;
    }
    
    function doApproveAccount($user_id){
        $sql1 = "UPDATE ".TABLE_USERDETAILS." "
                . "SET account_approved = 1 "
                . "WHERE user_id = ". db_input($user_id).";";
        return db_query($sql1);
    }
    
    function doSearchUsers($first_name, $last_name){
        require_once(CLASSES_DIR.'login_info.php');
        $return = array();
        $first_name = '%'.$first_name.'%';
        $last_name = '%'.$last_name.'%';
        $sql1 = "SELECT UD.*, U.email "
                . "FROM ".TABLE_USERDETAILS." UD "
                . "JOIN ".TABLE_USERS." U on UD.user_id = U.id "
                . "WHERE UD.first_name LIKE ".db_input($first_name)." "
                . "AND UD.last_name LIKE ". db_input($last_name).";";
        if(db_num_rows(db_query($sql1))){
            $query = db_query($sql1);
            while($row = db_fetch_array($query)){
                $newReturn = new login_info();                
                $newReturn->account_id = $row['user_id'];
                $newReturn->first_name = $row['first_name'];
                $newReturn->last_name = $row['last_name'];
                $newReturn->email = $row['email'];
                array_push($return, $newReturn);
            }
        }
        return $return;
    }
    
    function getUserProfile($user_id){
        require_once(CLASSES_DIR.'login_info.php');
        require_once(CLASSES_DIR.'season_profile.php');
        $return = array();
        $sql1 = "SELECT UD.first_name, UD.last_name, U.email, UD.account_approved, UD.dob "
                . "FROM ".TABLE_USERDETAILS." UD "
                . "JOIN ".TABLE_USERS." U on UD.user_id = U.id "
                . "WHERE U.id = ".db_input($user_id).";";
        if(db_num_rows(db_query($sql1))){
            $holding1 = db_fetch_array(db_query($sql1));
            $return1 = new login_info();
            $return1->first_name = $holding1['first_name'];
            $return1->last_name = $holding1['last_name'];
            $return1->email_address = $holding1['email'];
            $return1->account_approved = $holding1['account_approved'];
            $return1->dob = $holding1['dob'];
            array_push($return, $return1);
        }
        
        $sql2 = "SELECT UP.* "
                . "FROM ".TABLE_PROFILE." UP "
                . "JOIN ".TABLE_SEASONS." S on S.id = UP.season_id "
                . "WHERE UP.user_id = ".db_input($user_id)." "
                . "AND S.is_active = 1;";
            $holding2 = db_fetch_array(db_query($sql2));
            $return2 = new season_profile();
            if(db_num_rows(db_query($sql1))){
                $return2->id = $holding2['user_id'];
                $return2->registration_type = $holding2['registration_type'];
                $return2->preferred_first_name = $holding2['preferred_first_name'];
                $return2->profile_started = $holding2['profile_started'];
                $return2->behavior_contract = $holding2['behavior_contract'];
                $return2->cell_phone = $holding2['cell_phone'];
                $return2->gender = $holding2['gender'];
                $return2->shirt_size = $holding2['shirt_size'];
                $return2->address_1 = $holding2['address_1'];
                $return2->address_2 = $holding2['address_2'];
                $return2->address_city = $holding2['address_city'];
                $return2->address_state = $holding2['address_state'];
                $return2->address_zip = $holding2['address_zip'];
            }
            array_push($return, $return2);            
        return $return;
    }
    
    function getSeasonRoster(){
        require_once(CLASSES_DIR.'users_roster.php');
        $return = array();
        $sql1 = "SELECT u.id, ud.account_approved, ud.first_name, ud.last_name, up.preferred_first_name, up.registration_type "
                . "FROM ".TABLE_USERS." u "
                . "JOIN ".TABLE_USERDETAILS." ud ON u.id = ud.user_id "
                . "LEFT JOIN ".TABLE_PROFILE." up ON up.user_id = u.id "
                . "LEFT JOIN ".TABLE_SEASONS." s ON s.id = up.season_id AND s.is_active = 1 " 
                . "WHERE ud.last_name IS NOT NULL "
                . "ORDER BY up.registration_type DESC, ud.first_name DESC, u.id;";
        $query = db_query($sql1);
        while($row = db_fetch_array($query)){
            $return1 = new users_roster();
            $return1->user_id = $row['id'];
            $return1->account_approved = $row['account_approved'];
            $return1->first_name = $row['first_name'];
            $return1->last_name = $row['last_name'];
            $return1->preferred_first_name = $row['preferred_first_name'];
            $return1->user_reg_type = $row['registration_type'];
            array_push($return, $return1);
        }        
        return $return;
    }
}