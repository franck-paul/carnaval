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
use dcNamespace;
use dcNsProcess;
use Dotclear\Database\Structure;
use Exception;

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = My::checkContext(My::INSTALL);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        try {
            // Init
            $new_structure = new Structure(dcCore::app()->con, dcCore::app()->prefix);

            $new_structure->carnaval
                ->class_id('integer', 0, false)
                ->blog_id('varchar', 32, false)
                ->comment_author('varchar', 255, false)
                ->comment_author_mail('varchar', 255, false)
                ->comment_class('varchar', 255, false)
                ->comment_text_color('varchar', 7, false)
                ->comment_background_color('varchar', 7, false)

                ->primary('pk_carnaval', 'class_id')
                ->index('idx_class_blog_id', 'btree', 'blog_id')
            ;

            $current_structure = new Structure(dcCore::app()->con, dcCore::app()->prefix);
            $current_structure->synchronize($new_structure);

            $settings = dcCore::app()->blog->settings->get(My::id());
            $settings->put('carnaval_active', false, dcNamespace::NS_BOOL, 'Carnaval activation flag', false, true);
            $settings->put('carnaval_colors', false, dcNamespace::NS_BOOL, 'Use colors defined with Carnaval plugin', false, true);
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
