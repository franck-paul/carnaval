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
use Dotclear\Helper\File\Path;
use Exception;

class CoreHelper
{
    public static function imagesPath(): string
    {
        $p_root = is_string($p_root = Path::real(App::blog()->publicPath())) ? $p_root : '';

        return $p_root . '/carnaval-images';
    }

    public static function imagesURL(): string
    {
        $p_url = is_string($p_url = App::blog()->settings()->system->public_url) ? $p_url : '';

        return $p_url . '/carnaval-images';
    }

    public static function canWriteImages(bool $create = false): bool
    {
        return App::backend()->themeConfig()->canWriteImages(self::imagesPath(), $create);
    }

    public static function createImages(string $color, string $name): void
    {
        if (!self::canWriteImages(true)) {
            throw new Exception(__('Unable to create images.'));
        }

        $blowupConfigRoot = My::path();

        $comment_t = $blowupConfigRoot . '/img/comment-t.png';
        $comment_b = $blowupConfigRoot . '/img/comment-b.png';

        $cval_comment_t = $name . '-comment-t.png';
        $cval_comment_b = $name . '-comment-b.png';

        self::dropImage($cval_comment_t);
        self::dropImage($cval_comment_b);

        $color = App::backend()->themeConfig()->adjustColor($color);

        self::commentImages($color, $comment_t, $comment_b, $cval_comment_t, $cval_comment_b);
    }

    protected static function commentImages(string $comment_color, string $comment_t, string $comment_b, string $dest_t, string $dest_b): void
    {
        $colors = sscanf($comment_color, '#%2X%2X%2X');

        $red   = is_array($colors) && isset($colors[0]) && is_numeric($red = $colors[0]) ? (int) $red : 0;
        $green = is_array($colors) && isset($colors[1]) && is_numeric($green = $colors[1]) ? (int) $green : 0;
        $blue  = is_array($colors) && isset($colors[2]) && is_numeric($blue = $colors[2]) ? (int) $blue : 0;

        $red   = max(0, min(255, $red));
        $green = max(0, min(255, $green));
        $blue  = max(0, min(255, $blue));

        $d_comment_t = imagecreatetruecolor(500, 25);
        if ($d_comment_t === false) {
            return;
        }

        $fill = imagecolorallocate($d_comment_t, $red, $green, $blue);
        imagefill($d_comment_t, 0, 0, (int) $fill);

        $s_comment_t = imagecreatefrompng($comment_t);
        if ($s_comment_t === false) {
            return;
        }

        imagealphablending($s_comment_t, true);
        imagecopy($d_comment_t, $s_comment_t, 0, 0, 0, 0, 500, 25);
        imagepng($d_comment_t, self::imagesPath() . '/' . $dest_t);

        $d_comment_b = imagecreatetruecolor(500, 7);
        if ($d_comment_b === false) {
            return;
        }

        $fill = imagecolorallocate($d_comment_b, $red, $green, $blue);
        imagefill($d_comment_b, 0, 0, (int) $fill);

        $s_comment_b = imagecreatefrompng($comment_b);
        if ($s_comment_b === false) {
            return;
        }

        imagealphablending($s_comment_b, true);
        imagecopy($d_comment_b, $s_comment_b, 0, 0, 0, 0, 500, 7);
        imagepng($d_comment_b, self::imagesPath() . '/' . $dest_b);
    }

    public static function dropImage(string $img): void
    {
        $img = Path::real(self::imagesPath() . '/' . $img);
        if ($img && is_writable(dirname($img))) {
            @unlink($img);
            @unlink(dirname($img) . '/.' . basename($img, '.png') . '_sq.jpg');
            @unlink(dirname($img) . '/.' . basename($img, '.png') . '_m.jpg');
            @unlink(dirname($img) . '/.' . basename($img, '.png') . '_s.jpg');
            @unlink(dirname($img) . '/.' . basename($img, '.png') . '_t.jpg');
        }
    }
}
