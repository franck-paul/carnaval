<?php

# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Carnaval a plugin for Dotclear 2.
#
# Copyright (c) 2008-2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

use Dotclear\Helper\Html\Html;

if (dcCore::app()->blog->settings->carnaval->carnaval_active) {
    dcCore::app()->tpl->addValue('CommentIfMe', ['publicCarnaval','CommentIfMe']);

    if (dcCore::app()->blog->settings->carnaval->carnaval_colors) {
        dcCore::app()->addBehavior('publicHeadContent', ['publicCarnaval','publicHeadContent']);
    }
}

class publicCarnaval
{
    public static function CommentIfMe($attr)
    {
        $ret = $attr['return'] ?? 'me';
        $ret = Html::escapeHTML($ret);

        return
        '<?php if (dcCore::app()->ctx->comments->isMe()) { ' .
        "echo '" . addslashes($ret) . "'; } " .
        'echo publicCarnaval::getCommentClass(); ?>';
    }

    public static function getCommentClass()
    {
        $classe_perso = dcCore::app()->carnaval->getCommentClass(dcCore::app()->ctx->comments->getEmail(false));

        return Html::escapeHTML($classe_perso);
    }

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
        $file = carnavalConfig::imagesPath() . '/' . $image;
        if ($value && file_exists($file)) {
            $css[$selector]['background-image'] = 'url(' . carnavalConfig::imagesURL() . '/' . $image . ')';
        }
    }
}
