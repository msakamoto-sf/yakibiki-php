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

/**
 * YakiBiki: edit data info module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 558 2009-07-21 07:51:29Z msakamoto-sf $
 */

yb_Session::start();
$uc = yb_Session::user_context();

$did = (integer)yb_Var::request('id');
$dao_data =& yb_dao_Factory::get('data');
$datas = $dao_data->find_by_id($did);
if (count($datas) != 1) {
    yb_Util::forward_error_die(
        t('data (ID=%id) is not found.', array('id' => $did)), 
        null, 404);
}
$virtual = $datas[0];

$allowed_acls = yb_AclCache::evaluate($uc['id'], YB_ACL_PERM_READ);
$allow_readable = in_array($virtual['acl'], $allowed_acls);
$allow_update = (in_array('sys', $uc['role'])) || $virtual['owner'] == $uc['id'];

if (!$allow_update && !$allow_readable) {
    yb_Util::forward_error_die(
        t("You don't have any permission to access specified data (ID=%id).", 
        array('id' => $did)), 
        null, 403);
}

$errors = array();
$new_version = yb_Var::request('current_version');
$delete_version = yb_Var::request('delete');
$moderate_version = yb_Var::request('moderate_version');
if (!empty($new_version)) {
    if ($allow_update) {
        yb_Util::ticket_is_valid_or_error_die();
        if ($buf = _change_current_version($virtual, $new_version, $errors)) {
            $virtual = $buf;
        }
    } else {
        $errors[] = t("You don't have permission to change current version.");
    }
} else if (!empty($delete_version)) {
    if ($allow_update) {
        yb_Util::ticket_is_valid_or_error_die();
        if ($buf = _delete_version($virtual, $delete_version, $errors)) {
            $virtual = $buf;
        }
    } else {
        $errors[] = t("You don't have permission to delete versions.");
    }
} else if (!empty($moderate_version)) {
    if ($allow_update) {
        yb_Util::ticket_is_valid_or_error_die();
        _moderate_version($virtual, $moderate_version, $errors);
    } else {
        $errors[] = t("You don't have permission to moderate versions.");
    }
}

$dao_version =& yb_dao_Factory::get('version');
$_versions = $dao_version->find_by_id($virtual['versions'], 'id', ORDER_BY_DESC);
if ($allow_update) {
    $versions = $_versions;
} else {
    $versions = array();
    foreach ($_versions as $_v) {
        if ($_v['approved']) {
            $versions[] = $_v;
        }
    }
}

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle($virtual['title'] . ' : ' . t('Version History'));
$renderer->set('user_context', $uc);
$renderer->set('data', $virtual);
$renderer->set('versions', $versions);
$renderer->set('errors', $errors);
$renderer->set('allow_update', $allow_update);

list($ticket_form, $ticket_id) = yb_Util::issue_ticket();
$renderer->set('ticket_form', $ticket_form);
$renderer->set('ticket_id', $ticket_id);

$renderer->setViewName('theme:modules/history/history_tpl.html');
$output = $renderer->render();

echo $output;

// {{{ _change_current_version()

function _change_current_version($data, $new_version, &$errors)
{
    if (!preg_match('/^\d+$/mi', $new_version)) {
        $errors[] = t("version number MUST be integer.");
        return null;
    }

    $dao_data =& yb_dao_Factory::get('data');
    $dao_version =& yb_dao_Factory::get('version');
    $id = $data['id'];

    // forcely cast to integer.
    $new_vnum = (integer)$new_version;

    $versions = $dao_version->find_by_id($data['versions']);
    $new_version_id = null;
    $vinfo = null;
    foreach ($versions as $_v) {
        if ($_v['version'] == $new_vnum) {
            $new_version_id = $_v['id'];
            $vinfo = $_v;
        }
    }
    if (empty($new_version_id)) {
        $errors[] = t("Given new current version [%version] is not existed version.", 
            array('version' => $new_vnum));
        return null;
    }
    if (!$vinfo['approved']) {
        $errors[] = t("Given new current version [%version] is not approved yet.", 
        array('version' => $new_vnum));
        return null;
    }

    $updates = array('current_version' => $new_version_id);

    $old_updated_at = $data['updated_at'];
    $dao_data->update($id, $updates);
    $datas = $dao_data->find_by_id($id);
    $new_updated_at = $datas[0]['updated_at'];

    $log =& yb_Log::get_Logger();
    $log->info(sprintf('history change_current_version success: data_id=%d, new current_version_id=%d', 
        $id, $new_version_id));

    // refresh data_by_updated
    $idx =& grain_Factory::index('datetime', 'data_by_updated');
    $idx->delete($id, $old_updated_at);
    $idx->append($id, $new_updated_at);

    if ($data['type'] == 'text' && $data['format'] == 'wiki') {
        $cache =& new yb_Cache(YB_WIKI_CACHE_GROUP);
        $cache->remove($id, YB_WIKI_CACHE_GROUP);
    }

    yb_Session::set_flash('info', 
        t("Your changes has been saved successfully."));

    return $datas[0];
}

