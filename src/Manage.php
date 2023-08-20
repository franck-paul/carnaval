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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Text;
use Exception;
use form;

class Manage extends Process
{
    private static bool $can_write_images = false;
    private static bool $add_carnaval     = false;

    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $settings = dcCore::app()->blog->settings->get(My::id());

        self::$can_write_images = CoreHelper::canWriteImages();
        self::$add_carnaval     = false;

        if (!empty($_POST['carnaval_class'])) {
            $comment_author           = $_POST['comment_author'];
            $comment_author_mail      = $_POST['comment_author_mail'];
            $comment_class            = strtolower(Text::str2URL($_POST['comment_class']));
            $comment_text_color       = CoreHelper::adjustColor($_POST['comment_text_color']);
            $comment_background_color = CoreHelper::adjustColor($_POST['comment_background_color']);

            if (!empty($_REQUEST['id'])) {
                $id = $_REQUEST['id'];

                try {
                    dcCore::app()->carnaval ->updateClass(
                        $id,
                        $comment_author,
                        $comment_author_mail,
                        $comment_text_color,
                        $comment_background_color,
                        $comment_class
                    );
                    if (self::$can_write_images) {
                        CoreHelper::createImages($comment_background_color, $comment_class);
                    }

                    Notices::addSuccessNotice(__('CSS Class has been successfully updated.'));
                    dcCore::app()->admin->url->redirect('admin.plugin.' . My::id());
                } catch (Exception $e) {
                    dcCore::app()->error->add($e->getMessage());
                }
            } else {
                try {
                    dcCore::app()->carnaval->addClass(
                        $comment_author,
                        $comment_author_mail,
                        $comment_text_color,
                        $comment_background_color,
                        $comment_class
                    );
                    if (self::$can_write_images) {
                        CoreHelper::createImages($comment_background_color, $comment_class);
                    }

                    Notices::addSuccessNotice(__('Class has been successfully created.'));
                    dcCore::app()->admin->url->redirect('admin.plugin.' . My::id());
                } catch (Exception $e) {
                    self::$add_carnaval = true;
                    dcCore::app()->error->add($e->getMessage());
                }
            }
        }

        // Delete CSS Class
        if (!empty($_POST['removeaction']) && !empty($_POST['select'])) {
            foreach ($_POST['select'] as $k => $v) {
                try {
                    dcCore::app()->carnaval ->delClass($v);
                } catch (Exception $e) {
                    dcCore::app()->error->add($e->getMessage());

                    break;
                }
            }

            if (!dcCore::app()->error->flag()) {
                Notices::addSuccessNotice(__('Classes have been successfully removed.'));
                dcCore::app()->admin->url->redirect('admin.plugin.' . My::id());
            }
        }

