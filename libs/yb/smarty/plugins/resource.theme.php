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
 * YakiBiki Smarty Plugin : theme resource plugin
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: resource.theme.php 44 2007-11-20 14:21:01Z msakamoto-sf $
 */

/**
 * Smarty Template Resource Access Plugin
 *
 * @param string Template Resource Name
 * @param string Reference for Template Resource Contents
 * @param object Smarty Obuject Reference
 * @return bool if resource is valid , true. else false.
 */
function smarty_resource_theme_source(
    $tpl_name, &$tpl_source, &$smarty)
{
    $theme =& yb_smarty_Theme::getInstance();
    return ($tpl_source = $theme->getTplResource($tpl_name)) ? true : false;
}

/**
 * Smarty Template Resource Timestamp Determine Plugin
 *
 * @param string Template Resource Name
 * @param string Reference for Template Resource Timestamp
 * @param object Smarty Object Reference
 * @return bool if resource is valid , true. else false.
 */
function smarty_resource_theme_timestamp(
    $tpl_name, &$tpl_timestamp, &$smarty)
{
    $theme =& yb_smarty_Theme::getInstance();
    return ($tpl_source = $theme->getTplTimestamp($tpl_name)) ? true : false;
}

/**
 * Smarty Template Security Plugin
 *
 * for dummy use.
 *
 * @param string Template Resource Name
 * @param object Smarty Object Reference
 * @return bool always true
 */
function smarty_resource_theme_secure($tpl_name, &$smarty)
{
    // assume all templates are secure
    return true;
}

/**
 * Smarty Template Trusted Plugin
 *
 * for dummy use.
 *
 * @param string Template Resource Name
 * @param object Smarty Object Reference
 * @return null
 */
function smarty_resource_theme_trusted($tpl_name, &$smarty)
{
    // not used for templates
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
