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
 */

require_once('yb/tx/data/Edit.php');

/**
 * YakiBiki Data Type Plugin : bookmark-url type
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Bookmark.php 532 2009-06-21 03:33:33Z msakamoto-sf $
 */
class yb_datatype_Bookmark
{
    // {{{ title_create()

    function title_create()
    {
        return t('New Bookmark URL');
    }

    // }}}
    // {{{ title_create_img()

    function title_create_img()
    {
        return 'bookmark_add';
    }

    // }}}
    // {{{ title_copy()

    function title_copy($base)
    {
        return t("Copy Bookmark URL from %original", 
            array('original' => $base['display_title']));
    }

    // }}}
    // {{{ title_copy_img()

    function title_copy_img()
    {
        return 'bookmark_add';
    }

    // }}}
    // {{{ view()

    /**
     * Get view-mode html.
     *
     * @access public
     * @param yb_DataContext
     * @param string raw data file path
     * @param integer yb_Html::DETAIL_MODE() / LIST_MODE()
     * @param string data id for displaying (if specified)
     * @param string data name for displaying (if specified)
     * @return string html
     */
    function view($ctx, $filepath, $mode, 
        $display_did = null, $display_name = null)
    {
        if (is_null($display_did)) {
            $display_did = $ctx->get('id');
        }
        if (is_null($display_name)) {
            $display_name = $ctx->get('name');
        }

        $url = file_get_contents($filepath);
        $note = h($ctx->get('note'));
        $src = _YB('url.themes') . '/icons/new_window.png';
        $alt = t('view in another window');
        $img = '<img src="' . $src . '" alt="' . $alt . '" />&nbsp;';
        return $img . sprintf(
            '<a href="%s" alt="%s" title="%s" target="_blank">%s</a><br /><p>%s</p>',
            h($url), 
            h($display_name), h($display_name), h($url), $note);
    }

    // }}}
    // {{{ raw()

    function raw($ctx, $filepath)
    {
        echo file_get_contents($filepath);
    }

    // }}}
    // {{{ diff()

    function diff(
        $did, 
        $old_v, $old_display_name, $old_filename, 
        $new_v, $new_display_name, $new_filename)
    {
        $old_url = h(file_get_contents($old_filename));
        $new_url = h(file_get_contents($new_filename));

        $old_display_name = h($old_display_name);
        $new_display_name = h($new_display_name);

        $icon_src = _YB('url.themes') . '/icons/new_window.png';
        $icon_alt = t('view in another window');
        $icon_img = '<img src="' . $icon_src . '" alt="' . $icon_alt . '" />&nbsp;';

        $fmt = '<a href="%s" alt="%s" title="%s" target="_blank">%s&nbsp;:&nbsp;%s</a>';

        $diff = "<h4>version {$old_v} (old)</h4>" . PHP_EOL
            . sprintf($fmt, $old_url, $old_display_name, $old_display_name, 
                $icon_img, $old_display_name) . PHP_EOL;
        $diff .= '<hr />' . PHP_EOL;
        $diff .= "<h4>version {$new_v} (new)</h4>" . PHP_EOL
            . sprintf($fmt, $new_url, $new_display_name, $new_display_name, 
                $icon_img, $new_display_name) . PHP_EOL;
        $diff .= '<br /><br />' . PHP_EOL;

        return $diff;
    }

    // }}}
    // {{{ format_template_data_on_create_input()

    function format_template_data_on_create_input(&$data)
    {
    }

    // }}}
    // {{{ format_template_data_on_update_delete()

    function format_template_data_on_update_delete(&$data)
    {
    }

    // }}}
    // {{{ validate_template_data()

    function validate_template_data(&$errors, &$virtual)
    {
        return true;
    }

    // }}}
    // {{{ build_template_specific_form()

    function build_template_specific_form($template, &$smarty)
    {
        return '';
    }

    // }}}
    // {{{ freeze_template_specific_form()

    function freeze_template_specific_form($template, &$smarty)
    {
        return '';
    }

    // }}}
    // {{{ create_template_data()

