<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface UspsSliceInterface
{
    public const TYPE = 'usps';
    public const BLOCK_NAME = 'slices.usps';
    public const ATTRIBUTES = [
        'title',
        'usps'
    ];
    public function getTitle();
    public function getUsps();
}
