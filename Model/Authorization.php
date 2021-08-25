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

namespace Qsciences\Firebase\Model;

use Magento\Framework\Webapi\Request;
use Qsciences\Firebase\Api\AuthorizationInterface;
use Qsciences\Firebase\Helper\Data;
use Qsciences\Firebase\Model\Management\AuthManagement;

class Authorization implements AuthorizationInterface
{
    /**
     * @var AuthManagement
     */
    private $authManagement;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Authorization constructor.
     * @param Request $request
     * @param AuthManagement $authManagement
     * @param Data $helper
     */
    public function __construct(
        Request $request,
        AuthManagement $authManagement,
        Data $helper
    ) {
        $this->request = $request;
        $this->authManagement = $authManagement;
        $this->helper = $helper;
    }

    /**
     * @param string $jwtToken
     * @param string $firstname
     * @param string $lastname
     * @return array|false|mixed|string
     */
    public function getCustomerToken(string $jwtToken, string $firstname, string $lastname)
    {
        if (!$this->helper->isFireBaseAuthenticationEnabled()) {
            $response = [
                'status' => 'Error',
                'message' => __('Google Firebase Authentication is not Enabled')
            ];
            return json_encode($response);
        }
        if (empty($jwtToken)) {
            $response = [
                'status' => 'error',
                'message' => __('Firebase JWT Token is missing')
            ];
            return json_encode($response);
        }
        if (empty($firstname)) {
            $response = [
                'status' => 'error',
                'message' => __('Firstname Field value is missing')
            ];
            return json_encode($response);
        }
        if (empty($lastname)) {
            $response = [
                'status' => 'error',
                'message' => __('Lastname Field value is missing')
            ];
            return json_encode($response);
        }

        /** @var Magento Customer Token $tokenData */
        $customerData = [
            'jwt_token' => $jwtToken,
            'firstname' => $firstname,
            'lastname' => $lastname
        ];
        $customerTokenResponse = $this->authManagement->getCustomerToken($customerData);
        if ($customerTokenResponse) {
            $response = array_merge(['status' => 'success'], $customerTokenResponse);
            return json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => __('Invalid FireBase JWT Token')
            ];
            return json_encode($response);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|false|string
     */
    public function getFireBaseToken($email, $password)
    {
        if (!$this->helper->isFireBaseAuthenticationEnabled()) {
            $response = [
                'status' => 'error',
                'message' => __('Google Firebase Authentication is not Enabled')
            ];
            return json_encode($response);
        }

        if (empty($email)) {
            $response = [
                'status' => 'error',
                'message' => __('Email Address Field value is missing.')
            ];
            return json_encode($response);
        }

        if (empty($password)) {
            $response = [
                'status' => 'error',
                'message' => __('Password Field value is missing')
            ];
            return json_encode($response);
        }

        /** @var Firebase Token $tokenData */
        $tokenData = $this->authManagement->getFireBaseUserInfo($email, $password);
        if ($tokenData) {
            $response = [
                'status' => 'success',
                'message' => __('FireBase Token Generated Successfully'),
                'firebase_token' => $tokenData['idToken']
            ];
            return json_encode($response);
        } else {
            $response = [
                'status' => 'error',
                'message' => __('Invalid Information')
            ];
            return json_encode($response);
        }
    }
}
