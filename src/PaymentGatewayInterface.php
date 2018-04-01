<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;


/**
 * Interface PaymentGatewayInterface
 *
 *
 * @property array|MerchantInterface[] $merchants
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface PaymentGatewayInterface
{

    const EVENT_BEFORE_CHECKOUT = 'beforeCheckout';

    const EVENT_AFTER_CHECKOUT = 'afterCheckout';

    /**
     * @return string
     */
    public static function version(): string;

    /**
     * @return string
     */
    public static function baseUrl(): string;

    /**
     * @return array|MerchantInterface[]
     */
    public function getMerchants(): array;

    /**
     * @param array|MerchantInterface[] $merchants
     * @return bool
     */
    public function setMerchants(array $merchants): bool;


    /**
     * @param string|int $id
     * @return MerchantInterface
     */
    public function getMerchant($id): MerchantInterface;

    /**
     * @param $id
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($id, $merchant): bool;

    /**
     * @param array $data
     * @return CheckoutData|bool
     */
    public function checkout(array $data);


}