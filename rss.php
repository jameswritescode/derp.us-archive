<?php
include("./inc/functions.class.php");
$derp = new derp;
if ($_GET['login']) 
{
    $derp->rss_gen($_GET['login']);
}
else 
{
    $derp->rss_gen();
}
?>
