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
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->carnaval = new Carnaval();

        $settings = My::settings();
        if ($settings->carnaval_active) {
            dcCore::app()->tpl->addValue('CommentIfMe', FrontendTemplate::CommentIfMe(...));

            if ($settings->carnaval_colors) {
                dcCore::app()->addBehavior('publicHeadContent', FrontendBehaviors::publicHeadContent(...));
            }
        }

        return true;
    }
}
