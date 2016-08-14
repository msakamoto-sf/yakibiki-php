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
 * KinoWiki's Id tag:
 * "Id: charentityref.inc.php,v 1.2 2005/06/15 12:16:14 youka Exp "
 *
 * http://www.w3.org/TR/1999/REC-html401-19991224/sgml/entities.html
 */

/*
 * Wiki's special character entity adjusting regexp
 *
 * from KinoWiki's charentityref.inc.php
 *
 * Special Thanks to KinoWiki: http://kinowiki.net/
 */

define('CHARACTER_ENTITY_REFERENCES', '(?:e(?:grave|acute|circ|u(?:ro|ml)|t(?:a|h)|psilon|xist|m(?:sp|pty)|quiv|nsp)|r(?:e(?:al|g)|a(?:ng|dic|rr|quo)|ho|Arr|ceil|floor|lm|s(?:aquo|quo)|dquo)|l(?:a(?:ng|rr|mbda|quo)|Arr|o(?:z|wast)|e|ceil|floor|t|rm|s(?:aquo|quo)|dquo)|p(?:ound|lusmn|ar(?:a|t)|i(?:v)?|hi|si|r(?:o(?:d|p)|ime)|er(?:p|mil))|D(?:elta|agger)|d(?:e(?:lta|g)|i(?:ams|vide)|a(?:gger|rr)|Arr)|b(?:rvbar|eta|ull|dquo)|s(?:ect|hy|u(?:p(?:2|3|1|e)?|b(?:e)?|m)|zlig|i(?:m|gma(?:f)?)|dot|pades|caron|bquo)|m(?:acr|i(?:nus|ddot|cro)|u|dash)|n(?:bsp|ot(?:in)?|tilde|u|abla|i|e|sub|dash)|z(?:eta|w(?:j|nj))|t(?:i(?:lde|mes)|h(?:insp|e(?:ta(?:sym)?|re4)|orn)|au|rade)|c(?:e(?:dil|nt)|u(?:p|rren)|o(?:ng|py)|cedil|hi|rarr|ap|lubs|irc)|Y(?:acute|uml)|S(?:igma|caron)|o(?:r(?:d(?:f|m))?|grave|acute|circ|ti(?:lde|mes)|uml|slash|m(?:ega|icron)|line|plus|elig)|O(?:grave|acute|circ|tilde|uml|slash|m(?:ega|icron)|Elig)|g(?:amma|e|t)|a(?:c(?:irc|ute)|grave|acute|tilde|uml|ring|elig|l(?:efsym|pha)|n(?:d|g)|symp|mp)|quot|h(?:e(?:arts|llip)|arr|Arr)|i(?:excl|quest|grave|acute|circ|uml|ota|mage|sin|n(?:t|fin))|f(?:ra(?:c(?:34|1(?:4|2))|sl)|nof|orall)|u(?:ml|grave|a(?:rr|cute)|circ|uml|psi(?:h|lon)|Arr)|weierp|P(?:i|hi|si|rime)|xi|kappa|C(?:cedil|hi)|U(?:grave|acute|circ|uml|psilon)|T(?:HORN|heta|au)|Rho|Xi|N(?:tilde|u)|Mu|Lambda|Kappa|I(?:grave|acute|circ|uml|ota)|E(?:grave|acute|circ|uml|TH|psilon|ta)|Zeta|Gamma|Beta|A(?:grave|acute|circ|tilde|uml|ring|Elig|lpha)|y(?:en|acute|uml))');

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
