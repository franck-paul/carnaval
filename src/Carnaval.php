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
use Exception;

class Carnaval
{
    public const CARNAVAL_TABLE_NAME = 'carnaval';

    public $found;	// Avoid multiple SQL requests

    private $blog;
    private $con;
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

    public function getClasses($params = [])
    {
        $strReq = 'SELECT class_id, comment_author, comment_author_mail, comment_class,  ' .
            'comment_text_color, comment_background_color ' .
            'FROM ' . $this->table . ' ' .
            "WHERE blog_id = '" . $this->con->escape($this->blog->id) . "' ";

        if (isset($params['class_id'])) {
            $strReq .= 'AND class_id = ' . (int) $params['class_id'] . ' ';
        }
        if (isset($params['mail'])) {
            $strReq .= 'AND comment_author_mail <> \'\' ' .
                'AND comment_author_mail = \'' .
                $this->con->escape($params['mail']) . '\'';
        }

        return new MetaRecord($this->con->select($strReq));
    }

    public function getClass($id)
    {
        return $this->getClasses(['class_id' => $id]);
    }

    public function addClass($author, $mail, $text, $backg, $class)
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

    public function updateClass($id, $author, $mail = '', $text = '', $backg = '', $class = '')
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
            " AND blog_id = '" . $this->con->escape($this->blog->id) . "'");

        $this->blog->triggerBlog();
    }

    public function delClass($id)
    {
        $id = (int) $id;

        $strReq = 'DELETE FROM ' . $this->table . ' ' .
                "WHERE blog_id = '" . $this->con->escape($this->blog->id) . "' " .
                'AND class_id = ' . $id . ' ';

        $this->con->execute($strReq);
        $this->blog->triggerBlog();
    }

    public function getCommentClass($mail)
    {
        if (isset($this->found['comments'][$mail])) {
            return $this->found['comments'][$mail];
        }

        $rs                             = $this->getClasses(['mail' => $mail]);
        $this->found['comments'][$mail] = $rs->isEmpty() ? '' : ' ' . $rs->comment_class;

        return $this->found['comments'][$mail];
    }
}
