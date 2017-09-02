<?php

class season_profile_adult {
    public $employer = "";
    public $profession = "";
    public $season_profile_adult_complete = 0;
    
    function isProfileComplete(){
        if(!empty($this->employer) && !empty($this->profession)){
            $this->season_profile_adult_complete = 1;
            return 1;
        }
        else{
            $this->season_profile_adult_complete = 0;
            return 0;
        }
    }
}