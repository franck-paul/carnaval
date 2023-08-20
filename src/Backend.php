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

use dcCore;
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->carnaval = new Carnaval();

        dcCore::app()->addBehaviors([
            'exportFullV2'   => BackendBehaviors::exportFull(...),
            'exportSingleV2' => BackendBehaviors::exportSingle(...),
            'importInitV2'   => BackendBehaviors::importInit(...),
            'importFullV2'   => BackendBehaviors::importFull(...),
            'importSingleV2' => BackendBehaviors::importSingle(...),
        ]);

        dcCore::app()->admin->menus[Menus::MENU_BLOG]->addItem(
            __('Carnaval'),
            My::manageUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        return true;
    }
}
