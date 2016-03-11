<?php
include('./inc/gen.class.php');
$gen = new gen;
if ($_GET['type'] == 'rss') 
{
    if ($_GET['login']) 
    {
        $gen->rss_gen($_GET['login']);
    }
    else 
    {
        $gen->rss_gen();
    }
}
elseif ($_GET['type'] == 'json') 
{
    if ($_GET['login']) 
    {
        // code... 
    }
    else 
    {
        // code...
    }
}
elseif ($_GET['type'] == 'xml') 
{
    if ($_GET['login']) 
    {
        // code...
    }
    else 
    {
        // code...
    }
}
elseif ($_GET['type'] == 'qrcode') 
{
    if ($_GET['login']) 
    {
        // code...
    }
    else 
    {
        // code...
    }
}
?>
