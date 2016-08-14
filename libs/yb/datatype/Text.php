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
require_once('Text/Diff.php');

/**
 * YakiBiki Data Type Plugin : text type
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Text.php 546 2009-07-14 03:58:34Z msakamoto-sf $
 */
class yb_datatype_Text
{
    // {{{ title_create()

    function title_create()
    {
        return t('New Text Document');
    }

    // }}}
    // {{{ title_create_img()

    function title_create_img()
    {
        return 'text_add';
    }

    // }}}
    // {{{ title_copy()

    function title_copy($base)
    {
        return t("Copy Text Document from %original", 
            array('original' => $base['display_title']));
    }

    // }}}
    // {{{ title_copy_img()

    function title_copy_img()
    {
        return 'text_add';
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
        $ret = '';
        $src = file_get_contents($filepath);

        // if display and current version is not same, 
        // don't use wiki cache.
        $_d = $ctx->get('display_version_id');
        $_c = $ctx->get('current_version_id');
        $use_wiki_cache = ($_d == $_c);

        $ret = yb_Wiki::convert(
            $ctx->get('id'),
            $ctx->get('name'),
            $src, $ctx, $mode, $use_wiki_cache);

        $raw_url = yb_Util::make_url(array(
            'mdl' => 'raw', 'id' => $display_did));
        $ret .= "<br /><a href=\"{$raw_url}\" style=\"font-size: small\">";
        $ret .= t('download as plain text') . "</a>";

        return $ret;
    }

    // }}}
    // {{{ raw()

    function raw($ctx, $filepath)
    {
        $log =& yb_Log::get_logger();

        $did = $ctx->get('id');
        $params = array(
            'file' => $filepath,
            //'cache' => '', // whether to allow cs caching
            //'lastmodified' => '', // unix timestamp
            'contenttype' => 'text/plain', 
            'contentdisposition' => array(
                HTTP_DOWNLOAD_ATTACHMENT, "{$did}.txt"), 
            //'cachecontrol'=> '', // cache privacy and validity
        );
        $result = HTTP_Download::staticSend($params);

        if (PEAR::isError($result)) {
            $header =& new HTTP_Header();
            $header->sendStatusCode(500);
            $log->err(var_export($result, true));
            return;
        }
    }

    // }}}
    // {{{ diff()

    function diff(
        $did, 
        $old_v, $old_display_name, $old_filename, 
        $new_v, $new_display_name, $new_filename)
    {
        $old_ln = @file($old_filename);
        $new_ln = @file($new_filename);

        $diff_types = array(
            'unified' => 'unified',
            'inline' => 'inline',
            'context' => 'context');
        $text_diff = yb_Var::request('text_diff');
        if (!isset($diff_types[$text_diff])) {
            $text_diff = 'unified';
        } else {
            $text_diff = $diff_types[$text_diff];
        }
        $req = 'Text/Diff/Renderer/' . $text_diff . '.php';
        $klass = 'Text_Diff_Renderer_' . $text_diff;
        require_once($req);

        $differ =& new Text_Diff($old_ln, $new_ln);
        $diff_renderer =& new $klass();
        $body = $diff_renderer->render($differ);
        if ('inline' == $text_diff) {
            $body = str_replace("'", '&#039', $body);
        } else {
            $body = h($body);
        }
        $diff = "<h4>version {$old_v} and {$new_v}</h4>" . PHP_EOL
            . '<hr /><div id="diff">'
            . '<pre>' . $body . '</pre></div><hr /><br />';
        return $diff;
    }

    // }}}
    // {{{ format_template_data_on_create_input()

    function format_template_data_on_create_input(&$data)
    {
        if (!isset($data['textdata'])) {
            $data['textdata'] = '';
        }
    }

    // }}}
    // {{{ format_template_data_on_update_delete()

    function format_template_data_on_update_delete(&$data)
    {
        $raw_id = $data['raw_id'];
        $raw =& grain_Factory::raw('template');
        $filepath = $raw->filename($data['raw_id']);

        $data['textdata'] = file_get_contents($filepath);
    }

    // }}}
    // {{{ validate_template_data()

    function validate_template_data(&$errors, &$virtual)
    {
        $textdata = yb_Var::request('textdata');
        $virtual['textdata'] = $textdata;

        return true;
    }

    // }}}
    // {{{ build_template_specific_form()

    function build_template_specific_form($template, &$smarty)
    {
        $smarty->assign('template', $template);
        $ret = $smarty->fetch('theme:datatypes/text/template_edit_tpl.html');
        $smarty->clear_assign(array('template'));

        return $ret;
    }

    // }}}
    // {{{ freeze_template_specific_form()

