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

class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::frontend()->carnaval = new Carnaval();

        $settings = My::settings();
        if ($settings->carnaval_active) {
            App::frontend()->template()->addValue('CommentIfMe', FrontendTemplate::CommentIfMe(...));

            if ($settings->carnaval_colors) {
                App::behavior()->addBehaviors([
                    'publicHeadContent' => FrontendBehaviors::publicHeadContent(...),
                ]);
            }
        }

        return true;
    }
}
