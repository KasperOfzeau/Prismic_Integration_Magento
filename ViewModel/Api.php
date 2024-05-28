<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Api implements ArgumentInterface
{
    public function __construct(
        protected \Elgentos\PrismicIO\Model\Api $api,
    ) {
    }

    // get Prismic API
    public function getApi(): \Prismic\Api
    {
        return $this->api->create();
    }
}
