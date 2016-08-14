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
 * YakiBiki File/Directory Permission Checker
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: check-perms.php 442 2008-11-15 13:20:14Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/yb_local.php');

if (!_YB('check.support.scripts')) {
    echo "check support scripts is prohibited.";
    exit();
}

function check_dir_writable($dirname)
{
    if (empty($dirname)) {
        return false;
    }
    clearstatcache();
    $dummy_file = $dirname . DIRECTORY_SEPARATOR . 'dummy';
    return check_file_writable($dummy_file);
}

function check_file_writable($filename)
{
    clearstatcache();
    if (file_exists($filename)) {
        return is_writable($filename);
    } else {
        $ret = touch($filename);
        @unlink($filename);
        return $ret;
    }
}

$target_dirs = array();

$buf = _YB('xhwlay.bookmarkcontainer.params');
$target_dirs['Xhwlay data directory'] = $buf['dataDir'];
$target_dirs['session save path'] = _YB('session.save.path');
$target_dirs['Smarty template compile directory'] = _YB('dir.smarty.templates_c');
$target_dirs['Cache directory'] = _YB('dir.caches');

$_grain_configs = _YB('grain.configs');
$target_dirs['Grain data directory'] = $_grain_configs['grain.dir.grain'];
$target_dirs['Grain index directory'] = $_grain_configs['grain.dir.index'];
$target_dirs['Grain sequence directory'] = $_grain_configs['grain.dir.sequence'];
$target_dirs['Grain raw directory'] = $_grain_configs['grain.dir.raw'];

$target_dirs['Log directory for PEAR Log'] = dirname(_YB('log.out'));

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
<title>YakiBiki File/Directory Permission Checker</title>
</head>
<body>
<h1>YakiBiki File/Directory Permission Checker</h1>

<hr />

<h2>Directories</h2>
<table border="1">
<tr><th>config name</th><th>status</th><th>config dir</th><th>real path</th></tr>
<?php foreach ($target_dirs as $desc => $target_dir) : ?>
<tr>
<th align="right"><?php echo $desc ?></th>
<?php
$rp = realpath($target_dir);
if (check_dir_writable($rp)) {
    echo '<td><span style="color: green">Writable : OK</span></td>';
} else {
    echo '<td><span style="color: red">Not Writable : NG</span></td>';
}
?>
<td><?php echo $target_dir ?></td>
<td><?php echo $rp ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
