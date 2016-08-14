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
 * YakiBiki Data Type Plugin : image type
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Image.php 541 2009-06-21 08:07:48Z msakamoto-sf $
 */
class yb_datatype_Image
{
    var $acceptable_imagetypes = array(
        'image/jpeg' => 'image/jpeg',
        'image/gif' => 'image/gif',
        'image/png' => 'image/png',
    );

    var $ext2ct = array(
        'jpe' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
    );

    // {{{ title_create()

    function title_create()
    {
        return t('New Image File Upload');
    }

    // }}}
    // {{{ title_create_img()

    function title_create_img()
    {
        return 'image_add';
    }

    // }}}
    // {{{ title_copy()

    function title_copy($base)
    {
        return t("Copy Image File from %original", 
            array('original' => $base['display_title']));
    }

    // }}}
    // {{{ title_copy_img()

    function title_copy_img()
    {
        return 'image_add';
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

        // calculate default width attribute of image tag.
        $sizeinfo = getimagesize($filepath);
        if ($sizeinfo === false) {
            $_image_width = "";
        } else {
            $_w = $sizeinfo[0];
            $_dw = _YB('yb.view.image.width.default');
            $_image_width = sprintf('width="%d"', 
                ($_dw <= $_w) ? $_dw : $_w);
        }

        $display_name = h($display_name);
        $raw_url = yb_Util::make_url(
            array('mdl' => 'raw', 'id' => $display_did));
        return sprintf(
            '<a href="%s" title="%s" target="_blank">' 
            . '<img src="%s" alt="%s" title="%s" %s /></a>',
            $raw_url, $display_name, 
            $raw_url, $display_name, $display_name, $_image_width
        );
    }

    // }}}
    // {{{ raw()

    function raw($ctx, $filepath)
    {
        $log =& yb_Log::get_logger();
        $header =& new HTTP_Header();

        $original_filename = $ctx->get('original_filename');

        $_ct = $this->validate_imagetype($ctx->get('id'), $filepath);
        if ('' == $_ct) {
            $header->sendStatusCode(500);
            exit;
        }

        $vnum_d = $ctx->get('display_version_number');
        $vnum_c = $ctx->get('current_version_number');

        if ($vnum_d == $vnum_c) {
            $t =& yb_Time::singleton();
            $t->setInternalRaw($ctx->get('updated_at'));
            $modified_unixtime = $t->unixtime();

            /* we can't use $t->get(), because $t->get() 
             * is affected current timezone.
             * REALY we want to get is, GMT time strftime!
             */
            $modified = gmstrftime('%a, %d %b %Y %H:%M:%S GMT', 
                $modified_unixtime);
            $hms = yb_Var::server('HTTP_IF_MODIFIED_SINCE');
            if (!empty($hms)) {
                $hmsut = strtotime($hms);
                if ($hmsut >= $modified_unixtime) {
                    $header->sendStatusCode(304);
                    exit;
                }
            }
            $header->setHeader('Last-Modified', $modified);
        }

        // unset default header in HTTP_Header
        $header->setHeader('Pragma', null);
        $header->setHeader('Cache-Control', null);

        $header->setHeader('Content-Type', $_ct);
        $header->setHeader('Content-Length', filesize($filepath));
        $header->setHeader('Content-Disposition', 
            'inline; filename='.$original_filename);
        $data = @file_get_contents($filepath);
        if (false === $data) {
            $header->sendStatusCode(500);
            $log->err("{$filepath} read error in raw module, did = " 
                . $ctx->get('id'));
            exit;
        }
        $header->sendHeaders();
        echo $data;
    }

    // }}}
    // {{{ diff()

