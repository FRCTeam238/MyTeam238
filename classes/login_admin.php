<?php

class login_admin{
    public $account_id = 0;
    public $is_admin = 0;//if they have ANY permissions (a row in DB), they are an "admin" (in some form)
    
    public $can_approve_accounts = 0;
    public $can_view_profiles = 0;
    
}