    function create_template_data($virtual)
    {
        $copied = array(
            'owner', 'name', 'title', 'acl', 'categories', 'type', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $dao =& yb_dao_Factory::get('template');
        $id = $dao->create($data);
        $r = $dao->find_by_id($id);
        $data = $r[0];
        $this->format_template_data_on_update_delete($data);

        return $data;
    }

    // }}}
    // {{{ update_template_data()

    function update_template_data($id, $virtual)
    {
        // 'owner', 'type' are excluded when update.
        $copied = array(
            'name', 'title', 'acl', 'categories', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $dao =& yb_dao_Factory::get('template');
        $dao->update($id, $data);
        $r = $dao->find_by_id($id);
        $data = $r[0];
        $this->format_template_data_on_update_delete($data);

        return $data;
    }

    // }}}
    // {{{ delete_template_data()

    function delete_template_data($id, $data)
    {
        $dao =& yb_dao_Factory::get('template');
        $dao->delete($id);
    }

    // }}}
    // {{{ format_new_data()

    function format_new_data(&$data)
    {
    }

    // }}}
    // {{{ format_edit_data()

    function format_edit_data(&$data)
    {
        $data['url'] = file_get_contents($data['_raw_filepath']);
    }

    // }}}
    // {{{ format_copy_data()

    function format_copy_data(&$data, $base)
    {
        $data['note'] = $base['note'];
        $data['url'] = file_get_contents($base['_raw_filepath']);
    }

    // }}}
    // {{{ build_specific_form_on_new()

    function build_specific_form_on_new($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/bookmark/new_tpl.html');
        $smarty->clear_assign(array('data'));
        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_edit()

    function build_specific_form_on_edit($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/bookmark/edit_tpl.html');
        $smarty->clear_assign(array('data'));
        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_copy()

    function build_specific_form_on_copy($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/bookmark/copy_tpl.html');
        $smarty->clear_assign(array('data'));
        return $ret;
    }

    // }}}
    // {{{ validate_new_data()

    function validate_new_data(&$errors, &$data)
    {
        $ret = true;

        // title
        $title = trim(yb_Var::request('title'));
        if (empty($title)) {
            $errors[] = t('%label is required.', 
                array('label' => t('title')));
            $ret = false;
        } else {
            $data['title'] = $title;
        }

        // URL
        $url = trim(yb_Var::request('url'));
        if (empty($url)) {
            $errors[] = t('%label is required.', 
                array('label' => 'URL'));
            $ret = false;
        } else {
            $data['url'] = $url;
        }

        // note
        $data['note'] = trim(yb_Var::request('note'));

        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->case_sensitive(true);
        $data_ids = $idx->fullmatch($title);
        if (count($data_ids) > 0) {
            $errors[] = t(
                'Title (%title) is already exist, duplicated.',
                array('title' => $title));
            $ret = false;
        }

        return $ret;
    }

    // }}}
    // {{{ validate_edit_data()

    function validate_edit_data(&$errors, &$data)
    {
        $ret = true;

        // URL
        $url = trim(yb_Var::request('url'));
        if (empty($url)) {
            $errors[] = t('%label is required.', 
                array('label' => 'URL'));
            $ret = false;
        } else {
            $data['url'] = $url;
        }

        // note
        $data['note'] = trim(yb_Var::request('note'));

        return $ret;
    }

    // }}}
    // {{{ validate_copy_data()

    function validate_copy_data(&$errors, &$data)
    {
        $ret = true;

        // title
        $title = trim(yb_Var::request('title'));
        if (empty($title)) {
            $errors[] = t('%label is required.', 
                array('label' => t('title')));
            $ret = false;
        } else {
            $data['title'] = $title;
        }

        // URL
        $url = trim(yb_Var::request('url'));
        if (empty($url)) {
            $errors[] = t('%label is required.', 
                array('label' => 'URL'));
            $ret = false;
        } else {
            $data['url'] = $url;
        }

        // note
        $data['note'] = trim(yb_Var::request('note'));

        $idx =& grain_Factory::index('match', 'data_by_title');
        $idx->case_sensitive(true);
        $data_ids = $idx->fullmatch($title);
        if (count($data_ids) > 0) {
            $errors[] = t(
                'Title (%title) is already exist, duplicated.',
                array('title' => $title));
            $ret = false;
        }

        return $ret;
    }

    // }}}
    // {{{ create_data()

    function create_data($virtual)
    {
        $copied = array(
            'owner', 'title', 'acl', 'categories', 'type', 'note', 
            'published_at', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $raw_data = $virtual['url'];

        $r = yb_tx_data_New::go($data, $raw_data);

        return $r['id'];
    }

    // }}}
    // {{{ update_data()

    function update_data($did, $virtual, $do_vup, $changelog, $uid)
    {
        $copied = array('note');
        $updates = array();
        foreach ($copied as $c) {
            $updates[$c] = $virtual[$c];
        }

        $raw = $virtual['url'];

        return yb_tx_data_Edit::go(
            $did, $updates, $raw, $do_vup, $changelog, $uid);
    }

    // }}}
    // {{{ copy_data()

    function copy_data($virtual, $base)
    {
        $copied = array(
            'owner', 'title', 'acl', 'categories', 'type', 'note', 
            'published_at', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $r = yb_tx_data_New::go($data, $virtual['url']);

        return $r['id'];
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
