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

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::backend()->carnaval = new Carnaval();

        App::behavior()->addBehaviors([
            'exportFullV2'   => BackendBehaviors::exportFull(...),
            'exportSingleV2' => BackendBehaviors::exportSingle(...),
            'importInitV2'   => BackendBehaviors::importInit(...),
            'importFullV2'   => BackendBehaviors::importFull(...),
            'importSingleV2' => BackendBehaviors::importSingle(...),
        ]);

        My::addBackendMenuItem(App::backend()->menus()::MENU_BLOG);

        return true;
    }
}
