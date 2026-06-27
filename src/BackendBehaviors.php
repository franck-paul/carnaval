<?php

/**
 * @brief carnaval, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\carnaval;

use Dotclear\App;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Plugin\importExport\FlatBackupItem;
use Dotclear\Plugin\importExport\FlatExport;
use Dotclear\Plugin\importExport\FlatImportV2;

class BackendBehaviors
{
    public static function exportFull(FlatExport $exp): string
    {
        $exp->exportTable(Carnaval::CARNAVAL_TABLE_NAME);

        return '';
    }

    public static function exportSingle(FlatExport $exp, string $blog_id): string
    {
        $sql = new SelectStatement();
        $sql
            ->columns([
                'comment_author',
                'comment_author_mail',
                'comment_class',
                'comment_text_color',
                'comment_background_color',
            ])
            ->from($sql->as(App::db()->con()->prefix() . Carnaval::CARNAVAL_TABLE_NAME, 'C'))
            ->where('C.blog_id = ' . $sql->quote($blog_id))
        ;

        $exp->export(
            Carnaval::CARNAVAL_TABLE_NAME,
            $sql->statement()
        );

        return '';
    }

    public static function importInit(FlatImportV2 $bk): string
    {
        $bk->cur_alias = App::db()->con()->openCursor(App::db()->con()->prefix() . Carnaval::CARNAVAL_TABLE_NAME); // @phpstan-ignore property.notFound
        $bk->carnaval  = new Carnaval();                                                                // @phpstan-ignore property.notFound
        $bk->classes   = $bk->carnaval->getClasses();                                                   // @phpstan-ignore property.notFound

        return '';
    }

    public static function importFull(FlatBackupItem $line, FlatImportV2 $bk): string
    {
        if ($line->__name == Carnaval::CARNAVAL_TABLE_NAME) {
            $bk->cur_alias->clean();    // @phpstan-ignore method.nonObject, property.notFound

            $bk->cur_alias->blog_id                  = (string) $line->blog_id;                     // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $bk->cur_alias->comment_author           = (string) $line->comment_author;              // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $bk->cur_alias->comment_author_mail      = (string) $line->comment_author_mail;         // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $bk->cur_alias->comment_class            = (string) $line->comment_class;               // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $bk->cur_alias->comment_text_color       = (string) $line->comment_text_color;          // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $bk->cur_alias->comment_background_color = (string) $line->comment_background_color;    // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject

            $bk->cur_alias->insert();   // @phpstan-ignore method.nonObject, property.notFound
        }

        return '';
    }

    public static function importSingle(FlatBackupItem $line, FlatImportV2 $bk): string
    {
        if ($line->__name == Carnaval::CARNAVAL_TABLE_NAME) {
            $bk->carnaval->addClass(      // @phpstan-ignore method.nonObject, property.notFound
                $line->comment_author,              // @phpstan-ignore property.notFound
                $line->comment_author_mail,         // @phpstan-ignore property.notFound
                $line->comment_class,               // @phpstan-ignore property.notFound
                $line->comment_text_color,          // @phpstan-ignore property.notFound
                $line->comment_background_color     // @phpstan-ignore property.notFound
            );
        }

        return '';
    }
}
