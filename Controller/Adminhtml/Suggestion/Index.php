<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Suggestion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_AiSeo::suggestion';

    public function __construct(Context $context, private PageFactory $resultPageFactory)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->setActiveMenu('Etechflow_AiSeo::suggestion');
        $page->getConfig()->getTitle()->prepend(__('AI SEO Suggestions'));
        return $page;
    }
}
