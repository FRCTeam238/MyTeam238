<?php
/*********************************************************************
    buildpage.class.php

    Description: Page structure components and status codes. no content.

    Copyright (c)  2017 Alex Herreid

    See LICENSE.TXT for details and full copyright and contact info.

**********************************************************************/
class BuildPage {
	
    function makeHeader($pageTitle){   
        $html = '<html lang="en">
<head>
    <title>'.SITE_SHORTNAME.' :: '.$pageTitle.'</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="'.SITE_URL.'static/main.css" rel="stylesheet"/>
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no" />
    <link rel="icon" type="image/x-icon" href="https://az734578.vo.msecnd.net/cdn/eventweb-prod/favicon.ico" />
    <link rel="apple-touch-icon" href="'.SITE_URL.'images/apple-touch-icon-72.png" />
    <link rel="apple-touch-icon" sizes="114x114" href="'.SITE_URL.'images/apple-touch-icon-114.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="'.SITE_URL.'images/apple-touch-icon-180.png" />	
    <script>
        // google analytics here
    </script>
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <!-- Color Strip -->
        <div class="color-strip">
            <div class="topbar"></div>
        </div>
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="/2017" title="Home">
                    <div class="navbar-brand">
                        <strong>'.SITE_SHORTNAME.'</strong>
                    </div>
                </a>
            </div>
            <div id="navbar-collapse" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="'.SITE_URL.'" title="Home"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
                    <!-- Navigation -->
                    <li class="dropdown hidden-xs">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span><span class="hidden-lg">MenuLinkSmallScreen</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="/frc238/logout">Logout</a></li>
                            <li><a href="/page/abc2">Link2</a></li>
                            <li><a href="/page/abc3">Link3</a></li>
                            <li><a href="/page/abc4" title="Example Link">Link4</a></li>
                        </ul>
                    </li>
                    <li class=" hidden-sm hidden-md"><a href="/frc238/logout">Logout</a></li>
                    <li class=" active hidden-sm hidden-md"><a href="/frc238/invites">Invites</a></li>
                    <li class=" hidden-sm"><a href="/frc238/password" title="Example Click Link">Password</a></li>
                    <li class=" hidden-sm hidden-md"><a href="/page/abc4">Link4</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="flex" id="flex">
        <div class="whitebg">
            <div class="jumbotron">
                <div class="container">
                    <h3 class="col-xs-12 col-sm-9">'.$pageTitle.'</h3>
                    <div class="hidden-xs col-sm-3">
                        <img src="'.SITE_URL.'images/frc-logo.png" alt="Game Logo" style="margin-left:35px;" />
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="col-md-12">';                
        return $html;
    }

    function makeFooter() {       
       $html = '</div>
            <div class="visible-xs text-center">
                <img src="'.SITE_URL.'images/frc-logo.png" alt="Game Logo" style="margin:15px;" />
            </div>
            <div class="visible-xs visible-sm alert alert-danger hidden-print">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>Some content may be hidden on small displays like yours. Please try using a larger screen if you wish to see more content.
            </div>
        </div>
    </div>
    <div class="footer" id="footer">
        <div class="footer-text">
            Copyright &copy; <a href="'.SOCIAL_WWW.'" target="_blank">'.SITE_FULLNAME.'</a><br />
        </div>
    </div>
    </div>
</body>
</html>';       
        return $html;
    }
    
    function getAnnouncements(){
        $sql = "SELECT announcement "
                 . "FROM ".TABLE_CONFIG." "
                 . "LIMIT 1;";
        $row = db_fetch_row(db_query($sql));
        if(!empty($row[0])){
            $html = '<div class="alert alert-warning" role="alert"><b>Special Announcement:</b><br /><img src="'.SITE_URL.'images/announcement.png" width="50" height="50" salt="announcement" />'.$row[0].'</div>';		
            return $html;
        }
        else{
            return '';
        }
    }

    function getStatusCode($code){
        if($code != 0):
                $sql = "SELECT `isError`, `message`"
                         . " FROM ".TABLE_CODES
                         . " WHERE `id` = ".db_input($code).";";
                $row = db_fetch_row(db_query($sql));
                if($row[0]):
                    $html = '<div class="alert alert-danger" role="alert"><b>';
                else:
                    $html = '<div class="alert alert-success" role="alert"><b>';
                endif;
                    $html .= $row[1].'</b><br /><span style="font-style: italic;font-size:10px;">Code: '.$code.'</span></div>';
            return $html;
        else:
            return;
        endif;
    }
        
###################### END FUNCTIONS, BEGIN PRINT OPTIONS ######################

    // prints page header
    function printHeader($pageTitle = NULL) {
        print $this->makeHeader($pageTitle);
        $this->printAnnouncements();
        $this->showCode();
    }

    // prints page footer
    function printFooter() {
        print $this->makeFooter();
    }
	
    // prints status code (given or assumed)
    function showCode($codeIn = NULL){
        if(!isset($_SESSION['statusCode'])){
            $_SESSION['statusCode'] = 0;
        }
        if(is_null($codeIn)):
            $codeIn = $_SESSION['statusCode'];
            $_SESSION['statusCode'] = 0;
        endif;
        print $this->getStatusCode($codeIn);
    }
    
    // prints admin announcements
    function printAnnouncements() {
        print $this->getAnnouncements();
    }
}