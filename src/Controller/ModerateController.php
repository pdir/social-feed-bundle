<?php

declare(strict_types=1);

/*
 * social feed bundle for Contao Open Source CMS
 *
 * Copyright (c) 2024 pdir / digital agentur // pdir GmbH
 *
 * @package    social-feed-bundle
 * @link       https://github.com/pdir/social-feed-bundle
 * @license    http://www.gnu.org/licences/lgpl-3.0.html LGPL
 * @author     Mathias Arzberger <develop@pdir.de>
 * @author     Philipp Seibt <develop@pdir.de>
 * @author     pdir GmbH <https://pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pdir\SocialFeedBundle\Controller;

use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ModerateController
{
    private ContaoFramework $framework;

    private RequestStack $requestStack;

    private BackendTemplate $template;

    private string $message;

    /**
     * ExportController constructor.
     */
    public function __construct(ContaoFramework $framework, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->template = new BackendTemplate('be_sf_moderate');
    }

    /**
     * Run the controller.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function run(): string
    {
        $formId = 'tl_news_moderate';

        $request = $this->requestStack->getCurrentRequest();

        if ($request->request->get('FORM_SUBMIT') === $formId) {
            $this->processForm($request);
        }

        return $this->getTemplate($formId)->parse();
    }

    /**
     * Process the form.
     *
     * @codeCoverageIgnore
     */
    protected function processForm(Request $request): void
    {
        $importItems = null;

        if (!$request->request->get('account')) {
            return;
        }

        $socialFeedAccount = $request->request->get('account');
        //$numberPosts = $request->request->get('number_posts');
        $objSocialFeedModel = SocialFeedModel::findById($socialFeedAccount);
        $newsArchiveId = Input::get('id');

        $objImporter = new Importer();
        $items = $objImporter->getPostsByAccount($request->request->get('account'), $request->request->get('number_posts'));

        // import selected items
        $allValues = $request->request->all();

        // do import if importItems is set
        if (isset($allValues['importItems']) && \count($allValues['importItems']) > 0) {
            foreach ($items as $item) {
                if (\in_array($item['id'], $allValues['importItems'], true)) {
                    $importer = new NewsImporter();
                    $importer->setNews($item);
                    $importer->accountImage = $objImporter->getAccountImage();
                    $importer->execute($newsArchiveId, $objSocialFeedModel->socialFeedType, $objSocialFeedModel->id);
                }
            }
        }

        // set import message
        if (\is_array($items) && isset($allValues['importItems']) && \count($allValues['importItems']) > 0) {
            $this->message = sprintf($GLOBALS['TL_LANG']['BE_MOD']['socialFeedModerate']['importMessage'], \count($allValues['importItems']));
        }

        if (null === $items) {
            Message::addInfo($GLOBALS['TL_LANG']['BE_MOD']['socialFeedModerate']['noItems']);
        }

        // get items for moderation list
        if (null !== $items) {
            $moderationItems = $objImporter->moderation($items);

            if (0 < \count($moderationItems)) {
                $template = new BackendTemplate('be_sf_moderation_list');
                $template->arr = $moderationItems;
                $html = $template->parse();
            }
        }

        $this->template->activeAccount = $request->request->get('account');
        $this->template->moderationList = $html?? '';
        $this->template->message = isset($this->message)? '<div class="tl_sucess">' . $this->message . '</div></div>' : '';;
    }

    /**
     * Get the template.
     *
     * @param string $formId
     *
     * @return BackendTemplate
     *
     * @codeCoverageIgnore
     */
    protected function getTemplate(string $formId): BackendTemplate
    {
        /**
         * @var Environment
         * @var Message     $message
         * @var System      $system
         */
        $environment = $this->framework->getAdapter(Environment::class);
        $system = $this->framework->getAdapter(System::class);

        if (isset($this->message)) {
            Message::addInfo($this->message);
        }

        $this->template->backUrl = $system->getReferer();
        $this->template->action = $environment->get('request');
        $this->template->formId = $formId;
        $this->template->message = isset($this->message)? '<div class="tl_confirm">' . $this->message . '</div>' : '';
        $this->template->options = $this->generateOptions('Instagram');
        $this->template->headline = $GLOBALS['TL_LANG']['BE_MOD']['socialFeedModerate']['headline'].Input::get('id');

        return $this->template;
    }

    /**
     * Generate the options.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function generateOptions($filter = false): array
    {
        $options = [];

        if ($filter) {
            $objFeedModel = SocialFeedModel::findAll();
        }

        $objFeedModel = SocialFeedModel::findAll();

        dump($options);
        dump('Filter: '.$filter);

        foreach ($objFeedModel as $feed) {
            dump('TYPE: '.$feed->socialFeedType);

            if (false !== $filter && $feed->socialFeedType !== $filter) {
                dump($filter);
                dump($feed->socialFeedType);
                continue;
            }

            if ('Facebook' === $feed->socialFeedType && !$filter || $feed->socialFeedType == $filter) {
                dump('Facebook' === $feed->socialFeedType && !$filter);
                dump($feed->socialFeedType);
                dump($feed->socialFeedType === $filter);
                dump('Facebook');
                dump($feed->socialFeedType);
                $options[$feed->id] = $feed->socialFeedType.' '.$feed->pdir_sf_fb_account;
            }

            if ('Instagram' === $feed->socialFeedType && !$filter || $feed->socialFeedType == $filter) {
                dump('Instagram');
                #dump($feed);
                #dump($feed->socialFeedType.' '.$feed->instagram_account);
                # $options[$feed->id] = $feed->socialFeedType;
                $options[$feed->id] = $feed->instagram_account; // \sprintf('%s %s', $feed->socialFeedType, $feed->instagram_account);
                #dump($feed->socialFeedType);
                #dump($feed->instagram_account);
            }

            if ('Twitter' === $feed->socialFeedType && !$filter || $feed->socialFeedType === $filter) {
                dump('Twitter' === $feed->socialFeedType && !$filter);
                dump($feed->socialFeedType);
                dump($filter);
                dump($feed->socialFeedType === $filter);
                dump('Twitter');
                $options[$feed->id] = $feed->socialFeedType.' '.$feed->twitter_account;
            }

            if ('LinkedIn' === $feed->socialFeedType && !$filter || $feed->socialFeedType === $filter) {
                dump('LinkedIn');
                $options[$feed->id] = $feed->socialFeedType.' '.$feed->linkedin_account;
            }
        }

        dump($options);

        return $options;
    }
}
