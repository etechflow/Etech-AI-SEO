<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Suggestion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion\CollectionFactory;

class MassDelete extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_AiSeo::suggestion';

    public function __construct(
        Context $context,
        private Filter $filter,
        private CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count = 0;
        foreach ($collection as $item) {
            $item->delete();
            $count++;
        }
        $this->messageManager->addSuccessMessage(__('%1 suggestion(s) deleted.', $count));
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
