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
 * YakiBiki Profile Form Builder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: FormBuilder.php 521 2009-01-12 11:22:23Z msakamoto-sf $
 */
class yb_mdl_profile_FormBuilder extends yb_FormBuilderBase
{
    // {{{ &singleton()

    function &singleton()
    {
        static $builder = null;
        if (is_null($builder)) {
            $builder = new yb_mdl_profile_FormBuilder();
        }
        return $builder;
    }

    // }}}
    // {{{ accept()

    function accept($method)
    {
        if ($this->_already_accepted) {
            return;
        }
        $_els = array('name', 'mail', 'change_password', 
            'old_password', 'password', 'password2');
        foreach ($_els as $_n) {
            $v = (strtolower($method) == 'post') 
                ? yb_Var::post($_n)
                : yb_Var::get($_n);
            if (is_null($v)) {
                continue;
            }
            $this->_accepts[$_n] = $v;
        }
        $this->_already_accepted = true;
    }

    // }}}
    // {{{ validate()

    function validate(&$errors)
    {
        $ret1 = $this->_validate_name($errors);
        $ret2 = $this->_validate_mail($errors);
        $ret3 = $this->_validate_password($errors);

        return ($ret1 && $ret2 && $ret3);
    }

    // }}}
    // {{{ _validate_name()

    function _validate_name(&$errors)
    {
        $val = trim((string)@$this->_accepts['name']);
        if (empty($val)) {
            $errors[] = t('%label is required.', 
                array('label' => t('name')));
            return false;
        }

        if ('.' == $val[0]) {
            $errors[] = t("You can't start user name with '.'(dot)");
            return false;
        }
        if (preg_match('/\.$/mi', $val)) {
            $errors[] = t("You can't end user name with '.'(dot)");
            return false;
        }
        if (false !== strpos($val, '..')) {
            $errors[] = t("You can't use double '.'(dot) in user name.");
            return false;
        }

        if (!preg_match(YB_REGEXP_USER_NAME, $val)) {
            $errors[] = t('Given user name includes some prohibited characters.');
            return false;
        }

        $this->_defaults['name'] = $val;
        return true;
    }

    // }}}
    // {{{ _validate_mail()

    function _validate_mail(&$errors)
    {
        $val = trim((string)@$this->_accepts['mail']);
        if (empty($val)) {
            $errors[] = t('%label is required.', 
                array('label' => t('mail address')));
            return false;
        }

        if (!yb_Util::check_email_address($val, 
            _YB('email.check.use_strict_regexp'), 
            _YB('email.check.use_chkdnsrr'))) {
            $errors[] = t('Invalid mail address format.');
            return false;
        }

        list($p1, $p2) = explode('@', $val, 2);
        if ('.' == $p1[0]) {
            $errors[] = t("You can't start mail address with '.'(dot)");
            return false;
        }
        if (preg_match('/\.$/mi', $p1)) {
            $errors[] = t("You can't use '.'(dot) before '@' in mail address.");
            return false;
        }
        if (false !== strpos($p1, '..')) {
            $errors[] = t("You can't use double '.'(dot) in mail address.");
            return false;
        }

        $this->_defaults['mail'] = $val;
        return true;
    }

    // }}}
    // {{{ _validate_password()

    function _validate_password(&$errors)
    {
        $uc = yb_Session::user_context();
        $user_dao =& yb_dao_Factory::get('user');
        $users = $user_dao->find_by_id($uc['id']);
        $current_password_hashed = (string)$users[0]['password'];

        $_old_password = trim((string)@$this->_accepts['old_password']);
        $_password = trim((string)@$this->_accepts['password']);
        $_password2 = trim((string)@$this->_accepts['password2']);
        $_change_password = @$this->_accepts['change_password'];
        $this->_defaults['change_password'] = $_change_password;

        if ($_change_password) {
            if (empty($_old_password)) {
                $errors[] = t('%label is required.', 
                    array('label' => t('old password')));
                return false;
            }
            if (empty($_password)) {
                $errors[] = t('%label is required.', 
                    array('label' => t('new password')));
                return false;
            }
            if (empty($_password2)) {
                $errors[] = t('%label is required.', 
                    array('label' => t('new password (confirm)')));
                return false;
            }

            $_old_password_hashed = 
                (string)yb_Util::hash_password($_old_password);
            if ($_old_password_hashed !== $current_password_hashed) {
                $errors[] = t('old password is not correct.');
                return false;

            }
            if ($_password !== $_password2) {
                $errors[] = t('new password (confirm) is not match.');
                return false;
            }
            if ($_password === $_old_password) {
                $errors[] = t('new password is same to old.');
                return false;
            }
        }
        $this->_defaults['old_password'] = $_old_password;
        $this->_defaults['password'] = $_password;
        $this->_defaults['password2'] = $_password2;

        return true;
    }

    // }}}
    // {{{ export()

    function export()
    {
        // copy from defaults
        $_ex = $this->_defaults;

        return $_ex;
    }

    // }}}
    // {{{ build()

    function build()
    {
        $_forms = array();
        $_forms['hidden'] = '';
        $_forms['required_mark'] = $this->_required_mark;

        $_forms['name'] = $this->_defaults['name'];
        $_forms['mail'] = $this->_defaults['mail'];

        $_change_password = (@$this->_defaults['change_password']) 
            ? ' checked="checked" ' : '';
        $_forms['change_password'] = $_change_password;

        $_roles = $this->_defaults['role'];
        $_role_display = array();
        $_role_display_names = yb_Util::user_roles_displaynames();
        foreach ($_role_display_names as $r => $v) {
            if (in_array($r, $_roles)) {
                $_role_display[] = $v;
            }
        }
        $_forms['role'] = implode(', ', $_role_display);

        $_status = array();
        $_status[YB_USER_STATUS_OK] = t('valid');
        $_status[YB_USER_STATUS_DISABLED] = t('invalid');
        $_forms['status'] = @$_status[$this->_defaults['status']];

        return array(
            "html" => $_forms,
            "errors" => array(),
        );
    }

    // }}}
    // {{{ freeze()

    function freeze()
    {
        $_forms = array();
        $_forms['hidden'] = '';

        $_forms['name'] = $this->_defaults['name'];
        $_forms['mail'] = $this->_defaults['mail'];

        $_forms['password'] = '(not modified)';
        if (@$this->_defaults['change_password']) {
            $_forms['password'] = '********';
        }

        $_roles = $this->_defaults['role'];
        $_role_display = array();
        $_role_display_names = yb_Util::user_roles_displaynames();
        foreach ($_role_display_names as $r => $v) {
            if (in_array($r, $_roles)) {
                $_role_display[] = $v;
            }
        }
        $_forms['role'] = implode(', ', $_role_display);

        $_status = array();
        $_status[YB_USER_STATUS_OK] = t('valid');
        $_status[YB_USER_STATUS_DISABLED] = t('invalid');
        $_forms['status'] = @$_status[$this->_defaults['status']];

        return array(
            "html" => $_forms,
            "errors" => array(),
        );
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

