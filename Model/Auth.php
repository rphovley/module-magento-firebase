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

namespace Adobe\Firebase\Model;

use Adobe\Firebase\Helper\Configuration;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as AuthModel;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Store\Model\StoreManagerInterface;
use mysql_xdevapi\Exception;
use PhpParser\Node\Expr\Cast\Array_;

/**
 * Class Auth
 * Adobe\Firebase\Model
 */
class Auth
{
    /**
     * @var Factory
     */
    protected $_authFactory;

    /**
     * @var CustomerCollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    protected $_auth = null;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Token Model
     *
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerInterfaceFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;


    /**
     * Auth constructor.
     * @param Factory $authFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Configuration $configuration
     * @param UserFactory $userFactory
     * @param AuthModel $firebaseAuth
     * @param CustomerTokenService $customerTokenService
     * @param TokenFactory $tokenFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface|null $eventManager
     */
    public function __construct(
        Factory $authFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Configuration $configuration,
        UserFactory $userFactory,
        AuthModel $firebaseAuth,
        CustomerTokenService $customerTokenService,
        TokenFactory $tokenFactory,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager = null
    ) {
        $this->_authFactory = $authFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->configuration = $configuration;
        $this->userFactory = $userFactory;
        $this->_firebaseAuth = $firebaseAuth;
        $this->_customerTokenService = $customerTokenService;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->tokenFactory = $tokenFactory;
        $this->storeManager = $storeManager;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Generate Firebase Factory Auth Object
     *
     * @return AuthModel
     * @throws LocalizedException
     */
    protected function getAuth()
    {
        if ($this->_auth) {
            return $this->_auth;
        }
        try {
            $authFactory = $this->_authFactory->withServiceAccount($this->getCredentials());
            $this->_auth = $authFactory->createAuth();
            return $this->_auth;
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        return [
            "type" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_SERVICE_TYPE),
            "project_id" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_PROJECT_ID),
            // "private_key_id" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_PRIVATE_KEY_ID),
            "private_key_id" => '51e00632a44318f875ed4a9f63b18396170806e1',
            "private_key" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_PRIVATE_KEY),
            "client_email" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_CLIENT_EMAIL),
            "client_id" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_CLIENT_ID),
            "auth_uri" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_AUTH_URI),
            "token_uri" => $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_TOKEN_URI),
            "auth_provider_x509_cert_url" =>
                $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_AUTH_PROVIDER_CERT_URL),
            "client_x509_cert_url" =>
                $this->configuration->getConfigValue(Configuration::XPATH_SETTINGS_CLIENT_CERT_URL),
        ];
    }

    /**
     * Function to validate the Firebase Token
     *
     * @param $jwtToken
     * @return false|mixed
     */
    public function getTokenData($jwtToken)
    {
        /** @var Array $tokenData */
        $tokenData = [];
        /** @var Array $customerdata */
        $customerdata = [];

        if (!$jwtToken) {
            return false;
        }
        try {
            if (!$this->getAuth()) {
                return false;
            }
            /** @var $verifyToken */
            $verifyToken = $this->_auth->verifyIdToken($jwtToken);
            if ($payload = $verifyToken->claims()) {
                $firebaseUserId = $verifyToken->claims()->get('user_id');

                /** @var CustomerCollectionFactory $customer */
                $customer = $this->customerCollectionFactory->create()
                    ->addAttributeToFilter('firebase_user_id', $firebaseUserId)
                    ->getFirstItem();
                if ($customer && $customer->getId()) {
                    // Generate the Customer Token
                    $tokenData['customerToken'] = $this->createCustomerAccessToken($customer);
                    return $tokenData['customerToken'];
                } else {
                    $customerdata['email'] = $verifyToken->claims()->get('email');
                    $customerdata['name'] = $verifyToken->claims()->get('display_name') ?? preg_replace(
                            '/[^A-Za-z0-9\-]/',
                            '',
                            explode('@', $customerdata['email'])[0]
                        );
                    $customerdata['user_id'] = $firebaseUserId;
                    // Create Customer Account
                    $customer = $this->saveCustomerAccount($customerdata);
                    // Generate the Customer Token
                    $tokenData['customerToken'] = $this->createCustomerAccessToken($customer);
                    return $tokenData['customerToken'];
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isGoogleFBAuthenticationEnabled()
    {
        return $this->configuration->getConfigFlagValue(Configuration::XPATH_GENERAL_ENABLED);
    }

    /**
     * Generate Customer Access Token
     *
     * @param $customer
     * @return mixed
     * @throws AuthenticationException
     */
    public function createCustomerAccessToken($customer)
    {
        try {
            $this->getRequestThrottler()->throttle($customer->getData('email'), RequestThrottler::USER_TYPE_CUSTOMER);
            $this->eventManager->dispatch('customer_login', ['customer' => $customer]);
            $this->getRequestThrottler()->resetAuthenticationFailuresCount(
                $customer->getData('email'),
                RequestThrottler::USER_TYPE_CUSTOMER
            );
            return $this->tokenFactory->create()->createCustomerToken($customer->getId())->getToken();
        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure(
                $customer->getData('email'),
                RequestThrottler::USER_TYPE_CUSTOMER
            );
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }

    /**
     * Generate Firebase Token by using Email & Password
     *
     * @param $email
     * @param $password
     * @return string | false
     * @throws AuthenticationException
     */
    public function loginWithFirebase($email, $password)
    {
        try {
            $auth = $this->getAuth();
            $result = $auth->signInWithEmailAndPassword($email, $password);
            if ($result) {
                return array_values((array)$result)[0];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new AuthenticationException(__('Invalid Email or Password. Please Try again'));
        }
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }

    /**
     * Function to Create New Customer Account
     *
     * @param $customerData
     * @return CustomerRepository
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function saveCustomerAccount($customerData)
    {
        try {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
            $customerPassword = $customerData['user_id'] . rand();
            /** @var CustomerInterfaceFactory $customer */
            $customer = $this->customerInterfaceFactory->create();
            $customer->setEmail($customerData['email']);
            $customer->setFirstname($customerData['name']);
            $customer->setLastname($customerData['name']);
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($storeId);
            $customer->setCustomAttribute('firebase_user_id', $customerData['user_id']);
            /** @var CustomerRepository $customer */
            $customer = $this->customerRepository->save($customer, $customerPassword);
            return $customer;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