    function diff(
        $did, 
        $old_v, $old_display_name, $old_filename, 
        $new_v, $new_display_name, $new_filename)
    {

        $old_url = yb_Util::make_url(array(
            'mdl' => 'raw', 'id' => $did . '_' . $old_v));
        $new_url = yb_Util::make_url(array(
            'mdl' => 'raw', 'id' => $did . '_' . $new_v));

        $old_display_name = h($old_display_name);
        $new_display_name = h($new_display_name);

        $diff = "<h4>version {$old_v} (old)</h4>" . PHP_EOL
            . sprintf('<img src="%s" alt="%s" title="%s" />',
                $old_url, $old_display_name, $old_display_name) . PHP_EOL;
        $diff .= '<hr />' . PHP_EOL
            . "<h4>version {$new_v} (new)</h4>" . PHP_EOL
            . sprintf('<img src="%s" alt="%s" title="%s" />',
                $new_url, $new_display_name, $new_display_name) . PHP_EOL;
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
        if (empty($data['title'])) {
            $data['title'] = '{$upload_filename}';
        }
    }

    // }}}
    // {{{ format_edit_data()

    function format_edit_data(&$data)
    {
    }

    // }}}
    // {{{ format_copy_data()

    function format_copy_data(&$data, $base)
    {
        $data['original_filename'] = $base['original_filename'];
        $ctx =& new yb_DataContext($base);
        $this->_original_contents = $this->view(
            $ctx, 
            $base['_raw_filepath'], 
            yb_Html::DETAIL_MODE(), 
            $base['display_id'], $base['display_title']);
    }

    // }}}
    // {{{ build_specific_form_on_new()

