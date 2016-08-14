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
 * YakiBiki Group Form Builder
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: FormBuilder.php 351 2008-09-16 07:14:48Z msakamoto-sf $
 */
class yb_mdl_group_FormBuilder extends yb_FormBuilderBase
{

    // {{{ &singleton()

    function &singleton()
    {
        static $builder = null;
        if (is_null($builder)) {
            $builder = new yb_mdl_group_FormBuilder();
        }
        return $builder;
    }

    // }}}
    // {{{ accept()

    function accept($method)
    {
        $_els = array('name');
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
        $ret1 = $this->_validate_name($errors);

        return $ret1;
    }

    // }}}
    // {{{ _validate_name()

    function _validate_name(&$errors)
    {
        $value = trim((string)$this->_accepts['name']);
        if (empty($value)) {
            $errors[] = t("Group Name is empty.");
            return false;
        }

        // {{{ check group name duplication

        // We now use DAO's find_all(), NOT TX's find_all().
        // because, "name" duplication check needs ALL group datas
        // not limited by user context's role or ids.

        $dao_group =& yb_dao_Factory::get('group');
        $all_groups = $dao_group->find_all();
        foreach ($all_groups as $g) {
            $_gid = $g['id'];
            $_n = $g['name'];
            if (isset($this->_defaults['id'])) {
                if ($_n == $value && $_gid != $this->_defaults['id']) {
                    $errors[] = t(
                        "Given group name (%name) has been already used.",
                        array('name' => $_n));
                    return false;
                }
            } else if ($_n == $value) {
                $errors[] = t(
                    "Given group name (%name) has been already used.",
                    array('name' => $_n));
                return false;
            }
        }

        // }}}

        $this->_defaults['name'] = $value;

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

