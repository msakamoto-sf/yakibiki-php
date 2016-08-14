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
 * YakiBiki Data Type Utilities
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Utils.php 494 2009-01-04 02:23:39Z msakamoto-sf $
 */
class yb_datatype_Utils
{
    // {{{ validate_filename()

    function validate_filename($val, &$errors)
    {
        if (!preg_match('/^[0-9A-Za-z_,\.\-]+$/m', $val)) {
            $errors[] = t("You can use ONLY next characters as file name : Alphabet, Numbers, '_'(under score), '-'(hyphen), ','(comma), '.'(dot)");
            return false;
        }
        if (strpos($val, '..') !== false) {
            $errors[] = t("You CAN'T use more than 2 dots together ('..', '...', ...) in file name.");
            return false;
        }
        if (strlen($val) > UPLOAD_FILENAME_MAXLEN) {
            $errors[] = t('The length of file name MUST be lesser than equal %maxlen characters.', 
                array('maxlen' => UPLOAD_FILENAME_MAXLEN));
            return false;
        }
        return true;
    }

    // }}}
    // {{{ validate_acl()

    function validate_acl(&$errors, $acl)
    {
        $dao =& yb_dao_Factory::get('acl');

        if (empty($acl)) {
            $errors[] = t("ACL is required.");
            return false;
        }

        $acls = $dao->find_by_id($acl);
        if (count($acls) != 1) {
            $errors[] = t("Specified ACL (ID=%id) is not found.", 
                array('id' => $acl));

            return false;
        }

        // NOTICE: WHY DOES NOT CHECK ACL PERMISSION ?
        // BECAUSE: Remember, now here is data 'create' time.
        // So, current user will be 'owner' of data.
        // 'owner' can read, write own data without any acl limitation.
        // Yes, there's NO MEAN to check acl.

        return true;
    }

    // }}}
    // {{{ validate_categories()

    function validate_categories(&$errors, $categories, &$ret)
    {
        $ret = array();
        if (empty($categories)) {
            return true;
        }
        if (!is_array($categories)) {
            $errors[] = t("Invalid categories selected.");
            return false;
        }

        // convert "categories[$id] => 1"(POST form) to $id.
        $categories = array_keys($categories);

        $dao =& yb_dao_Factory::get('category');

        $results = $dao->find_by_id($categories);
        if (count($results) != count($categories)) {
            $errors[] = t("Invalid categories selected.");
            return false;
        }

        $ret = $categories;
        return true;
    }

    // }}}
    // {{{ user_can_create_data()

    function user_can_create_data($uid, $role)
    {
        if ($uid == YB_GUEST_UID) {
            return false;
        }
        if (in_array('sys', $role) || 
            in_array('new', $role)) {
                return true;
            }
        return false;
    }

    // }}}
    // {{{ data_type_is_correct()

    function data_type_is_correct($type)
    {
        $datatypes = _YB('datatypes');
        return isset($datatypes[$type]);
    }

    // }}}
    // {{{ redirect_to_login_and_exit()

    function redirect_to_login_and_exit()
    {
        HTTP_Header::redirect(
            yb_Util::redirect_url(array(
                'mdl' => 'login',
                'back' => yb_Util::current_url())
            )
        );
        exit();
    }

    // }}}
    // {{{ validate_new_data()

