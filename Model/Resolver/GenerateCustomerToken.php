<?php
declare(strict_types=1);

namespace Adobe\Firebase\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GenerateCustomerToken implements ResolverInterface
{

    /**
     * @var \Adobe\Firebase\Model\Authorization
     */
    private $authorization;

    /**
     * GenerateCustomerToken constructor.
     * @param \Adobe\Firebase\Model\Authorization $authorization
     */
    public function __construct(\Adobe\Firebase\Model\Authorization $authorization)
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

            $output['result'] = $this->authorization->authorize($args['input']['jwt_token'],
                $args['input']['first_name'], $args['input']['last_name']);

            return $output;

        } catch (Exception $e) {
            throw new GraphQlInputException(__('Error while processing request.'), $e);
        }
    }
}
