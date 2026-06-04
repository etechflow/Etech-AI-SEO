<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Suggestion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Etechflow\AiSeo\Service\SuggestionProcessor;

class Apply extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_AiSeo::suggestion';

    public function __construct(Context $context, private SuggestionProcessor $processor)
    {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('suggestion_id');
        try {
            $this->processor->apply($id);
            $this->messageManager->addSuccessMessage(__('Meta applied to the product.'));
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
