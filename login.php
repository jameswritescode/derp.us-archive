<?php
include("./inc/functions.class.php");
$derp = new derp;
if ($_GET['login'])
{
    if ($_GET['p']) 
    {
        $derp->user_gen($_GET['login'], $_GET['p']);
    }
    else
    {
        $derp->user_gen($_GET['login']);
    }
}
else
{
    if ($_GET['p'])
    {
        $derp->user_gen(FALSE, $_GET['p']);
    }
    else
    {
        $derp->user_gen();
    }
}
?>
