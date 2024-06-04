<?php

namespace Marleen\PrismicIntegration\Console\Command;

use Elgentos\PrismicIO\Api\ConfigurationInterface;
use Elgentos\PrismicIO\Helper\GetStoreView;
use Elgentos\PrismicIO\Model\Api;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResource;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Prismic\Predicates;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

class UpdateUrlRewrites extends Command
{
    private ConfigurationInterface $configuration;
    private StoreManagerInterface $storeManager;
    private Api $apiFactory;
    private UrlFinderInterface $urlFinder;
    private UrlRewriteFactory $urlRewriteFactory;
    private UrlPersistInterface $urlPersist;
    private UrlRewriteResource $urlRewriteResource;
    private LoggerInterface $logger;
    private GetStoreView $getStoreView;
    private State $state;

    public function __construct(
        ConfigurationInterface $configuration,
        StoreManagerInterface $storeManager,
        Api $apiFactory,
        UrlFinderInterface $urlFinder,
        UrlRewriteFactory $urlRewriteFactory,
        UrlPersistInterface $urlPersist,
        UrlRewriteResource $urlRewriteResource,
        LoggerInterface $logger,
        GetStoreView $getStoreView,
        State $state
    ) {
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
        $this->apiFactory = $apiFactory;
        $this->urlFinder = $urlFinder;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlPersist = $urlPersist;
        $this->urlRewriteResource = $urlRewriteResource;
        $this->logger = $logger;
        $this->getStoreView = $getStoreView;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('prismic:update-url-rewrites')
            ->setDescription('Update URL rewrites for Prismic documents');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('adminhtml');

        // Fetch all page IDs from Prismic
        $api = $this->apiFactory->create();
        $allDocuments = $api->query(
            \Prismic\Predicates::at('document.type', 'page')
        );
        $documentIds = array_map(function ($doc) {
            return $doc->id;
        }, $allDocuments->results);

        // Create the payload with all document IDs
        $payload = [
            'secret' => '123', // Ensure this matches your configuration
            'documents' => $documentIds // Use the fetched document IDs
        ];

        if (!$this->protectRoute($payload)) {
            $output->writeln('<error>Invalid secret in payload</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            // Haal de store ID op (bijv. 1 voor de default store)
            $storeId = 1; // Pas dit aan naar de juiste store ID
            $store = $this->storeManager->getStore($storeId);
            $this->storeManager->setCurrentStore($storeId);
        } catch (NoSuchEntityException $e) {
            $output->writeln('<error>Store not found</error>');
            return Cli::RETURN_FAILURE;
        }
        $urlRewriteDocumentTypes = $this->configuration->getUrlRewriteContentTypes($store);

        if (!$urlRewriteDocumentTypes) {
            $output->writeln('<info>No URL rewrite document types configured</info>');
            return Cli::RETURN_SUCCESS;
        } else {
            $output->writeln('<info>URL rewrite document types found: ' .
                implode(', ', $urlRewriteDocumentTypes) . '</info>');
        }

        $documentIds = $payload['documents'] ?? [];
        if (empty($documentIds)) {
            $output->writeln('<info>No document IDs in payload</info>');
            return Cli::RETURN_SUCCESS;
        }

        foreach ($documentIds as $documentId) {
            $document = $api->getByID($documentId);
            if (!$document) {
                $output->writeln('<info>Document not found: ' . $documentId . '</info>');
                continue;
            }

            $output->writeln('<info>Processing document ID: ' . $documentId . '</info>');

//            $document->lang = 'nl_nl';
//            $currentStore = $this->getStoreView->getCurrentStoreView($document);
//
//            if (!$currentStore) {
//                $output->writeln('<info>Store not found for document: ' . $documentId . '</info>');
//                continue;
//            }
            $currentStore = $store;
            $urlRewrite = $this->findUrlRewrite($document, $currentStore);

            if ($urlRewrite && $urlRewrite->getEntityType() === CmsPageUrlRewriteGenerator::ENTITY_TYPE) {
                $this->deleteUrlRewrite($document, $currentStore);
                $output->writeln('<info>Deleted URL rewrite for document: ' . $documentId . '</info>');
            }

            if (!$urlRewrite || $urlRewrite->getEntityType() === CmsPageUrlRewriteGenerator::ENTITY_TYPE) {
                $this->createUrlRewrite($document, $currentStore);
                $output->writeln('<info>Created URL rewrite for document: ' . $documentId . '</info>');
            }
        }

        $output->writeln('<info>URL rewrites updated successfully</info>');
        return Cli::RETURN_SUCCESS;
    }

    protected function findUrlRewrite(stdClass $document, StoreInterface $store): ?UrlRewrite
    {
        $this->logger->info(
            sprintf("Finding URL rewrite for document UID: %s and store ID: %s", $document->uid, $store->getId())
        );
        $urlRewrite = $this->urlFinder->findOneByData([
            UrlRewrite::REQUEST_PATH => $document->uid,
            UrlRewrite::STORE_ID => $store->getId()
        ]);
        if ($urlRewrite === null) {
            $this->logger->info(
                sprintf("No URL rewrite found for document UID: %s and store ID: %s", $document->uid, $store->getId())
            );
        }
        return $urlRewrite;
    }

    protected function deleteUrlRewrite(stdClass $document, StoreInterface $store): void
    {
        $this->urlPersist->deleteByData([
            UrlRewrite::REQUEST_PATH => $document->uid,
            UrlRewrite::STORE_ID => $store->getId()
        ]);
    }

    protected function createUrlRewrite(stdClass $document, StoreInterface $store): void
    {
        $urlRewrite = $this->urlRewriteFactory->create();

        $urlRewrite->setEntityType('custom');
        $urlRewrite->setRequestPath($document->uid);
        $urlRewrite->setTargetPath('prismicio/direct/page/type/' . $document->type . '/uid/' . $document->uid);
        $urlRewrite->setStoreId($store->getId());

        try {
            $this->urlRewriteResource->save($urlRewrite);
        } catch (\Exception $exception) {
            $this->logger->error('Could not save url rewrite for published prismic page: ' . $exception->getMessage());
        }
    }

    private function protectRoute(array $payload): bool
    {
        $accessToken = $this->configuration->getWebhookSecret($this->storeManager->getStore());

        if ($payload['secret'] ?? '' === $accessToken) {
            return true;
        }

        return false;
    }
}
