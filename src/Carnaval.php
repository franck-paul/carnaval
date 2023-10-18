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
use Dotclear\Database\MetaRecord;
use Dotclear\Interface\Core\BlogInterface;
use Dotclear\Interface\Core\ConnectionInterface;
use Exception;

class Carnaval
{
    public const CARNAVAL_TABLE_NAME = 'carnaval';

    /**
     * @var array<string, mixed>
     */
    public array $found;    // Avoid multiple SQL requests

    private BlogInterface $blog;
    private ?ConnectionInterface $con;
    private string $table;

    public function __construct()
    {
        $this->blog = & dcCore::app()->blog;

        $this->con   = $this->blog->con;
        $this->table = $this->blog->prefix . self::CARNAVAL_TABLE_NAME;

        $this->found = [
            'comments' => [],
        ];
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
        $strReq = 'SELECT class_id, comment_author, comment_author_mail, comment_class,  ' .
            'comment_text_color, comment_background_color ' .
            'FROM ' . $this->table . ' ' .
            "WHERE blog_id = '" . $this->con->escapeStr($this->blog->id) . "' ";

        if (isset($params['class_id'])) {
            $strReq .= 'AND class_id = ' . (int) $params['class_id'] . ' ';
        }
        if (isset($params['mail'])) {
            $strReq .= 'AND comment_author_mail <> \'\' ' .
                'AND comment_author_mail = \'' .
                $this->con->escapeStr($params['mail']) . '\'';
        }

        return new MetaRecord($this->con->select($strReq));
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

        $cur->update('WHERE class_id = ' . (int) $id .
            " AND blog_id = '" . $this->con->escapeStr($this->blog->id) . "'");

        $this->blog->triggerBlog();
    }

    public function delClass(string $id): void
    {
        $id = (int) $id;

        $strReq = 'DELETE FROM ' . $this->table . ' ' .
                "WHERE blog_id = '" . $this->con->escapeStr($this->blog->id) . "' " .
                'AND class_id = ' . $id . ' ';

        $this->con->execute($strReq);
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
