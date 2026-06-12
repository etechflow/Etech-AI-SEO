<?php

declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\License;

use Etechflow\AiSeo\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * License-required gate page. Shows plan cards + "Enter License Key".
 * Redirects to the AI SEO Suggestions grid when the license is already valid.
 */
class Gate extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_AiSeo::config';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        if ($this->licenseValidator->isValid()) {
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('aiseo/suggestion/index');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('AI SEO — License Required'));
        return $page;
    }
}
