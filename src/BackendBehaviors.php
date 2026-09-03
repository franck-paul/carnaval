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
    public static function exportFull(FlatExport $flatExport): string
    {
        $flatExport->exportTable(Carnaval::CARNAVAL_TABLE_NAME);

        return '';
    }

    public static function exportSingle(FlatExport $flatExport, string $blog_id): string
    {
        $selectStatement = new SelectStatement();
        $selectStatement
            ->columns([
                'comment_author',
                'comment_author_mail',
                'comment_class',
                'comment_text_color',
                'comment_background_color',
            ])
            ->from($selectStatement->as(App::db()->con()->prefix() . Carnaval::CARNAVAL_TABLE_NAME, 'C'))
            ->where('C.blog_id = ' . $selectStatement->quote($blog_id))
        ;

        $flatExport->export(
            Carnaval::CARNAVAL_TABLE_NAME,
            $selectStatement->statement()
        );

        return '';
    }

    public static function importInit(FlatImportV2 $flatImportV2): string
    {
        $flatImportV2->cur_alias = App::db()->con()->openCursor(App::db()->con()->prefix() . Carnaval::CARNAVAL_TABLE_NAME); // @phpstan-ignore property.notFound
        $flatImportV2->carnaval  = new Carnaval();                                                                // @phpstan-ignore property.notFound
        $flatImportV2->classes   = $flatImportV2->carnaval->getClasses();                                                   // @phpstan-ignore property.notFound

        return '';
    }

    public static function importFull(FlatBackupItem $flatBackupItem, FlatImportV2 $flatImportV2): string
    {
        if ($flatBackupItem->__name == Carnaval::CARNAVAL_TABLE_NAME) {
            $flatImportV2->cur_alias->clean();    // @phpstan-ignore method.nonObject, property.notFound

            $flatImportV2->cur_alias->blog_id                  = (string) $flatBackupItem->blog_id;                     // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $flatImportV2->cur_alias->comment_author           = (string) $flatBackupItem->comment_author;              // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $flatImportV2->cur_alias->comment_author_mail      = (string) $flatBackupItem->comment_author_mail;         // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $flatImportV2->cur_alias->comment_class            = (string) $flatBackupItem->comment_class;               // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $flatImportV2->cur_alias->comment_text_color       = (string) $flatBackupItem->comment_text_color;          // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject
            $flatImportV2->cur_alias->comment_background_color = (string) $flatBackupItem->comment_background_color;    // @phpstan-ignore property.notFound, cast.string, property.notFound, property.nonObject

            $flatImportV2->cur_alias->insert();   // @phpstan-ignore method.nonObject, property.notFound
        }

        return '';
    }

    public static function importSingle(FlatBackupItem $flatBackupItem, FlatImportV2 $flatImportV2): string
    {
        if ($flatBackupItem->__name == Carnaval::CARNAVAL_TABLE_NAME) {
            $flatImportV2->carnaval->addClass(      // @phpstan-ignore method.nonObject, property.notFound
                $flatBackupItem->comment_author,              // @phpstan-ignore property.notFound
                $flatBackupItem->comment_author_mail,         // @phpstan-ignore property.notFound
                $flatBackupItem->comment_class,               // @phpstan-ignore property.notFound
                $flatBackupItem->comment_text_color,          // @phpstan-ignore property.notFound
                $flatBackupItem->comment_background_color     // @phpstan-ignore property.notFound
            );
        }

        return '';
    }
}
