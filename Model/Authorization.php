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

namespace Qsciences\Firebase\Model;

use Qsciences\Firebase\Api\AuthorizationInterface;
use Qsciences\Firebase\Model\Management\AuthManagement;
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
     * @var \Qsciences\Firebase\Helper\Data
     */
    private $helper;

    /**
     * Authorization constructor.
     * @param Request $request
     * @param AuthManagement $authManagement
     * @param \Qsciences\Firebase\Helper\Data $helper
     */
    public function __construct(
        Request $request,
        AuthManagement $authManagement,
        \Qsciences\Firebase\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->authManagement = $authManagement;
        $this->helper = $helper;
    }

    /**
     * @param string $jwtToken
     * @param string $firstname
     * @param string $lastname
     * @return array|mixed|string[]
     */
    public function authorize(string $jwtToken, string $firstname, string $lastname)
    {
        $response = [];
        if (!$this->helper->isFireBaseAuthenticationEnabled()) {
            $response = [
                'status' => 'Error',
                'message' => 'Google Firebase Authentication is not Enabled'
            ];
            return json_encode($response);
        }
        if (!$jwtToken) {
            $response = [
                'status' => 'Error',
                'message' => 'Firebase JWT Token is missing'
            ];
            return json_encode($response);
        }
        if (!$firstname) {
            $response = [
                'status' => 'Error',
                'message' => 'Firstname Field value is missing'
            ];
            return json_encode($response);
        }
        if (!$lastname) {
            $response = [
                'status' => 'Error',
                'message' => 'Lastname Field value is missing'
            ];
            return json_encode($response);
        }

        /** @var Magento Customer Token $tokenData */
        $customerData = [
            'jwt_token' => $jwtToken,
            'firstname' => $firstname,
            'lastname'  => $lastname
            ];
        $customerToken = $this->authManagement->getCustomerToken($customerData);
        if (!$customerToken) {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid FireBase JWT Token'
            ];
            return json_encode($response);
        }
        return json_encode($customerToken);
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
            return json_encode($response);
        }
        if (!$email || !$password) {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid Password'
            ];
            return json_encode($response);
        }

        /** @var Firebase Token $tokenData */
        $tokenData = $this->authManagement->getFireBaseUserInfo($email, $password);
        if ($tokenData) {
            $response = array(
                'status' => 'success',
                'firebase_token' => $tokenData['idToken']
            );
            return json_encode($response);
        } else {
            $response = [
                'status' => 'Error',
                'message' => 'Invalid Email / Password'
            ];
            return json_encode($response);
        }
    }
}
