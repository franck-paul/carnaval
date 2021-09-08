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

if (version_compare(DC_VERSION,'2.2-beta','<')) { return; }
$core->blog->settings->addNamespace('carnaval');
$__autoload['dcCarnaval'] = dirname(__FILE__).'/inc/class.carnaval.php';
$__autoload['carnavalConfig'] = dirname(__FILE__).'/inc/class.carnaval.config.php';

$core->carnaval = new dcCarnaval ($core);
?>
