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

namespace Adobe\Firebase\Model;

use Adobe\Firebase\Helper\Data;
use Exception;
use Kreait\Firebase\Auth as AuthModel;
use Kreait\Firebase\Factory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Integration\Model\CustomerTokenService;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;

/**
 * Class Auth
 * @package Adobe\Firebase\Model
 */
class Auth
{
    /**
     * @var Factory
     */
    private $authFactory;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var UserFactory
     */
    private $userFactory;

    private $auth = null;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var RequestThrottler
     */
    private $requestThrottler;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * Token Model
     *
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * Auth constructor.
     * @param Factory $authFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Data $helper
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
        Data $helper,
        UserFactory $userFactory,
        AuthModel $firebaseAuth,
        CustomerTokenService $customerTokenService,
        TokenFactory $tokenFactory,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager = null
    ) {
        $this->authFactory = $authFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->helper = $helper;
        $this->userFactory = $userFactory;
        $this->firebaseAuth = $firebaseAuth;
        $this->customerTokenService = $customerTokenService;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->tokenFactory = $tokenFactory;
        $this->storeManager = $storeManager;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
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
            $verifyToken = $this->auth->verifyIdToken($jwtToken);
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
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Generate Firebase Factory Auth Object
     *
     * @return AuthModel
     * @throws LocalizedException
     */
    protected function getAuth()
    {
        if ($this->auth) {
            return $this->auth;
        }
        try {
            $authFactory = $this->authFactory->withServiceAccount($this->getCredentials());
            $this->auth = $authFactory->createAuth();
            return $this->auth;
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    public function getCredentials()
    {
        return [
            "type" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_SERVICE_TYPE),
            "project_id" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_PROJECT_ID),
            "private_key_id" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_PRIVATE_KEY_ID),
            "private_key" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_PRIVATE_KEY),
            "client_email" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_CLIENT_EMAIL),
            "client_id" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_CLIENT_ID),
            "auth_uri" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_AUTH_URI),
            "token_uri" => $this->helper->getConfigValue(Data::XPATH_SETTINGS_TOKEN_URI),
            "auth_provider_x509_cert_url" =>
                $this->helper->getConfigValue(Data::XPATH_SETTINGS_AUTH_PROVIDER_CERT_URL),
            "client_x509_cert_url" =>
                $this->helper->getConfigValue(Data::XPATH_SETTINGS_CLIENT_CERT_URL),
        ];
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
        } catch (Exception $e) {
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
     * Get request throttler instance
     *
     * @return RequestThrottler
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }

    /**
     * Function to Create New Customer Account
     *
     * @param $customerData
     * @return CustomerRepository
     * @throws LocalizedException
     * @throws InputException
     * @throws InputMismatchException
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

    /**
     * @return bool
     */
    public function isGoogleFBAuthenticationEnabled()
    {
        return $this->helper->getConfigFlagValue(Data::XPATH_GENERAL_ENABLED);
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
        } catch (Exception $e) {
            throw new AuthenticationException(__('Invalid Email or Password. Please Try again'));
        }
    }
}