// }}}
// {{{ _delete_version()

function _delete_version($data, $delete_version, &$errors)
{
    if (!preg_match('/^\d+$/mi', $delete_version)) {
        $errors[] = t("version number MUST be integer.");
        return;
    }

    $dao_data =& yb_dao_Factory::get('data');
    $dao_version =& yb_dao_Factory::get('version');
    $id = $data['id'];

    // forcely cast to integer.
    $delete_vnum = (integer)$delete_version;

    $versions = $dao_version->find_by_id($data['versions']);
    $delete_version_id = null;
    $vinfo = null;
    foreach ($versions as $_v) {
        if ($_v['version'] == $delete_vnum) {
            $delete_version_id = $_v['id'];
            $vinfo = $_v;
        }
    }
    if (empty($delete_version_id)) {
        $errors[] = t("Given version [%version] is not existed version.", 
            array('version' => $delete_version));
        return null;
    }
    if ($data['current_version'] == $delete_version_id) {
        $errors[] = t("You can't delete current version [%version].", 
            array('version' => $delete_version));
        return;
    }

    $_versions = $data['versions'];
    $_new_versions = array();
    foreach ($_versions as $_v) {
        if ($_v == $delete_version_id) {
            continue;
        }
        $_new_versions[] = $_v;
    }

    $dao_version->delete($delete_version_id);

    $updates = array('versions' => $_new_versions);

    $old_updated_at = $data['updated_at'];
    $dao_data->update($id, $updates);
    $datas = $dao_data->find_by_id($id);
    $new_updated_at = $datas[0]['updated_at'];

    $log =& yb_Log::get_Logger();
    $log->info(sprintf('history delete_version success: data_id=%d, deleted_version_id=%d', 
        $id, $delete_version_id));

    // refresh data_by_updated
    $idx =& grain_Factory::index('datetime', 'data_by_updated');
    $idx->delete($id, $old_updated_at);
    $idx->append($id, $new_updated_at);

    if ($data['type'] == 'text' && $data['format'] == 'wiki') {
        $cache =& new yb_Cache(YB_WIKI_CACHE_GROUP);
        $cache->remove($id, YB_WIKI_CACHE_GROUP);
    }

    yb_Session::set_flash('info', 
        t('Specified version [%version] was deleted successfully.', 
        array('version' => $delete_version))
    );

    return $datas[0];
}

// }}}
// {{{ _moderate_version()

function _moderate_version($data, $moderate_version, &$errors)
{
    if (!preg_match('/^\d+$/mi', $moderate_version)) {
        $errors[] = t("version number MUST be integer.");
        return;
    }

    $dao_version =& yb_dao_Factory::get('version');

    // forcely cast to integer.
    $moderate_vnum = (integer)$moderate_version;

    $versions = $dao_version->find_by_id($data['versions']);
    $moderate_version_id = null;
    $vinfo = null;
    foreach ($versions as $_v) {
        if ($_v['version'] == $moderate_vnum) {
            $moderate_version_id = $_v['id'];
            $switch_approved = !$_v['approved'];
            $vinfo = $_v;
        }
    }
    if (empty($moderate_version_id)) {
        $errors[] = t("Given moderate version [%version] is not existed version.", 
            array('version' => $moderate_vnum));
        return;
    }
    if ($vinfo['id'] == $data['current_version']) {
        $errors[] = t("You can't switch version approved status which is specified as current version.");
        return;
    }

    $updates = array('approved' => $switch_approved);
    $dao_version->update($moderate_version_id, $updates);

    $log =& yb_Log::get_Logger();
    $log->info(sprintf('history moderate_version success: data_id=%d, version_id=%d, approved=%d', 
        $data['id'], $moderate_version_id, $switch_approved));

    yb_Session::set_flash('info', 
        t("Your changes has been saved successfully."));
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
