<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Service;

use Etechflow\AiSeo\Model\SuggestionFactory;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion as ResourceSuggestion;
use Etechflow\AiSeo\Model\Suggestion;
use Etechflow\AiSeo\Model\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Generates an AI suggestion for an entity (storing it for review) and applies
 * an approved suggestion back to the product's meta fields.
 */
class SuggestionProcessor
{
    public function __construct(
        private MetaGenerator $metaGenerator,
        private SuggestionFactory $suggestionFactory,
        private ResourceSuggestion $resource,
        private ProductRepositoryInterface $productRepository,
        private Config $config
    ) {
    }

    public function generateForProduct(int $productId, int $storeId = 0): Suggestion
    {
        $suggestion = $this->suggestionFactory->create();
        $suggestion->addData([
            'entity_type' => 'product',
            'entity_id'   => $productId,
            'store_id'    => $storeId,
            'status'      => 'pending',
        ]);
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            $suggestion->setData('current_meta_title', $product->getMetaTitle());
            $suggestion->setData('current_meta_description', $product->getMetaDescription());

            $res = $this->metaGenerator->generateForProduct($productId, $storeId);
            $suggestion->setData('suggested_meta_title', $res['title']);
            $suggestion->setData('suggested_meta_description', $res['description']);
            $suggestion->setData('status', 'generated');
            $this->resource->save($suggestion);

            if ($this->config->isAutoApply($storeId)) {
                $this->apply((int)$suggestion->getId());
            }
        } catch (\Throwable $e) {
            $suggestion->setData('status', 'error');
            $suggestion->setData('message', $e->getMessage());
            $this->resource->save($suggestion);
        }
        return $suggestion;
    }

    public function apply(int $suggestionId): void
    {
        $suggestion = $this->suggestionFactory->create();
        $this->resource->load($suggestion, $suggestionId);
        if (!$suggestion->getId()) {
            throw new \RuntimeException(__('Suggestion not found.')->render());
        }
        if ($suggestion->getData('entity_type') !== 'product') {
            return;
        }
        $storeId = (int)$suggestion->getData('store_id');
        $product = $this->productRepository->getById((int)$suggestion->getData('entity_id'), true, $storeId);
        if ($t = $suggestion->getData('suggested_meta_title')) {
            $product->setMetaTitle($t);
        }
        if ($d = $suggestion->getData('suggested_meta_description')) {
            $product->setMetaDescription($d);
        }
        $this->productRepository->save($product);
        $suggestion->setData('status', 'applied');
        $this->resource->save($suggestion);
    }
}
