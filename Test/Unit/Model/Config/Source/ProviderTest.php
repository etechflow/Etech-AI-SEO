<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Test\Unit\Model\Config\Source;

use Etechflow\AiSeo\Model\Config\Source\Provider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Etechflow\AiSeo\Model\Config\Source\Provider
 */
class ProviderTest extends TestCase
{
    private Provider $source;

    protected function setUp(): void
    {
        $this->source = new Provider();
    }

    public function testOffersAnthropicAndOpenAi(): void
    {
        $values = array_column($this->source->toOptionArray(), 'value');
        $this->assertContains('anthropic', $values);
        $this->assertContains('openai', $values);
    }

    public function testEveryOptionHasValueAndLabel(): void
    {
        foreach ($this->source->toOptionArray() as $opt) {
            $this->assertArrayHasKey('value', $opt);
            $this->assertArrayHasKey('label', $opt);
            $this->assertNotEmpty((string)$opt['label']);
        }
    }
}
