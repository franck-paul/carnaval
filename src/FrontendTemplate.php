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
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\TemplateHelper\Code;

class FrontendTemplate
{
    /**
     * @param      array<string, mixed>|\ArrayObject<string, mixed>  $attr      The attribute
     */
    public static function CommentIfMe(array|ArrayObject $attr): string
    {
        return Code::getPHPTemplateValueCode(
            FrontendTemplateCode::CommentIfMe(...),
            [
                addslashes(Html::escapeHTML($attr['return'] ?? 'me')),
            ],
        );
    }
}
