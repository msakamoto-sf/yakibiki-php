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

/**
 * YakiBiki Acl Form Builder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: FormBuilder.php 505 2009-01-06 02:50:13Z msakamoto-sf $
 */
class yb_mdl_acl_FormBuilder extends yb_FormBuilderBase
{
    var $_cache_users = array();

    var $_cache_groups = array();

    var $_opts = array();

    // {{{ &singleton()

    function &singleton()
    {
        static $builder = null;
        if (is_null($builder)) {
            $builder = new yb_mdl_acl_FormBuilder();
        }
        return $builder;
    }

    // }}}
    // {{{ setOpts()

    function setOpts($k, $v)
    {
        $this->_opts[$k] = $v;
    }

    // }}}
    // {{{ accept()

    function accept($method)
    {
        $_els = array('name', 'policy', 'spuid_logined', 'spuid_guest');
        $_sp_perms = array();
        $_accepts = array();
        foreach ($_els as $_n) {
            $v = (strtolower($method) == 'post') 
                ? yb_Var::post($_n)
                : yb_Var::get($_n);
            if (is_null($v)) {
                continue;
            }
            $this->_accepts[$_n] = $v;
        }
    }

    // }}}
    // {{{ validate()

    function validate(&$errors)
    {
        // {{{ check acl name is not empty.
        if (empty($this->_accepts['name']) ||
            trim($this->_accepts['name']) == "") {
            $errors[] = t("ACL Name is required.");
            return false;
        }
        // }}}
        // check ok. defaults['name'] is over writed.
        $this->_defaults['name'] = trim($this->_accepts['name']);

        // check acl policy is one of POSI, NEGA.
        // {{{ check acl policy is not empty.
        if (empty($this->_accepts['policy']) ||
            trim($this->_accepts['policy']) == "") {
            $errors[] = t("ACL Policy is required.");
            return false;
        }
        // }}}
        $_policy = trim($this->_accepts['policy']);
        // {{{ check acl policy is one of POSI, NEGA.
        $_valid_policies = array(YB_ACL_POLICY_POSI, YB_ACL_POLICY_NEGA);
        if (!in_array($_policy, $_valid_policies)) {
            $errors[] = t("ACL Policy is not valid value.");
            return false;
        }
        // }}}
        // check ok. defaults['policy'] is over writed.
        $this->_defaults['policy'] = $_policy;

        // {{{ check each special uid permission is not empty.
        $_els = array(
            'spuid_guest' => t('Guest (NOT Logined) users only'),
            'spuid_logined' => t('Logined users only'),
        );
        foreach ($_els as $_el => $_label) {
            if (!isset($this->_accepts[$_el])) {
                $errors[] = t("ACL Permission for %label is required.", 
                    array('label' => $_label));
                return false;
            }
        }
        // }}}
        // {{{ check each special uid permission is valid perm value.
        $_sp_perms = array(
            YB_GUEST_UID => $this->_accepts['spuid_guest'],
            YB_LOGINED_UID => $this->_accepts['spuid_logined'],
        );
        $_valid_perms = array(
            YB_ACL_PERM_NONE, YB_ACL_PERM_READ, YB_ACL_PERM_READWRITE);
        foreach ($_sp_perms as $_sp_uid => $_p) {
            // strip illegal characters by enforcing type casting.
            $__p = (integer)$_p;
            if (!in_array($__p, $_valid_perms)) {
                $errors[] = t("ACL Policy is not valid value.");
                return false;
            }
        }
        // }}}
        // adjust defaults['perm'] array.
        $_perms_temp = array();
        $_sp_uids = array_keys($_sp_perms);
        foreach ($this->_defaults['perms'] as $_perm) {
            if ($_perm['type'] == YB_ACL_TYPE_USER &&
                in_array($_perm['id'], $_sp_uids)) {
                continue;
            }
            $_perm_temp[] = $_perm;
        }
        foreach ($_sp_perms as $_sp_uid => $_p) {
            // strip illegal characters by enforcing type casting.
            $__p = (integer)$_p;
            $_perm_temp[] = array(
                'type' => YB_ACL_TYPE_USER,
                'id' => $_sp_uid,
                'perm' => $__p,
            );
        }
        $this->_defaults['perms'] = $_perm_temp;

        return true;
    }

    // }}}
    // {{{ export()

    function export()
    {
        $dummy_errors = array();
        $this->validate($dummy_errors);

        return $this->_defaults;
    }

    // }}}
    // {{{ build()

