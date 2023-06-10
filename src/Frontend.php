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
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = My::checkContext(My::FRONTEND);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->carnaval = new Carnaval();

        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->carnaval_active) {
            dcCore::app()->tpl->addValue('CommentIfMe', [FrontendTemplate::class,'CommentIfMe']);

            if ($settings->carnaval_colors) {
                dcCore::app()->addBehavior('publicHeadContent', [FrontendBehaviors::class,'publicHeadContent']);
            }
        }

        return true;
    }
}
