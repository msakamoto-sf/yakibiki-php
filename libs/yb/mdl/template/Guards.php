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
 * YakiBiki template module Xhwlay guard class
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Guards.php 362 2008-09-19 13:49:44Z msakamoto-sf $
 */
class yb_mdl_template_Guards
{
    // {{{ guard_on_select_template_datatype()

    function guard_on_select_template_datatype(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $dt = (string)yb_Var::request('dt');
        $datatypes = _YB('datatypes');
        if (!isset($datatypes[$dt])) {
            $errors[] = t('Invalid Data Type(%datatype).', 
                array('datatype' => $dt));
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $bookmark->set('datatype', $dt);

        return true;
    }

    // }}}
    // {{{ guard_on_select_template()

    function guard_on_select_template(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $id = (integer)yb_Var::request('id');
        $dao =& yb_dao_Factory::get('template');
        $templates = $dao->find_by_id($id);
        if (count($templates) != 1) {
            $errors[] = t('Invalid Template ID.', null, 'template');
            $renderer->set('validate_errors', $errors);
            return false;
        }

        $bookmark->set('id', $id);

        // copy template data to virtual record.
        $virtual = $templates[0];
        $dtplugin =& yb_Util::factoryDataType($virtual['type']);
        $dtplugin->format_template_data_on_update_delete($virtual);

        $bookmark->set('virtual', $virtual);

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
