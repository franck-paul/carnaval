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
        $sql = new SelectStatement();
        $sql
            ->columns([
                'class_id',
                'comment_author',
                'comment_author_mail',
                'comment_class',
                'comment_text_color',
                'comment_background_color',
            ])
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog->id()));
        ;

        if (isset($params['class_id'])) {
            $sql->and('class_id = ' . $params['class_id']);
        }

        if (isset($params['mail'])) {
            $sql
                ->and('comment_author_mail <> ' . $sql->quote(''))
                ->and('comment_author_mail = ' . $sql->quote($params['mail']))
            ;
        }

        return $sql->select() ?? MetaRecord::newFromArray([]);
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
        $cur                           = App::db()->con()->openCursor($this->table);
        $cur->blog_id                  = $this->blog->id();
        $cur->comment_author           = $author;
        $cur->comment_author_mail      = $mail;
        $cur->comment_class            = $class;
        $cur->comment_text_color       = $text;
        $cur->comment_background_color = $backg;

        if ($cur->comment_author === '') {
            throw new Exception(__('You must provide a name.'));
        }

        if ($cur->comment_class === '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cur->comment_author_mail === '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $sql = new SelectStatement();
        $sql
            ->from($this->table)
            ->column($sql->max('class_id'));

        $rs = $sql->select();
        if ($rs instanceof MetaRecord) {
            $last_id = is_numeric($last_id = $rs->f(0)) ? (int) $last_id : 0;
        } else {
            $last_id = 0;
        }

        $cur->class_id = $last_id + 1;
        $cur->insert();

        $this->blog->triggerBlog();
    }

    public function updateClass(int $id, string $author, string $mail = '', string $text = '', string $backg = '', string $class = ''): void
    {
        $cur = App::db()->con()->openCursor($this->table);

        $cur->comment_author_mail      = $mail;
        $cur->comment_class            = $class;
        $cur->comment_text_color       = $text;
        $cur->comment_background_color = $backg;

        if ($author !== '') {
            $cur->comment_author = $author;
        }

        if ($cur->comment_class === '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cur->comment_author_mail === '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('class_id = ' . $id)
            ->update($cur);

        $this->blog->triggerBlog();
    }

    public function delClass(int $id): void
    {
        $sql = new DeleteStatement();
        $sql
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('class_id = ' . $id)
            ->delete();

        $this->blog->triggerBlog();
    }

    public function getCommentClass(string $mail): string
    {
        if (isset($this->cache[$mail])) {
            return $this->cache[$mail];
        }

        $rs    = $this->getClasses(['mail' => $mail]);
        $class = !$rs->isEmpty() && is_string($class = $rs->comment_class) ? $class : '';

        $this->cache[$mail] = $class;

        return $class;
    }
}
