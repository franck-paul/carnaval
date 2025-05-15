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
    '6.1',
    [
        'date'     => '2025-05-15T17:19:45+0200',
        'requires' => [
            ['core', '2.34'],
            ['TemplateHelper'],
        ],
        'permissions' => '',
        'type'        => 'plugin',
        'settings'    => [
        ],

        'details'    => 'https://open-time.net/?q=carnaval',
        'support'    => 'https://github.com/franck-paul/carnaval',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/carnaval/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
