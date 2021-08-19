<?php
declare(strict_types=1);

namespace Qsciences\Firebase\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GenerateFireBaseToken implements ResolverInterface
{
    /**
     * @var \Qsciences\Firebase\Model\Authorization
     */
    private $authorization;

    /**
     * GenerateFireBaseToken constructor.
     * @param \Qsciences\Firebase\Model\Authorization $authorization
     */
    public function __construct(\Qsciences\Firebase\Model\Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {

            if (!isset($args['input']['email'])) {
                throw new GraphQlInputException(__('"email" can not be empty'));
            }

            if (!isset($args['input']['password'])) {
                throw new GraphQlInputException(__('"password" can not be empty'));
            }

            $output['result'] = $this->authorization->generateToken($args['input']['email'],
                $args['input']['password']);

            return $output;

        } catch (Exception $e) {
            throw new GraphQlInputException(__('Error while processing request.'), $e);
        }
    }
}
