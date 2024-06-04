<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Usps implements ArgumentInterface
{
    protected CustomType $customType;

    public function __construct(CustomType $customType)
    {
        $this->customType = $customType;
    }

    public function getUsps() : ?array
    {
        // Fetch header usps from Prismic
        $document = $this->customType->getByType('message_bar');
        // Sort usps messages in an array
        $usps = [];
        if (isset($document->results[0]->data->messages) && !empty($document->results[0]->data->messages)) {
            foreach ($document->results[0]->data->messages as $message) {
                if (isset($message)) {
                    $usps[] = $message;
                }
            }
            return $usps;
        } else {
            return null;
        }
    }
}
