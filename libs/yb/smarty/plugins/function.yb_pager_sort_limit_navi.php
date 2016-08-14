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
 * YakiBiki Smarty Plugin : display pager's sort-order-limit box block
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id$
 */

/**
 *
 */
function smarty_function_yb_pager_sort_limit_navi($params, &$smarty)
{
    $mdl = yb_Var::get('mdl');
    $bcid = null;
    $bcid_hidden_tag = '';
    if (class_exists('Xhwlay_Var') && 
        !is_null($bcid = Xhwlay_Var::get(XHWLAY_VAR_KEY_BCID))) {
        $bcid_hidden_tag = '<input type="hidden" name="_bcid_" value="'
            . $bcid . '" />';
    }
    if (isset($params['action'])) {
        $action = $params['action'];
    } else {
        $action = '';
    }
    $hidden_tags = '';
    if (isset($params['hidden_tags'])) {
        foreach ($params['hidden_tags'] as $k => $v) {
            $hidden_tags .= '<input type="hidden" name="' . $k . '" value="' . $v . '" />';
        }
    }
    $navi = $params['navi'];
    $sort_by_label = t('Sort By');
    $order_by_label = t('Order By');
    $show_label = t('Show');
    $per_page_label = t('per Page');
    $apply_label = t('apply');


    $html = <<<HTML
<div style="width: 100%; margin-top: 0.5em; text-align: left;">
<form action="{$action}" method="GET">
{$bcid_hidden_tag}
<input type="hidden" name="mdl" value="{$mdl}" />
{$hidden_tags}
{$sort_by_label} {$navi['html_select_lists']['sort_by']} / 
{$order_by_label} {$navi['html_select_lists']['order_by']}&nbsp;&nbsp;&nbsp;
{$show_label} {$navi['html_select_lists']['limit']} {$per_page_label}
<input type="submit" value="{$apply_label}" />
</form>
</div>
HTML;

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
