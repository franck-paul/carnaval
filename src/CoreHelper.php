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
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Exception;

class CoreHelper
{
    /**
     * Check a CSS color
     *
     * @param  null|string  $c  CSS color
     *
     * @return string    checked CSS color
     */
    public static function adjustColor(?string $c): string
    {
        if ($c === '') {
            return '';
        }

        $c = strtoupper($c);

        if (preg_match('/^[A-F0-9]{3,6}$/', $c)) {
            $c = '#' . $c;
        }

        if (preg_match('/^#[A-F0-9]{6}$/', $c)) {
            return $c;
        }

        if (preg_match('/^#[A-F0-9]{3,}$/', $c)) {
            return '#' . substr($c, 1, 1) . substr($c, 1, 1) . substr($c, 2, 1) . substr($c, 2, 1) . substr($c, 3, 1) . substr($c, 3, 1);
        }

        return '';
    }

    /**
     * @return     false|string
     */
    public static function imagesPath(): string|bool
    {
        return Path::real(dcCore::app()->blog->public_path) . '/carnaval-images';
    }

    public static function imagesURL(): string
    {
        return dcCore::app()->blog->settings->system->public_url . '/carnaval-images';
    }

    public static function canWriteImages(bool $create = false): bool
    {
        $public = Path::real(dcCore::app()->blog->public_path);
        $imgs   = self::imagesPath();

        if ($public === false) {
            return false;
        }

        if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng') || !function_exists('imagecreatefrompng')) {
            return false;
        }

        if (!is_dir($public)) {
            return false;
        }

        if ($imgs !== false && !is_dir($imgs)) {
            if (!is_writable($public)) {
                return false;
            }
            if ($create) {
                Files::makeDir($imgs);
            }

            return true;
        }

        if ($imgs === false || !is_writable($imgs)) {
            return false;
        }

        return true;
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

        $color = self::adjustColor($color);

        self::commentImages($color, $comment_t, $comment_b, $cval_comment_t, $cval_comment_b);
    }

    protected static function commentImages(string $comment_color, string $comment_t, string $comment_b, string $dest_t, string $dest_b): void
    {
        $comment_color = sscanf($comment_color, '#%2X%2X%2X');

        $d_comment_t = imagecreatetruecolor(500, 25);
        if ($d_comment_t === false) {
            return;
        }

        $fill = imagecolorallocate($d_comment_t, $comment_color[0], $comment_color[1], $comment_color[2]);
        imagefill($d_comment_t, 0, 0, (int) $fill);

        $s_comment_t = imagecreatefrompng($comment_t);
        if ($s_comment_t === false) {
            imagedestroy($d_comment_t);

            return;
        }

        imagealphablending($s_comment_t, true);
        imagecopy($d_comment_t, $s_comment_t, 0, 0, 0, 0, 500, 25);

        imagepng($d_comment_t, self::imagesPath() . '/' . $dest_t);
        imagedestroy($d_comment_t);
        imagedestroy($s_comment_t);

        $d_comment_b = imagecreatetruecolor(500, 7);
        if ($d_comment_b === false) {
            return;
        }

        $fill = imagecolorallocate($d_comment_b, $comment_color[0], $comment_color[1], $comment_color[2]);
        imagefill($d_comment_b, 0, 0, (int) $fill);

        $s_comment_b = imagecreatefrompng($comment_b);
        if ($s_comment_b === false) {
            imagedestroy($d_comment_b);

            return;
        }

        imagealphablending($s_comment_b, true);
        imagecopy($d_comment_b, $s_comment_b, 0, 0, 0, 0, 500, 7);
        imagedestroy($s_comment_b);

        imagepng($d_comment_b, self::imagesPath() . '/' . $dest_b);
        imagedestroy($d_comment_b);
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
