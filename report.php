<?php
if ($_GET['id']) 
{
    include('./inc/functions.class.php');
    $derp = new derp;
    $derp->report($_GET['id']);
}
else 
{
    header("Content-Type: text/plain");
    echo "no id specified\r\n";
}
?>
