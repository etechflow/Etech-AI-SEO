<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Model;

use Magento\Framework\Model\AbstractModel;
use Etechflow\AiSeo\Model\ResourceModel\Suggestion as ResourceSuggestion;

class Suggestion extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(ResourceSuggestion::class);
    }
}