        // Saving new configuration
        if (!empty($_POST['saveconfig'])) {
            try {
                $active = (empty($_POST['active'])) ? false : true;
                $colors = (empty($_POST['colors'])) ? false : true;

                $settings->put('carnaval_active', $active, 'boolean', 'Carnaval activation flag');
                $settings->put('carnaval_colors', $colors, 'boolean', 'Use colors defined with Carnaval plugin');

                dcCore::app()->blog->triggerBlog();

                Notices::addSuccessNotice(__('Configuration successfully updated.'));
                dcCore::app()->admin->url->redirect('admin.plugin.' . My::id());
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $settings = dcCore::app()->blog->settings->get(My::id());

        $comment_author = $comment_author_mail = $comment_class = $comment_text_color = $comment_background_color = '';

        $legend = __('New CSS Class');
        $button = __('save');

        // Getting current parameters
        $active = (bool) $settings->carnaval_active;
        $colors = (bool) $settings->carnaval_colors;

        try {
            if (!empty($_REQUEST['id'])) {
                $rs = dcCore::app()->carnaval ->getClass($_REQUEST['id']);

                self::$add_carnaval = true;
                $legend             = __('Edit CSS Class');
                $button             = __('update');

                $comment_author           = $rs->comment_author;
                $comment_author_mail      = $rs->comment_author_mail;
                $comment_class            = $rs->comment_class;
                $comment_text_color       = $rs->comment_text_color;
                $comment_background_color = $rs->comment_background_color;
                unset($rs);
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        // Get CSS Classes
        $rs = null;

        try {
            $rs = dcCore::app()->carnaval ->getClasses();
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        $head = My::jsLoad('admin.js') .
        My::cssLoad('style.css') .
        Page::jsJson('carnaval', ['delete_records' => __('Are you sure you want to delete selected CSS Classes ?')]);

        if (!self::$add_carnaval) {
            $head .= My::jsLoad('form.js');
        }

        Page::openModule(__('Carnaval'), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Carnaval')                              => '',
            ]
        );
        echo Notices::getNotices();

        // Form
        echo
        '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="config-form"><fieldset><legend>' . __('Plugin activation') . '</legend><p class="field">' .
        form::checkbox('active', 1, $active) .
        '<label class=" classic" for="active">' . __('Enable Carnaval') . '</label></p><p class="field">' .
        form::checkbox('colors', 1, $colors) .
        '<label class=" classic" for="colors">' . __('Use defined colors') . '</label></p><p>' . form::hidden(['p'], 'carnaval') .
        dcCore::app()->formNonce() .
        '<input type="submit" name="saveconfig" accesskey="s" value="' . __('Save configuration') . '"/>' .
        '</p>' .
        '</fieldset></form>';

        if (!$rs->isEmpty()) {
            echo
            '<form class="clear" action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="classes-form">' .
            '<fieldset class="two-cols"><legend>' . __('My CSS Classes') . '</legend>' .
            '<table class="maximal">' .
            '<thead>' .
            '<tr>' .
                '<th colspan="2">' . __('Name') . '</th>' .
                '<th>' . __('CSS Class') . '</th>' .
                '<th>' . __('Mail') . '</th>' .
                '<th colspan="2">' . __('Colors') . '</th>' .
            '</tr>' .
            '</thead>' .
            '<tbody id="classes-list">';

            while ($rs->fetch()) {
                $color           = $rs->comment_text_color       ?? 'inherit';
                $backgroundcolor = $rs->comment_background_color ?? 'inherit';

                echo
                '<tr class="line" id="l_' . $rs->class_id . '">' .
                '<td class="minimal">' . form::checkbox(['select[]'], $rs->class_id) . '</td>' .
                '<td>' . Html::escapeHTML($rs->comment_author) . '</td>' .
                '<td><code>' . Html::escapeHTML($rs->comment_class) . '</code></td>' .
                '<td>' . Html::escapeHTML($rs->comment_author_mail) . '</td>' .
                '<td><span style="padding:1px 5px;color:' . $color . ';background-color:' . $backgroundcolor . '">' . __('Thanks to use Carnaval') . '</span></td>' .
                '<td class="nowrap status"><a href="' . dcCore::app()->admin->getPageURL() . '&amp;id=' . $rs->class_id . '"><img src="images/edit-mini.png" alt="" title="' . __('Edit this record') . '" /></a></td>' .
                '</tr>';
            }

            echo '</tbody></table>';

            echo
            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .
            '<p class="col right">' .
                form::hidden(['p'], 'carnaval') .
                dcCore::app()->formNonce() .
                '<input type="submit" class="delete" name="removeaction" accesskey="d" value="' . __('delete') . '" onclick="return window.confirm(dotclear.msg.delete_records)" />' .
            '</p></div></fieldset></form>';
        }

        if (!self::$add_carnaval) {
            echo '<div id="new-class"><h3><a class="new" id="carnaval-control" href="#">' .
            __('New CSS class') . '</a></h3></div>';
        }

        echo
        '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="add-css"><fieldset class="clear"><legend>' . $legend . '</legend><p class="field"><label class="classic required" title="' . __('Required field') . '">' . __('Name:') .
        form::field('comment_author', 30, 255, Html::escapeHTML($comment_author), '', '2') .
        '</label></p><p class="field"><label class="classic required" title="' . __('Required field') . '">' . __('CSS Class:') .
        form::field('comment_class', 30, 255, Html::escapeHTML($comment_class), '', '3') .
        '</label></p><p class="field"><label class="classic required">' . __('Mail:') .
        form::field('comment_author_mail', 30, 255, Html::escapeHTML($comment_author_mail), '', '4') .
        '</label></p>' .
        '<p class="field"><label class="classic">' . __('Text color:') .
        form::field('comment_text_color', 7, 7, Html::escapeHTML($comment_text_color), 'colorpicker', '6') .
        '</label></p><p class="field"><label class="classic">' . __('Background color:') .
        form::field('comment_background_color', 7, 7, Html::escapeHTML($comment_background_color), 'colorpicker', '7') .
        '</label></p>' .
        form::hidden(['p'], 'carnaval') .
        dcCore::app()->formNonce();

        if (!empty($_REQUEST['id'])) {
            echo form::hidden('id', $_REQUEST['id']);
        }

        echo
        '<input type="submit" name="carnaval_class" accesskey="a" value="' . $button . '" tabindex="6" /></fieldset></form>';

        Page::helpBlock('carnaval');

        Page::closeModule();
    }
}
