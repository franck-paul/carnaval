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

class FrontendBehaviors
{
    public static function publicHeadContent(): string
    {
        echo '<style type="text/css">' . "\n" . self::carnavalStyleHelper() . "\n</style>\n";

        return '';
    }

    public static function carnavalStyleHelper(): string
    {
        if (!App::frontend()->carnaval instanceof Carnaval) {
            return '';
        }

        $rs = App::frontend()->carnaval->getClasses();

        /**
         * @var array<string, array<string>>
         */
        $css = [];

        $res = '';

        while ($rs->fetch()) {
            $comment_class            = $rs->strField('comment_class');
            $comment_text_color       = $rs->strField('comment_text_color', true) ?: '#ffffff';
            $comment_background_color = $rs->strField('comment_background_color') ?: '#000000';

            $css['#comments dd.' . $comment_class] = [
                'color: ' . $comment_text_color . ';',
                'background-color: ', $comment_background_color,';',
            ];

            $theme = App::blog()->settings()->get('system')->getStr('theme', false);
            if ($theme === 'blowup') {
                $image = $comment_class . '-comment-t.png';
                $file  = CoreHelper::imagesPath() . '/' . $image;
                if (file_exists($file)) {
                    $css['#comments dt.' . $comment_class] = [
                        'background-image: url(' . CoreHelper::imagesURL() . '/' . $image . ');',
                    ];
                }

                $image = $comment_class . '-comment-b.png';
                $file  = CoreHelper::imagesPath() . '/' . $image;
                if (file_exists($file)) {
                    $css['#comments dd.' . $comment_class] = [
                        'background-image: url(' . CoreHelper::imagesURL() . '/' . $image . ');',
                    ];
                }
            }

            foreach ($css as $selector => $rules) {
                $res .= $selector . " {\n";
                foreach ($rules as $rule) {
                    $res .= $rule . "\n";
                }

                $res .= "}\n";
            }
        }

        return $res;
    }
}
