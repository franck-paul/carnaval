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

class BackendBehaviors
{
    public static function exportFull($exp)
    {
        $exp->exportTable('carnaval');
    }

    public static function exportSingle($exp, $blog_id)
    {
        $exp->export(
            'carnaval',
            'SELECT comment_author, comment_author_mail, comment_class, ' .
            'comment_text_color, comment_background_color ' .
            'FROM ' . dcCore::app()->prefix . 'carnaval C ' .
            "WHERE C.blog_id = '" . $blog_id . "'"
        );
    }

    public static function importInit($bk)
    {
        $bk->cur_alias = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'carnaval');
        $bk->carnaval  = new Carnaval();
        $bk->classes   = $bk->carnaval->getClasses();
    }

    public static function importFull($line, $bk)
    {
        if ($line->__name == 'carnaval') {
            $bk->cur_alias->clean();

            $bk->cur_alias->blog_id                  = (string) $line->blog_id;
            $bk->cur_alias->comment_author           = (string) $line->comment_author;
            $bk->cur_alias->comment_author_mail      = (string) $line->comment_author_mail;
            $bk->cur_alias->comment_class            = (string) $line->comment_class;
            $bk->cur_alias->comment_text_color       = (string) $line->comment_text_color;
            $bk->cur_alias->comment_background_color = (string) $line->comment_background_color;

            $bk->cur_alias->insert();
        }
    }

    public static function importSingle($line, $bk)
    {
        if ($line->__name == 'carnaval') {
            $bk->carnaval->addClass(
                $line->comment_author,
                $line->comment_author_mail,
                $line->comment_class,
                $line->comment_text_color,
                $line->comment_background_color
            );
        }
    }
}
