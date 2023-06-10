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

class FrontendBehaviors
{
    public static function publicHeadContent()
    {
        echo '<style type="text/css">' . "\n" . self::carnavalStyleHelper() . "\n</style>\n";
    }

    public static function carnavalStyleHelper()
    {
        $cval = dcCore::app()->carnaval->getClasses();
        $css  = [];
        $res  = '';
        while ($cval->fetch()) {
            $cl_class = $cval->comment_class;
            $cl_txt   = $cval->comment_text_color;
            $cl_backg = $cval->comment_background_color;
            self::prop($css, '#comments dd.' . $cl_class, 'color', $cl_txt);
            self::prop($css, '#comments dd.' . $cl_class, 'background-color', $cl_backg);
            if (dcCore::app()->blog->settings->system->theme == 'blowup') {
                self::backgroundImg($css, '#comments dt.' . $cl_class, $cl_backg, $cl_class . '-comment-t.png');
                self::backgroundImg($css, '#comments dd.' . $cl_class, $cl_backg, $cl_class . '-comment-b.png');
            }
            foreach ($css as $selector => $values) {
                $res .= $selector . " {\n";
                foreach ($values as $k => $v) {
                    $res .= $k . ':' . $v . ";\n";
                }
                $res .= "}\n";
            }
        }

        return $res;
    }

    protected static function prop(&$css, $selector, $prop, $value)
    {
        if ($value) {
            $css[$selector][$prop] = $value;
        }
    }

    protected static function backgroundImg(&$css, $selector, $value, $image)
    {
        $file = CoreHelper::imagesPath() . '/' . $image;
        if ($value && file_exists($file)) {
            $css[$selector]['background-image'] = 'url(' . CoreHelper::imagesURL() . '/' . $image . ')';
        }
    }
}
