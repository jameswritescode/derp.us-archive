<?php
/*!
 *
 * Copyright © 2010 James Newton <james@Zaphyous.com>. All Rights Reserved.
 *
 */

class derp
{
    private $mysqli;

    public function __construct()
    {
        $this->mysqli = new mysqli('localhost', 'xxx', 'xxx', 'xxx');
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }

    public function auth($login, $token)
    {
        $login = $this->mysqli->real_escape_string(strtolower($login));
        $token = $this->mysqli->real_escape_string($token);
        $data = $this->mysqli->query("SELECT login, token FROM derp_users WHERE login='$login'");
        $result = $data->fetch_array();
        if ($token && !$login)
        {
            die("Error: login missing!\r\n");
        }
        elseif ($login != $result['login'])
        {
            if (is_numeric($login))
            {
                die("Error: login must contain letters.\r\n");
            }
            elseif (!$token)
            {
                die("Error: token missing!\r\n");
            }
            elseif ($login == $token)
            {
                die("Error: your login and token are the same, please use another password.\r\n");
            }
            else
            {
                mysql_query("INSERT INTO derp_users (login, token) VALUES ('$login', '".md5($token)."')");
                echo "User $login created!\r\n";
                echo "http://derp.us/user/$login\r\n";
                echo "Your password is $token - remember that.\r\n\r\n";
                echo "If you have any problems using derp, or have any suggestions,\r\n";
                echo "drop by irc.splitnet.org, #Zaphyous and let us know!\r\n";
                return $login;
            }
        }
        elseif (md5($token) != $result['token'])
        {
            die("Error: token is incorrect!\r\n");
        }
        else
        {
            return $login;
        }
    }

    public function rm_id($id, $login = FALSE)
    {
        $id = $this->mysqli->real_escape_string($id);
        $login = $this->mysqli->real_escape_string($login);
        if ($login)
        {
            $data = $this->mysqli->query("SELECT login FROM derp_paste WHERE url='$id'");
            $result = $data->fetch_array();
            if ($result['login'] == $login || $login == 'sindacious')
            {
                if (is_numeric($id))
                {
                    if ($login == 'sindacious')
                    {
                        $this->mysqli->query("DELTE FROM derp_img WHERE url='$id'");
                    }
                    else
                    {
                        $this->mysqli->query("DELETE FROM derp_img WHERE url='$id' AND login='$login'");
                    }
                    echo "Paste $id deleted!\r\n";
                }
                else
                {
                    if ($login == 'sindacious')
                    {
                        $this->mysqli->query("DELETE FROM derp_paste WHERE url='$id'");
                    }
                    else
                    {
                        $this->mysqli->query("DELETE FROM derp_paste WHERE url='$id' AND login='$login'");
                    }
                    echo "URL $id deleted!\r\n";
                }
            }
            else
            {
                if (is_numeric($id))
                {
                    echo "Error: image $id is not owned by $login!\r\n";
                }
                else
                {
                    echo "Error: paste $id is not owned by $login!\r\n";
                }
            }
        }
        else
        {
            echo "Error: you need to own an account to delete a paste or image!\r\n";
        }
    }

    public function stats($login = FALSE)
    {
        $login = $this->mysqli->real_escape_string($login);
        echo "derp.us statistics:\r\n";
        $data = $this->mysqli->query("SELECT * FROM derp_paste");
        $result = $data->num_rows;
        echo "pastes: $result\r\n";
        $data = $this->mysqli->query("SELECT * FROM derp_img");
        $result = $data->num_rows;
        echo "images: $result\r\n\r\n";
        echo "your statistics:\r\n";
        if ($login)
        {
            $data = $this->mysqli->query("SELECT * FROM derp_paste WHERE login='$login'");
            $result = $data->num_rows;
            echo "pastes: $result\r\n";
            $data = $this->mysqli->query("SELECT * FROM derp_img WHERE login='$login'");
            $result = $data->num_rows;
            echo "images: $result\r\n";
        }
        else
        {
            $data = $this->mysqli->query("SELECT * FROM derp_paste WHERE ip='{$_SERVER['REMOTE_ADDR']}'");
            $result = $data->num_rows;
            echo "pastes: $result\r\n";
            $data = $this->mysqli->query("SELECT * FROM derp_img WHERE ip='{$_SERVER['REMOTE_ADDR']}'");
            $result = $data->num_rows;
            echo "images: $result\r\n\r\n";
            echo "Send with login and token for personal statistics.\r\n";
        }
    }

