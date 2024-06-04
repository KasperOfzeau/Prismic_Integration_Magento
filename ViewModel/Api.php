<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Elgentos\PrismicIO\Exception\ApiNotEnabledException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Api implements ArgumentInterface
{
    protected \Prismic\Api $apiInstance;

    public function __construct(
        protected \Elgentos\PrismicIO\Model\Api $api,
    ) {
    }

    /**
     * Get Prismic API
     *
     * @throws NoSuchEntityException
     * @throws ApiNotEnabledException
     */
    public function getApi(): \Prismic\Api
    {
        if (!isset($this->apiInstance)) {
            $this->apiInstance = $this->api->create();
        }

        return $this->apiInstance;
    }
}
