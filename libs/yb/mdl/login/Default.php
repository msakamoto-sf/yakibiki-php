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
 * YakiBiki login module default page
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Default.php 513 2009-01-07 07:12:42Z msakamoto-sf $
 */
class yb_mdl_login_Default
{
    // {{{ input_page()

    function input_page(&$runner, $page, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('login'));
        $renderer->set('f_user', $bookmark->get('user'));
        $renderer->set('f_password', $bookmark->get('password'));
        $renderer->set('hide_backurl', true);

        if (!is_null(yb_Var::request('back'))) {
            $bookmark->set('back', yb_Var::request('back'));
        }
        $renderer->set('f_back', $bookmark->get('back'));

        return "theme:modules/login/input_tpl.html";
    }

    // }}}
    // {{{ guard_validateLogin()

    function guard_validateLogin(&$runner, $event, &$bookmark, $params)
    {
        $vars = array("user", "password");

        foreach ($vars as $_k) {
            if (!is_null(yb_Var::request($_k))) {
                $bookmark->set($_k, yb_Var::request($_k));
            }
        }
        $_user = $bookmark->get('user');
        $_password = $bookmark->get('password');

        $log =& yb_Log::get_logger();
        $_ip = yb_Var::env('REMOTE_ADDR');
        $_ua = yb_Var::env('HTTP_USER_AGENT');

        $user = yb_Util::hook('login_authenticate', array($_user, $_password));

        $renderer =& $runner->getRenderer();
        if (is_null($user)) {
            $log->info(sprintf(
                'login failure: user [%s], host [%s], user-agent [%s]', 
                $_user, $_ip, $_ua));
            $renderer->set('invalid_login', true);
            return false;
        }

        $log->info(sprintf(
            'login success : user [%s], host [%s], user-agent [%s]', 
            $user['name'], $_ip, $_ua));

        // renegenerate session id
        yb_Session::regenerate_id();
        // update user_context by logined user data.
        yb_Session::user_context($user);
        $renderer->set('user_context', $user);
        $renderer->set('invalid_login', false);

        return true;
    }

    // }}}
    // {{{ logined_page()

    function logined_page(&$runner, $page, &$bookmark, $params)
    {
        $renderer =& $runner->getRenderer();
        $renderer->setTitle(t('Login Success!!'));
        $renderer->set('f_user', $bookmark->get('user'));
        $renderer->set('f_name', $bookmark->get('name'));
        $renderer->set('hide_backurl', true);

        $back = $bookmark->get('back');
        if (!is_null($back)) {
            if (preg_match('!^' . _YB('url') . '!mi', $back)) {
                // in _YB('url') url
                $renderer->set('f_back', $back);
            } else {
                // out of _YB('url') url -> attack!! 
                // -> force redirect to _YB('url')
                $renderer->set('f_back', _YB('url'));
            }
        }

        return "theme:modules/login/logined_tpl.html";
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
