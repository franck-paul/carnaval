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

Clearbricks::lib()->autoload([
    'dcCarnaval'     => __DIR__ . '/inc/class.carnaval.php',
    'carnavalConfig' => __DIR__ . '/inc/class.carnaval.config.php',
]);

dcCore::app()->carnaval = new dcCarnaval();
