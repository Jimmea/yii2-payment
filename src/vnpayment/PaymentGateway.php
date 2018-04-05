<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\vnpayment;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const CHECKOUT_METHOD_DYNAMIC = 'dynamic';

    const CHECKOUT_METHOD_DETECT = 'detect';

    const PAYMENT_URL = '/paymentv2/vpcpay.html';

    const QUERY_DR_URL = '';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'http://sandbox.vnpayment.vn' : 'http://vnpayment.vn';
    }

    public static function version(): string
    {
        return '2.0.0';
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        /** @var Merchant $merchant */
        $merchant = $data->merchant;
        $queryData = $data->getData();
        ksort($queryData);
        $queryData['vnp_SecureHash'] = md5($merchant->hashSecret . urldecode(http_build_query($queryData)));
        $queryData['vnp_SecureHashType'] = 'md5';

        $location = rtrim(static::baseUrl()) . self::PAYMENT_URL . '?' . http_build_query($queryData);

        return ['location' => $location, 'code' => '00'];
    }

    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [

            ]
        ];
    }

    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_DETECT;
    }

}