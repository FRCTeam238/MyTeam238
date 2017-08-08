<?php
require_once '../main.inc.php';
$BuildPage = new BuildPage();
$BuildPage->printHeader('Page Not Found');

echo('<script>
function goBack()
  {
  window.history.back()
  }
</script><h1>Page Not Found</h1>Please go back and try your previous action again.<br /><br /><input type="button" value="Back" onclick="goBack()" id="back" />');

$BuildPage->printFooterSpecial();
?>