<?php

namespace Pdir\SocialFeedBundle\Controller;

use Contao\BackendTemplate;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Environment;
use Contao\Message;
use Contao\System;
use Contao\Input;
use Pdir\SocialFeedBundle\Importer\Importer;
use Pdir\SocialFeedBundle\Importer\NewsImporter;
use Pdir\SocialFeedBundle\Model\SocialFeedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ModerateController
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var BackendTemplate
     */
    private $template;

    /**
     * @var string
     */
    private $message;

    /**
     * ExportController constructor.
     *
     * @param ContaoFramework $framework
     * @param RequestStack $requestStack
     */
    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack
    )
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
    public function run()
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
     * @param Request $request
     *
     * @codeCoverageIgnore
     */
    protected function processForm(Request $request)
    {
        if (!$request->request->get('account')) {
            return;
        }

        $socialFeedAccount = $request->request->get('account');
        $objSocialFeedModel = SocialFeedModel::findById($socialFeedAccount);
        $newsArchiveId = Input::get('id');

        $objImporter = new Importer();
        $items = $objImporter->getPostsByAccount($request->request->get('account'));

        // import selected items
        $importItems = $request->request->get('importItems');

        if($importItems && count($importItems) > 0)
        {
            foreach($items as $item)
            {
                if(in_array($item['id'], $importItems)) {
                    $importer = new NewsImporter($item);
                    $importer->accountImage = $objImporter->getAccountImage();
                    $importer->execute($newsArchiveId, $objSocialFeedModel->socialFeedType, $objSocialFeedModel->id);
                }
            }
        }

        // set import message
        if(is_array($items) && isset($importItems) && count($importItems) > 0) {
            $this->message = sprintf($GLOBALS['TL_LANG']['BE_MOD']['socialFeedModerate']['importMessage'], count($importItems));
        }

        // get items for moderation list
        $moderationItems = $objImporter->moderation($items);
        if(count($moderationItems) > 0) {
            $template = new BackendTemplate('be_sf_moderation_list');
            $template->arr = $moderationItems;
            $html = $template->parse();
        }

        $this->template->activeAccount = $request->request->get('account');
        $this->template->moderationList = $html;
        $this->template->message = $this->message;
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
    protected function getTemplate($formId)
    {
        /**
         * @var Environment
         * @var Message $message
         * @var System $system
         */
        $environment = $this->framework->getAdapter(Environment::class);
        $message = $this->framework->getAdapter(Message::class);
        $system = $this->framework->getAdapter(System::class);

        if($this->message)
        {
            $message->addInfo($this->message);
        }

        $this->template->backUrl = $system->getReferer();
        $this->template->action = $environment->get('request');
        $this->template->formId = $formId;
        $this->template->message = $message->generate();
        $this->template->options = $this->generateOptions();
        $this->template->headline = 'Nachrichten › Social Feed  › Moderate  ›  Achiv ' . Input::get('id');

        return $this->template;
    }

    /**
     * Generate the options.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function generateOptions()
    {
        $options = [];

        $objFeedModel = SocialFeedModel::findAll();

        foreach ($objFeedModel as $feed) {
            if($feed->socialFeedType == 'Facebook')
            {
                $options[$feed->id] = $feed->socialFeedType . ' ' . $feed->pdir_sf_fb_account;
            }
            if($feed->socialFeedType == 'Instagram')
            {
                $options[$feed->id] = $feed->socialFeedType . ' ' . $feed->instagram_account;
            }
            if($feed->socialFeedType == 'Twitter')
            {
                $options[$feed->id] = $feed->socialFeedType . ' ' . $feed->twitter_account;
            }
        }

        return $options;
    }

}
