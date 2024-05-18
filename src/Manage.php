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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Button;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Color;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Text as Txt;
use Exception;

class Manage extends Process
{
    private static bool $can_write_images = false;

    private static bool $add_carnaval = false;

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

        $settings = My::settings();

        self::$can_write_images = CoreHelper::canWriteImages();
        self::$add_carnaval     = false;

        if (!empty($_POST['carnaval_class'])) {
            $comment_author           = $_POST['comment_author'];
            $comment_author_mail      = $_POST['comment_author_mail'];
            $comment_class            = strtolower(Txt::str2URL($_POST['comment_class']));
            $comment_text_color       = CoreHelper::adjustColor($_POST['comment_text_color']);
            $comment_background_color = CoreHelper::adjustColor($_POST['comment_background_color']);

            if (!empty($_REQUEST['id'])) {
                $id = $_REQUEST['id'];

                try {
                    App::backend()->carnaval->updateClass(
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
                    My::redirect();
                } catch (Exception $e) {
                    App::error()->add($e->getMessage());
                }
            } else {
                try {
                    App::backend()->carnaval->addClass(
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
                    My::redirect();
                } catch (Exception $e) {
                    self::$add_carnaval = true;
                    App::error()->add($e->getMessage());
                }
            }
        }

        // Delete CSS Class
        if (!empty($_POST['removeaction']) && !empty($_POST['select'])) {
            foreach ($_POST['select'] as $v) {
                try {
                    App::backend()->carnaval->delClass($v);
                } catch (Exception $e) {
                    App::error()->add($e->getMessage());

                    break;
                }
            }

            if (!App::error()->flag()) {
                Notices::addSuccessNotice(__('Classes have been successfully removed.'));
                My::redirect();
            }
        }

        // Saving new configuration
        if (!empty($_POST['saveconfig'])) {
            try {
                $active = !empty($_POST['active']);
                $colors = !empty($_POST['colors']);

                $settings->put('carnaval_active', $active, 'boolean', 'Carnaval activation flag');
                $settings->put('carnaval_colors', $colors, 'boolean', 'Use colors defined with Carnaval plugin');

                App::blog()->triggerBlog();

                Notices::addSuccessNotice(__('Configuration successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
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

        $settings                 = My::settings();
        $comment_author           = '';
        $comment_author_mail      = '';
        $comment_class            = '';
        $comment_text_color       = '#ffffff';
        $comment_background_color = '#000000';

        $legend = __('New CSS Class');
        $button = __('save');

        // Getting current parameters
        $active = (bool) $settings->carnaval_active;
        $colors = (bool) $settings->carnaval_colors;

        try {
            if (!empty($_REQUEST['id'])) {
                $rs = App::backend()->carnaval->getClass($_REQUEST['id']);

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
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        // Get CSS Classes
        $rs = null;

        try {
            $rs = App::backend()->carnaval->getClasses();
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        $head = My::jsLoad('admin.js') .
        My::cssLoad('style.css') .
        Page::jsJson('carnaval', ['delete_records' => __('Are you sure you want to delete selected CSS Classes ?')]);

        if (!self::$add_carnaval) {
            $head .= My::jsLoad('form.js');
        }

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('Carnaval')                        => '',
            ]
        );
        echo Notices::getNotices();

        // Form
        echo
        (new Form('config-form'))
            ->action(App::backend()->getPageURL())
            ->method('post')
            ->fields([
                (new Fieldset())
                    ->legend((new Legend(__('Plugin activation'))))
                    ->fields([
                        (new Para())
                            ->items([
                                (new Checkbox('active', $active))
                                    ->value(1)
                                    ->label((new Label(__('Enable Carnaval'), Label::INSIDE_TEXT_AFTER))),
                                (new Checkbox('colors', $colors))
                                    ->value(1)
                                    ->label((new Label(__('Use defined colors'), Label::INSIDE_TEXT_AFTER))),
                            ]),
                        (new Submit(['saveconfig']))
                            ->accesskey('s')
                            ->value(__('Save configuration')),
                        ... My::hiddenFields(),
                    ]),
            ])
        ->render();

        if (!$rs->isEmpty()) {
            $rows = [];
            while ($rs->fetch()) {
                $color           = $rs->comment_text_color       ?? '#ffffff';
                $backgroundcolor = $rs->comment_background_color ?? '#000000';

                $rows[] = (new Tr('l_' . $rs->class_id))
                    ->class('line')
                    ->items([
                        (new Td())
                            ->class('minimal')
                            ->items([
                                (new Checkbox(['select[]']))
                                    ->label(new Label($rs->class_id, Label::INSIDE_TEXT_AFTER)),
                            ]),
                        (new Td())
                            ->text(Html::escapeHTML($rs->comment_author)),
                        (new Td())
                            ->text('<code>' . Html::escapeHTML($rs->comment_class) . '</code>'),
                        (new Td())
                            ->text(Html::escapeHTML($rs->comment_author_mail)),
                        (new Td())
                            ->text('<span style="padding:1px 5px;color:' . $color . ';background-color:' . $backgroundcolor . '">' . __('Thanks to use Carnaval') . '</span>'),
                        (new Td())
                            ->class(['nowrap', 'status'])
                            ->items([
                                (new Link())
                                    ->href(My::manageUrl(['id' => $rs->class_id]))
                                    ->items([
                                        (new Img('images/edit.svg'))
                                            ->class('mark mark-edit light-only')
                                            ->alt(__('Edit this record'))
                                            ->title(__('Edit this record')),
                                        (new Img('images/edit-dark.svg'))
                                            ->class('mark mark-edit dark-only')
                                            ->alt(__('Edit this record'))
                                            ->title(__('Edit this record')),
                                    ]),
                            ]),
                    ]);
            }

            echo
            (new Form('classes-form'))
                ->action(App::backend()->getPageURL())
                ->method('post')
                ->class('clear')
                ->fields([
                    (new Fieldset())
                        ->class('two-cols')
                        ->legend(new Legend(__('My CSS Classes')))
                        ->fields([
                            (new Table())
                                ->class('maximal')
                                ->thead(
                                    (new Thead())
                                    ->items([
                                        (new Tr())
                                            ->items([
                                                (new Th())
                                                    ->colspan(2)
                                                    ->text(__('Name')),
                                                (new Th())
                                                    ->text(__('CSS Class')),
                                                (new Th())
                                                    ->text(__('Mail')),
                                                (new Th())
                                                    ->colspan(2)
                                                    ->text(__('Colors')),
                                            ]),
                                    ])
                                )
                                ->tbody(
                                    (new Tbody('classes-list'))
                                    ->items($rows)
                                ),
                            (new Div())
                                ->class('two-cols')
                                ->items([
                                    (new Para())
                                        ->class(['col', 'checkboxes-helpers']),
                                    (new Para())
                                        ->class(['col', 'right', 'form-buttons'])
                                        ->items([
                                            (new Submit('removeaction', __('delete')))
                                                ->accesskey('d')
                                                ->class('delete')
                                                ->extra('onclick="return window.confirm(dotclear.msg.delete_records)'),
                                            ... My::hiddenFields(),
                                        ]),
                                ]),
                        ]),
                ])
            ->render();
        }

        if (!self::$add_carnaval) {
            echo
            (new Button('carnaval-control', __('New CSS class')))
            ->class(['add', 'button'])
            ->render();
        }

        $params = [];
        if (!empty($_REQUEST['id'])) {
            $params = ['id' => $_REQUEST['id']];
        }

        echo
        (new Form('add-css'))
            ->action(App::backend()->getPageURL())
            ->method('post')
            ->items([
                (new Fieldset())
                    ->class('clear')
                    ->legend(new Legend($legend))
                    ->items([
                        (new Para())
                            ->items([
                                (new Input('comment_author'))
                                    ->size(30)
                                    ->maxlength(255)
                                    ->default(Html::escapeHTML($comment_author))
                                    ->label(
                                        (new Label(
                                            '<abbr title="' . __('Required field') . '">*</abbr> ' . __('Name:'),
                                            Label::OUTSIDE_LABEL_BEFORE
                                        ))
                                        ->class('required')
                                    ),
                            ]),
                        (new Para())
                            ->items([
                                (new Input('comment_class'))
                                    ->size(30)
                                    ->maxlength(255)
                                    ->default(Html::escapeHTML($comment_class))
                                    ->label(
                                        (new Label(
                                            '<abbr title="' . __('Required field') . '">*</abbr> ' . __('CSS Class:'),
                                            Label::OUTSIDE_LABEL_BEFORE
                                        ))
                                        ->class('required')
                                    ),
                            ]),
                        (new Para())
                            ->items([
                                (new Input('comment_author_mail'))
                                    ->size(30)
                                    ->maxlength(255)
                                    ->default(Html::escapeHTML($comment_author_mail))
                                    ->label(
                                        (new Label(
                                            '<abbr title="' . __('Required field') . '">*</abbr> ' . __('Mail:'),
                                            Label::OUTSIDE_LABEL_BEFORE
                                        ))
                                        ->class('required')
                                    ),
                            ]),
                        (new Para())
                            ->items([
                                (new Color('comment_text_color'))
                                    ->default(Html::escapeHTML($comment_text_color))
                                    ->label((new Label(__('Text color:'), Label::OUTSIDE_LABEL_BEFORE))),
                            ]),
                        (new Para())
                            ->items([
                                (new Color('comment_background_color'))
                                    ->default(Html::escapeHTML($comment_background_color))
                                    ->label((new Label(__('Background color:'), Label::OUTSIDE_LABEL_BEFORE))),
                            ]),
                        (new Para())
                            ->items([
                                (new Submit('carnaval_class', $button))
                                    ->accesskey('a'),
                                ... My::hiddenFields($params),
                            ]),
                    ]),
            ])
        ->render();

        Page::helpBlock('carnaval');

        Page::closeModule();
    }
}
