<?php

class login_info{
    public $account_found_valid = 0;
    public $account_id = 0;
    public $email_address = "";
    public $account_approved = 0;
    public $forcePwChange = 0;
    public $emailVerified = 0;
    public $profileComplete = 0;//bool
    
    public $first_name = "";
    public $last_name = "";
    public $preferred_first_name = "";
    public $registrant_type = 0;
    public $dob = null;
    
    //called after the other vars are set to determine if the profile is complete
    function isProfileComplete(){
        if(empty($this->first_name) || empty($this->last_name) || empty($this->dob)|| $this->dob == '0000-00-00'){
            $this->profileComplete = false;
        }
        else{
            $this->profileComplete = true;
        }        
    }
}