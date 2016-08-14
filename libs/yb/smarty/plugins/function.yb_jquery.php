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
 * YakiBiki jQuery include and print onload setup javascrip code.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_jquery.php 433 2008-11-09 08:55:01Z msakamoto-sf $
 */

/**
 * YakiBiki jQuery include and print onload setup javascrip code.
 *
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_jquery($params, &$smarty)
{
    $html = '';
    $include_js = h(_YB('js.jquery.path'));
    $html .= sprintf('<script src="%s" type="text/javascript" ></script>', 
        $include_js) . PHP_EOL;

    $_jss = yb_jQuery::get_onload_event();
    if (count($_jss) > 0) {
        $onload_jss = implode(PHP_EOL, $_jss);
        $onload = <<<JS
<script type="text/javascript">
<!--
\$(function(){
    {$onload_jss}
});
//-->
</script>
JS;
        $html .= $onload;
    }

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