    public function pastes($login = FALSE)
    {
        $login = $this->mysqli->real_escape_string($login);
        if ($login)
        {
            printf("%-25s%-15s%-30s%-20s%s\r\n", "link", "type", "name", "priv", "preview");
            $result = $this->mysqli->query("SELECT * FROM derp_paste WHERE login='$login'");
            while ($info = $result->fetch_array())
            {
                printf("%-25s", "http://derp.us/{$info['url']}");
                printf("%-15s", $info['type']);
                printf("%-30s", substr($info['name'], 0, 25));
                if ($info['priv'])
                {
                    if ($info['passwd'])
                    {
                        $pw = "pw: ({$info['passwd']})";
                    }
                    printf("%-20s", "yes$pw");
                }
                else
                {
                    printf("%-20s", "no");
                }
                $bad = array("\n", "\r", "\t");
                printf("%s\r\n", substr(str_replace($bad, "", $info['paste']), 0, 30));
            }
        }
        else
        {
            echo "Error: login to see pastes!\r\n";
        }
    }

    private function rss_top($override = FALSE)
    {
        echo "<?xml version='1.0' encoding='utf-8'?>";
        echo "<rss version='2.0'>";
        echo "<channel>";
        echo "<title>derp.us - epic</title>";
        echo "<link>http://derp.us/</link>";
        if(!$override)
        {
            echo "<description>feed of recent pastes</description>";
        }
        else
        {
            echo "<description>$override</description>";
        }
        echo "<language>en</language>";
    }

    private function rss_bottom()
    {
        echo "</channel>";
        echo "</rss>";
    }

    public function rss_gen($login = FALSE)
    {
        $login = $this->mysqli->real_escape_string($login);
        if (!$login)
        {
            $this->rss_top();
            $feed = $this->mysqli->query("SELECT paste, login, date, type, name, url FROM derp_paste WHERE priv=0 ORDER BY id DESC LIMIT 10");
            while ($info = $feed->fetch_array())
            {
                if ($info['name'])
                {
                    if ($info['login'])
                    {
                        $title = "{$info['name']} by {$info['login']}";
                    }
                    else
                    {
                        $title = $info['name'];
                    }
                }
                elseif ($info['type'])
                {
                    if ($info['login'])
                    {
                        $title = "a {$info['type']} paste by {$info['login']}";
                    }
                    else
                    {
                        $title = "a {$info['type']} paste";
                    }
                }
                elseif ($info['login'])
                {
                    $title = "a paste by {$info['login']}";
                }
                else
                {
                    $title = "an anonymous paste";
                }
                echo "<item>";
                echo "<title>$title</title>";
                echo "<link>http://derp.us/{$info['url']}/</link>";
                echo "<pubDate>".date("r", $info['date'])."</pubDate>";
                echo "<description>";
                echo "<![CDATA[<pre>";
                echo implode("<br />", array_slice(explode("\n", htmlentities($info['paste'])), 0, 4));
                echo "</pre>]]>";
                echo "</description>";
                echo "</item>";
            }
            $this->rss_bottom();
        }
        else
        {
            $data = $this->mysqli->query("SELECT login FROM derp_paste WHERE login='$login'");
            $result = $data->num_rows;
            if (!$result)
            {
                header("Content-Type: text/plain");
                echo "pastes from user do not exist";
            }
            else
            {
                $this->rss_top("feed of $login's pastes");
                $feed = $this->mysqli->query("SELECT paste, login, date, url, type, name FROM derp_paste WHERE login='$login' and priv=0 ORDER BY id DESC LIMIT 10");
                while ($info = $feed->fetch_array())
                {
                    if ($info['name'])
                    {
                        $title = $info['name'];
                    }
                    elseif ($info['type'])
                    {
                        $title = "a {$info['type']} paste";
                    }
                    else
                    {
                        $title = "a paste by {$info['login']}";
                    }
                    echo "<item>";
                    echo "<title>$title</title>";
                    echo "<link>http://derp.us/{$info['url']}/</link>";
                    echo "<pubDate>".date("r", $info['date'])."</pubDate>";
                    echo "<description>";
                    echo "<![CDATA[<pre>";
                    echo implode("<br />", array_slice(explode("\n", htmlentities($info['paste'])), 0, 4));
                    echo "</pre>]]>";
                    echo "</description>";
                    echo "</item>";
                }
                $this->rss_bottom();
            }
        }
    }

