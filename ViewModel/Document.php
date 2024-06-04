<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Elgentos\PrismicIO\Registry\CurrentDocument;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use stdClass;

class Document implements ArgumentInterface
{
    public function __construct(
        protected CurrentDocument $currentDocument,
    ) {
    }

    public function getDocument(): ?stdClass
    {
        $document = $this->currentDocument->getDocument();
        if ($document->id !== "") {
            return $document;
        } else {
            return null;
        }
    }
}
