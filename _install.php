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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$new_version = $core->plugins->moduleInfo('carnaval','version');
 
$current_version = $core->getVersion('carnaval');
 
if (version_compare($current_version,$new_version,'>=')) {
        return;
}

$s = new dbStruct($core->con,$core->prefix);
 
$s->carnaval
	->class_id('integer',0,false)
	->blog_id('varchar',32,	false)
	->comment_author('varchar',255,false)
	->comment_author_mail('varchar',255,false)
	->comment_class('varchar',255,false)
	->comment_text_color('varchar',7,false)
	->comment_background_color('varchar',7,false)
	
	->primary('pk_carnaval','class_id')
	->index('idx_class_blog_id','btree','blog_id')
	;

$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$s =& $core->blog->settings->carnaval;
$s->put('carnaval_active',false,'boolean','Carnaval activation flag',true,true);
$s->put('carnaval_colors',false,'boolean','Use colors defined with Carnaval plugin',true,true);

$core->setVersion('carnaval',$new_version);
return true;
?>