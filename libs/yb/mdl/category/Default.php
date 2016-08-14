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
 * requires
 */
require_once('yb/tx/category/Finder.php');
require_once('yb/tx/category/Create.php');
require_once('yb/tx/category/Update.php');
require_once('yb/tx/category/Delete.php');
require_once('yb/mdl/category/Pager.php');

/**
 * YakiBiki category module default page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Default.php 507 2009-01-06 05:59:54Z msakamoto-sf $
 */
class yb_mdl_category_Default
{
    // {{{ list_page()

    function list_page(&$runner, $page, &$bookmark, $params)
    {
        $navi =& new yb_mdl_category_Pager();
        $params = $navi->setup();

        $categories = yb_tx_category_Finder::all($params['sb'], $params['ob']);
        $navi->itemData($categories);
        $navi_datas= $navi->build();

        $pager = $navi_datas['pager'];
        $navi_datas['links'] = $pager->getLinks();
        $page_datas = $pager->getPageData();

        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Category Manager'));
        $renderer->set('navi', $navi_datas);
        $renderer->set('categories', $page_datas);

        // for category module special : embed uniqid.
        $uniq = md5(uniqid(rand(), true));
        yb_Session::set('uniqid', $uniq);
        $renderer->set('uniqid', $uniq);

        return "theme:modules/category/list_tpl.html";
    }

    // }}}
    // {{{ finish_page()

    function finish_page(&$runner, $page, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $renderer->setTitle(
            t('Category Manager') . ' (' . t('Operation Completed') . ')');
        $renderer->set('results', $bookmark->get('operation_result'));

        return "theme:modules/category/finish_tpl.html";
    }

    // }}}
    // {{{ on_create()

    function on_create(&$runner, $event, &$bookmark, $params)
    {
        $cat = yb_Var::post('name');
        $uc = yb_Session::user_context();

        $result = yb_tx_category_Create::go($uc['id'], $cat);

        $log =& yb_Log::get_Logger();
        $log->info(sprintf('category create success: id=%d, name=%s', 
            $result['id'], $result['name']));

        $results = array();
        $results[] = array(
            'data' => $result,
            'operation' => t('create'),
            'result' => t('success'),
        );

        $bookmark->set('operation_result', $results);
        return "success";
    }

    // }}}
    // {{{ on_save()

    function on_save(&$runner, $event, &$bookmark, $params)
    {
        $targets = $bookmark->get('targets');

        // backup targets's old records.
        $cids = array_keys($targets);
        sort($cids);
        $cinfos = yb_tx_category_Finder::by_id($cids);
        foreach ($cinfos as $cinfo) {
            $cid = $cinfo['id'];
            $targets[$cid]['old'] = $cinfo;
        }

        $log =& yb_Log::get_Logger();
        $results = array();
        foreach ($targets as $id => $op) {
            $data = $op['data'];
            $_op = $op['operation'];
            $ret = null;
            if ($_op == 'update') {
                $ret = yb_tx_category_Update::go($id, $data['name']);
                if ($ret) {
                    $new_datas = yb_tx_category_Finder::by_id($id);
                    $new = @$new_datas[0];
                    // overwrite data by new data for showing op result.
                    $op['data'] = $new;

                    $log->info(sprintf(
                        'category update success: id=%d, name=%s',
                        $id, $new['name']));

                }
            } else if ($_op == 'delete') {
                $ret = yb_tx_category_Delete::go($id);
                // overwrite data by old data for showing op result.
                $op['data'] = $op['old'];

                $log->info(sprintf(
                    'category delete success: id=%d, name=%s',
                    $id, $data['name']));

            } else {
                continue;
            }
            $op['result'] = ($ret) ? t('success') : t('failure');
            $op['operation'] = t($op['operation']);
            $results[] = $op;
        }

        $bookmark->set('operation_result', $results);
        return "success";
    }

    // }}}
    // {{{ guard_on_validate_create()

    function guard_on_validate_create(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $uniqid = yb_Var::post('uniqid');
        if (yb_Session::get('uniqid') != $uniqid) {
            return false;
        }
        $cat = yb_Var::post('name');
        if (empty($cat)) {
            $errors[] = t('Category name is required');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $categories = yb_tx_category_Finder::all();
        foreach ($categories as $c) {
            if ($c['name'] == $cat) {
                $errors[] = t(
                    "Given category name (%name) has been already used.", 
                    array('name' => $cat));
                $renderer->set('validate_errors', $errors);
                return false;
            }
        }

        return true;
    }

    // }}}
    // {{{ guard_on_validate_save()

    function guard_on_validate_save(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();
        $c2d =& grain_Factory::index('pair', 'category_to_data');

        $uniqid = yb_Var::post('uniqid');
        if (yb_Session::get('uniqid') != $uniqid) {
            return false;
        }

        $categories = yb_Var::request('name');
        $ops = yb_Var::request('op');

        $targets = array();
        foreach ($ops as $id => $op) {
            $_c = @$categories[$id];
            if ($op != 'update' && $op != 'delete') {
                continue;
            }
            if (!yb_mdl_category_Default::_on_save_validate_params(
                $id, $_c, $op, $errors)) {
                $renderer->set('validate_errors', $errors);
                return false;
            }
            if ($op == 'delete') {
                $cnt = $c2d->count_for($id);
                if ($cnt > 0) {
                    $errors[] = t("There're more than one pages assigned to category [ %name ]. Reject delete operation.", 
                        array('name' => $_c));
                    $renderer->set('validate_errors', $errors);
                    return false;
                }
            }
            $targets[$id] = array(
                'data' => array('name' => $_c),
                'operation' => $op,
                'result' => null,
            );
        }
        if (count($targets) == 0) {
            $errors[] = t("No records are checked as update/delete.");
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $bookmark->set('targets', $targets);
        return true;
    }

    // }}}
    // {{{ _on_save_validate_params()

    function _on_save_validate_params($id, $category, $op, &$errors)
    {
        $dao =& yb_dao_Factory::get('category');
        $categories = $dao->find_all();

        // check category id is correct
        if (!preg_match('/^\d+$/mi', $id)) {
            $errors[] = t("Invalid Category ID.");
            return false;
        }
        $_categories = $dao->find_by_id($id);
        if (count($_categories) != 1) {
            $errors[] = t("Invalid Category ID.");
            return false;
        }

        // check name is not empty and not duplicated.
        $category = trim((string)$category);
        if (empty($category)) {
            $errors[] = t("Category name is required");
            return false;
        }
        if ($op != 'delete') {
            foreach ($categories as $c) {
                if ($c['name'] == $category && $c['id'] != $id) {
                    $errors[] = t(
                        "Given category name (%name) has been already used.", 
                        array('name' => $category));
                    return false;
                }
            }
        }

        return true;
    }

    // }}}
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
