<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Service;

use Etechflow\AiSeo\Model\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Builds the prompt for an entity, calls the LLM, and returns a clamped
 * {title, description} pair.
 */
class MetaGenerator
{
    public function __construct(
        private AiClient $aiClient,
        private Config $config,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @return array{title:string,description:string}
     */
    public function generateForProduct(int $productId, $storeId = null): array
    {
        $product = $this->productRepository->getById($productId, false, $storeId ? (int)$storeId : 0);
        $name = (string)$product->getName();
        $sku  = (string)$product->getSku();
        $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string)($product->getShortDescription() ?: $product->getDescription()))));
        $desc = mb_substr($desc, 0, 800);

        $tMax = $this->config->getTitleMax($storeId);
        $dMax = $this->config->getDescriptionMax($storeId);
        $tone = $this->config->getBrandTone($storeId);

        $system = 'You are an expert e-commerce SEO copywriter. Write compelling, keyword-rich meta tags '
            . 'that maximise click-through from search engine results. ' . $tone
            . ' Respond ONLY with strict minified JSON of the form {"title":"...","description":"..."} and nothing else.';

        $userMsg = "Product name: {$name}\nSKU: {$sku}\nDescription: {$desc}\n\n"
            . "Write a meta title (max {$tMax} characters, naturally include the product name) and a meta "
            . "description (max {$dMax} characters, benefit-led, with a soft call to action). "
            . "Stay within the character limits. Return JSON only.";

        $raw  = $this->aiClient->complete($system, $userMsg, $storeId);
        $data = $this->parseJson($raw);

        return [
            'title'       => mb_substr(trim((string)($data['title'] ?? '')), 0, $tMax),
            'description' => mb_substr(trim((string)($data['description'] ?? '')), 0, $dMax),
        ];
    }

    private function parseJson(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
