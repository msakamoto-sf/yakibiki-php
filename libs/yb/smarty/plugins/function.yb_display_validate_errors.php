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
 * YakiBiki Smarty Plugin : displaying validation errors
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_display_validate_errors.php 220 2008-03-24 00:37:58Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_display_validate_errors($params, &$smarty)
{
    if (!isset($params['errors'])) {
        return "";
    }
    $errors = $params['errors'];
    if (!is_array($errors)) {
        $errors = array($errors);
    }
    if (count($errors) == 0) {
        return "";
    }

    $html = '<div style="color:red">' . t('input errors') . ':<ul>';
    foreach ($errors as $error) {
        $html .= "<li>{$error}</li>" . PHP_EOL;
    }
    $html .= '</ul></div>';

    return $html;
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