    function build()
    {
        $_forms = array();
        $_forms['hidden'] = '';
        $_forms['required_mark'] = $this->_required_mark;
        $_forms['name'] = $this->_defaults['name'];

        $_forms['policy'] = array();
        $_forms['policy'][] = array(
            'value' => YB_ACL_POLICY_POSI,
            'id' => 'policy_posi',
            'checked' => ($this->_defaults['policy'] == YB_ACL_POLICY_POSI),
            'label' => t('Positive Policy'),
        );
        $_forms['policy'][] = array(
            'value' => YB_ACL_POLICY_NEGA,
            'id' => 'policy_nega',
            'checked' => ($this->_defaults['policy'] == YB_ACL_POLICY_NEGA),
            'label' => t('Negative Policy'),
        );

        list($_spuids, $_normal_uid_perms, $_normal_gid_perms) = 
            $this->_split_perms($this->_defaults['perms']);

        $_forms['spuid'] = $_spuids;

        $this->_cache_user_groups(
            array_keys($_normal_uid_perms), 
            array_keys($_normal_gid_perms)
        );

        $_perm_user_lists = array();
        $_perm_group_lists = array();
        foreach ($_normal_uid_perms as $_uid => $_perm) {
            $_u_info = $this->_cache_users[$_uid];
            $_perm_user_lists[] = array(
                'id' => $_uid,
                'name' => $_u_info['name'],
                'perm' => $this->_perm_label($_perm),
            );
        }
        foreach ($_normal_gid_perms as $_gid => $_perm) {
            $_g_info = $this->_cache_groups[$_gid];
            $_perm_group_lists[] = array(
                'id' => $_gid,
                'name' => $_g_info['name'],
                'perm' => $this->_perm_label($_perm),
            );
        }

        $_forms['perms'] = array(
            'user' => $_perm_user_lists,
            'group' => $_perm_group_lists,
        );

        return array(
            "html" => $_forms,
            "errors" => array(),
        );
    }

    // }}}
    // {{{ freeze_usermanager()

    function freeze_usermanager()
    {
        $_forms = array();
        $_forms['hidden'] = '';

        $_forms['name'] = $this->_defaults['name'];

        $_policy = array();
        $_policy[YB_ACL_POLICY_POSI] = t('Positive Policy');
        $_policy[YB_ACL_POLICY_NEGA] = t('Negative Policy');
        $_forms['policy'] = @$_policy[$this->_defaults['policy']];

        list($_spuids, $_normal_uids, $_normal_gids) = 
            $this->_split_perms($this->_defaults['perms']);

        $_spuids['guest'] = $this->_perm_label($_spuids['guest']);
        $_spuids['logined'] = $this->_perm_label($_spuids['logined']);
        $_forms['spuid'] = $_spuids;

        $this->_cache_user_groups(
            array_keys($_normal_uids), 
            array_keys($_normal_gids)
        );

        $_perm_user_lists = array();
        $_perm_group_lists = array();
        foreach ($_normal_uids as $_uid => $_perm) {
            $_u_info = $this->_cache_users[$_uid];
            $_perm_user_lists[] = array(
                'id' => $_uid,
                'name' => $_u_info['name'],
                'select_name' => 'u[' . $_uid . ']',
                'perm' => $_perm,
            );
        }
        foreach ($_normal_gids as $_gid => $_perm) {
            $_g_info = $this->_cache_groups[$_gid];
            $_perm_group_lists[] = array(
                'id' => $_gid,
                'name' => $_g_info['name'],
                'perm' => $this->_perm_label($_perm),
            );
        }

        $_forms['perms'] = array(
            'user' => $_perm_user_lists,
            'group' => $_perm_group_lists,
        );

        return array(
            "html" => $_forms,
            "errors" => array(),
        );
    }

    // }}}
    // {{{ freeze_groupmanager()

