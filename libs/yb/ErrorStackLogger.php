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
 * YakiBiki's Xhwlay_ErrorStack Logger
 *
 * @author msakamoto-sf <msakamoto-sf@users.sourceforge.net>
 * @version $Id: ErrorStackLogger.php 242 2008-03-29 04:56:07Z msakamoto-sf $
 */
class yb_ErrorStackLogger
{
    // {{{ errorHandler()

    function errorHandler($error)
    {
        $logger =& yb_Log::get_logger();
        $msg = sprintf("[code:%s] %s", $error['code'], $error['message']);
        switch ($error['level']) {
        case 'info':
            $logger->info($msg);
            break;
        case 'warn':
            $logger->warning($msg);
            break;
        case 'error':
            $logger->err($msg);
        default:
            $logger->debug($msg);
            break;
        }

        return PEAR_ERRORSTACK_IGNORE;
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
