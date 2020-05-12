<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 pdir / digital agentur
 * @package social-feed-bundle
 * @author Mathias Arzberger <develop@pdir.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace Pdir\SocialFeedBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;

class Plugin implements BundlePluginInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBundles(ParserInterface $parser)
	{
		return [
			BundleConfig::create(PdirSocialFeedBundle::class)
				->setLoadAfter([ContaoCoreBundle::class])
				->setReplace(['socialfeedbundle']),
		];
	}
}
