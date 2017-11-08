<?php

class season_profile{
    public $id = 0;
    public $registration_type = 0;
    public $preferred_first_name = "";
    public $profile_started = "2017-1-1 00:00:00";
    public $behavior_contract = 0;
    public $cell_phone = "0000000000";
    public $gender = "M";
    public $shirt_size = "L";
    public $address_1 = "";
    public $address_2 = "";
    public $address_city = "";
    public $address_state = "";
    public $address_zip = 00000;
    public $emergency_contact_id = 0;
    public $emergency_contact_user_id = 0;
    public $biography = "";
    public $season_profile_complete = 0;
    public $account_approved = 0;
    
    function isProfileComplete(){
        if(!empty($this->registration_type) && !empty($this->cell_phone) && !empty($this->address_1)){
            $this->season_profile_complete = 1;
            return 1;
        }
        else{
            $this->season_profile_complete = 0;
            return 0;
        }
    }
}