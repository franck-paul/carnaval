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
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Interface\Core\BlogInterface;
use Exception;

class Carnaval
{
    /**
     * @var string CARNAVAL_TABLE_NAME
     */
    public const CARNAVAL_TABLE_NAME = 'carnaval';

    /**
     * @var array<string, string>
     */
    private array $cache = [];

    private readonly BlogInterface $blog;

    private readonly string $table;

    public function __construct()
    {
        $this->blog = App::blog();

        $this->table = App::db()->con()->prefix() . self::CARNAVAL_TABLE_NAME;
    }

    /**
     * Gets the classes.
     *
     * @param      array{class_id?: int, mail?: string}       $params  The parameters
     *
     * @return     MetaRecord  The classes.
     */
    public function getClasses(array $params = []): MetaRecord
    {
        $selectStatement = new SelectStatement();
        $selectStatement
            ->columns([
                'class_id',
                'comment_author',
                'comment_author_mail',
                'comment_class',
                'comment_text_color',
                'comment_background_color',
            ])
            ->from($this->table)
            ->where('blog_id = ' . $selectStatement->quote($this->blog->id()));
        ;

        if (isset($params['class_id'])) {
            $selectStatement->and('class_id = ' . $params['class_id']);
        }

        if (isset($params['mail'])) {
            $selectStatement
                ->and('comment_author_mail <> ' . $selectStatement->quote(''))
                ->and('comment_author_mail = ' . $selectStatement->quote($params['mail']))
            ;
        }

        return $selectStatement->select() ?? MetaRecord::newFromArray([]);
    }

    /**
     * Gets the class.
     *
     * @param      int      $id     The identifier
     *
     * @return     MetaRecord  The class.
     */
    public function getClass(int $id): MetaRecord
    {
        return $this->getClasses(['class_id' => $id]);
    }

    /**
     * Adds a class.
     *
     * @param      string      $author  The author
     * @param      string      $mail    The mail
     * @param      string      $text    The text
     * @param      string      $backg   The backg
     * @param      string      $class   The class
     *
     * @throws     Exception
     */
    public function addClass(string $author, string $mail, string $text, string $backg, string $class): void
    {
        $cursor = App::db()->con()->openCursor($this->table);
        $cursor->setStrField('blog_id', $this->blog->id());
        $cursor->setStrField('comment_author', $author);
        $cursor->setStrField('comment_author_mail', $mail);
        $cursor->setStrField('comment_class', $class);
        $cursor->setStrField('comment_text_color', $text);
        $cursor->setStrField('comment_background_color', $backg);

        if ($cursor->strField('comment_author') === '') {
            throw new Exception(__('You must provide a name.'));
        }

        if ($cursor->strField('comment_class') === '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cursor->strField('comment_author_mail') === '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $selectStatement = new SelectStatement();
        $selectStatement
            ->from($this->table)
            ->column($selectStatement->max('class_id'));

        $metaRecord = $selectStatement->select();
        $last_id    = $metaRecord instanceof MetaRecord ? $metaRecord->cardinal() : 0;

        $cursor->setIntField('class_id', $last_id + 1);
        $cursor->insert();

        $this->blog->triggerBlog();
    }

    public function updateClass(int $id, string $author, string $mail = '', string $text = '', string $backg = '', string $class = ''): void
    {
        $cursor = App::db()->con()->openCursor($this->table);

        $cursor->setStrField('comment_author_mail', $mail);
        $cursor->setStrField('comment_class', $class);
        $cursor->setStrField('comment_text_color', $text);
        $cursor->setStrField('comment_background_color', $backg);

        if ($author !== '') {
            $cursor->setStrField('comment_author', $author);
        }

        if ($cursor->strField('comment_class') === '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cursor->strField('comment_author_mail') === '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $updateStatement = new UpdateStatement();
        $updateStatement
            ->where('blog_id = ' . $updateStatement->quote($this->blog->id()))
            ->and('class_id = ' . $id)
            ->update($cursor);

        $this->blog->triggerBlog();
    }

    public function delClass(int $id): void
    {
        $deleteStatement = new DeleteStatement();
        $deleteStatement
            ->from($this->table)
            ->where('blog_id = ' . $deleteStatement->quote($this->blog->id()))
            ->and('class_id = ' . $id)
            ->delete();

        $this->blog->triggerBlog();
    }

    public function getCommentClass(string $mail): string
    {
        if (isset($this->cache[$mail])) {
            return $this->cache[$mail];
        }

        $metaRecord = $this->getClasses(['mail' => $mail]);
        $class      = $metaRecord->isEmpty() ? '' : $metaRecord->strField('comment_class');

        $this->cache[$mail] = $class;

        return $class;
    }
}
