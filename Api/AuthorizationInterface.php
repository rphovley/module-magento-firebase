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

namespace Adobe\Firebase\Api;


interface AuthorizationInterface
{
    /**
     * @param string $jwtToken
     * @param string $firstname
     * @param string $lastname
     * @return mixed
     */
    public function authorize(string $jwtToken, string $firstname, string $lastname);

    /**
     * @param string $email
     * @param string $password
     * @return mixed
     */
    public function generateToken($email, $password);
}
