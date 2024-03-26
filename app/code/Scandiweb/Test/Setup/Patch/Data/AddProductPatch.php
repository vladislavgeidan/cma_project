<?php
declare(strict_types=1);

namespace Scandiweb\Test\Setup\Patch\Data;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface as CategoryRepository;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\App\State;

use Psr\Log\LoggerInterface;

class AddProductPatch implements SchemaPatchInterface, DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var CategoryRepository
     */
    protected CategoryRepository $categoryRepository;

    /**
     * @var Category
     */
    protected Category $category;

    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var Product
     */
    protected Product $product;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategoryRepository $categoryRepository
     * @param Category $category
     * @param Product $product
     * @param ProductRepository $productRepository
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param LoggerInterface $logger
     * @param State $state
     */
    public function __construct(
        ModuleDataSetupInterface        $moduleDataSetup,
        CategoryRepository              $categoryRepository,
        Category                        $category,
        Product                         $product,
        ProductRepository               $productRepository,
        CategoryLinkManagementInterface $categoryLinkManagement,
        LoggerInterface                 $logger,
        State                           $state
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categoryRepository = $categoryRepository;
        $this->category = $category;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->logger = $logger;
        $this->state = $state;
    }

    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();
        $this->state->setAreaCode(Area::AREA_FRONTEND);

        try {
            $product = $this->product;
            $product->setName('Test Product');
            $product->setTypeId('simple');
            $product->setAttributeSetId(4);
            $product->setSku('test-SKU');
            $product->setVisibility(4);
            $product->setPrice(100);
            $product->setStockData(array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'min_sale_qty' => 1,
                    'max_sale_qty' => 2,
                    'is_in_stock' => 1,
                    'qty' => 100
                )
            );
            $product->save();

            $category = $this->category;
            $category->setName('Test Man');
            $category->setParentId(2);
            $category->setIsActive(true);
            $this->categoryRepository->save($category);

            $this->categoryLinkManagement->assignProductToCategories(
                'test-SKU',
                [$category->getId()]
            );
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
