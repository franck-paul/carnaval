<?php
/**
 * @brief carnaval, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\carnaval;

use dcAdmin;
use dcCore;
use dcNsProcess;

class Backend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->carnaval = new Carnaval();

        dcCore::app()->addBehaviors([
            'exportFullV2'   => [BackendBehaviors::class, 'exportFull'],
            'exportSingleV2' => [BackendBehaviors::class, 'exportSingle'],
            'importInitV2'   => [BackendBehaviors::class, 'importInit'],
            'importFullV2'   => [BackendBehaviors::class, 'importFull'],
            'importSingleV2' => [BackendBehaviors::class, 'importSingle'],
        ]);

        dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
            __('Carnaval'),
            My::makeUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        return true;
    }
}
