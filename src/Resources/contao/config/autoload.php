<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Pdir',
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_socialfeed_list'  => 'system/modules/socialFeed/templates/elements',
    'ce_socialfeed_item'  => 'system/modules/socialFeed/templates/elements',
	'be_socialfeed_setup' => 'system/modules/socialFeed/templates/be',
));
