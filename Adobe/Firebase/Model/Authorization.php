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

use Adobe\Firebase\Api\AuthorizationInterface;
use Magento\Framework\Webapi\Request;
use Adobe\Firebase\Model\Auth as FireBaseAuth;

class Authorization implements AuthorizationInterface
{

    /**
     * @var Auth
     */
    private $firebaseAuth;

    /**
     * @var Request
     */
    private $request;

    /**
     * Authorization constructor.
     * @param Request $request
     * @param Auth $firebaseAuth
     */
    public function __construct(
        Request $request,
        FireBaseAuth $firebaseAuth
    ) {
        $this->request = $request;
        $this->firebaseAuth = $firebaseAuth;
    }

    /**
     * @param string $jwtToken
     * @return array|mixed|\string[][]
     */
    public function authenticate($jwtToken)
    {
        if(!$this->firebaseAuth->isGoogleFBAuthenticationEnabled()){
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Google Firebase Authentication is not Enabled'
            ];
            return [$responsefinal];
        }
        if (!$jwtToken) {
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Firebase JWT Token is missing'
            ];
            return [$responsefinal];
        }

        /** @var Magento Customer Token $tokenData */
        $tokenData = $this->firebaseAuth->getTokenData($jwtToken);
        if (!$tokenData) {
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Invalid Firebase JWT Token'
            ];
            return [$responsefinal];
        }
        return [$tokenData];
    }

    /**
     * @param string $email
     * @param string $password
     * @return mixed|\string[][]
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function generateFBToken($email, $password)
    {
        if(!$this->firebaseAuth->isGoogleFBAuthenticationEnabled()){
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Google Firebase Authentication is not Enabled'
            ];
            return [$responsefinal];
        }
        if (!$email || !$password) {
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Invalid Email / Password'
            ];
            return [$responsefinal];
        }
        /** @var Firebase Token $tokenData */
        $tokenData = $this->firebaseAuth->loginWithFirebase($email, $password);
        if($tokenData){
            $responsefinal = [
                'status' => 'success',
                'firebase_token' => $tokenData
            ];
            return [$responsefinal];
        }else{
            $responsefinal = [
                'status' => 'Error',
                'message' => 'Invalid Email / Password'
            ];
            return [$responsefinal];
        }
    }
}
