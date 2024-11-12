<?php
/**
 * @brief Carnaval, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Osku and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
$this->registerModule(
    'Carnaval',
    'Identify comments',
    'Osku and contributors',
    '5.6',
    [
        'requires'    => [['core', '2.30']],
        'permissions' => '',
        'type'        => 'plugin',
        'settings'    => [
        ],

        'details'    => 'https://open-time.net/?q=carnaval',
        'support'    => 'https://github.com/franck-paul/carnaval',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/carnaval/main/dcstore.xml',
    ]
);
