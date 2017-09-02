<?php

class season_profile_student {
    public $grade_level = 0;
    public $msd_school_id = 0;
    public $permission_slip_signed = 0;
    public $permission_slip_signed_when = NULL;
    public $permission_slip_signed_who = "";    
    public $season_profile_student_complete = 0;
    
    function isProfileComplete(){
        if($this->grade_level > 0 && $this->msd_school_id > 0){ //doesn't require permission slip
            $this->season_profile_student_complete = 1;
            return 1;
        }
        else{
            $this->season_profile_student_complete = 0;
            return 0;
        }
    }
}