    function build_specific_form_on_new($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $smarty->assign('upload_max_filesize', yb_Util::upload_max_filesize());
        $smarty->assign('filename_maxlen', UPLOAD_FILENAME_MAXLEN);
        $ret = $smarty->fetch('theme:datatypes/image/new_tpl.html');
        $smarty->clear_assign(array('data', 'upload_max_filesize', 'filename_maxlen'));

        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_edit()

    function build_specific_form_on_edit($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $smarty->assign('upload_max_filesize', yb_Util::upload_max_filesize());
        $smarty->assign('filename_maxlen', UPLOAD_FILENAME_MAXLEN);
        $ret = $smarty->fetch('theme:datatypes/image/edit_tpl.html');
        $smarty->clear_assign(array('data', 'upload_max_filesize', 'filename_maxlen'));

        return $ret;
    }

    // }}}
    // {{{ build_specific_form_on_copy()

    function build_specific_form_on_copy($data, &$smarty)
    {
        $smarty->assign('data', $data);
        $smarty->assign('original_contents', $this->_original_contents);
        $ret = $smarty->fetch('theme:datatypes/image/copy_tpl.html');
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

        // see
        // http://jp.php.net/manual/ja/features.file-upload.php
        $upfile = yb_Var::files('upfile');
        if (empty($upfile)) {
            $errors[] = t('File is not uploaded.');
            $ret = false;
        } else if ($upfile['error'] != UPLOAD_ERR_OK) {
            // http://jp.php.net/manual/ja/features.file-upload.errors.php
            $errors[] = t('File upload error : php error code = %code', 
                array('code' => $upfile['error']));
            $ret = false;
        } else {
            $exts = array_map('trim', 
                explode(',', _YB('yb.acceptable.image.extensions')));
            $ext = strtolower(pathinfo(
                basename($upfile['name']), PATHINFO_EXTENSION));
            if (!in_array($ext, $exts)) {
                $errors[] = t('Unknown image file extension : ".%ext"', 
                    array('ext' => $ext));
                return false;
            }

            $filepath = $upfile['tmp_name'];
            $_ct = $this->validate_imagetype('(new)', $filepath);
            if ('' == $_ct) {
                $errors[] = t('input errors');
                return false;
            }

            if ($_ct != $this->ext2ct[$ext]) {
                // extension doesn't match with getimagesize()'s mime
                $errors[] = t('input errors');
                return false;
            }

            if (!yb_datatype_Utils::validate_filename(
                $upfile['name'], $errors)) {
                return false;
            }

            $title = str_replace('{$upload_filename}', 
                basename($upfile['name']), $title);

            $idx =& grain_Factory::index('match', 'data_by_title');
            $idx->case_sensitive(true);
            $data_ids = $idx->fullmatch($title);
            if (count($data_ids) > 0) {
                $errors[] = t(
                    'Title (%title) is already exist, duplicated.',
                    array('title' => $title));
                $ret = false;
            }

            $data['title'] = $title;
        }

        return $ret;
    }

    // }}}
    // {{{ validate_edit_data()

    function validate_edit_data(&$errors, &$data)
    {
        $ret = true;

        // original_filename
        $original_filename = trim(yb_Var::request('original_filename'));
        if (empty($original_filename)) {
            $errors[] = t('%label is required.', 
                array('label' => t('download filename')));
            $ret = false;
        } else {
            $data['original_filename'] = $original_filename;
        }

        // see
        // http://jp.php.net/manual/ja/features.file-upload.php
        $upfile = yb_Var::files('upfile');
        switch (@$upfile['error']) {
        case UPLOAD_ERR_OK : 
            $exts = array_map('trim', 
                explode(',', _YB('yb.acceptable.image.extensions')));
            $ext = strtolower(pathinfo(
                basename($upfile['name']), PATHINFO_EXTENSION));
            if (!in_array($ext, $exts)) {
                $errors[] = t('Unknown image file extension : ".%ext"', 
                    array('ext' => $ext));
                return false;
            }

            $filepath = $upfile['tmp_name'];
            $_ct = $this->validate_imagetype('(new)', $filepath);
            if ('' == $_ct) {
                $errors[] = t('input errors');
                return false;
            }

            if ($_ct != $this->ext2ct[$ext]) {
                // extension doesn't match with getimagesize()'s mime
                $errors[] = t('input errors');
                return false;
            }

            if (!yb_datatype_Utils::validate_filename(
                $upfile['name'], $errors)) {
                return false;
            }

            $data['original_filename'] = str_replace('{$upload_filename}', 
                basename($upfile['name']), $data['original_filename']);

            break;

        default:
            // http://jp.php.net/manual/ja/features.file-upload.errors.php
            $errors[] = t('File upload error : php error code = %code', 
                array('code' => @$upfile['error']));
            $ret = false;
        }

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

        // original_filename
        $original_filename = trim(yb_Var::request('original_filename'));
        if (empty($original_filename)) {
            $errors[] = t('%label is required.', 
                array('label' => t('download filename')));
            $ret = false;
        } else {
            $data['original_filename'] = $original_filename;
        }

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

        $upfile = yb_Var::files('upfile');
        $raw_data = file_get_contents($upfile['tmp_name']);
        $data['original_filename'] = basename($upfile['name']);

        $r = yb_tx_data_New::go($data, $raw_data);

        return $r['id'];
    }

    // }}}
    // {{{ update_data()

    function update_data($did, $virtual, $do_vup, $changelog, $uid)
    {
        $copied = array('original_filename');
        $updates = array();
        foreach ($copied as $c) {
            $updates[$c] = $virtual[$c];
        }

        $upfile = yb_Var::files('upfile');
        $raw = file_get_contents($upfile['tmp_name']);

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
            'original_filename', 
            'is_versions_moderated', 'is_comments_moderated', 
        );
        $data = array();
        foreach ($copied as $c) {
            $data[$c] = $virtual[$c];
        }

        $src = @file_get_contents($base['_raw_filepath']);

        $r = yb_tx_data_New::go($data, $src);

        return $r['id'];
    }

    // }}}
    // {{{ validate_imagetype()

    function validate_imagetype($id, $filepath)
    {
        $log =& yb_Log::get_logger();

        // TODO we NEEDS more correct image file validateion.
        // getimagesize() is not recommended.
        // see following url (sorry, japanese only!)
        // http://blog.ohgaki.net/index.php/yohgaki/c_ra_a_a_ia_ca_la_lphpa_sa_fa_a_a_a_effa
        $size = getimagesize($filepath);

        if (false === $size) {
            $log->err("getimagesize() failed for {$filepath} in raw module, did = {$id}");
            return '';
        }

        if (0 == $size[0] || 0 == $size[1]) {
            // illegal image size
            $log->err("getimagesize() tells width or height is 0, {$filepath} may be illegal image data in raw module, did = {$id}");
            return '';
        }

        $_ct = $size['mime'];

        if (!isset($this->acceptable_imagetypes[$_ct])) {
            $log->err("mime-type '{$_ct}' returned by getimagesize() is not acceptable image type for {$filepath} in raw module, did = {$id}");
            return '';
        }

        return $this->acceptable_imagetypes[$_ct];
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
