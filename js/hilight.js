function hashcheck()
{
    if (location.hash) 
    {
        var hash = location.hash.replace('#line-', '');
        document.getElementById('line-' + hash).style.backgroundColor = '#FFFABF';
    }
}
