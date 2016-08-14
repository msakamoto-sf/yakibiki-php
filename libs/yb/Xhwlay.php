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

require_once('Xhwlay/Runner.php');
require_once('Xhwlay/Bookmark/FileStoreContainer.php');
require_once('Xhwlay/Config/PHPArray.php');
require_once('Xhwlay/Renderer/Include.php');
require_once('HTTP/Header.php');

/**
 * YakiBiki Xhwlay Hooks
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Xhwlay.php 461 2008-11-21 15:09:12Z msakamoto-sf $
 */
class yb_Xhwlay
{
    /**
     * Page Flow Configuration
     *
     * @access public
     * @type array
     */
    var $pageFlow= array();

    /**
     * Login required
     *
     * @access public
     * @type boolean
     */
    var $need_login = false;

    /**
     * Required roles
     *
     * @access public
     * @type boolean
     */
    var $roles = array();

    // {{{ run()

    function run()
    {
        Xhwlay_ErrorStack::clear();
        Xhwlay_ErrorStack::pushCallback(
            array('yb_ErrorStackLogger', 'errorHandler'));

        $renderer =& new yb_smarty_Renderer();
        $config =& new Xhwlay_Config_PHPArray($this->pageFlow);

        // setup "setup" hooks (executed before xhwlay)
        $h1 =& Xhwlay_Hook::getInstance(XHWLAY_RUNNER_HOOK_SETUP);
        $h1->pushCallback(array(&$this, 'hookSetupSession'));
        $h1->pushCallback(array(&$this, 'hookLoginRedirect'));

        $h2 =& Xhwlay_Hook::getInstance(XHWLAY_RUNNER_HOOK_CLASSLOAD);
        $h2->pushCallback(array(&$this, 'hookClassload'));

        $runner =& new Xhwlay_Runner();
        $runner->setBookmarkContainerClassName(
            "Xhwlay_Bookmark_FileStoreContainer");
        $bcparams = _YB('xhwlay.bookmarkcontainer.params');
        $bcparams['identKeys'] = array('session_id' => session_id());
        $runner->setBookmarkContainerParams($bcparams);
        $runner->setConfig($config);
        $runner->setRenderer($renderer);

        return $runner->run();
    }

    // }}}
    // {{{ hookSetupSession()

    function hookSetupSession($hook, &$runner)
    {
        yb_Session::start();

        // get BCID from request variables.
        $bcid = yb_Var::request('_bcid_');
        if (is_null($bcid)) { $bcid = ""; }
        // get Event from request parameters.
        $event = '';
        $_requests = yb_Var::request();
        foreach ($_requests as $_k => $_v) {
            if (preg_match('/^_event_(\w+)$/', $_k, $m)) {
                $event = $m[1];
            }
        }
        if (empty($event) && isset($_requests['_event_'])) {
            $event = $_requests['_event_'];
        }

        Xhwlay_Var::set(XHWLAY_VAR_KEY_BCID, $bcid);
        Xhwlay_Var::set(XHWLAY_VAR_KEY_EVENT, $event);

        $renderer =& $runner->getRenderer();
        $user = yb_Session::user_context();
        $renderer->set('user_context', $user);
    }

    // }}}
    // {{{ hookLoginRedirect()

    function hookLoginRedirect($hook, &$runner)
    {
        $login_url = yb_Util::redirect_url(array('mdl' => 'login', 
            'back' => yb_Util::current_url()));

        if ($this->need_login && !yb_Session::isAuthenticated()) {
            HTTP_Header::redirect($login_url);
            $runner->wipeout();
        }

        if (count($this->roles) == 0) {
            return;
        }

        $role_found = false;
        foreach ($this->roles as $_r) {
            if (yb_Session::hasRole($_r)) {
                $role_found = true;
                break;
            }
        }

        if (!$role_found) {
            HTTP_Header::redirect($login_url);
            $runner->wipeout();
        }
    }

    // }}}
    // {{{ hookClassload()

    /**
     * Custom class loading hook
     *
     * @param string Hook name
     * @param array Callback array
     */
    function hookClassload($hook, $params)
    {
        if (!isset($params['class'])) {
            return;
        }
        $klass = $params['class'];
        // translate PEAR-like class name to actual file path
        $klass = strtr($klass, "_", "/");
        $file = $klass . ".php";
        require_once($file);
    }

    // }}}
    // {{{ onAlwaysSuccessEvent()

    function onAlwaysSuccessEvent(&$runner, $event, &$bookmark, $params)
    {
        return "success";
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
