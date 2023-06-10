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

use Dotclear\Helper\Html\Html;

class FrontendTemplate
{
    public static function CommentIfMe($attr)
    {
        $ret = $attr['return'] ?? 'me';
        $ret = Html::escapeHTML($ret);

        return
        '<?php if (dcCore::app()->ctx->comments->isMe()) { ' .
        "echo '" . addslashes($ret) . "'; } " .
        'echo ' . self::class . '::getCommentClass(); ?>';
    }

    public static function getCommentClass()
    {
        $classe_perso = dcCore::app()->carnaval->getCommentClass(dcCore::app()->ctx->comments->getEmail(false));

        return Html::escapeHTML($classe_perso);
    }
}
