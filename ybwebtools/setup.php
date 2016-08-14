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
 * YakiBiki Initial Setup Script
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: setup.php 495 2009-01-04 04:38:56Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/yb_local.php');
require_once('System.php');
require_once('yb/tx/data/New.php');

if (!_YB('setup.menu')) {
    echo "setup menu is prohibited.";
    exit();
}

$errors = array();
$values = array();
$ok = null;

if (strtolower(yb_Var::env('REQUEST_METHOD')) == 'post' &&
    validate($errors, $values)) {

    destroy_olddatas();
    $ok = setup($errors, $values);
}

?>
<html>
<head>
<title>YakiBiki Initial Setup Script</title>
</head>
<body>
<h1>YakiBiki Initial Setup Script</h1>
<!-- {{{ result -->
<?php if (is_bool($ok)) : ?>
<h2>Result</h2>
<?php if ($ok === true) : ?>
<span style="color: green;">OK</span><br />
Go to <a href="<?php echo yb_Util::make_url(array('mdl' => 'login')) ?>">login</a> page.
<?php else : ?>
<span style="color: red;">NG</span><br />
See Log files for error detail.
<?php endif; ?>
<?php endif; ?>
<!-- }}} -->
<!-- {{{ validate errors -->
<?php if (count($errors) != 0): ?>
<ul>
<?php foreach ($errors as $e) : ?>
<li style="color: red;"><?php echo $e ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<!-- }}} -->
<!-- {{{ main configuration -->
<h2>Please confirm your YakiBiki's main configurations : </h2>
<table border="1">
<tr>
<th>Initiali data directory</th>
<td><?php echo realpath(_YB('dir.initdata')) ?></td>
</tr>
<tr>
<th>Default MIME-Type</th>
<td><?php echo _YB('default.mime.type') ?></td>
</tr>
<tr>
<th>Default Time Zone</th>
<td><?php echo _YB('default.timezone') ?></td>
</tr>
<tr>
<th>Default Locale</th>
<td><?php echo _YB('resource.locale') ?></td>
</tr>
<tr>
<th>Main Title</th>
<td><?php echo _YB('title') ?></td>
</tr>
<tr>
<th>Theme</th>
<td><?php echo _YB('theme') ?></td>
</tr>
<tr>
<th>Top URL</th>
<td><?php echo _YB('url') ?></td>
</tr>
<tr>
<th>Entry Point PHP</th>
<td><?php echo _YB('index_file') ?></td>
</tr>
<tr>
<th>Theme URL</th>
<td><?php echo _YB('url.themes') ?></td>
</tr>
<tr>
<th>PHP's include_path</th>
<td><?php echo get_include_path() ?></td>
</tr>
<tr>
<th>data directory</th>
<td>
<?php
$ar = _YB('grain.configs');
$dirs = array(
    $ar['grain.dir.grain'],
    $ar['grain.dir.index'],
    $ar['grain.dir.sequence'],
    $ar['grain.dir.raw'],
    );
echo implode('<br />', $dirs);
?>
</td>
</tr>
<tr>
<th>cache directory</th>
<td><?php echo _YB('dir.caches') ?></td>
</tr>
</table>
<!-- }}} -->
<!-- {{{ input system administrator information -->
<h2>Input your YakiBiki's system administrator information : </h2>
<form method="POST">
<table border="1" cellpadding="5">
<tr>
<th>mail address</th>
<td><input type="text" name="mail" value="" size="64"></td>
</tr>
<tr>
<th>name</th>
<td><input type="text" name="name" value="" size="64"></td>
</tr>
<tr>
<th>password</th>
<td><input type="password" name="password" value="" size="64"></td>
</tr>
<tr>
<th>password<br />(confirm)</th>
<td><input type="password" name="password2" value="" size="64"></td>
</tr>
</table>
<input type="submit" value="setup" />
</form>
<!-- }}} -->
</body>
</html>
<?php
// {{{ validate()

