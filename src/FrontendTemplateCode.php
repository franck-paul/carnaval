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

class FrontendTemplateCode
{
    /**
     * PHP code for tpl:CommentIfMe value
     */
    public static function CommentIfMe(
        string $_ret_,
    ): void {
        if (App::frontend()->context()->comments->isMe()) {
            echo $_ret_;
        }
        echo \Dotclear\Helper\Html\Html::escapeHTML(App::frontend()->carnaval->getCommentClass(App::frontend()->context()->comments->getEmail(false)));
    }
}
