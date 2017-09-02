<?php

class index_status {
    public $join_season = 0;
    public $behavior_contract = 0;
    public $season_profile = 0;
    public $emergency_contact = 0;
    public $registrant_specific = 0;//the portion of the profile specific to the reg type
    public $percent_complete = 0;
    
    function percentComplete($reg_type){
        require_once(CLASSES_DIR.'registrant_types.php');        
        $percent_per_item = 20;
        if($this->join_season){$this->percent_complete += $percent_per_item;}
        if($this->behavior_contract || $reg_type == RegistrantTypes::Alumni){$this->percent_complete += $percent_per_item;}
        if($this->season_profile){$this->percent_complete += $percent_per_item;}
        if($this->emergency_contact || $reg_type == RegistrantTypes::Alumni){$this->percent_complete += $percent_per_item;}
        if($this->registrant_specific){$this->percent_complete += $percent_per_item;}
    }
}