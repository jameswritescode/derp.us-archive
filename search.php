<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Search</title>
        <link rel="stylesheet" href="/css/main.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="/css/navi.css" type="text/css" media="screen" />
    </head>
    <body>
        <form action="/search" method="post">
            <span>Search:</span> <input type="text" name="search" /> <input type="submit" name="submit_search" value="search" />
        </form>
        <?php
        include('./inc/functions.class.php');
        $derp = new derp;
        if ($_GET['q']) 
            $derp->search($_GET['q']);
        elseif ($_POST['search'])
            $derp->search($_POST['search']);
        ?>
    </body>
</html>
