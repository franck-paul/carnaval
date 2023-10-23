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
use Dotclear\Core\Process;
use Dotclear\Database\Structure;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            // Init
            $new_structure = new Structure(App::con(), App::con()->prefix());

            $new_structure->carnaval
                ->field('class_id', 'integer', 0, false)
                ->field('blog_id', 'varchar', 32, false)
                ->field('comment_author', 'varchar', 255, false)
                ->field('comment_author_mail', 'varchar', 255, false)
                ->field('comment_class', 'varchar', 255, false)
                ->field('comment_text_color', 'varchar', 7, false)
                ->field('comment_background_color', 'varchar', 7, false)

                ->primary('pk_carnaval', 'class_id')
                ->index('idx_class_blog_id', 'btree', 'blog_id')
            ;

            $current_structure = new Structure(App::con(), App::con()->prefix());
            $current_structure->synchronize($new_structure);

            $settings = My::settings();
            $settings->put('carnaval_active', false, App::blogWorkspace()::NS_BOOL, 'Carnaval activation flag', false, true);
            $settings->put('carnaval_colors', false, App::blogWorkspace()::NS_BOOL, 'Use colors defined with Carnaval plugin', false, true);
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }
}
