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
 * YakiBiki Smarty Plugin : import yb data plugin
 *
 * <code>
 * {yb_import_data id=1} // import data id = 1.
 * {yb_import_data id=sidebar} // import data page name = _YB('default.sidebar')
 * {yb_import_data title=FrontPage} // import data page name = 'FrontPage'
 * {yb_import_data id=1 title=SideBar} // import data id = 1. (title is ignored)
 * </code>
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: function.yb_import_data.php 374 2008-09-27 16:48:41Z msakamoto-sf $
 * @param array Parameters specified in Smarty Template.
 * @return string Output Contents
 */
function smarty_function_yb_import_data($params, &$smarty)
{
    $user_context = yb_Session::user_context();
    $error_fmt = '<span style="color: red;">%s</span>';

    $did = 0;
    if (!preg_match('/^(\d+)$/mi', $params['id'], $matches)) {
        if (preg_match('/^sidebar$/mi', $params['id'], $matches)) {
            $title = _YB('default.sidebar');
        } else if (isset($params['title'])) {
            $title = $params['title'];
        } else {
            return sprintf($error_fmt, t('data id is not given.'));
        }
    } else {
        $did = $matches[1];
    }

    $err = array();
    if ($did === 0) {
        $data = yb_Finder::find_by_title(
            $user_context, 
            $title, 
            $err, 
            YB_ACL_PERM_READ, 
            false // don't expand owner, updated_by, categories data
        );
    } else {
        $data = yb_Finder::find_by_id(
            $user_context, 
            $did, 
            $err, 
            YB_ACL_PERM_READ, 
            false // don't expand owner, updated_by, categories data
        );
    }

    if (is_null($data)) {
        return sprintf($error_fmt, t($err['msg'], $err['args']));
    }

    $contents = "";
    $type = $data['type'];
    $ctx =& new yb_DataContext($data);

    $dtplugin =& yb_Util::factoryDataType($type);
    if (is_null($dtplugin)) {
        return sprintf($error_fmt, 
            t('Illegal data type : [%type]', array('type' => $type)));
    }
    $contents = $dtplugin->view(
        $ctx, 
        $data['_raw_filepath'], 
        yb_Html::DETAIL_MODE(), 
        $data['display_id'], $data['display_title']);

    return $contents;
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
