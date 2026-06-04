<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Service;

use Magento\Framework\HTTP\Client\CurlFactory;
use Etechflow\AiSeo\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Thin LLM client supporting Anthropic (Messages API) and OpenAI (Chat Completions).
 * A fresh Curl is created per call so headers never accumulate across requests.
 */
class AiClient
{
    public function __construct(
        private CurlFactory $curlFactory,
        private Config $config,
        private LoggerInterface $logger
    ) {
    }

    public function complete(string $system, string $user, $storeId = null): string
    {
        $key = $this->config->getApiKey($storeId);
        if ($key === '') {
            throw new \RuntimeException('AI SEO: API key is not configured.');
        }
        $provider = $this->config->getProvider($storeId);
        $model    = $this->config->getModel($storeId);

        $curl = $this->curlFactory->create();
        $curl->setOption(CURLOPT_TIMEOUT, 60);
        $curl->addHeader('Content-Type', 'application/json');

        if ($provider === 'openai') {
            $curl->addHeader('Authorization', 'Bearer ' . $key);
            $body = json_encode([
                'model'       => $model,
                'messages'    => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.4,
                'max_tokens'  => 600,
            ]);
            $curl->post('https://api.openai.com/v1/chat/completions', $body);
            $resp = json_decode((string)$curl->getBody(), true);
            $text = $resp['choices'][0]['message']['content'] ?? '';
        } else {
            $curl->addHeader('x-api-key', $key);
            $curl->addHeader('anthropic-version', '2023-06-01');
            $body = json_encode([
                'model'      => $model,
                'max_tokens' => 600,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $user]],
            ]);
            $curl->post('https://api.anthropic.com/v1/messages', $body);
            $resp = json_decode((string)$curl->getBody(), true);
            $text = $resp['content'][0]['text'] ?? '';
        }

        if (!is_string($text) || trim($text) === '') {
            $this->logger->error('AI SEO empty response (status ' . $curl->getStatus() . '): ' . substr((string)$curl->getBody(), 0, 500));
            throw new \RuntimeException('AI SEO: empty/invalid response from ' . $provider . ' (HTTP ' . $curl->getStatus() . '). Check API key and model.');
        }
        return $text;
    }
}
