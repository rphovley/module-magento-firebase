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

use Adobe\Firebase\Api\AuthorizationInterface;
use Adobe\Firebase\Model\Management\AuthManagement;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Webapi\Request;

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
     * @var \Adobe\Firebase\Helper\Data
     */
    private $helper;

    /**
     * Authorization constructor.
     * @param Request $request
     * @param AuthManagement $authManagement
     * @param \Adobe\Firebase\Helper\Data $helper
     */
    public function __construct(
        Request $request,
        AuthManagement $authManagement,
        \Adobe\Firebase\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->authManagement = $authManagement;
        $this->helper = $helper;
    }

    /**
     * @param string $jwtToken
     * @return array|mixed
     */
    public function authenticate(string $jwtToken)
    {
        $response = [];
        if (!$this->helper->isFireBaseAuthenticationEnabled()) {
            $response = [
                'status' => 'Error',
                'message' => 'Google Firebase Authentication is not Enabled'
            ];

            return $response;
        }
        if (!$jwtToken) {
            $response = [
                'status' => 'Error',
                'message' => 'Firebase JWT Token is missing'
            ];

            return $response;
        }

        /** @var Magento Customer Token $tokenData */
        $customerToken = $this->authManagement->getCustomerToken($jwtToken);
        if (!$customerToken) {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid Firebase JWT Token'
            ];
            return $response;
        }
        return [$customerToken];
    }

    /**
     * @param string $email
     * @param string $password
     * @return mixed|string[]
     * @throws AuthenticationException
     */
    public function generateToken($email, $password)
    {
        if (!$this->helper->isFireBaseAuthenticationEnabled()) {
            $response = [
                'status' => 'Error',
                'message' => 'Google Firebase Authentication is not Enabled'
            ];
            return $response;
        }
        if (!$email || !$password) {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid Email / Password'
            ];
            return $response;
        }

        /** @var Firebase Token $tokenData */
        $tokenData = $this->authManagement->getFireBaseToken($email, $password);
        if ($tokenData) {
            $response = [
                'status' => 'success',
                'firebase_token' => $tokenData
            ];
            return $response;
        } else {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid Email / Password'
            ];
            return $response;
        }
    }
}
