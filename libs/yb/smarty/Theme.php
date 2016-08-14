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
 * YakiBiki Smarty Theme (Smarty resource plugin)
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: Theme.php 333 2008-09-12 06:10:15Z msakamoto-sf $
 */
class yb_smarty_Theme
{

    // {{{ properties

    /**
     * Assoc-array for storing template resource.
     * (Smarty interface)
     *
     * @type array
     * @access private
     */
    var $_tpl_resources;

    /**
     * Field container
     *
     * @type array
     * @access private
     */
    var $_properties = array();

    // }}}
    // {{{ getInstance()

    /**
     * Singleton interface
     *
     * @static
     * @access public
     * @return object reference
     */
    function &getInstance()
    {
        static $instance = null;
        if(is_null($instance)) {
            $instance = new yb_smarty_Theme();
        }

        return $instance;
    }

    // }}}
    // {{{ set()

    /**
     * set field value.
     *
     * @access public
     * @param string field name
     * @param string value
     */
    function set($property, $value)
    {
        $this->_properties[$property] = $value;
    }

    // }}}
    // {{{ get()

    /**
     * get field value.
     *
     * @access public
     * @param string field name
     * @return string value
     */
    function get($property)
    {
        if(isset($this->_properties[$property])) {
            return $this->_properties[$property];
        } else {
            return null;
        }
    }

    // }}}
    // {{{ getTplTimestamp()

    /**
     * Get timestamp of template resource.
     * (also, load template resource.)
     * (Smarty resource plugin interface)
     *
     * @access public
     * @param string template resource name
     * @return integer unit-timestamp of template resource
     */
    function getTplTimestamp($tpl_name)
    {
        if (isset($this->_tpl_resources[$tpl_name]['update_date'])) {
            // If cached, return it.
            return $this->_tpl_resources[$tpl_name]['update_date'];
        }

        // template file name (Theme directory)
        $fn_theme = _YB('dir.themes') . '/' . _YB('theme') . '/templates/' . $tpl_name;

        // template file name (Default theme directory)
        $fn_default = _YB('dir.themes') . '/default/templates/' . $tpl_name;

        if (is_readable($fn_theme)) {
            // If theme file is valid

            // load template file
            $this->_tpl_resources[$tpl_name]['tpl_resource'] = 
                $this->_getFileResource($fn_theme);
            $this->_tpl_resources[$tpl_name]['update_date'] = 
                filemtime($fn_theme);

            return $this->_tpl_resources[$tpl_name]['update_date'];

        } else if (is_readable($fn_default)) {
            // If default file is valid

            // load template file
            $this->_tpl_resources[$tpl_name]['tpl_resource'] = 
                $this->_getFileResource($fn_default);
            $this->_tpl_resources[$tpl_name]['update_date'] = 
                filemtime($fn_default);

            return $this->_tpl_resources[$tpl_name]['update_date'];

        } else {
            // invalid template name

            return false;
        }
    }

    // }}}
    // {{{ getTplResource()

    /**
     * Get template resource.
     * (Smarty resource plugin interface)
     *
     * @access public
     * @param string template resource name
     * @return string template resource data. If failure, return false.
     */
    function getTplResource($tpl_name)
    {
        if ($this->getTplTimestamp($tpl_name)) {
            return $this->_tpl_resources[$tpl_name]['tpl_resource'];
        } else {
            return false;
        }
    }

    // }}}
    // {{{ _getFileResource()

    /**
     * Load given template file and return its data.
     *
     * @access private
     * @param string file name
     * @return string file contents data. If failure, return false.
     */
    function _getFileResource($filepath)
    {
        if (!is_readable($filepath)) {
            return false;
        }

        $_lines = @file($filepath);
        if($_lines === false) {
            return false;
        }

        $filesource = implode("", $_lines);
        return $filesource;
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
