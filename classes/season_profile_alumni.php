<?php

class season_profile_alumni {
    public $graduation_year = 0;
    public $season_profile_alumni_complete = 0;
    
    function isProfileComplete(){
        if($this->graduation_year > 0){
            $this->season_profile_alumni_complete = 1;
            return 1;
        }
        else{
            $this->season_profile_alumni_complete = 0;
            return 0;
        }
    }
}