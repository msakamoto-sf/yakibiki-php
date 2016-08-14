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
 * YakiBiki Default Index Dispatcher
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: index.php 476 2008-12-03 14:35:05Z msakamoto-sf $
 */

require_once(dirname(__FILE__) . '/ybprepare.php');

$mdl = yb_Var::request('mdl');
if (is_null($mdl) || !preg_match('/^[0-9A-Za-z\-_]+$/m', $mdl)) {
    $mdl = _YB('default.module');
}

$conv = _YB('module.convert.' . $mdl);
if (is_string($conv) && preg_match('/^[0-9A-Za-z\-_]+$/m', $conv)) {
    $mdl = $conv;
}

$dispatcher = 'yb/mdl/' . $mdl . '/dispatcher.php';
$ret = include_once $dispatcher;
if (!$ret) {
    dlog("module [ {$mdl} ] include result : {$ret}");
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
