<?php

/**
 * @brief Carnaval, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Osku and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Carnaval',
        'Identify comments',
        'Osku and contributors',
        '9.0',
        [
            'date'     => '2026-08-03T09:46:11+0200',
            'requires' => [
                ['core', '2.40'],
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
}
