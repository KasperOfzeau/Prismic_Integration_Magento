<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class HtmlSerializer implements ArgumentInterface
{
    protected $linkResolver;

    public function __construct(
        \Marleen\PrismicIntegration\ViewModel\LinkResolver $linkResolver,
        protected \Magento\Framework\Escaper $escaper,
    ) {
        $this->linkResolver = $linkResolver;
    }

    /**
     * Returns a callable function for serializing HTML based on element types.
     *
     * @return \Closure
     */
    public function getSerializerFunction()
    {
        $linkResolver = $this->linkResolver;

        return function ($element, $content) use ($linkResolver) {
            switch ($element->type) {
                case 'heading1':
                    return nl2br('<h1>' . $this->escaper->escapeHtml($content) . '</h1>');
                case 'heading2':
                    return nl2br('<h2>' . $this->escaper->escapeHtml($content) . '</h2>');
                case 'heading3':
                    return nl2br('<h3>' . $this->escaper->escapeHtml($content) . '</h3>');
                case 'heading4':
                    return nl2br('<h4>' . $this->escaper->escapeHtml($content) . '</h4>');
                case 'heading5':
                    return nl2br('<h5>' . $this->escaper->escapeHtml($content) . '</h5>');
                case 'heading6':
                    return nl2br('<h6>' . $this->escaper->escapeHtml($content) . '</h6>');
                case 'list-item':
                case 'o-list-item':
                    return nl2br('<li>' . $this->escaper->escapeHtml($content) . '</li>');
                case 'image':
                    return '<p class="block-img' . (property_exists($element, 'label') ? ' '
                            . $this->escaper->escapeHtml($element->label) : '') . '">' .
                            '<img src="' . $this->escaper->escapeHtmlAttr($element->url) .
                            '" alt="' . htmlentities($element->alt) . '"></p>';
                case 'embed':
                    $providerAttr = '';
                    if ($element->oembed->provider_name) {
                        $providerAttr = ' data-oembed-provider="' .
                        strtolower($element->oembed->provider_name) . '"';
                    }
                    return '<div data-oembed="' . $element->oembed->embed_url .
                        '" data-oembed-type="' . strtolower($element->oembed->type) .
                        '"' . $providerAttr . '>' .
                        $element->oembed->html . '</div>';
                case 'preformatted':
                    return '<pre>' . $this->escaper->escapeHtml($content) . '</pre>';
                case 'strong':
                    return '<strong>' . $this->escaper->escapeHtml($content) . '</strong>';
                case 'em':
                    return '<em>' . $this->escaper->escapeHtml($content) . '</em>';
                case 'hyperlink':
                    $linkUrl = $this->linkResolver ? $this->linkResolver->resolve($element->data) : '';
                    $targetAttr = property_exists($element->data, 'target') ? ' target="' .
                    $element->data->target . '" rel="noopener"' : '';
                    return '<a href="' . $this->escaper->escapeHtmlAttr($linkUrl) . '" ' .
                    $this->escaper->escapeHtmlAttr($targetAttr) . '>' . $this->escaper->escapeHtml($content)
                    . '</a>';
                case 'label':
                    return '<span class="' .
                        (property_exists($element->data, 'label') ? $element->data->label : '') . '">' .
                        $this->escaper->escapeHtml($content) . '</span>';
                default:
                    return null;
            }
        };
    }
}
