<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class SuggestionActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (empty($item['suggestion_id'])) {
                continue;
            }
            $id = $item['suggestion_id'];
            if (($item['status'] ?? '') === 'generated') {
                $item[$name]['apply'] = [
                    'href'    => $this->urlBuilder->getUrl('aiseo/suggestion/apply', ['suggestion_id' => $id]),
                    'label'   => __('Apply'),
                    'confirm' => ['title' => __('Apply'), 'message' => __('Write this meta to the product?')],
                ];
            }
            $item[$name]['delete'] = [
                'href'    => $this->urlBuilder->getUrl('aiseo/suggestion/delete', ['suggestion_id' => $id]),
                'label'   => __('Delete'),
                'confirm' => ['title' => __('Delete'), 'message' => __('Delete this suggestion?')],
            ];
        }
        return $dataSource;
    }
}