function validate(&$errors, &$values)
{
    $val = trim((string)yb_Var::post('mail'));
    if (empty($val)) {
        $errors[] = 'mail address is required.';
        return false;
    }
    $values['mail'] = $val;

    $val = trim((string)yb_Var::post('name'));
    if (empty($val)) {
        $errors[] = 'name is required.';
        return false;
    }
    $values['name'] = $val;

    $val = trim((string)yb_Var::post('password'));
    $val2 = trim((string)yb_Var::post('password2'));
    if (empty($val)) {
        $errors[] = 'password is required.';
        return false;
    }
    if ($val !== $val2) {
        $errors[] = 'password is not confirmed.';
        return false;
    }
    $values['password'] = $val;


    $seq =& grain_Factory::sequence('user');
    $cur = $seq->current();
    if ($cur > 0) {
        $errors[] = 'Already YakiBiki existed. aborted setup.';
        return false;
    }

    return true;
}

// }}}
// {{{ destroy_olddatas()

function destroy_olddatas()
{
    $dirs = array(
        'grain' => grain_Config::get('grain.dir.grain'),
        'index' => grain_Config::get('grain.dir.index'),
        'sequence' => grain_Config::get('grain.dir.sequence'),
        'raw' => grain_Config::get('grain.dir.raw'),
    );

    foreach ($dirs as $k => $v) {
        $pat = realpath($v) . '/*';
        $l = glob($pat);
        foreach ($l as $t) {
            $t = realpath($t);
            if (is_dir($t)) {
                System::rm(" -rf {$t}");
            } else {
                @unlink($t);
            }
        }
    }
}

// }}}
// {{{ setup()

