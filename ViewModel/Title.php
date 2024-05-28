<?php

namespace Marleen\PrismicIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Title implements ArgumentInterface
{
    protected Api $apiModel;
    protected Slug $slugModel;

    public function __construct(Api $apiModel, Slug $slugModel)
    {
        $this->apiModel = $apiModel;
        $this->slugModel = $slugModel;
    }

    public function getTitle() :string
    {
        // Get the Prismic API instance from the API model
        $api = $this->apiModel->getApi();
        // Get the slug
        $slug = $this->slugModel->getSlug();

        // Fetch the document from the Prismic API using the slug
        $document = $api->getByUID('page', $slug);
        return $document->data->title[0]->text; // Return page title
    }
}
