<?php
namespace Prostor\CumDiscount\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\SetFactory;

class AddPromoExcludedAttribute implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @var SetFactory
     */
    private SetFactory $attributeSetFactory;

    /**
     * AddPromoExcludedAttribute constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, SetFactory $attributeSetFactory)
    {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return DataPatchInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            Product::ENTITY,
            'promo_excluded',
            [
                'type' => 'int',
                'label' => 'Exclude from Promotions',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'required' => false,
                'default' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
        $attributeCode = 'promo_excluded';
        $attributeSetFactory = $this->attributeSetFactory->create();
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSets = $attributeSetFactory->getCollection()->setEntityTypeFilter($entityTypeId);

        foreach ($attributeSets as $set) {
            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $set->getAttributeSetId(),
                'General',
                $attributeCode
            );
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}
