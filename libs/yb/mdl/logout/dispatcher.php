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
 * YakiBiki: logout module dispatcher.
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: dispatcher.php 421 2008-11-06 23:10:05Z msakamoto-sf $
 */

require_once('yb/smarty/Renderer.php');

yb_Session::start();

$uc = yb_Session::user_context();
$log =& yb_Log::get_logger();
$_ip = yb_Var::env('REMOTE_ADDR');
$_ua = yb_Var::env('HTTP_USER_AGENT');
$log->info(sprintf(
    'logout: user [%s], host [%s], user-agent [%s]', 
    $uc['name'], $_ip, $_ua));

yb_Session::regenerate_id();
yb_Session::user_context(yb_Session::anonymous_user_context());

$renderer =& new yb_smarty_Renderer();
$renderer->set('user_context', yb_Session::user_context());
$renderer->set('hide_backurl', true);
$back = yb_Var::request('back');
if (!empty($back)) {
    if (preg_match('!^' . _YB('url') . '!mi', $back)) {
        // in _YB('url') url
        $renderer->set('back', $back);
    } else {
        // out of _YB('url') url -> attack!! -> force redirect to _YB('url')
        $renderer->set('back', _YB('url'));
    }
}
$renderer->setTitle(t('Logouted'));
$renderer->setViewName("theme:modules/logout/logouted_tpl.html");

echo $renderer->render();

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
