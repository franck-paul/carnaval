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

//$carnaval = new dcCarnaval ($core->blog);
$can_write_images = carnavalConfig::canWriteImages();
$comment_author = $comment_author_mail = $comment_class = 
$comment_text_color = $comment_background_color =  '';

$add_carnaval = false;
$edit_carnaval = false;

$legend = __('New CSS Class');
$button = __('save');

$s =& $core->blog->settings->carnaval;
// Getting current parameters
$active = (boolean)$s->carnaval_active;
$colors = (boolean)$s->carnaval_colors;

try
{
	if (!empty($_REQUEST['id']) ) {
		$rs = $core->carnaval ->getClass($_REQUEST['id']);
		if (!$rs->isEmpty())
		{
			$edit_carnaval = true;
		}
		
		$add_carnaval = true;
		$legend = __('Edit CSS Class');
		$button = __('update');
		
		$comment_author = $rs->comment_author;
		$comment_author_mail = $rs->comment_author_mail;
		$comment_class = $rs->comment_class;
		$comment_text_color = $rs->comment_text_color;
		$comment_background_color = $rs->comment_background_color;
		unset($rs);
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}


if (!empty($_POST['carnaval_class']))
{
	$comment_author = $_POST['comment_author'];
	$comment_author_mail = $_POST['comment_author_mail'];
	$comment_class = strtolower(text::str2URL($_POST['comment_class']));
	$comment_text_color = carnavalConfig::adjustColor($_POST['comment_text_color']);
	$comment_background_color = carnavalConfig::adjustColor($_POST['comment_background_color']);
		
	if (!empty($_REQUEST['id']))
	{
		$id = $_REQUEST['id'];

		try {
			$core->carnaval ->updateClass($id,$comment_author,$comment_author_mail,
					$comment_text_color,$comment_background_color,$comment_class);
					
			if ($can_write_images)
			{
				carnavalConfig::createImages($comment_background_color,$comment_class);
			}
			$redir='&upd=1';
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
	else
	{
		try {
			$core->carnaval->addClass($comment_author,$comment_author_mail,
				$comment_text_color,$comment_background_color,$comment_class);
				
			if ($can_write_images)
			{
				carnavalConfig::createImages($comment_background_color,$comment_class);
			}
			$redir='&add=1';

		} catch (Exception $e) {
			$add_carnaval = true;
			$core->error->add($e->getMessage());

		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.$redir);
	}	
}

# Delete CSS Class
if (!empty($_POST['removeaction']) && !empty($_POST['select'])) {
	foreach ($_POST['select'] as $k => $v)
	{
		try {
			$core->carnaval ->delClass($v);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&removed=1');
	}
}

// Saving new configuration
if (!empty($_POST['saveconfig'])) {
	try
	{
		$active = (empty($_POST['active'])) ? false : true;
		$colors = (empty($_POST['colors'])) ? false : true;
		
		$s->put('carnaval_active',$active,'boolean','Carnaval activation flag');
		$s->put('carnaval_colors',$colors,'boolean','Use colors defined with Carnaval plugin');
		$core->blog->triggerBlog();
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) {
		http::redirect($p_url.'&config=1');
	}
}

# Get CSS Classes
try {
	$rs = $core->carnaval ->getClasses();
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
	<title><?php echo __('Carnaval'); ?></title>
	<?php echo dcPage::jsColorPicker(); ?>
	<?php echo dcPage::jsLoad('index.php?pf=carnaval/admin.js'); ?>
	<link rel="stylesheet" type="text/css" href="index.php?pf=carnaval/style.css" />
	<?php if (!$add_carnaval) {
		echo dcPage::jsLoad('index.php?pf=carnaval/form.js');
	}?>
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.delete_records',__("Are you sure you want to delete selected CSS Classes ?")); ?>
	//]]>
	</script>
</head>
<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Carnaval').'</h2>';

if (!empty($_GET['config'])) {
echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

if (!empty($_GET['removed'])) {
echo '<p class="message">'.__('Classes have been successfully removed.').'</p>';
}

if (!empty($_GET['add'])) {
echo '<p class="message">'.__('Class has been successfully created.').'</p>';
}

if (!empty($_GET['upd'])) {
echo '<p class="message">'.__('CSS Class has been successfully updated.').'</p>';
}

echo 
'<form action="'.$p_url.'" method="post" id="config-form">
<fieldset><legend>'.__('Plugin activation').'</legend>
<p class="field">'.
form::checkbox('active', 1, $active).
'<label class=" classic" for="active">'.__('Enable Carnaval').'</label>
</p>
<p class="field">'.
form::checkbox('colors', 1, $colors).
'<label class=" classic" for="active">'.__('Use defined colors').'</label>
</p>
<p>'.form::hidden(array('p'),'carnaval').
$core->formNonce().
'<input type="submit" name="saveconfig" accesskey="s" value="'.__('Save configuration').'"/>'.
'</p>'.
'</fieldset>
</form>';


if (!$rs->isEmpty())
{
	echo 
	'<form class="clear" action="'.$p_url.'" method="post" id="classes-form">'.
	'<fieldset class="two-cols"><legend>'.__('My CSS Classes').'</legend>'.
	'<table class="maximal">'.
	'<thead>'.
	'<tr>'.
		'<th colspan="2">'.__('Name').'</th>'.
		'<th>'.__('CSS Class').'</th>'.
		'<th>'.__('Mail').'</th>'.
		'<th colspan="2">'.__('Colors').'</th>'.
	'</tr>'.
	'</thead>'.
	'<tbody id="classes-list">';

	while ($rs->fetch())
	{
		$color = ($rs->comment_text_color) ? $rs->comment_text_color : 'inherit';
		$backgroundcolor = ($rs->comment_background_color) ? $rs->comment_background_color : 'inherit';
	
		echo
		'<tr class="line" id="l_'.$rs->class_id.'">'.
		'<td class="minimal">'.form::checkbox(array('select[]'),$rs->class_id).'</td>'.
		'<td>'.html::escapeHTML($rs->comment_author).'</td>'.		
		'<td><code>'.html::escapeHTML($rs->comment_class).'</code></td>'.	
		'<td>'.html::escapeHTML($rs->comment_author_mail).'</td>'.
		'<td><span style="padding:1px 5px;color:'.$color.';background-color:'.$backgroundcolor.'">'.__('Thanks to use Carnaval').'</span></td>'.
		'<td class="nowrap status"><a href="'.$p_url.'&amp;id='.$rs->class_id.'"><img src="images/edit-mini.png" alt="" title="'.__('Edit this record').'" /></a></td>'.
		'</tr>';
	}

	echo '</tbody></table>';

	echo 
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.
		form::hidden(array('p'),'carnaval').
		$core->formNonce().
		'<input type="submit" class="delete" name="removeaction" accesskey="d" value="'.__('delete').'" onclick="return window.confirm(dotclear.msg.delete_records)" />'.
	'</p></div></fieldset></form>';
}

if (!$add_carnaval) {
	echo '<div id="new-class"><h3><a class="new" id="carnaval-control" href="#">'.
	__('New CSS class').'</a></h3></div>';
}

echo 
'<form action="'.$p_url.'" method="post" id="add-css">
<fieldset class="clear"><legend>'.$legend.'</legend>
<p class="field"><label class="classic required" title="'.__('Required field').'">'.__('Name:').
form::field('comment_author',30,255,html::escapeHTML($comment_author),'',2).
'</label>
</p>
<p class="field"><label class="classic required" title="'.__('Required field').'">'.__('CSS Class:').
form::field('comment_class',30,255,html::escapeHTML($comment_class),'',3).
'</label>
</p>
<p class="field"><label class="classic required">'.__('Mail:').
form::field('comment_author_mail',30,255,html::escapeHTML($comment_author_mail),'',4).
'</label>
</p>'.
'<p class="field"><label class="classic">'.__('Text color:').
form::field('comment_text_color',7,7,html::escapeHTML($comment_text_color),'colorpicker',6).
'</label></p>
<p class="field"><label class="classic">'.__('Background color:').
form::field('comment_background_color',7,7,html::escapeHTML($comment_background_color),'colorpicker',7).
'</label></p>'.
form::hidden(array('p'),'carnaval').
$core->formNonce();

if (!empty($_REQUEST['id'])) {echo form::hidden('id',$_REQUEST['id']);}

echo
'<input type="submit" name="carnaval_class" accesskey="a" value="'.$button.'" tabindex="6" />
</fieldset>
</form>';

dcPage::helpBlock('carnaval');
echo '</body></html>';
?>