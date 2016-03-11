<?php
/*!
 *
 * Copyright © 2010 James Newton <james@Zaphyous.com>. All Rights Reserved.
 *
 */

/*!
 *
 * functions.inc includes the derp class that holds the core
 * functions that handles the requests sent to the website.
 *
 */

include("./inc/functions.class.php");
$derp = new derp;

/*!
 *
 * GeoIP.dat is needed for Net_GeoIP to work correctly. Used
 * for tracking user and use statistics.
 *
 */

require_once("Net/GeoIP.php");
$geoip = Net_GeoIP::getInstance("./inc/GeoIP.dat");
$cn = $geoip->lookupCountryName($_SERVER['REMOTE_ADDR']);
$cc = $geoip->lookupCountryCode($_SERVER['REMOTE_ADDR']);

/*!
 *
 * disallow.inc holds disallowed urls, id's, and IP's.
 *
 * comes with $disallow_ip, $disallow_url, $disallow_id.
 *
 */

include("./inc/arrays.inc");

// Stop banned IP addresses from using the website.

if (in_array($_SERVER['REMOTE_ADDR'], $disallow_ip))
{
    echo "You're banned from using derp.us!\r\n";
    echo "http://derp.us/banned\r\n";
}
elseif ($_POST) 
{
    if ($_POST['f'] || $_POST['url'] || $_POST['type'] || $_POST['name'] || $_POST['img'] || $_POST['passwd'] || $_POST['priv'])
    {
        echo "Error: post is valid, use post:N.\r\n";
        echo "http://derp.us/\r\n";
    }
    else
    {
        if ($_POST['login'] || $_POST['token'])
        {
            $login = $derp->auth($_POST['login'], $_POST['token']);
        }
    
        if ($_POST['rm']) 
        {
            $derp->rm_id($_POST['rm'], $login);
        }

        if ($_POST['stats'])
        {
            $derp->stats($login);
        }

        if ($_POST['pastes'])
        {
            $derp->pastes($login);
        }
    
        foreach ($_POST as $k => $v) 
        {
            if (preg_match("/f:[1-9]/", $k))
            {
                $f .= "$k,";
            }

            if (preg_match("/img:[1-9]/", $k))
            {
                $img .= "$k,";
            }

            if (preg_match("/url:[1-9]/" ,$k))
            {
                $url .= "$k,";
            }
        }

        $f = explode(",", $f);
        $img = explode(",", $img);
        $url = explode(",", $url);

        if ($f)
        {
            foreach ($f as $k => $v)
            {
                if ($v != "")
                {
                    $num = str_replace("f:", "", $v);
                    $type = $_POST['type:'.$num];
                    $name = $_POST['name:'.$num];
                    $passwd = $_POST['passwd:'.$num];
                    if ($_POST['priv:'.$num]) 
                    {
                        $priv = 1;
                    }

                    if ($passwd)
                    {
                        $priv = 1;
                    }

                    $derp->new_paste($_POST['f:'.$num], $type, $name, $login, $priv, $passwd, $cn, $cc); 
                }
            }
        }

        if ($img)
        {
            foreach ($img as $k => $v) 
            {
                if ($v != "") 
                {
                    $num = str_replace("img:", "", $v);
                    $name = $_POST['name:'.$num];
                    $type = $_POST['type:'.$num];
                    if ($_POST['priv:'.$num])
                    {
                        $priv = 1;
                    }

                    $derp->new_img($_POST['img:'.$num], $type, $name, $login, $priv, $cn, $cc);
                }
            }
        }

        // TODO: stop using regex, let neap.us handle the validation of the URL.
        if ($url) 
        {
            foreach ($url as $k => $v) 
            {
                if ($v != "")
                {
                    $num = str_replace("url:", "", $v);
                    if(!preg_match("/[file|gopher|news|nntp|telnet|http|ftp|https|ftps|sftp]+:\/\/[\/\-a-zA-Z0-9:_.@?$,;~=#&%+]+[\w]?|www\.+[\/\-a-zA-Z0-9:_.@?$,;~=#&%+]+[\w]?/", $_POST['url:'.$num]))
                    {
                        echo "Error: invalid URL!\r\n";
                        echo "http://derp.us/\r\n";
                    }
                    else
                    {
                        if ($_POST['priv:'.$num])
                        {
                            $priv = 1;
                        }

                        $derp->new_url($_POST['url:'.$num]);
                    }
                }
            }
        }
    }
}
else 
{
    include("./inc/index.html");
}
?>
