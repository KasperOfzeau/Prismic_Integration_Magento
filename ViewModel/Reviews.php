<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Reviews implements ArgumentInterface
{
    public function __construct(
        protected \Marleen\PrismicIntegration\ViewModel\Api $api
    ) {
    }

    public function getReviews($reviewIds)
    {
        $api = $this->api->getApi();
        $reviews = $api->getByIDs($reviewIds);
        return $reviews;
    }
}
