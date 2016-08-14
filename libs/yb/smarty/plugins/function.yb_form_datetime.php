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
 * YakiBiki Smarty Plugin : DateTime Input From from YB_TIME_FMT_INTERNAL_RAW
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_form_datetime.php 363 2008-09-21 13:25:09Z msakamoto-sf $
 */

/**
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_form_datetime($params, &$smarty)
{
    $t = @$params['t'];
    $name = @$params['name'];

    $curr_t =& new yb_Time();

    if (!empty($t) && 
        preg_match(YB_TIME_REGEXP_INTERNAL_RAW, $t)) {
        // set as GMT internal representation
        $curr_t->setInternalRaw($t);
    }

    // get current timezone and split into assoc-array
    $vals = $curr_t->splitInternalRaw($curr_t->get(YB_TIME_FMT_INTERNAL_RAW));

    $fmt = '<input type="text" name="%s[%s]" value="%s" size="%d" maxlength="%d">';
    $els = array(
        'year' => 4, 
        'month' => 2, 
        'day' => 2, 
        'hour' => 2, 
        'min' => 2, 
        'sec' => 2, 
    );
    foreach ($els as $e => $len) {
        $f[$e] = sprintf($fmt, $name, $e, $vals[$e], $len + 1, $len);
    }

    $smarty->assign('yb_form_datetime', $f);
    $output = $smarty->fetch("theme:plugins/yb_form_datetime_tpl.html");
    // clear for next/outer smarty object user.
    $smarty->clear_assign(array('yb_form_datetime'));

    return $output;
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