    public function download($id)
    {
        $id = $this->mysqli->real_escape_string($id);
        $data = $this->mysqli->query("SELECT paste, url, type FROM derp_paste WHERE url='$id'");
        $result = $data->fetch_array();
        if (!$result['paste'])
        {
            header("Content-Type: text/plain");
            echo "paste does not exist\r\n";
        }
        else
        {
            switch ($result['type'])
            {
                case 'actionscript':  $ext = 'as'; break;
                case 'actionscript3': $ext = 'as'; break;
                case 'abap':          $ext = 'abap'; break;
                case 'ada':           $ext = 'ada'; break;
                case 'scpt':          $ext = 'scpt'; break;
                case 'asm':           $ext = 'asm'; break;
                case 'asp':           $ext = 'asp'; break;
                case 'autohotkey':    $ext = 'ahk'; break;
                case 'autoit':        $ext = 'au3'; break;
                case 'awk':           $ext = 'awk'; break;
                case 'bash':          $ext = 'sh'; break;
                case 'bf':            $ext = 'bf'; break;
                case 'bnf':           $ext = 'bnf'; break;
                case 'boo':           $ext = 'boo'; break;
                case 'cfm':           $ext = 'cfm'; break;
                case 'clojure':       $ext = 'clj'; break;
                case 'cobol':         $ext = 'cob'; break;
                case 'c':             $ext = 'c'; break;
                case 'cpp':           $ext = 'cpp'; break;
                case 'cpp-qt':        $ext = 'cpp'; break;
                case 'csharp':        $ext = 'cs'; break;
                case 'css':           $ext = 'css'; break;
                case 'diff':          $ext = 'diff'; break;
                case 'dot':           $ext = 'dot'; break;
                case 'd':             $ext = 'd'; break;
                case 'fortran':       $ext = 'f'; break;
                case 'html4strict':   $ext = 'html'; break;
                case 'ini':           $ext = 'ini'; break;
                case 'java5':         $ext = 'java'; break;
                case 'java':          $ext = 'java'; break;
                case 'javascript':    $ext = 'js'; break;
                case 'jquery':        $ext = 'js'; break;
                case 'latex':         $ext = 'tex'; break;
                case 'lisp':          $ext = 'lisp'; break;
                case 'lua':           $ext = 'lua'; break;
                case 'mirc':          $ext = 'msl'; break;
                case 'mysql':         $ext = 'sql'; break;
                case 'perl6':         $ext = 'pl'; break;
                case 'perl':          $ext = 'pl'; break;
                case 'php-brief':     $ext = 'php'; break;
                case 'php':           $ext = 'php'; break;
                case 'python':        $ext = 'py'; break;
                case 'rails':         $ext = 'rb'; break;
                case 'ruby':          $ext = 'rb'; break;
                case 'smarty':        $ext = 'php'; break;
                case 'sql':           $ext = 'sql'; break;
                case 'tcl':           $ext = 'tcl'; break;
                case 'vb':            $ext = 'vb'; break;
                case 'vim':           $ext = 'vim'; break;
                case 'whitespace':    $ext = 'ws'; break;
                case 'xml':           $ext = 'xml'; break;
                case '':              $ext = 'txt'; break;
                case NULL:            $ext = 'txt'; break;
                default:              $ext = 'txt'; break;
            }
            header("Content-Type: application/octet-stream");
            header("Content-disposition: attatchment; filename={$result['url']}.$ext");
            header("Content-transfer-encoding: binary");
            header("Expires: 0");
            echo $result['paste'];
        }
    }

    /*!
     *
     * $type needs to correspond with the table name. ex: derp_$name
     *
     */

    private function get_next_id($type)
    {
        $data = $this->mysqli->query("SELECT url FROM derp_$type ORDER BY id DESC LIMIT 1");
        $result = $data->fetch_array();
        $url = $result['url'];
        $url++;
        return $url;
    }

    public function new_paste($paste, $type, $name, $login, $priv, $passwd, $cn, $cc)
    {
        $paste = $this->mysqli->real_escape_string($paste);
        $type = $this->mysqli->real_escape_string($type);
        $name = $this->mysqli->real_escape_string($name);
        $passwd = $this->mysqli->real_escape_string($passwd);
        $url = $this->get_next_id('paste');
        $this->mysqli->query("INSERT INTO derp_paste (paste, ip, date, url, name, type, login, priv, passwd, s_cn, s_cc) VALUES ('$paste', '{$_SERVER['REMOTE_ADDR']}', '".time()."', '$url', '$name', '$type', '$login', '$priv', '$passwd', '$cn', '$cc')");
        echo "http://derp.us/$url\r\n";
    }

