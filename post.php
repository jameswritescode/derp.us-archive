<?php
    include("./inc/functions.class.php");
    $derp = new derp;
    if ($_POST['passwd'])
    {
        if ($_POST['id']) 
        {
            $id = $_POST['id'];
        }
        else 
        {
            $id = $_GET['id'];
        }
        $derp->show_passwd_paste($id, $_POST['passwd']);
    }
    elseif (is_numeric($_GET['id']))
    {
        if (isset($_GET['extend'])) 
        {
            $derp->show_img($_GET['id'], TRUE);
        }
        else
        {
            $derp->show_img($_GET['id']);
        }
    }
    elseif (!is_numeric($_GET['id']))
    {
        if (isset($_GET['extend'])) 
        {
            $derp->show_paste($_GET['id'], TRUE);
        }
        else 
        {
            $derp->show_paste($_GET['id']);
        }
    }
    else
    {
        include("./404.shtml");
    }
?>
