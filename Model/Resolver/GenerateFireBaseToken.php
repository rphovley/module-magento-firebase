<?php
declare(strict_types=1);

namespace Adobe\Firebase\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GenerateFireBaseToken implements ResolverInterface
{
    /**
     * @var \Adobe\Firebase\Model\Authorization
     */
    private $authorization;

    /**
     * GenerateFireBaseToken constructor.
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

            if (!isset($args['input']['email'])) {
                throw new LocalizedException(__('"email" can not be empty'));
            }

            if (!isset($args['input']['password'])) {
                throw new LocalizedException(__('"password" can not be empty'));
            }

            $output['result'] = $this->authorization->generateToken($args['input']['email'],$args['input']['password']);

            return $output;

        } catch (Exception $e) {
            throw new GraphQlInputException(__('Error while processing request.'), $e);
        }
    }
}
