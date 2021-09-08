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
if (!defined('DC_RC_PATH')) { return; }

if ($core->blog->settings->carnaval->carnaval_active)
{
	$core->tpl->addValue('CommentIfMe',array('publicCarnaval','CommentIfMe'));

	if ($core->blog->settings->carnaval->carnaval_colors){
		$core->addBehavior('publicHeadContent',array('publicCarnaval','publicHeadContent'));
	}
}

class publicCarnaval
{
	public static function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ".
		"echo publicCarnaval::getCommentClass(); ?>";
	}
	
	public static function getCommentClass()
	{
		global $core, $_ctx;
		$classe_perso = $core->carnaval->getCommentClass($_ctx->comments->getEmail(false));
		
		return html::escapeHTML($classe_perso);
	}
	
	public static function publicHeadContent()
	{
		echo '<style type="text/css">'."\n".self::carnavalStyleHelper()."\n</style>\n";
	}
	
	public static function carnavalStyleHelper()
	{
		global $core;
	
		$cval = $core->carnaval->getClasses();
		$css = array();
		while ($cval->fetch())
			{
				$res = '';
				$cl_class = $cval->comment_class;
				$cl_txt = $cval->comment_text_color;
				$cl_backg = $cval->comment_background_color;
				self::prop($css,'#comments dd.'.$cl_class,'color',$cl_txt);
				self::prop($css,'#comments dd.'.$cl_class,'background-color',$cl_backg);
				if ($core->blog->settings->system->theme == 'default') {
					self::backgroundImg($css,'#comments dt.'.$cl_class, $cl_backg,$cl_class.'-comment-t.png');
					self::backgroundImg($css,'#comments dd.'.$cl_class,$cl_backg,$cl_class.'-comment-b.png');
				}
				foreach ($css as $selector => $values)
				{
					$res .= $selector." {\n";
					foreach ($values as $k => $v) {
						$res .= $k.':'.$v.";\n";
					}
					$res .= "}\n";
				}
			}
			return $res;
	}

	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
	
	protected static function backgroundImg(&$css,$selector,$value,$image)
	{
		$file = carnavalConfig::imagesPath().'/'.$image;
		if ($value && file_exists($file)){
			$css[$selector]['background-image'] = 'url('.carnavalConfig::imagesURL().'/'.$image.')';
		}
	}
}
?>