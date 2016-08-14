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
 * YakiBiki Smarty Renderer
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Renderer.php 324 2008-09-10 04:10:10Z msakamoto-sf $
 */

/**
 * requires
 */
require_once('Xhwlay/Renderer/AbstractRenderer.php');
require_once(_YB('dir.libs') . '/smarty/Smarty.class.php');

/**
 * YakiBiki Smarty Renderer for Xhwlay
 */
class yb_smarty_Renderer extends Xhwlay_Renderer_AbstractRenderer
{
    // {{{ properties

    /**
     * Smarty instance
     *
     * @access public
     * @type Smarty Object
     */
    var $smarty;

    // }}}
    // {{{ yb_smarty_Renderer()

    /**
     * Constructor
     *
     * @access public
     */
    function yb_smarty_Renderer()
    {
        $this->smarty =& new Smarty();
        $this->smarty->left_delimiter = '<{';
        $this->smarty->right_delimiter = '}>';
        $this->smarty->compile_dir = realpath(_YB('dir.smarty.templates_c'));
        $this->smarty->compile_check = _YB('smarty.compile_check');
        $this->smarty->force_compile = _YB('smarty.force_compile');
        $this->smarty->plugins_dir = array(
            SMARTY_DIR . '/plugins/',
            _YB('dir.smarty.plugins'),
        );
        // disable <{php}> - <{/php}>
        $this->smarty->secure = false;

        // YakiBiki's default modifier wrapper
        $this->smarty->default_modifiers = array('yb_escape');

        $this->smarty->assign('hide_backurl', false);
    }

    // }}}
    // {{{ render()

    /**
     * Interface for rendering handler
     *
     * @access public
     * @return mixed Depends on implements/extends class.
     */
    function render()
    {
        foreach ($this->_vars as $k => $v) {
            $this->smarty->assign($k, $v);
        }
        return $this->smarty->fetch($this->_viewName);
    }

    // }}}
    // {{{ setTitle()

    /**
     * Set page title
     *
     * @access public
     * @param string page title
     */
    function setTitle($title)
    {
        $this->smarty->assign('title', $title);
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
