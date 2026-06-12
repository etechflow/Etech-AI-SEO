<?php

declare(strict_types=1);

namespace Etechflow\AiSeo\Observer;

use Etechflow\AiSeo\Model\LicenseValidator;
use Magento\Backend\Model\UrlInterface as BackendUrl;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Admin-only licence gate for AI SEO.
 *
 * Fires on controller_action_predispatch_aiseo — i.e. EVERY aiseo/* admin
 * controller (Suggestions grid, mass-generate, apply, delete). When the licence
 * is invalid it stops dispatch and redirects to the gate page, EXCEPT for the
 * License controllers themselves (gate/checkout/activated) so the gate can render
 * and Stripe can return.
 *
 * AI SEO is admin-only tooling with no storefront surface, so this single
 * pre-dispatch observer is the whole enforcement layer for the admin UI (the
 * Config::isEnabled() licence check and the gated console command cover the
 * service + CLI paths).
 */
class AdminGate implements ObserverInterface
{
    public function __construct(
        private readonly LicenseValidator $licenseValidator,
        private readonly ActionFlag $actionFlag,
        private readonly BackendUrl $backendUrl,
        private readonly ResponseInterface $response
    ) {
    }

    public function execute(Observer $observer): void
    {
        $request = $observer->getEvent()->getRequest();
        if ($request === null) {
            return;
        }
        // Never gate the gate itself (gate/checkout/activated) — would loop.
        if (strtolower((string) $request->getControllerName()) === 'license') {
            return;
        }
        if ($this->licenseValidator->isValid()) {
            return;
        }
        $this->actionFlag->set('', AbstractAction::FLAG_NO_DISPATCH, true);
        $this->response->setRedirect($this->backendUrl->getUrl('aiseo/license/gate'));
    }
}
