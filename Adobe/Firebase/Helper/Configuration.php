<?php
/*
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2020 Adobe
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
 */

namespace Adobe\Firebase\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class Configuration
 * Adobe\Firebase\Helper
 */
class Configuration
{
    const XPATH_GENERAL_ENABLED = 'firebase/general/enabled';
    const XPATH_GENERAL_ENABLED_FOR_ADMIN_WEB = 'firebase/general/enabled_for_admin_web';
    const XPATH_GENERAL_ADMIN_ROLES = 'firebase/general/admin_roles';
    const XPATH_GENERAL_ADMIN_USER_IDENTIFIER = 'firebase/general/admin_user_identifier';
    const XPATH_SETTINGS_SERVICE_TYPE = 'firebase/settings/service_type';
    const XPATH_SETTINGS_PROJECT_ID = 'firebase/settings/project_id';
    const XPATH_SETTINGS_PRIVATE_KEY_ID = 'firebase/settings/private_key_id';
    const XPATH_SETTINGS_PRIVATE_KEY = 'firebase/settings/private_key';
    const XPATH_SETTINGS_CLIENT_EMAIL = 'firebase/settings/client_email';
    const XPATH_SETTINGS_CLIENT_ID = 'firebase/settings/client_id';
    const XPATH_SETTINGS_AUTH_URI = 'firebase/settings/auth_uri';
    const XPATH_SETTINGS_TOKEN_URI = 'firebase/settings/token_uri';
    const XPATH_SETTINGS_AUTH_PROVIDER_CERT_URL = 'firebase/settings/auth_provider_cert_url';
    const XPATH_SETTINGS_CLIENT_CERT_URL = 'firebase/settings/client_cert_url';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Configuration constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $path
     * @param null $storeId
     * @return bool
     */
    public function getConfigFlagValue($path, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
