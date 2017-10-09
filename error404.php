<?php
require_once 'main.inc.php';

$BuildPage = new BuildPage();
$BuildPage->printHeader('Error 404');
?>
Oh no!! We can't find that page! Please try again, and contact us if you still have trouble.<br /><br />

<a href="index" class="btn btn-info center-block" role="button">Return Home</a><br />
<?php
$BuildPage->printFooter();