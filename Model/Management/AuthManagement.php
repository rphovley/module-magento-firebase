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

namespace Qsciences\Firebase\Model\Management;

use Exception;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;
use Lcobucci\JWT\Token\Plain;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Store\Model\StoreManagerInterface;
use Qsciences\Firebase\Helper\Data;

/**
 * Class AuthManagement
 * @package Qsciences\Firebase\Model\Management
 */
class AuthManagement
{
    /**
     * @var Factory
     */
    private $fireBaseAuthFactory;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Token Model
     *
     * @var TokenFactory
     */
    private $tokenFactory;

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
     * @var mixed
     */
    private $eventManager;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * AuthManagement constructor.
     * @param Factory $fireBaseAuthFactory
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Data $helper
     * @param TokenFactory $tokenFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface|null $eventManager
     */
    public function __construct(
        Factory $fireBaseAuthFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        Data $helper,
        TokenFactory $tokenFactory,
        StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $eventManager = null
    ) {
        $this->fireBaseAuthFactory = $fireBaseAuthFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->helper = $helper;
        $this->tokenFactory = $tokenFactory;
        $this->storeManager = $storeManager;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(ManagerInterface::class);
    }

    /**
     * @param array $customerData
     * @return array|bool
     */
    public function getCustomerToken(array $customerData)
    {
        try {
            /** @var Auth $fireBaseAuth */
            $fireBaseAuth = $this->getFireBaseAuth();

            /** @var Plain $verifyToken */
            $verifyToken = $fireBaseAuth->verifyIdToken($customerData['jwt_token']);
            if ($payload = $verifyToken->claims()) {
                $customerData['firebase_user_id'] = $verifyToken->claims()->get('user_id');
                $customerData['email'] = $verifyToken->claims()->get('email');
                $response = $this->getCustomerTokenByFireBaseUserData($customerData);
                return $response;
            }

            return false;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate Firebase Factory Auth Object
     *
     * @return Auth|null
     * @throws LocalizedException
     */
    private function getFireBaseAuth()
    {
        try {
            $authFactory = $this->fireBaseAuthFactory->withServiceAccount($this->getCredentials());
            return $authFactory->createAuth();
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    private function getCredentials()
    {
        return [
            "type" => $this->helper->getServiceType(),
            "project_id" => $this->helper->getProjectId(),
            "private_key_id" => $this->helper->getPrivateKeyId(),
            "private_key" => $this->helper->getPrivateKey(),
            "client_email" => $this->helper->getClientEmail(),
            "client_id" => $this->helper->getClientId(),
            "auth_uri" => $this->helper->getAuthUrl(),
            "token_uri" => $this->helper->getTokenUrl(),
            "auth_provider_x509_cert_url" => $this->helper->getAuthProviderCertUrl(),
            "client_x509_cert_url" => $this->helper->getClientCertUrl(),
        ];
    }

    /**
     * @param array $customerData
     * @return array
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    private function getCustomerTokenByFireBaseUserData(array $customerData): array
    {
        /** @var CustomerCollectionFactory $customer */
        $customer = $this->customerCollectionFactory->create()
            ->addAttributeToFilter('email', $customerData['email'])
            ->getFirstItem();
        if ($customer && $customer->getId()) {
            /* Check Whether Customer Firebase User ID is exist on customer account or not,
               If not, Save the User Id as part of Customer Account */
            $this->setFirebaseDetails($customerData['email'], $customerData['firebase_user_id']);
            // Generate the Customer Token
            $customerToken = $this->createCustomerAccessToken($customer);
            $response = [
                'customer_token' => $customerToken,
                'isNewCustomer' => 0
            ];
            return $response;
        } else {
            // Create Customer Account
            $customer = $this->createCustomerAccount($customerData);
            // Generate the Customer Token
            $customerToken = $this->createCustomerAccessToken($customer);
            $response = [
                'customer_token' => $customerToken,
                'isNewCustomer' => 1
            ];
            return $response;
        }
    }

    /**
     * @param string $email
     * @param string $firebaseUserId
     * @return CustomerInterface | string
     */
    public function setFirebaseDetails(string $email, string $firebaseUserId)
    {
        try {
            $customer = $this->customerRepository->get($email);
            if (!$customer->getCustomAttribute('firebase_user_id')) {
                $customer->setCustomAttribute('firebase_user_id', $firebaseUserId);
                // Return Customer Object
                return $this->customerRepository->save($customer);
            }
            return $customer;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Generate Customer Access Token
     *
     * @param $customer
     * @return mixed
     * @throws AuthenticationException
     */
    private function createCustomerAccessToken($customer)
    {
        try {
            $this->getRequestThrottler()->throttle($customer->getEmail(), RequestThrottler::USER_TYPE_CUSTOMER);
            $this->eventManager->dispatch('customer_login', ['customer' => $customer]);
            $this->getRequestThrottler()->resetAuthenticationFailuresCount(
                $customer->getEmail(),
                RequestThrottler::USER_TYPE_CUSTOMER
            );
            return $this->tokenFactory->create()->createCustomerToken($customer->getId())->getToken();
        } catch (Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure(
                $customer->getEmail(),
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
     * @param $customerData
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function createCustomerAccount($customerData)
    {
        try {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
            $customerPassword = md5($customerData['firebase_user_id']);

            $customer = $this->customerInterfaceFactory->create();
            $customer->setEmail($customerData['email']);
            $customer->setFirstname($customerData['firstname']);
            $customer->setLastname($customerData['lastname']);
            $customer->setWebsiteId($websiteId);
            $customer->setStoreId($storeId);
            $customer->setCustomAttribute('firebase_user_id', $customerData['firebase_user_id']);
            $customer->setCustomAttribute('associate_id', $customerData['associate_id']);
            $customer->setCustomAttribute('legacy_associate_id', $customerData['legacy_associate_id']);

            $customer = $this->customerRepository->save($customer, $customerPassword);

            return $customer;

        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param $email
     * @param $password
     * @return array|bool
     */
    public function getFireBaseUserInfo($email, $password)
    {
        try {
            /** @var Auth $fireBaseAuth */
            $fireBaseAuth = $this->getFireBaseAuth();
            $result = $fireBaseAuth->signInWithEmailAndPassword($email, $password);
            if ($result) {
                /**
                 * Array(
                 * [localId] => {Customer Firebase User ID}
                 * [email] => { Customer Email Address }
                 * [displayName] => {Customer Display Name }
                 * [idToken] => {JWT TOKEN}
                 * )
                 */
                return $result->data();
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param array $customerData
     * @return mixed
     * @throws LocalizedException
     */
    public function getCustomerData(array $customerData)
    {
        /** @var CustomerCollectionFactory $customer */
        $customer = $this->customerCollectionFactory->create()
            ->addAttributeToFilter('email', $customerData['email'])
            ->getFirstItem();
        if ($customer && $customer->getId()) {
            /* Check Whether Customer Firebase User ID is exist or not,
               If not, Save the User Id as part of Customer Account */
            return $this->setFirebaseDetails($customerData['email'], $customerData['firebase_user_id']);
        } else {
            // Create Customer Account
            $customer = $this->createCustomerAccount($customerData);
            // Return Customer Object
            return $customer;
        }
    }
}
