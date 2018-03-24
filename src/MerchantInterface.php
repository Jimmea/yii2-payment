<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;


/**
 * @package yii2vn\payment
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface MerchantInterface
{

    public function getPaymentGateway();

    public function setPaymentGateway(PaymentGatewayInterface $paymentGateway);

    public function checkout(PaymentInfoInterface $info, $method = PaymentGatewayInterface::CHECKOUT_METHOD_IB);

}