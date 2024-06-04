<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Prismic\Predicates;

class CustomType implements ArgumentInterface
{
    public function __construct(
        protected \Marleen\PrismicIntegration\ViewModel\Api $api
    ) {
    }

    public function getByID($ids): \stdClass
    {
        $api = $this->api->getApi();
        return $api->getByIDs($ids);
    }

    public function getByType($type): \stdClass
    {
        $api = $this->api->getApi();
        return $api->query(Predicates::at('document.type', $type));
    }
}
