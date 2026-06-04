<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Model\ResourceModel\Suggestion;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Etechflow\AiSeo\Model\Suggestion;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion as ResourceSuggestion;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(Suggestion::class, ResourceSuggestion::class);
    }
}
