<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Provider implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'anthropic', 'label' => __('Anthropic (Claude)')],
            ['value' => 'openai', 'label' => __('OpenAI (GPT)')],
        ];
    }
}
