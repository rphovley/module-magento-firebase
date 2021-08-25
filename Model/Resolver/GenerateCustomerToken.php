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

namespace Qsciences\Firebase\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Qsciences\Firebase\Model\Authorization;

class GenerateCustomerToken implements ResolverInterface
{

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * GenerateCustomerToken constructor.
     * @param Authorization $authorization
     */
    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            if (!isset($args['input']['jwt_token'])) {
                throw new GraphQlInputException(__('"jwt_token" can not be empty'));
            }

            if (!isset($args['input']['first_name'])) {
                throw new GraphQlInputException(__('"first_name" can not be empty'));
            }

            if (!isset($args['input']['last_name'])) {
                throw new GraphQlInputException(__('"last_name" can not be empty'));
            }

            $output['result'] = $this->authorization->getCustomerToken($args['input']['jwt_token'],
                $args['input']['first_name'], $args['input']['last_name']);

            return $output;

        } catch (Exception $e) {
            throw new GraphQlInputException(__('Error while processing request.'), $e);
        }
    }
}
