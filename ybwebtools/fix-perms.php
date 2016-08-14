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
 * YakiBiki File/Directory Permission Adjuster
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: fix-perms.php 487 2008-12-14 13:06:06Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/yb_local.php');

if (!_YB('check.support.scripts')) {
    echo "check support scripts is prohibited.";
    exit();
}

$curr_umask = umask();
$pfile_default = 0666 & ~$curr_umask;
$pdir_default = 0777 & ~$curr_umask;

$pfile_new = $pfile_default;
$pdir_new = $pdir_default;
if (isset($_POST['pfile'])) {
    $pfile_new = octdec($_POST['pfile']);
}
if (isset($_POST['pdir'])) {
    $pdir_new = octdec($_POST['pdir']);
}

$results = array();
function _fix_perms_entry($e)
{
    global $results, $pfile_new, $pdir_new;
$r = false;
    if (is_dir($e)) {
        $r = chmod($e, $pdir_new);
    } else {
        $r = chmod($e, $pfile_new);
    }
    $results[$e] = (boolean)$r;
    return $e;
}

$_callback = (isset($_POST['go']) && !empty($_POST['go'])) 
    ? '_fix_perms_entry' : '';

$xhwlay_files = array();
$buf = _YB('xhwlay.bookmarkcontainer.params');
find_files($buf['dataDir'], $xhwlay_files, $_callback);

$_grain_configs = _YB('grain.configs');
$grain_files = array();
find_files($_grain_configs['grain.dir.grain'], $grain_files, $_callback);
$index_files = array();
find_files($_grain_configs['grain.dir.index'], $index_files, $_callback);
$seq_files = array();
find_files($_grain_configs['grain.dir.sequence'], $seq_files, $_callback);
$raw_files = array();
find_files($_grain_configs['grain.dir.raw'], $raw_files, $_callback);

$log_files = array();
find_files(dirname(_YB('log.out')), $log_files, $_callback);
$templates_files = array();
find_files(_YB('dir.smarty.templates_c'), $templates_files, $_callback);
$cache_files = array();
find_files(_YB('dir.caches'), $cache_files, $_callback);

function print_file_entries($title, $list)
{
    global $results;
    $html = <<<HTML
<h2>{$title}</h2>
<table border="1">
<tr><th>file/dir</th><th>mode</th></tr>
HTML;
    foreach ($list as $entry) {

        $html .= '<tr>' . PHP_EOL;
        $html .= '<td>' . $entry . '</td>' . PHP_EOL;
        $html .= '<td>';
        $_perm = sprintf("%o", fileperms($entry));
        if (isset($results[$entry])) {
            $_perm .= ", " . okng($results[$entry]);
        }
        $html .= $_perm . '</td>' . PHP_EOL;
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
<title>YakiBiki File/Directory Permission Fix</title>
</head>
<body>
<h1>YakiBiki File/Directory Permission Fix</h1>

<form action="" method="POST">
<table>
<tr><th>Current umask value : </th><td><?php printf("%o", $curr_umask); ?></td></tr>
<tr><th>Default File Permission : </th><td><?php printf("%o", $pfile_default); ?></td></tr>
<tr><th>Default Directory Permission : </th><td><?php printf("%o", $pdir_default); ?></td></tr>
<tr><th>NEW File Permission : </th><td><input type="text" name="pfile" value="<?php echo decoct($pfile_new); ?>" /></td></tr>
<tr><th>NEW Directory Permission: </th><td><input type="text" name="pdir" value="<?php echo decoct($pdir_new); ?>" /></td></tr>
</table>
<input type="submit" name="go" value="Okay, fix permissions." />
</form>
<?php
echo "<br />\n";
if (isset($_POST['go']) && !empty($_POST['go'])) {
    echo '<span style="color: green;">PERMISSION FIX INVOKED.</span>';
    echo "\n";
}
?>

<hr />

<?php print_file_entries('Xhwlay Data Files', $xhwlay_files); ?>

<?php print_file_entries('grain Files', $grain_files); ?>

<?php print_file_entries('index Files', $index_files); ?>

<?php print_file_entries('sequence Files', $seq_files); ?>

<?php print_file_entries('raw Files', $raw_files); ?>

<?php print_file_entries('Log Files', $log_files); ?>

<?php print_file_entries('Smarty Compiled Files', $templates_files); ?>

<?php print_file_entries('Cache Files', $cache_files); ?>

</body>
</html>