    public function new_img($img, $type, $name, $login, $priv, $cn, $cc)
    {
        $img = $this->mysqli->real_escape_string($img);
        $type = $this->mysqli->real_escape_string($type);
        $name = $this->mysqli->real_escape_string($name);
        $url = $this->get_next_id('img');
        $mysqli->query("INSERT INTO derp_img (image, ip, date, url, name, login, priv, s_cn, s_cc) VALUES ('$img', '{$_SERVER['REMOTE_ADDR']}', '".time()."', '$url', '$name', '$login', '$priv', '$cn', '$cc')");
        echo "http://derp.us/$url\r\n";
    }

    public function new_url($address)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://neap.us/api/api.php?url='.base64_encode($address));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $url = curl_exec($ch);
        curl_close($ch);
        echo "$url\r\n";
    }

    public function show_passwd_paste($id, $passwd)
    {
        $id = $this->mysqli->real_escape_string($id);
        $passwd = $this->mysqli->real_escape_string($passwd);
        $data = $this->mysqli->query("SELECT passwd, paste FROM derp_paste WHERE url='$id'");
        $result = $data->fetch_array();
        if ($result['passwd'] == $passwd)
        {
            setcookie("canview$id", TRUE, 0, "/", ".derp.us");
            if ($_POST['submit_pass'])
            {
                header("Location: http://derp.us/$id");
            }
            else
            {
                header("Content-Type: text/plain");
                $paste = explode("\n", $result['paste']);
                foreach ($paste as $lines)
                {
                    echo $lines."\r\n";
                }
            }
        }
        else
        {
            header("Content-Type: text/plain");
            echo "password incorrect\r\n";
        }
    }

    private function prot($id)
    {
        echo "<!DOCTYPE HTML>".
            "<html>".
            "<head>".
            "<title>protected, derp</title>".
            "<link rel='stylesheet' href='/css/prot.css' type='text/css' media='screen' />".
            "</head>".
            "<body>".
            "<form method='post' action='/post.php'>".
            "<input type='hidden' name='id' value='$id' />".
            "<span>Password:</span> <input type='password' name='passwd' /> <input type='submit' name='submit_pass' value='submit' />".
            "</form>".
            "</body>".
            "</html>";
    }

    public function show_paste($id, $ex = FALSE)
    {
        $id = $this->mysqli->real_escape_string($id);
        $data = $this->mysqli->query("SELECT passwd, paste, type FROM derp_paste WHERE url='$id'");
        $result = $data->fetch_array();
        if ($ex)
        {
            if ($result['passwd'] && !$_COOKIE['canview'.$id])
            {
                $this->prot($id);
            }
            elseif (!$result['paste'])
            {
                header("Content-Type: text/plain");
                echo "paste does not exist\r\n";
            }
            else
            {
                echo "<!DOCTYPE HTML>".
                    "<html>".
                    "<head>".
                    "<title>derp</title>".
                    "<script src='/js/hilight.js' type='text/javascript'></script>".
                    "</head>".
                    "<body onload='hashcheck()'>";
                if ($result['type'])
                {
                    include_once("geshi/geshi.php");
                    $geshi = new GeSHi($result['paste'], $result['type']);
                    $geshi->enable_line_numbers(TRUE);
                    $geshi->enable_ids(TRUE);
                    $geshi->set_overall_id('line');
                    echo $geshi->parse_code();
                    echo "</body>".
                        "</html>";
                }
                else
                {
                    echo "<pre>".
                        "<ol>";
                    $array = explode("\n", $result['paste']);
                    $num = 0;
                    foreach ($array as $lines)
                    {
                        $num++;
                        echo "<li style='font-family: monospace; font-weight: normal; vertical-align: top;' id='line-$num'>".
                            "<div style='font: normal normal 1em/1.2em monospace; margin: 0px; padding: 0px; background: none; vertical-align: top;'>".
                            str_replace(' ', '&nbsp;', htmlentities($lines)).
                            "&nbsp;".
                            "</div>".
                            "</li>";
                    }
                    echo "</ol>".
                        "</pre>".
                        "</body>".
                        "</html>";
                }
            }
        }
        else
        {
            if ($result['passwd'] && !$_COOKIE['canview'.$id])
            {
                $this->prot($id);
            }
            elseif (!$result['paste'])
            {
                header("Content-Type: text/plain");
                echo "paste does not exist";
            }
            else
            {
                header("Content-Type: text/plain");
                $array = explode("\n", $result['paste']);
                foreach ($array as $lines)
                {
                    echo $lines."\r\n";
                }
            }
        }
    }

    public function show_img($id, $ex = FALSE)
    {
        $id = $this->mysqli->real_escape_string($id);
        if ($ex)
        {
            $data = $this->mysqli->query("SELECT name, url, priv, date, login FROM derp_img WHERE url='$id'");
            $result = $data->fetch_array();
            if (!$result['url'])
            {
                header("Content-Type: text/plain");
                echo "image does not exist\r\n";
            }
            else
            {
                header('Content-Type: text/plain');
                if ($result['name'])
                {
                    echo "Name:        {$result['name']}\r\n";
                }
                if (!$result['login'] || $result['priv'])
                {
                    $c = "Anonymous";
                }
                else
                {
                    $c = $result['login'];
                }
                echo "Uploader:    $c\r\n";
                echo "Uploaded:    ".date('r', $result['date'])."\r\n\r\n\r\n";
                echo "is this a nasty image? go to http://derp.us/report/{$result['url']}/ to report the url.\r\n";
            }
        }
        else
        {
            $data = $this->mysqli->query("SELECT image FROM derp_img WHERE url='$id'");
            $result = $data->fetch_array();
            if (!$result['image'])
            {
                header("Content-Type: text/plain");
                echo "image does not exist\r\n";
            }
            else
            {
                header("Content-Type: text/plain");
                echo $result['image'];
            }
        }
    }

    private function user_top($type, $login = FALSE, $page = FALSE, $count = FALSE, $last = FALSE)
    {
        echo "<!DOCTYPE HTML>".
            "<html>".
            "<head>";
        if (!$login)
        {
            echo "<title>paste feed</title>";
        }
        else
        {
            echo "<title>$login</title>";
        }
        if (!$login)
        {
            echo "<link rel='alternate' type='application/rss+xml' title='recent rss feed' href='/rss/' />";
        }
        else
        {
            echo "<link rel='alternate' type='application/rss+xml' title='recent rss feed' href='/rss/$login' />";
        }
        if ($page && $count && $last)
        {
            echo "<link rel='stylesheet' href='/css/navi.css' type='text/css' media='screen' />";
        }
        echo "</head>".
            "<body>";
        if ($page && $count && $last && $type)
        {
            $this->user_nav($type, $page, $count, $last, $login);
        }
        echo "<pre>";
    }

    private function user_bottom()
    {
        echo "</pre>".
            "</body>".
            "</html>";
    }

    private function user_nav($type, $page, $count, $last, $login)
    {
        if ($count > 10)
        {
            $xox = "( Page $page of $last )";
            $prev = $page - 1;
            $next = $page + 1;
            echo "<div id='navi'>";
            if ($page == 1)
            {
                if ($type == 'main')
                {
                    echo "$xox <a href='/user/$next'>NEXT</a>";
                }
                elseif ($type == 'user')
                {
                    echo "$xox <a href='/user/$login/$next'>NEXT</a>";
                }
            }
            elseif ($page == $last)
            {
                if ($type == 'main')
                {
                    echo "<a href='/user/$prev'>PREV</a> $xox";
                }
                elseif ($type == 'user')
                {
                    echo "<a href='/user/$login/$prev'>PREV</a> $xox";
                }
            }
            else
            {
                if ($type == 'main')
                {
                    echo "<a href='/user/$prev'>PREV</a> $xox <a href='/user/$next'>NEXT</a>";
                }
                elseif ($type == 'user')
                {
                    echo "<a href='/user/$login/$prev'>PREV</a> $xox <a href='/user/$login/$next'>NEXT</a>";
                }
            }
            echo "</div>";
        }
    }

    public function user_gen($login = FALSE, $page = FALSE)
    {
        $login = $this->mysqli->real_escape_string($login);
        if ($login)
        {
            $data = $this->mysqli->query("SELECT login FROM derp_paste WHERE login='$login'");
            $result = $data->num_rows;
            if (!$result)
            {
                header("Content-Type: text/plain");
                echo "user does not exist";
            }
            else
            {
                $count_query = $this->mysqli->query("SELECT COUNT(*) FROM derp_paste WHERE priv=0 AND login='$login'");
                $count = $count_query->fetch_row();
                $lastpage = ceil($count[0]/10);
                if (!$page)
                {
                    $page = 1;
                }
                $page = (int)$page;
                if ($page > $lastpage)
                {
                    $page = $lastpage;
                }
                $limit = ($page - 1) * 10 .",". 10;
                $this->user_top('user', $login, $page, $count[0], $lastpage);
                $feed = $this->mysqli->query("SELECT url, date, paste, name FROM derp_paste WHERE priv=0 AND login='$login' ORDER BY id DESC LIMIT $limit");
                while ($info = $feed->fetch_array())
                {
                    echo "[<a href='/{$info['url']}'>raw</a>] ".
                        "[<a href='/{$info['url']}/'>num</a>] ".
                        "[<a href='/download/{$info['url']}'>down</a>] ".
                        "pasted on ".date('r', $info['date']);
                    if ($info['name'])
                    {
                        echo " ({$info['name']})";
                    }
                    echo "<ol>".
                        "<li>".
                        implode("</li><li>", array_slice(explode("\n", htmlentities($info['paste'])), 0, 4)).
                        "</li>".
                        "</ol>";
                }
                $this->user_bottom();
            }
        }
        else
        {
            $count_query = $this->mysqli->query("SELECT COUNT(*) FROM derp_paste WHERE priv=0");
            $count = $count_query->fetch_row();
            $lastpage = ceil($count[0]/10);
            if (!$page)
            {
                $page = 1;
            }
            $page = (int)$page;
            if ($page > $lastpage)
            {
                $page = $lastpage;
            }
            $limit = ($page - 1) * 10 .",". 10;
            $this->user_top('main', FALSE, $page, $count[0], $lastpage);
            $feed = $this->mysqli->query("SELECT url, date, paste, name, login FROM derp_paste WHERE priv=0 ORDER BY id DESC LIMIT $limit");
            while ($info = $feed->fetch_array())
            {
                echo "[<a href='/{$info['url']}'>raw</a>] ".
                    "[<a href='/{$info['url']}/'>num</a>] ".
                    "[<a href='/download/{$info['url']}/'>down</a>] ";
                if ($info['login'])
                {
                    echo "[<a href='/login/{$info['login']}'>user</a>] ";
                }
                echo "pasted on ".date('r', $info['date']);
                if ($info['name'])
                {
                    echo " ({$info['name']})";
                }
                echo "<ol>".
                    "<li>".
                    implode("</li><li>", array_slice(explode("\n", htmlentities($info['paste'])), 0, 4)).
                    "</li>".
                    "</ol>";
            }
            $this->user_bottom();
        }
    }

    public function search($q)
    {
        $q = $this->mysqli->real_escape_string($q);
        $qo = str_replace(' ', '%', $q);
        $data = $this->mysqli->query("SELECT url, date, name, paste, login FROM derp_paste WHERE priv=0 AND paste LIKE '%$qo%'");
        $result = $data->num_rows;
        echo "<pre>";
        if (!$result)
        {
            echo "no pastes matching $q";
        }
        else
        {
            $search = $this->mysqli->query("SELECT url, date, name, paste, login FROM derp_paste WHERE priv=0 AND paste LIKE '%$qo%' ORDER BY id DESC");
            while ($info = $search->fetch_array())
            {
                echo "[<a href='/{$info['url']}'>raw</a>] ".
                    "[<a href='/{$info['url']}/'>num</a>] ".
                    "[<a href='/download/{$info['url']}/'>down</a>] ";
                if ($info['login'])
                {
                    echo "[<a href='/login/{$info['login']}'>user</a>] ";
                }
                echo "pasted on ".date('r', $info['date']);
                if ($info['name'])
                {
                    echo "({$info['name']})";
                }
                echo "<ol>".
                    "<li>".
                    implode("</li><li>", array_slice(explode("\n", htmlentities($info['paste'])), 0, 4)).
                    "</li>".
                    "</ol>";
            }
        }
        echo "</pre>";
    }

    public function report($id)
    {
        if (is_numeric($id))
        {
            $type = 'img';
        }
        else
        {
            $type = 'paste';
        }
        $msg = "a user is bawww'ing over this $type id: $id";
        mail('derp@derp.us', '[derp] report', $msg, "From: baww@derp.us\r\n");
        header("Content-Type: text/plain");
        echo "$type $id has been reported.";
    }


    // kevin
    public function testing()
    {
        $result = $this->mysqli->query("SELECT * FROM derp_paste WHERE id='118'");
        $meta = $result->fetch_object();
	echo '<pre>';
        echo $meta->paste;
        echo '</pre>';
    }
}
?>
