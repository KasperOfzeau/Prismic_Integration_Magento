<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface StepsSliceInterface
{
    public const TYPE = 'steps';
    public const BLOCK_NAME = 'slices.steps';
    public const ATTRIBUTES = [
        'steps',
    ];
    public function getSteps();
}
