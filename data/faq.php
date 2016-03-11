<?php
    $faq = array(
        "What is derp?" => array("#what_is_derp", "derp is a pastebin for command line. Though we do have a gui pastebin at <a href='http://gui.derp.us/' >gui.derp.us</a>."),
        "How do I use it?" => array("#how_to_use", "You can use derp in a veriety of ways. We have scripts on the <a href='http://derp.us/clients/'>clients</a> page. Following is other ways derp can be used:<blockquote>~$ cat file.txt | curl -F 'f:1=<-' http://derp.us</blockquote>This will post the data in file.txt to derp.us. derp will respond with the URI to the paste.<blockquote>~$ curl http://derp.us/1 >> file.txt</blockquote>This would take the paste from http://derp.us/1 and put it in file.txt so you can edit on the fly.<blockquote>~$ curl -F 'url:1=http://derp.us/' http://derp.us</blockquote>This will create a derp.us short url to http://derp.us/ and return the URI.<blockquote>~$ cat google.gif | curl -F 'img:1=<-' http://derp.us</blockquote>This will take the image data, send to derp.us, and return a url of the image. This is functional, but still in slight development."),
        "How does private work?" => array("#private", "derp provides the ability to make pastes private, which makes pastes not show up in <a href='http://derp.us/user/'>/user/</a> and <a href='http://derp.us/rss/'>/rss/</a>. However, this does not stop people from being able to find the paste. So, we also provide the ability to create a password for a private paste in the following way:<blockquote>~$ cat file.txt | curl -F 'f:1=<-' -F 'priv:1=y' -F 'passwd:1=securepass' http://derp.us</blockquote>This will force users to use a password to see the post.<br /><br />If the user is using a browser, they'll need to insert the password and submit. If the user is using terminal, they can use the following method to get the paste:<blockquote>~$ curl -F 'passwd=test' http://derp.us/36 >> file.txt</blockquote>This would send the password test to the paste, then take the paste from http://derp.us/36 and put it in file.txt.<br /><br />The above commands work for a live paste, try it out yourself!"),
        "What was the site made in?" => array("#how_it_was_made", "derp.us was coded in <a href='http://www.vim.org/'>Vim</a>. The backend is in PHP, and we use <a href='http://geshi.org/'>GeSHi</a> for syntax highlighting."),
        "I use Microsoft Windows, how do I use derp?" => array("#windows", "Get something like <a href='http://cygwin.com/'>Cygwin</a>, a linux emulator for Windows.<br /><br />You can also get <a href='http://curl.haxx.se/download.html'>curl for Windows</a>, however we don't know of a cat-like program for Windows. If you know of one, please, let us know."),
        "How can I get all my pastes?" => array("#pastes", "To get all you pastes you can send your login and token with the pastes POST, and it till show you a list of all your pastes (urls).<blockquote>~$ curl -F 'login=blah' -F 'token=blah' -F 'pastes=y'</blockquote>"),
        "How do I get my own?" => array("#source", "derp.us is not open source (subject to change in the future). We will release information on how to get a private derp.us setup."),
    );
    $html_top = "<style type='text/css'>";
    $html_top .= "* { font-family: monospace; }";
    $html_top .= "a { text-decoration: none; }";
    $html_top .= ".values li { padding-top: 4px; }";
    $html_top .= "</style>";
    echo $html_top;
    echo "<h1><a name='top'>Table of Contents</a></h1>";
    foreach($faq as $k => $v)
    {
        $html_toc = "<a href='{$v[0]}'>$k</span></a><br />";
        echo $html_toc;
    }
    echo "<h1>FAQ</h1>";
    foreach($faq as $k => $v)
    {
        $html_middle = "<h2><a name='".str_replace("#", "", $v[0])."'>$k</a></h2>";
        $html_middle .= "{$v[1]}";
        $html_middle .= "<div style='position: absolute; right: 0px;'>[<a href='#top'>top</a>]</div>";
        echo $html_middle;
    }
?>
