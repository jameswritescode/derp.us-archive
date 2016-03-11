<?php
if ($_GET['id']) 
{
    include("./inc/functions.class.php");
    $derp = new derp;
    $derp->download($_GET['id']);
}
else 
{
    header("Content-Type: text/plain");
    echo "no paste id specified\r\n";
}
?>