    function freeze_template_specific_form($template, &$smarty)
    {
        $smarty->assign('template', $template);
        $ret = $smarty->fetch('theme:datatypes/text/template_freeze_tpl.html');
        $smarty->clear_assign(array('template'));

        return $ret;
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

        $data['format'] = 'wiki'; // enforce text format to wiki.

        $textdata = $virtual['textdata'];
        $raw =& grain_Factory::raw('template');
        $raw_seq =& grain_Factory::sequence('template_raw');
        $raw_id = $raw_seq->next();
        $raw->save($raw_id, $textdata);
        $data['raw_id'] = $raw_id;

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

        $data['format'] = 'wiki'; // enforce text format to wiki.

        $textdata = $virtual['textdata'];
        $raw_id = $virtual['raw_id'];
        $raw =& grain_Factory::raw('template');
        $raw->save($raw_id, $textdata);

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
        $raw_id = $data['raw_id'];
        $raw =& grain_Factory::raw('template');
        $raw->delete($raw_id);

        $dao =& yb_dao_Factory::get('template');
        $dao->delete($id);
    }

    // }}}
    // {{{ format_new_data()

    function format_new_data(&$data, $template = null)
    {
        if (!is_null($template)) {
            $raw =& grain_Factory::raw('template');
            $filepath = $raw->filename($template['raw_id']);
            $data['textdata'] = @file_get_contents($filepath);
            return;
        }
        if (!isset($data['textdata'])) {
            $data['textdata'] = '';
        }
    }

    // }}}
    // {{{ format_edit_data()

    function format_edit_data(&$data)
    {
        $data['textdata'] = file_get_contents($data['_raw_filepath']);
    }

    // }}}
    // {{{ format_copy_data()

    function format_copy_data(&$data, $base)
    {
        $data['textdata'] = file_get_contents($base['_raw_filepath']);
    }

    // }}}
    // {{{ build_specific_form_on_new()

    function build_specific_form_on_new($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/text/new_tpl.html');
        $smarty->clear_assign(array('data'));

        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_edit()

    function build_specific_form_on_edit($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/text/edit_tpl.html');
        $smarty->clear_assign(array('data'));
        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_copy()

    function build_specific_form_on_copy($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $ret = $smarty->fetch('theme:datatypes/text/copy_tpl.html');
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

            $idx =& grain_Factory::index('match', 'data_by_title');
            $idx->case_sensitive(true);
            $data_ids = $idx->fullmatch($title);
            if (count($data_ids) > 0) {
                $errors[] = t(
                    'Title (%title) is already exist, duplicated.',
                    array('title' => $title));
                $ret = false;
            }
        }

        $textdata = yb_Var::request('textdata');
        $data['textdata'] = $textdata;

        return $ret;
    }

    // }}}
    // {{{ validate_edit_data()

    function validate_edit_data(&$errors, &$data)
    {
        $ret = true;

        $textdata = yb_Var::request('textdata');
        $data['textdata'] = $textdata;

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

            $idx =& grain_Factory::index('match', 'data_by_title');
            $idx->case_sensitive(true);
            $data_ids = $idx->fullmatch($title);
            if (count($data_ids) > 0) {
                $errors[] = t(
                    'Title (%title) is already exist, duplicated.',
                    array('title' => $title));
                $ret = false;
            }
        }

        $textdata = yb_Var::request('textdata');
        $data['textdata'] = $textdata;

        return $ret;
    }

    // }}}
    // {{{ create_data()

    function create_data($virtual)
    {
        $copied = array(
            'owner', 'title', 'acl', 'categories', 'type', 
            'published_at', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $data['format'] = 'wiki'; // enforce text format to wiki.

        $r = yb_tx_data_New::go($data, $virtual['textdata']);

        return $r['id'];
    }

    // }}}
    // {{{ update_data()

    function update_data($did, $virtual, $do_vup, $changelog, $uid)
    {
        $updates = array('format' => 'wiki'); // enforce text format to wiki.
        $raw = $virtual['textdata'];

        $cache =& new yb_Cache(YB_WIKI_CACHE_GROUP);
        $cache->remove($did, YB_WIKI_CACHE_GROUP);

        return yb_tx_data_Edit::go(
            $did, $updates, $raw, $do_vup, $changelog, $uid);
    }

    // }}}
    // {{{ copy_data()

    function copy_data($virtual, $base)
    {
        $copied = array(
            'owner', 'title', 'acl', 'categories', 'type', 
            'published_at', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $data['format'] = 'wiki'; // enforce text format to wiki.

        $r = yb_tx_data_New::go($data, $virtual['textdata']);

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
