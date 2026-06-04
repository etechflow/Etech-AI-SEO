<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Suggestion;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion\CollectionFactory;
use Etechflow\AiSeo\Service\SuggestionProcessor;

class MassApply extends Action
{
    const ADMIN_RESOURCE = 'Etechflow_AiSeo::suggestion';

    public function __construct(
        Context $context,
        private Filter $filter,
        private CollectionFactory $collectionFactory,
        private SuggestionProcessor $processor
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ok = 0;
        $err = 0;
        foreach ($collection->getAllIds() as $id) {
            try {
                $this->processor->apply((int)$id);
                $ok++;
            } catch (\Throwable $e) {
                $err++;
            }
        }
        if ($ok) {
            $this->messageManager->addSuccessMessage(__('%1 suggestion(s) applied.', $ok));
        }
        if ($err) {
            $this->messageManager->addErrorMessage(__('%1 could not be applied.', $err));
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
