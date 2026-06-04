<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Suggestion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Etechflow\AiSeo\Model\SuggestionFactory;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion as ResourceSuggestion;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_AiSeo::suggestion';

    public function __construct(
        Context $context,
        private SuggestionFactory $factory,
        private ResourceSuggestion $resource
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('suggestion_id');
        if ($id) {
            try {
                $model = $this->factory->create();
                $this->resource->load($model, $id);
                $this->resource->delete($model);
                $this->messageManager->addSuccessMessage(__('Suggestion deleted.'));
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
