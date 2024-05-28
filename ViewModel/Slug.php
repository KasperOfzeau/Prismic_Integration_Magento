<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Slug implements ArgumentInterface
{
    protected HttpRequest $http;
    public function __construct(HttpRequest $http)
    {
        $this->http = $http;
    }
    // Get the last part of the URL which can be used as a slug
    public function getSlug(): string
    {
        $currentUrl = $this->http->getServer('REQUEST_URI');
        $parts = explode('/', trim($currentUrl, '/'));
        return end($parts);
    }
}
