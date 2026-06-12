<?php

declare(strict_types=1);

namespace Etechflow\AiSeo\Controller\Adminhtml\License;

use Etechflow\AiSeo\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Creates a Stripe Checkout session using the keys entered in
 * Stores -> Config -> Etechflow -> AI SEO -> Payment,
 * then redirects the browser to Stripe for card payment.
 */
class Checkout extends Action
{
    public const ADMIN_RESOURCE = 'Etechflow_AiSeo::config';

    private const XML_STRIPE_SECRET = 'etechflow_aiseo/payment/stripe_secret_key';
    private const XML_STRIPE_CURR   = 'etechflow_aiseo/payment/stripe_currency';

    /** Plan slugs -> [name, amount in cents, display]. Tier-based plans. */
    private const PLAN_INFO = [
        'aiseo_starter'      => ['name' => 'AI SEO — Starter',      'amount' => 1900, 'display' => '$19/month'],
        'aiseo_professional' => ['name' => 'AI SEO — Professional', 'amount' => 4900, 'display' => '$49/month'],
        'aiseo_enterprise'   => ['name' => 'AI SEO — Enterprise',   'amount' => 9900, 'display' => '$99/month'],
    ];

    public function __construct(
        Context $context,
        private readonly Curl $curl,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $plan   = trim((string) $this->getRequest()->getPost('plan', ''));
        $name   = trim((string) $this->getRequest()->getPost('name', ''));
        $email  = trim((string) $this->getRequest()->getPost('email', ''));
        $domain = $this->licenseValidator->getCurrentHost();

        if (!$plan || !isset(self::PLAN_INFO[$plan])) {
            $this->messageManager->addErrorMessage(__('Invalid plan selected.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('aiseo/license/gate');
        }
        if (!$name || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->messageManager->addErrorMessage(__('Please enter a valid name and email address.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('aiseo/license/gate');
        }

        $stripeRaw = trim((string) $this->scopeConfig->getValue(self::XML_STRIPE_SECRET));
        $stripeKey = $stripeRaw !== '' ? trim((string) $this->encryptor->decrypt($stripeRaw)) : '';
        $currency  = strtolower(trim((string) $this->scopeConfig->getValue(self::XML_STRIPE_CURR))) ?: 'usd';

        if (!$stripeKey || str_starts_with($stripeKey, '****')) {
            $this->messageManager->addErrorMessage(
                __('Stripe Secret Key is not configured. Go to Stores -> Config -> Etechflow -> AI SEO -> Payment.')
            );
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('aiseo/license/gate');
        }

        $planInfo   = self::PLAN_INFO[$plan];
        $successUrl = $this->getUrl('aiseo/license/activated')
            . '?session_id={CHECKOUT_SESSION_ID}&plan=' . urlencode($plan)
            . '&domain=' . urlencode($domain) . '&name=' . urlencode($name) . '&email=' . urlencode($email);
        $cancelUrl  = $this->getUrl('aiseo/license/gate');

        $postData = http_build_query([
            'payment_method_types[0]'                              => 'card',
            'line_items[0][price_data][currency]'                  => $currency,
            'line_items[0][price_data][product_data][name]'        => $planInfo['name'] . ' — ETechFlow',
            'line_items[0][price_data][product_data][description]' => 'AI SEO module for ' . $domain,
            'line_items[0][price_data][unit_amount]'               => $planInfo['amount'],
            'line_items[0][quantity]'                              => 1,
            'mode'                                                 => 'payment',
            'customer_email'                                       => $email,
            'metadata[plan]'                                       => $plan,
            'metadata[domain]'                                     => $domain,
            'metadata[name]'                                       => $name,
            'metadata[email]'                                      => $email,
            'success_url'                                          => $successUrl,
            'cancel_url'                                           => $cancelUrl,
        ]);

        try {
            $this->curl->setTimeout(15);
            $this->curl->addHeader('Authorization', 'Bearer ' . $stripeKey);
            $this->curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $this->curl->post('https://api.stripe.com/v1/checkout/sessions', $postData);
            $status = (int) $this->curl->getStatus();
            $body   = (string) $this->curl->getBody();
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Could not connect to Stripe. Please try again.'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('aiseo/license/gate');
        }

        $data = json_decode($body, true);
        if ($status !== 200 || empty($data['url'])) {
            $err = $data['error']['message'] ?? ('Stripe returned status ' . $status);
            $this->messageManager->addErrorMessage(__('Stripe error: %1', $err));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('aiseo/license/gate');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($data['url']);
    }
}
