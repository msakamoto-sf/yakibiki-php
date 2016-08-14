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
 * requires
 */
require_once('yb/mdl/profile/FormBuilder.php');

/**
 * YakiBiki user module Xhwlay guard class
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Guards.php 388 2008-10-20 14:09:38Z msakamoto-sf $
 */
class yb_mdl_profile_Guards
{
    // {{{ guard_on_validate()

    function guard_on_validate(&$runner, $event, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $errors = array();

        $virtual = $bookmark->get('virtual');
        $form =& yb_mdl_profile_FormBuilder::singleton();
        $form->setDefaults($virtual);
        $form->accept('post');
        if (!$form->validate($errors)) {
            $renderer->set('validate_errors', $errors);
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
