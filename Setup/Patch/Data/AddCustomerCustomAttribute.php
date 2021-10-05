<?php
/*******************************************************************************
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2021 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Adobe permits you to use and modify this file
 * in accordance with the terms of the Adobe license agreement
 * accompanying it (see LICENSE_ADOBE_PS.txt).
 * If you have received this file from a source other than Adobe,
 * then your use, modification, or distribution of it
 * requires the prior written permission from Adobe.
 ******************************************************************************/
declare(strict_types=1);

namespace Qsciences\Firebase\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCustomerCustomAttribute implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetup
     */
    private $customerSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->createFirebaseIdAttribute($customerSetup);
        $this->createAssociateIdAttribute($customerSetup);
        $this->createLegacyAssociateIdAttribute($customerSetup);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    private function createFirebaseIdAttribute($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'firebase_user_id',
            [
                'type' => 'varchar',
                'label' => 'Firebase User ID',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 500,
                'system' => false,
                'backend' => ''
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            'customer', 'firebase_user_id')->addData(
            [
                'used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]
        );
        $attribute->save();
    }

    private function createAssociateIdAttribute($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'associate_id',
            [
                'type' => 'varchar',
                'label' => 'Associate ID',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 501,
                'system' => false,
                'backend' => ''
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            'customer', 'associate_id')->addData(
            [
                'used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]
        );
        $attribute->save();
    }

    private function createLegacyAssociateIdAttribute($customerSetup)
    {
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'legacy_associate_id',
            [
                'type' => 'varchar',
                'label' => 'Legacy Associate ID',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 502,
                'system' => false,
                'backend' => ''
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(
            'customer', 'legacy_associate_id')->addData(
            [
                'used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]
        );
        $attribute->save();
    }
    /*
     *
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(Customer::ENTITY, 'firebase_user_id');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
