<?php
/*
 *   Copyright (c) 2007 msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

/*
 * YakiBiki Cache Cleaner
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: clean-cache.php 487 2008-12-14 13:06:06Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/yb_local.php');

if (!_YB('check.support.scripts')) {
    echo "check support scripts is prohibited.";
    exit();
}

$results = array();
function _clean_cache_delete_entry($e)
{
    global $results;
    if (is_dir($e)) {
        $r = rmdir($e);
    } else {
        $r = unlink($e);
    }
    $results[$e] = (boolean)$r;
    return $e;
}

$_callback = (isset($_POST['go']) && !empty($_POST['go'])) 
    ? '_clean_cache_delete_entry' : '';
$cache_files = array();
find_files(_YB('dir.caches'), $cache_files, $_callback);

function print_file_modes($title, $list)
{
    global $results;
    $html = <<<HTML
<h2>{$title}</h2>
<table border="1">
<tr><th>file/dir</th><th>result</th></tr>
HTML;
    foreach ($list as $entry) {

        $html .= '<tr>' . PHP_EOL;
        $html .= '<td>' . $entry . '</td>' . PHP_EOL;
        $html .= '<td>';
        if (isset($results[$entry])) {
            $html .= okng($results[$entry]);
        }
        $html .= '</td>' . PHP_EOL;
        $html .= '</tr>' . PHP_EOL;
    }
    $html .= '</table>' . PHP_EOL;

    print $html;
}


/**
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 * vim: set expandtab tabstop=4 shiftwidth=4:
 */
?>
<html>
<head>
<title>YakiBiki Cache Cleaner</title>
</head>
<body>
<h1>YakiBiki Cache Cleaner</h1>

<form action="" method="POST">
Clean Cache, Are You Sure ? : <input type="submit" name="go" value="Yes, Clean Cache!!" />
</form>
<?php
echo "<br />\n";
if (isset($_POST['go']) && !empty($_POST['go'])) {
    echo '<span style="color: green;">Cache Cleaning Invoked.</span>';
    echo "\n";
}
?>

<hr />

<?php print_file_modes('Cache Files', $cache_files); ?>

</body>
</html>