function setup(&$errors, $values)
{
    // add system administrator user
    $dao_user =& yb_dao_Factory::get('user');
    $sysuser = array(
        'mail' => $values['mail'],
        'name' => $values['name'],
        'password' => yb_Util::hash_password($values['password']),
        'status' => YB_USER_STATUS_OK,
        'role' => array('sys'),
    );
    $sysuser['id'] = $dao_user->create($sysuser);
    if (empty($sysuser['id'])) {
        $errors[] = 'system administrator user creation failure.';
        return false;
    }

    _YB('setup.tmp.sysuser.id', $sysuser['id']);
    require_once(_YB('dir.initdata') . '/datas.php');

    // add default groups
    $dao_group =& yb_dao_Factory::get('group');
    foreach ($YB_INIT_DATA['group'] as $k => $v) {
        $r = $dao_group->create($v);
        if (empty($r)) {
            $errors[] = "group : {$v['name']} creation failure.";
            return false;
        }
        $YB_INIT_DATA['group'][$k]['id'] = $r;
    }

    // add default categories
    $dao_category =& yb_dao_Factory::get('category');
    foreach ($YB_INIT_DATA['category'] as $k => $v) {
        $r = $dao_category->create($v);
        if (empty($r)) {
            $errors[] = "category : {$v['name']} creation failure.";
            return false;
        }
        $YB_INIT_DATA['category'][$k]['id'] = $r;
    }

    // add default acls
    $dao_acl =& yb_dao_Factory::get('acl');
    foreach ($YB_INIT_DATA['acl'] as $k => $v) {
        $r = $dao_acl->create($v);
        if (empty($r)) {
            $errors[] = "acl : {$v['name']} creation failure.";
            return false;
        }
        $YB_INIT_DATA['acl'][$k]['id'] = $r;
    }

    $acl_id_publish = $YB_INIT_DATA['acl']['publish']['id'];
    $acl_id_draft = $YB_INIT_DATA['acl']['draft']['id'];

    // add default typical templates
    $dao_template =& yb_dao_Factory::get('template');

    // typical diary type text template
    $raw =& grain_Factory::raw('template');
    $raw_seq =& grain_Factory::sequence('template_raw');
    $raw_id = $raw_seq->next();
    if (!$raw->save($raw_id, '')) {
        $errors[] = "template : 'diary' text data creation failure.";
        return false;
    }

    $t = array(
        'owner' => $sysuser['id'],
        'name' => setupT('Diary'),
        'title' => setupT('Diary/{$now_year}/{$now_month}/{$now_day}/'),
        'acl' => $acl_id_publish,
        'categories' => array(),
        'is_versions_moderated' => false,
        'is_comments_moderated' => true,
        'type' => 'text',
        'format' => 'wiki',
        'raw_id' => $raw_id
    );
    $r = $dao_template->create($t);
    if (empty($r)) {
        $errors[] = "template : 'diary' creation failure.";
        return false;
    }

    // typical draft type text template
    $raw_id = $raw_seq->next();
    if (!$raw->save($raw_id, '')) {
        $errors[] = "template : 'draft' text data creation failure.";
        return false;
    }

    $t = array(
        'owner' => $sysuser['id'],
        'name' => setupT('Draft'),
        'title' => setupT('Draft/{$now_year}/{$now_month}/{$now_day}/'),
        'acl' => $acl_id_draft,
        'categories' => array(),
        'is_versions_moderated' => false,
        'is_comments_moderated' => true,
        'type' => 'text',
        'format' => 'wiki',
        'raw_id' => $raw_id
    );
    $r = $dao_template->create($t);
    if (empty($r)) {
        $errors[] = "template : 'draft' creation failure.";
        return false;
    }

    // typical image type template
    $t = array(
        'owner' => $sysuser['id'],
        'name' => setupT('Images'),
        'title' => setupT('Images/{$now_year}/{$now_month}/{$now_day}/{$now_hhmmss}/{$upload_filename}'),
        'acl' => $acl_id_publish,
        'categories' => array($YB_INIT_DATA['category']['images']['id']),
        'is_versions_moderated' => false,
        'is_comments_moderated' => true,
        'type' => 'image',
    );
    $r = $dao_template->create($t);
    if (empty($r)) {
        $errors[] = "template : 'images' creation failure.";
        return false;
    }

    // typical file upload type template
    $t = array(
        'owner' => $sysuser['id'],
        'name' => setupT('Files'),
        'title' => setupT('Files/{$now_year}/{$now_month}/{$now_day}/{$now_hhmmss}/{$upload_filename}'),
        'acl' => $acl_id_publish,
        'categories' => array($YB_INIT_DATA['category']['files']['id']),
        'is_versions_moderated' => false,
        'is_comments_moderated' => true,
        'type' => 'attach',
    );
    $r = $dao_template->create($t);
    if (empty($r)) {
        $errors[] = "template : 'files' creation failure.";
        return false;
    }

    // typical bookmark type template
    $t = array(
        'owner' => $sysuser['id'],
        'name' => setupT('Bookmarks'),
        'title' => setupT('Bookmarks/{$now_year}/{$now_month}/{$now_day}/{$now_hhmmss}/'),
        'acl' => $acl_id_publish,
        'categories' => array($YB_INIT_DATA['category']['bookmarks']['id']),
        'is_versions_moderated' => false,
        'is_comments_moderated' => true,
        'type' => 'bookmark',
    );
    $r = $dao_template->create($t);
    if (empty($r)) {
        $errors[] = "template : 'bookmarks' creation failure.";
        return false;
    }

    // add default frontpage, sidebar, and help wikis
    $wikis = $YB_INIT_DATA['wiki'];
    $wiki_dir = _YB('dir.initdata') . '/' . _YB('resource.locale');
    foreach ($wikis as $wiki) {
        $data_file = realpath($wiki_dir . '/' . $wiki[0]);
        $title = $wiki[1];
        $wiki_data = file_get_contents($data_file);

        $t =& yb_Time::singleton();
        $data = array(
            'owner' => $sysuser['id'],
            'title' => $title,
            'acl' => $YB_INIT_DATA['acl']['publish']['id'],
            'categories' => array(),
            'is_versions_moderated' => true,
            'is_comments_moderated' => true,
            'published_at' => $t->getGMT(YB_TIME_FMT_INTERNAL_RAW),
            'type' => 'text',
            'format' => 'wiki',
        );
        $result = yb_tx_data_New::go($data, $wiki_data);
        if (empty($result)) {
            $errors[] = "data : '{$title}' wiki data creation failure.";
            return false;
        }
    }

    return true;
}

// }}}

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
