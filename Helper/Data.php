<?php
declare(strict_types=1);
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

namespace Adobe\Firebase\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Adobe\Firebase\Helper
 */
class Data extends AbstractHelper
{
    const XPATH_GENERAL_ENABLED = 'firebase/general/enabled';
    const XPATH_GENERAL_FRONTEND_ENABLED = 'firebase/general/frontend_enabled';
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
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Data constructor.
     * @param EncryptorInterface $encryptor
     * @param Context $context
     */
    public function __construct(
        EncryptorInterface $encryptor,
        Context $context
    ) {
        $this->encryptor = $encryptor;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isFireBaseAuthenticationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
                self::XPATH_GENERAL_ENABLED,
                ScopeInterface::SCOPE_STORE
            ) == 1;
    }

    public function isFireBaseFrontendAuthenticationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
                self::XPATH_GENERAL_FRONTEND_ENABLED,
                ScopeInterface::SCOPE_STORE
            ) == 1;
    }

    /**
     * @return string
     */
    public function getServiceType(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_SERVICE_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_PROJECT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getPrivateKeyId(): string
    {
        $privateKeyId = $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_PRIVATE_KEY_ID,
            ScopeInterface::SCOPE_STORE
        );

        return $this->encryptor->decrypt($privateKeyId);
    }

    /**
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_PRIVATE_KEY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getClientEmail(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_CLIENT_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_CLIENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_AUTH_URI,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getTokenUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_TOKEN_URI,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getAuthProviderCertUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_AUTH_PROVIDER_CERT_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getClientCertUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XPATH_SETTINGS_CLIENT_CERT_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
}
