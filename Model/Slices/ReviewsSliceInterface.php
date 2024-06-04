<?php

namespace Marleen\PrismicIntegration\Model\Slices;

interface ReviewsSliceInterface
{
    public const TYPE = 'reviews';
    public const BLOCK_NAME = 'slices.reviews';
    public const ATTRIBUTES = [
        'title',
        'background_image',
        'reviews'
    ];
    public function getTitle();
    public function getBackgroundImage();
    public function getReviews();
}
