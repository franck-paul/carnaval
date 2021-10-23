<?php
/**
 * @brief Carnaval, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Carnaval',  // Name
    'Identify comments', // Description
    'Osku and contributors',                // Author
    '1.6.1',                        // Version
    [
        //        'requires'    => [['core', '2.14']],
        'permissions' => 'contentadmin',
        'type'        => 'plugin',
        'settings'    => [
        ],

        'details'    => 'https://open-time.net/?q=carnaval',       // Details URL
        'support'    => 'https://github.com/franck-paul/carnaval', // Support URL
        'repository' => 'https://raw.githubusercontent.com/franck-paul/carnaval/main/dcstore.xml'
    ]
);
