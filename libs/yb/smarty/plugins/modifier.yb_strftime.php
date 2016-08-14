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
 * YakiBiki Smarty Plugin : echo back-to-url
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: modifier.yb_strftime.php 196 2008-03-14 14:31:26Z msakamoto-sf $
 */

/**
 * YakiBiki yb_Time format (by strftime())
 *
 * @param string yb_Time internal raw value (ex. created_at, updated_at)
 * @param string strftime()'s format string
 * @return string
 */
function smarty_modifier_yb_strftime($string, $format = null)
{
    $_fmt = _YB('default.datetime_format');
    if (!empty($format)) {
        $_fmt = $format;
    }
    $t =& yb_Time::singleton();
    $t->setInternalRaw($string);
    return $t->get($_fmt);
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