    function freeze_groupmanager()
    {
        $_forms = array();
        $_forms['hidden'] = '';

        $_forms['name'] = $this->_defaults['name'];

        $_policy = array();
        $_policy[YB_ACL_POLICY_POSI] = t('Positive Policy');
        $_policy[YB_ACL_POLICY_NEGA] = t('Negative Policy');
        $_forms['policy'] = @$_policy[$this->_defaults['policy']];

        list($_spuids, $_normal_uids, $_normal_gids) = 
            $this->_split_perms($this->_defaults['perms']);

        $_spuids['guest'] = $this->_perm_label($_spuids['guest']);
        $_spuids['logined'] = $this->_perm_label($_spuids['logined']);
        $_forms['spuid'] = $_spuids;

        $this->_cache_user_groups(
            array_keys($_normal_uids), 
            array_keys($_normal_gids)
        );

        $_perm_user_lists = array();
        $_perm_group_lists = array();
        foreach ($_normal_uids as $_uid => $_perm) {
            $_u_info = $this->_cache_users[$_uid];
            $_perm_user_lists[] = array(
                'id' => $_uid,
                'name' => $_u_info['name'], 
                'perm' => $this->_perm_label($_perm),
            );
        }
        foreach ($_normal_gids as $_gid => $_perm) {
            $_g_info = $this->_cache_groups[$_gid];
            $_perm_group_lists[] = array(
                'id' => $_gid,
                'name' => $_g_info['name'],
                'select_name' => 'g[' . $_gid . ']',
                'perm' => $_perm,
            );
        }

        $_forms['perms'] = array(
            'user' => $_perm_user_lists,
            'group' => $_perm_group_lists,
        );

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

        $_policy = array();
        $_policy[YB_ACL_POLICY_POSI] = t('Positive Policy');
        $_policy[YB_ACL_POLICY_NEGA] = t('Negative Policy');
        $_forms['policy'] = @$_policy[$this->_defaults['policy']];

        list($_spuids, $_normal_uids, $_normal_gids) = 
            $this->_split_perms($this->_defaults['perms']);

        $_spuids['guest'] = $this->_perm_label($_spuids['guest']);
        $_spuids['logined'] = $this->_perm_label($_spuids['logined']);
        $_forms['spuid'] = $_spuids;

        $this->_cache_user_groups(
            array_keys($_normal_uids), 
            array_keys($_normal_gids)
        );

        $_perm_user_lists = array();
        $_perm_group_lists = array();
        foreach ($_normal_uids as $_uid => $_perm) {
            $_u_info = $this->_cache_users[$_uid];
            $_perm_user_lists[] = array(
                'id' => $_uid,
                'name' => $_u_info['name'], 
                'perm' => $this->_perm_label($_perm),
            );
        }
        foreach ($_normal_gids as $_gid => $_perm) {
            $_g_info = $this->_cache_groups[$_gid];
            $_perm_group_lists[] = array(
                'id' => $_gid,
                'name' => $_g_info['name'],
                'perm' => $this->_perm_label($_perm),
            );
        }

        $_forms['perms'] = array(
            'user' => $_perm_user_lists,
            'group' => $_perm_group_lists,
        );

        return array(
            "html" => $_forms,
            "errors" => array(),
        );
    }

    // }}}
    // {{{ _cache_user_groups($uids, $gids)

    function _cache_user_groups($uids, $gids)
    {
        $dao_user =& yb_dao_Factory::get('user');
        $dao_group =& yb_dao_Factory::get('group');
        sort($uids);
        sort($gids);
        $_users = array();
        if (count($uids) > 0) {
            $_users = $dao_user->find_by_id($uids);
        }
        $_groups = array();
        if (count($gids) > 0) {
            $_groups = $dao_group->find_by_id($gids);
        }
        foreach ($_users as $_u) {
            $this->_cache_users[$_u['id']] = $_u;
        }
        foreach ($_groups as $_g) {
            $this->_cache_groups[$_g['id']] = $_g;
        }
    }

    // }}}
    // {{{ _split_perms()

    function _split_perms($perms)
    {
        $_spuids = array(
            'guest' => YB_ACL_PERM_NONE, 
            'logined' => YB_ACL_PERM_NONE, 
            );
        $_users = array();
        $_groups = array();

        foreach ($perms as $perm) {

            if ($perm['type'] == YB_ACL_TYPE_USER) {
                // type = user

                switch ($perm['id']) {
                case YB_GUEST_UID :
                    $_spuids['guest'] = $perm['perm'];
                    break;
                case YB_LOGINED_UID : 
                    $_spuids['logined'] = $perm['perm'];
                    break;
                default:
                    $_users[$perm['id']] = $perm['perm'];
                }

            } else {
                // type = group

                $_groups[$perm['id']] = $perm['perm'];
            }
        }
        ksort($_users);
        ksort($_groups);

        return array($_spuids, $_users, $_groups);
    }

    // }}}
    // {{{ _perm_label()

    function _perm_label($p)
    {
        static $_l = null;
        if (is_null($_l)) {
            $_l = array(
                YB_ACL_PERM_NONE => t('(none:invisible)'),
                YB_ACL_PERM_READ => t('Read Only'),
                YB_ACL_PERM_READWRITE => t('Read and Edit'),
            );
        }

        return @$_l[(integer)$p];
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