    function validate_new_data(&$data, &$errors)
    {
        $type = $data['type'];
        $dtplugin =& yb_Util::factoryDataType($type);

        // datatype-specific data
        $dtplugin->validate_new_data($errors, $data);

        // published_at
        $pa = yb_Var::post('published_at');
        if (yb_Util::checkdatetime(
            @$pa['year'], @$pa['month'], @$pa['day'], 
            @$pa['hour'], @$pa['min'], @$pa['sec'])) {

            $t =& new yb_Time();
            $t->set($pa['year'], $pa['month'], $pa['day'], 
                $pa['hour'], $pa['min'], $pa['sec']);
            $data['published_at'] = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        } else {
            $errors[] = t(
                'Specified date time is not existed date time.');
        }

        // acl
        if (yb_datatype_Utils::validate_acl(
            $errors, yb_Var::post('acl'))) {
                $data['acl'] = yb_Var::post('acl');
        }

        // categories
        $cs = array();
        if (yb_datatype_Utils::validate_categories(
            $errors, yb_Var::post('categories'), $cs)) {
                $data['categories'] = $cs;
        }

        // is_{version|comments}_moderated
        $data['is_versions_moderated'] = 
            (yb_Var::post('is_versions_moderated') == '1');
        $data['is_comments_moderated'] = 
            (yb_Var::post('is_comments_moderated') == '1');

        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    // }}}
    // {{{ validate_copy_data()

    function validate_copy_data(&$data, &$errors)
    {
        $type = $data['type'];
        $dtplugin =& yb_Util::factoryDataType($type);

        // datatype-specific data
        $dtplugin->validate_copy_data($errors, $data);

        // published_at
        $pa = yb_Var::post('published_at');
        if (yb_Util::checkdatetime(
            @$pa['year'], @$pa['month'], @$pa['day'], 
            @$pa['hour'], @$pa['min'], @$pa['sec'])) {

            $t =& new yb_Time();
            $t->set($pa['year'], $pa['month'], $pa['day'], 
                $pa['hour'], $pa['min'], $pa['sec']);
            $data['published_at'] = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        } else {
            $errors[] = t(
                'Specified date time is not existed date time.');
        }

        // acl
        if (yb_datatype_Utils::validate_acl(
            $errors, yb_Var::post('acl'))) {
                $data['acl'] = yb_Var::post('acl');
        }

        // categories
        $cs = array();
        if (yb_datatype_Utils::validate_categories(
            $errors, yb_Var::post('categories'), $cs)) {
                $data['categories'] = $cs;
        }

        // is_{version|comments}_moderated
        $data['is_versions_moderated'] = 
            (yb_Var::post('is_versions_moderated') == '1');
        $data['is_comments_moderated'] = 
            (yb_Var::post('is_comments_moderated') == '1');

        if (count($errors) > 0) {
            return false;
        }

        return true;
    }

    // }}}
    // {{{ validate_datainfo_update()

    function validate_datainfo_update(&$data, &$errors)
    {
        // title
        $title = trim(yb_Var::post('title'));
        if (empty($title)) {
            $errors[] = t('%label is required.', 
                array('label' => t('title')));
        } else {
            $idx =& grain_Factory::index('match', 'data_by_title');
            $idx->case_sensitive(true);
            $m = $idx->fullmatch($title);
            if (count($m) != 0 && !in_array($data['id'], $m)) {
                $errors[] = t(
                    'Title (%title) is already exist, duplicated.',
                    array('title' => $title));
            } else {
                $data['title'] = $title;
            }
        }

        // published_at
        $pa = yb_Var::post('published_at');
        if (yb_Util::checkdatetime(
            @$pa['year'], @$pa['month'], @$pa['day'], 
            @$pa['hour'], @$pa['min'], @$pa['sec'])) {

            $t =& new yb_Time();
            $t->set($pa['year'], $pa['month'], $pa['day'], 
                $pa['hour'], $pa['min'], $pa['sec']);
            $data['published_at'] = $t->getGMT(YB_TIME_FMT_INTERNAL_RAW);
        } else {
            $errors[] = t(
                'Specified date time is not existed date time.');
        }

        // acl
        if (yb_datatype_Utils::validate_acl(
            $errors, yb_Var::post('acl'))) {
                $data['acl'] = yb_Var::post('acl');
        }

        // categories
        $cs = array();
        if (yb_datatype_Utils::validate_categories(
            $errors, yb_Var::post('categories'), $cs)) {
                $data['categories'] = $cs;
        }

        // is_{version|comments}_moderated
        $data['is_versions_moderated'] = 
            (yb_Var::post('is_versions_moderated') == '1');
        $data['is_comments_moderated'] = 
            (yb_Var::post('is_comments_moderated') == '1');

        if (count($errors) > 0) {
            return false;
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

