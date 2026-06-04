<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Suggestion extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('etechflow_aiseo_suggestion', 'suggestion_id');
    }
}
