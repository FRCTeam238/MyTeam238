<?php
/*********************************************************************
    email.class.php

    Description: Handles all system emails based on var passed

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class Email {
	
    /*
     * The mother of all functions...You break it you fix it!
     * And by that I mean you break it and no one gets any notifications.
     */
    
    private function getMessageContent($to, $type, $paramaters){
        if($type == 'createaccount'){
            //param 0 is email key, param 1 is user id
            $link = SITE_URL.'login?activate&key='.$paramaters[0].'&id='.$paramaters[1];
            $content = 'Thanks for creating your '.SITE_SHORTNAME.' account! In order to continue your registration and open your account, '
                    . 'please click on the button below.<br /><br /><a href="'.$link.'" target="_blank"><img src="'.SITE_URL.'images/email/verifyemail.jpg" alt="Activate" /></a>'
                    . '<br /><br />We look forward to seeing you soon!<br /><br />If the link does not work, you could also copy and '
                    . 'paste the following link into your favorite browser:<br /><a href="'.$link.'">'.$link.'</a>';            
            $subject = 'Verify your '.SITE_SHORTNAME.' Account';
            $subject = $this->makeSubject($subject);
            $content = $this->buildMessage($to, $content);
        }
        elseif($type == 'pwreset'){
            $link = SITE_URL.'password?reset&key='.$paramaters[0].'&email='.$paramaters[1];
            $content = 'We\'ve received a request to reset the password of the account associated with this email address. '
                    . '<b>If you did not submit this request, you can safely ignore this message</b> as your password cannot be changed '
                    . 'without the link below. To continue with the password reset request, click the button below or copy the full '
                    . 'link into your favorite browser.<br /><br /><a href="'.$link.'" target="_blank"><img src="'.SITE_URL.'images/email/resetpassword.jpg" alt="Reset" /></a>'
                    . '<br /><br /><a href="'.$link.'">'.$link.'</a>';
            $subject = SITE_SHORTNAME.' Account Password Reset';
            $subject = $this->makeSubject($subject);
            $content = $this->buildMessage($to, $content);
        }
        elseif($type == 'reginvite'){
            $link = SITE_URL.'login?activate&invitation&id='.$paramaters[0].'&key='.$paramaters[1];//0 is invite id, 1 is invite key, 2 is users name, 3 is requester
            $content = 'Hey there '.$paramaters[2].'- we heard you were interested in joining us here on '.SITE_SHORTNAME.'! In fact, '.$paramaters[3].' '
                    . 'has sent you an invitation to expedite your registration process. If you are no longer interested in joining us, you can safely ignore this message '
                    . 'and you won\'t receive further messages from us. <b>To accept this invitation, please click the button below or copy the address into your favorite '
                    . ' browser.</b>s<br /><br /><a href="'.$link.'" target="_blank"><img src="'.SITE_URL.'images/email/verifyemail.jpg" alt="Verify" /></a>'
                    . '<br /><br /><a href="'.$link.'">'.$link.'</a>';
            $subject = SITE_SHORTNAME.' Invitation to Join';
            $subject = $this->makeSubject($subject);
            $content = $this->buildMessage($to, $content);
        }
        //etc
        return array($content, $subject);
    }
    
#################################################### END of EDITABLE functions ####################################################
    private function makeSubject($subject){
        $subject = SITE_SHORTNAME.' :: '.$subject;
        return $subject;
    }	
    private function isEmailEnabled(){
        $sql = "SELECT s.site_email_enabled "
                 . "FROM ".TABLE_CONFIG." s;";
        $row = db_fetch_row(db_query($sql));
        return $row[0];
    }	
    private function getHeaders(){
        $domain =  preg_replace('/^www\./','',$_SERVER['HTTP_HOST']);
        $from = 'noreply'.$domain;
        $headers =   'From: '.SITE_SHORTNAME.' Notifications <'.$from.'>' . "\r\n" 
                           . 'Reply-To: ' . $from . "\r\n" 
                           . 'MIME-Version: 1.0' . "\r\n" 
                           . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" 
                           . 'X-Mailer: PHP/' . phpversion();
        return $headers;
    }	
    private function buildMessage($to, $content){
        $now = Format::displayDateTimeFromDB(Format::currentDateTime());
        $message = '
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                        <html xmlns="http://www.w3.org/1999/xhtml">
                        <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <style type="text/css">
                                table {border: 2px solid #003F72; border-top:none; border-bottom:none; padding-left:7px; padding-right:7px;}
                                td, tr {border: 0}
                        </style>
                        </head>
                        <body>
                        <center><img src="'.SITE_URL.'images/email/emailBanner.png" alt="Header" /></center>
                        <table width="650" align="center" cellpadding="0" cellspacing="0" style="border: 2px solid #003F72; border-top:none; border-bottom:none; padding-left:7px; padding-right:7px; border-color:#000;">
                                <tr>
                                        <td width="100%" colspan="2" style="border: 0"><br />
                                         Greetings '. $this->getUsersNameFromEmail($to) . ',<br /><br />'.$content.'<br /><br />Regards,<br />- '.SITE_SHORTNAME.'<br /><br /><span style="font-size:11px;"><br /><i>This message was sent to you because of an action you took or by request of another '.SITE_SHORTNAME.' site user.<br />'.SITE_FULLNAME.' will not sell or use your email without permission.</i></span><hr width="500" />
                                  </td>
                          </tr>
                                <tr>
                                  <td style="color:#999; font-size:12px; border:0;" width="490">
                                        &gt;&gt;This email was sent from a notification-only address.<br />&gt;&gt;Please do not reply to this message.<br />Copyright &copy; '.SITE_SHORTNAME.' <br />Information delivered on '.$now.' (EST). Please visit us at '.SITE_URL.' for assistance.
                                  </td>
                                  <td width="125" align="right" style="border: 0">
                                        <a href="https://www.facebook.com/'.SOCIAL_FB.'"><img src="'.SITE_URL.'images/email/fb.png" alt="Facebook" /></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="https://www.twitter.com/'.SOCIAL_TWITTER.'"><img src="'.SITE_URL.'images/email/twitter.png" alt="Twitter" /></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                  </td>
                                </tr>
                        </table>
                        <center><img src="'.SITE_URL.'images/email/emailFooter.png" alt="Footer" /></center>
                        </body>
                </html>
        ';
        return $message;
    }

    function getUsersNameFromEmail($user){
        return Data::getUsersNameFromEmail($user);
    }

    function sendEmail($to, $type, $paramaters){
        if($this->isEmailEnabled()) :
            $content = $this->getMessageContent($to, $type, $paramaters);
            $subject = $content[1];
            $body = $content[0];
            $headers = $this->getHeaders();
            //do the send
            $send = mail($to, $subject, $body, $headers);
            if(!$send){
                return 0;
            }
            else{
                return 1;
            }
        endif;
        return 1;//disabled, so that counts as a success
    }
}
?>
