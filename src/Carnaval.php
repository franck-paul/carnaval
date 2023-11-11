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
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Dotclear\Interface\Core\BlogInterface;
use Dotclear\Interface\Core\ConnectionInterface;
use Exception;

class Carnaval
{
    public const CARNAVAL_TABLE_NAME = 'carnaval';

    /**
     * @var array<string, mixed>
     */
    public array $found = [
        'comments' => [],
    ];    // Avoid multiple SQL requests

    private BlogInterface $blog;

    private ConnectionInterface $con;

    private string $table;

    public function __construct()
    {
        $this->blog = App::blog();

        $this->con   = App::con();
        $this->table = App::con()->prefix() . self::CARNAVAL_TABLE_NAME;
    }

    /**
     * Gets the classes.
     *
     * @param      array<string, mixed>       $params  The parameters
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
            $sql->and('class_id = ' . (int) $params['class_id']);
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
     * @param      string      $id     The identifier
     *
     * @return     MetaRecord  The class.
     */
    public function getClass(string $id): MetaRecord
    {
        return $this->getClasses(['class_id' => $id]);
    }

    /**
     * Adds a class.
     *
     * @param      mixed      $author  The author
     * @param      mixed      $mail    The mail
     * @param      mixed      $text    The text
     * @param      mixed      $backg   The backg
     * @param      mixed      $class   The class
     *
     * @throws     Exception
     */
    public function addClass($author, $mail, $text, $backg, $class): void
    {
        $cur                           = $this->con->openCursor($this->table);
        $cur->blog_id                  = (string) $this->blog->id;
        $cur->comment_author           = (string) $author;
        $cur->comment_author_mail      = (string) $mail;
        $cur->comment_class            = (string) $class;
        $cur->comment_text_color       = (string) $text;
        $cur->comment_background_color = (string) $backg;

        if ($cur->comment_author == '') {
            throw new Exception(__('You must provide a name.'));
        }

        if ($cur->comment_class == '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cur->comment_author_mail == '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $strReq = 'SELECT MAX(class_id) FROM ' . $this->table;

        $rs            = new MetaRecord($this->con->select($strReq));
        $cur->class_id = (int) $rs->f(0) + 1;
        $cur->insert();

        $this->blog->triggerBlog();
    }

    public function updateClass(string $id, string $author, string $mail = '', string $text = '', string $backg = '', string $class = ''): void
    {
        $cur = $this->con->openCursor($this->table);

        $cur->comment_author_mail      = $mail;
        $cur->comment_class            = $class;
        $cur->comment_text_color       = $text;
        $cur->comment_background_color = $backg;

        if ($author != '') {
            $cur->comment_author = $author;
        }

        if ($cur->comment_class == '') {
            throw new Exception(__('You must provide a CSS Class.'));
        }

        if ($cur->comment_author_mail == '') {
            throw new Exception(__('You must provide an e-mail.'));
        }

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('class_id = ' . (int) $id)
            ->update($cur);

        $this->blog->triggerBlog();
    }

    public function delClass(string $id): void
    {
        $sql = new DeleteStatement();
        $sql
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog->id()))
            ->and('class_id = ' . (int) $id)
            ->delete();

        $this->blog->triggerBlog();
    }

    public function getCommentClass(string $mail): string
    {
        if (isset($this->found['comments'][$mail])) {
            return $this->found['comments'][$mail];
        }

        $rs = $this->getClasses(['mail' => $mail]);

        $this->found['comments'][$mail] = $rs->isEmpty() ? '' : ' ' . $rs->comment_class;

        return $this->found['comments'][$mail];
    }
}
