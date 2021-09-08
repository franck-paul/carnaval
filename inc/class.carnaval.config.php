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

class carnavalConfig
{
	public static function adjustColor($c)
	{
		if ($c === '') {
			return '';
		}
		
		$c = strtoupper($c);
		
		if (preg_match('/^[A-F0-9]{3,6}$/',$c)) {
			$c = '#'.$c;
		}
		
		if (preg_match('/^#[A-F0-9]{6}$/',$c)) {
			return $c;
		}
		
		if (preg_match('/^#[A-F0-9]{3,}$/',$c)) {
			return '#'.substr($c,1,1).substr($c,1,1).substr($c,2,1).substr($c,2,1).substr($c,3,1).substr($c,3,1);
		}
		
		return '';
	}
	
	public static function imagesPath()
	{
		global $core;
		return path::real($core->blog->public_path).'/carnaval-images';
	}
	
	public static function imagesURL()
	{
		global $core;
		return $core->blog->settings->system->public_url.'/carnaval-images';
	}
	
	public static function canWriteImages($create=false)
	{
		global $core;
		
		$public = path::real($core->blog->public_path);
		$imgs = self::imagesPath();
		
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng') || !function_exists('imagecreatefrompng')) {
			return false;
		}
		
		if (!is_dir($public)) {
			return false;
		}
		
		if (!is_dir($imgs)) {
			if (!is_writable($public)) {
				return false;
			}
			if ($create) {
				files::makeDir($imgs);
			}
			return true;
		}
		
		if (!is_writable($imgs)) {
			return false;
		}
		
		return true;
	}
	
	
	public static function createImages($color,$name)
	{
		if (!self::canWriteImages(true)) {
			throw new Exception(__('Unable to create images.'));
		}
				
		$comment_t = dirname(__FILE__).'/../../../plugins/blowupConfig/alpha-img/comment-t.png';
		$comment_b = dirname(__FILE__).'/../../../plugins/blowupConfig/alpha-img/comment-b.png';

		$cval_comment_t = $name.'-comment-t.png';
		$cval_comment_b = $name.'-comment-b.png';
		
		self::dropImage($cval_comment_t);
		self::dropImage($cval_comment_b);
		
		$color = self::adjustColor($color);
				
		self::commentImages($color,$comment_t,$comment_b,$cval_comment_t,$cval_comment_b);

	}
	
	protected static function commentImages($comment_color,$comment_t,$comment_b,$dest_t,$dest_b)
	{
		$comment_color = sscanf($comment_color,'#%2X%2X%2X');
			
		$d_comment_t = imagecreatetruecolor(500,25);
		$fill = imagecolorallocate($d_comment_t,$comment_color[0],$comment_color[1],$comment_color[2]);
		imagefill($d_comment_t,0,0,$fill);
		
		$s_comment_t = imagecreatefrompng($comment_t);
		imagealphablending($s_comment_t,true);
		imagecopy($d_comment_t,$s_comment_t,0,0,0,0,500,25);
		
		imagepng($d_comment_t,self::imagesPath().'/'.$dest_t);
		imagedestroy($d_comment_t);
		imagedestroy($s_comment_t);
		
		$d_comment_b = imagecreatetruecolor(500,7);
		$fill = imagecolorallocate($d_comment_b,$comment_color[0],$comment_color[1],$comment_color[2]);
		imagefill($d_comment_b,0,0,$fill);
		
		$s_comment_b = imagecreatefrompng($comment_b);
		imagealphablending($s_comment_b,true);
		imagecopy($d_comment_b,$s_comment_b,0,0,0,0,500,7);
		
		imagepng($d_comment_b,self::imagesPath().'/'.$dest_b);
		imagedestroy($d_comment_b);
		imagedestroy($s_comment_b);
	}
	
	public static function dropImage($img)
	{
		$img = path::real(self::imagesPath().'/'.$img);
		if (is_writable(dirname($img))) {
			@unlink($img);
			@unlink(dirname($img).'/.'.basename($img,'.png').'_sq.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_m.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_s.jpg');
			@unlink(dirname($img).'/.'.basename($img,'.png').'_t.jpg');
		}
	}
}
?>
