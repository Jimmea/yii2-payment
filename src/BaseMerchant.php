<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Component;
use yii\base\NotSupportedException;

/**
 * @property BasePaymentGateway $paymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseMerchant extends Component
{

    /**
     * BaseMerchant constructor.
     * @param BasePaymentGateway $paymentGateway
     * @param array $config
     */
    public function __construct(BasePaymentGateway $paymentGateway, array $config = [])
    {
        $this->_paymentGateway = $paymentGateway;
        parent::__construct($config);
    }

    public function init()
    {
        if ($this->getPaymentGateway()->sandbox) {
            $this->initSandboxEnvironment();
        }

        parent::init();
    }


    protected function initSandboxEnvironment()
    {

    }

    /**
     * @var BasePaymentGateway
     */
    private $_paymentGateway;

    /**
     * @return BasePaymentGateway
     */
    public function getPaymentGateway(): BasePaymentGateway
    {
        return $this->_paymentGateway;
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function signature(string $data, string $type = null): string
    {
        if ($dataSignature = $this->initDataSignature($data, $type)) {
            return $dataSignature->generate();
        } else {
            throw new NotSupportedException("Signature data with type: '$type' is not supported!");
        }
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function validateSignature(string $data, string $expectSignature, string $type = null): bool
    {
        if ($dataSignature = $this->initDataSignature($data, $type)) {
            return $dataSignature->validate($expectSignature);
        } else {
            throw new NotSupportedException("Validate signature with type: '$type' is not supported!");
        }
    }

    /**
     * @param string $data
     * @param string $type
     * @return DataSignature
     */
    abstract protected function initDataSignature(string $data, string $type): ?DataSignature;


}