<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Etechflow\AiSeo\Service\SuggestionProcessor;

/**
 * Product-grid mass action: generate AI meta suggestions for the selected products.
 */
class MassGenerate extends Action
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
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('catalog/product/index');
        }
        $ids = $collection->getAllIds();
        if (count($ids) > 25) {
            $this->messageManager->addNoticeMessage(__('Only the first 25 products are processed per run (synchronous). Select fewer for large catalogs.'));
            $ids = array_slice($ids, 0, 25);
        }
        $ok = 0;
        $err = 0;
        foreach ($ids as $pid) {
            $s = $this->processor->generateForProduct((int)$pid, 0);
            $s->getData('status') === 'error' ? $err++ : $ok++;
        }
        if ($ok) {
            $this->messageManager->addSuccessMessage(__('%1 AI suggestion(s) generated — review them under AI SEO Suggestions.', $ok));
        }
        if ($err) {
            $this->messageManager->addErrorMessage(__('%1 failed (check the API key/model under Etechflow > AI SEO).', $err));
        }
        return $resultRedirect->setPath('aiseo/suggestion/index');
    }
}
