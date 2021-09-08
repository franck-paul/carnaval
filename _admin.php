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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('exportFull',array('carnavalBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('carnavalBehaviors','exportSingle'));
$core->addBehavior('importInit',array('carnavalBehaviors','importInit'));
$core->addBehavior('importFull',array('carnavalBehaviors','importFull'));
$core->addBehavior('importSingle',array('carnavalBehaviors','importSingle'));

$_menu['Plugins']->addItem(__('Carnaval'),
	'plugin.php?p=carnaval','index.php?pf=carnaval/icon.png',
	preg_match('/plugin.php\?p=carnaval(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id));

# Behaviors
class carnavalBehaviors
{
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('carnaval');
	}

	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('carnaval',
			'SELECT comment_author, comment_author_mail, comment_class, '.
			'comment_text_color, comment_background_color '.
			'FROM '.$core->prefix.'carnaval C '.
			"WHERE C.blog_id = '".$blog_id."'"
		);
	}

	public static function importInit($bk,$core)
	{
		$bk->cur_alias = $core->con->openCursor($core->prefix.'carnaval');
		$bk->carnaval = new dcCarnaval($core);
		$bk->classes = $bk->carnaval->getClasses();
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'carnaval')
		{
			$bk->cur_alias->clean();

			$bk->cur_alias->blog_id = (string) $line->blog_id;
			$bk->cur_alias->comment_author = (string) $line->comment_author;
			$bk->cur_alias->comment_author_mail = (string) $line->comment_author_mail;
			$bk->cur_alias->comment_class = (string) $line->comment_class;
			$bk->cur_alias->comment_text_color = (string) $line->comment_text_color;
			$bk->cur_alias->comment_background_color = (string) $line->comment_background_color;

			$bk->cur_alias->insert();
		}
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'carnaval')
		{
			$bk->carnaval->addClass($line->comment_author,$line->comment_author_mail,$line->comment_class,
			$line->comment_text_color,$line->comment_background_color);
		}
	}
}
?>