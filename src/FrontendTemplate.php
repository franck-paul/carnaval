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

use ArrayObject;
use Dotclear\App;
use Dotclear\Helper\Html\Html;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     *
     * @return     string
     */
    public static function CommentIfMe(array|ArrayObject $attr): string
    {
        $ret = $attr['return'] ?? 'me';
        $ret = Html::escapeHTML($ret);

        return
        '<?php if (App::frontend()->context()->comments->isMe()) { ' .
        "echo '" . addslashes($ret) . "'; } " .
        'echo ' . self::class . '::getCommentClass(); ?>';
    }

    public static function getCommentClass(): string
    {
        $classe_perso = App::frontend()->carnaval->getCommentClass(App::frontend()->context()->comments->getEmail(false));

        return Html::escapeHTML($classe_perso);
    }
}
