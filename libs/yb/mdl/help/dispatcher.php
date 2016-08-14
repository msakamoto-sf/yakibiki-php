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

/*
 * YakiBiki Help Dispatcher
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 469 2008-11-22 08:28:33Z msakamoto-sf $
 */

yb_Session::start();
$uc = yb_Session::user_context();

$query = yb_Var::get('h');
$locale = _YB('resource.locale');
$msg_not_found = t('Specified help document was not found.');

if (!empty($query) && !preg_match('/^[0-9A-Za-z_\-]+$/mi', $query)) {
    yb_Util::forward_error_die($msg_not_found, null, 404);
}

$help_file = _YB('dir.helpdocs') . "/{$locale}/{$query}.txt";

if (!is_readable($help_file)) {
    // default index
    $query = 'HelpTop';
    $help_file = _YB('dir.helpdocs') . "/{$locale}/{$query}.txt";
}

$help = _parse_help_wiki(
    $query, // pseudo did
    $query, // pseudo pagename
    @file_get_contents($help_file)
);

$renderer =& new yb_smarty_Renderer();
$renderer->setTitle(t($query, null, 'help'));
$renderer->set('user_context', $uc);
$renderer->set('help', $help);
$renderer->setViewName('theme:help_tpl.html');
echo $renderer->render();

function _parse_help_wiki($did, $pagename, $src)
{
    $wiki = wiki_Parser::convert_block($src, $did, $pagename);
    $ft =& wiki_Footnote::singleton();
    $wiki .= $ft->getnote($did);

    // dummy html context (we can't use yb_ls, yb_navi plugins)
    $ctx =& new yb_DataContext(array('id' => 1, 'name' => $pagename));
    $html = yb_Html::convert($wiki, $ctx, yb_Html::DETAIL_MODE());

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
