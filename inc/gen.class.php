<?php
class gen
{
    public function __construct()
    {
        $this->mysqli = new mysqli('localhost', 'james_derpus', '4~Jp$.VwNo$%', 'james_derpus');
    }

    public function __destruct()
    {
        $this->mysqli->close();
    }

    private function rss_top($override = FALSE)
    {
        echo '<?xml version="1.0" encoding="utf-8" ?>
            <rss version="2.0">
            <channel>
            <title>derp.us - epic</title>
            <link>http://derp.us/</link>';
        if ($override) 
        {
            echo '<description>feed of recent pastes</description>';
        }
        else 
        {
            echo "<description>$override</description>";
        }
        echo '<language>en</language>';
    }

    private function rss_bottom()
    {
        echo '</channel>
            </rss>';
    }

    private function rss_gen($login = FALSE)
    {
        $login = $this->mysqli->real_escape_string($login);
        if (!$login) 
        {
            $this->rss_top();
            $feed = $this->mysqli->query('SELECT paste, login, date, type, name, url FROP derp_paste WHERE priv=0 ORDER BY id DESC LIMIT 10');
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
                echo "<item>
                    <title>$title</title>
                    <link>http://derp.us/{$info['url']}/</link>
                    <pubDate>".date('r', $info['date'])."</pubDate>
                    <description>
                    <![CDATA[<pre>".
                    implode('<br />', array_slice(explode("\n", htmlentities($info['paste'])), 0, 4))."
                    </pre>]]>
                    </description>
                    </item>";
            }
            $this->rss_bottom();
        }
        else 
        {
            // code...
        }
    }
}
?>
