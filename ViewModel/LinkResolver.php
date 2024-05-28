<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Prismic\LinkResolver as PrismicLinkResolver;

class LinkResolver extends PrismicLinkResolver implements ArgumentInterface
{
    public function resolve($link): ?string
    {
        if (property_exists($link, 'isBroken') && $link->isBroken === true) {
            return '/404';
        }
        if ($link->link_type === 'category') {
            return '/category/' . $link->uid;
        }
        if ($link->link_type === 'post') {
            return '/post/' . $link->uid;
        }
        return '/';
    }
}
