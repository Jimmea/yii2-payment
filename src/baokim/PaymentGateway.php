<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii\di\Instance;
use yii\base\NotSupportedException;
use yii\httpclient\Client as HttpClient;
use yii\helpers\ArrayHelper;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\Data;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const RC_PURCHASE_PRO = 0x04;

    const RC_GET_MERCHANT_DATA = 0x08;

    const EVENT_BEFORE_PURCHASE_PRO = 'beforePurchasePro';

    const EVENT_AFTER_PURCHASE_PRO = 'afterPurchasePro';

    const EVENT_BEFORE_GET_MERCHANT_DATA = 'beforeGetMerchantData';

    const EVENT_AFTER_GET_MERCHANT_DATA = 'afterGetMerchantData';

    const PURCHASE_URL = '/payment/order/version11';

    const PURCHASE_PRO_URL = '/payment/rest/payment_pro_api/pay_by_card';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const QUERY_DR_URL = '/payment/order/queryTransaction';

    const MUI_CHARGE = 'charge';

    const MUI_BASE = 'base';

    const MUI_IFRAME = 'iframe';

    const DIRECT_TRANSACTION = 1;

    const SAFE_TRANSACTION = 2;

    /**
     * @var bool|string|array|\yii\caching\Cache
     */
    public $merchantDataCache = 'cache';

    public $merchantDataCacheDuration = 86400;

    /**
     * @var array
     */
    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => RequestData::class];

    public $verifyReturnDataConfig = ['class' => VerifyReturnData::class];

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://sandbox.baokim.vn' : 'https://www.baokim.vn';
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @inheritdoc
     */
    public function init()
    {
        if ($this->merchantDataCache) {
            $this->merchantDataCache = Instance::ensure($this->merchantDataCache, 'yii\caching\Cache');
        }

        $this->on(self::EVENT_BEFORE_REQUEST, function (\yii2vn\payment\RequestEvent $event) {
            if ($event->command === self::RC_PURCHASE_PRO) {
                $this->trigger(self::EVENT_BEFORE_PURCHASE_PRO, $event);
            } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
                $this->trigger(self::EVENT_BEFORE_GET_MERCHANT_DATA, $event);
            }
        });

        $this->on(self::EVENT_AFTER_REQUEST, function (\yii2vn\payment\RequestEvent $event) {
            if ($event->command === self::RC_PURCHASE_PRO) {
                $this->trigger(self::EVENT_AFTER_PURCHASE_PRO, $event);
            } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
                $this->trigger(self::EVENT_AFTER_GET_MERCHANT_DATA, $event);
            }
        });

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => 'json',
                'options' => [
                    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }


    /**
     * @param string $emailBusiness
     * @param int|string|null $merchantId
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     * @return object|ResponseData|bool
     */
    public function getMerchantData(string $emailBusiness = null, $merchantId = null): ResponseData
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant($merchantId);
        $cacheKey = [
            __METHOD__,
            $emailBusiness,
            $merchant->id
        ];

        if (!$this->merchantDataCache || !$responseData = $this->merchantDataCache->get($cacheKey)) {
            $responseData = $this->request(self::RC_GET_MERCHANT_DATA, [
                'business' => $emailBusiness ?? $merchant->email
            ]);

            if ($this->merchantDataCache) {
                $this->merchantDataCache->set($cacheKey, $responseData, $this->merchantDataCacheDuration);
            }
        }

        return $responseData;
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException|\yii\base\InvalidConfigException
     */
    protected function requestInternal($command, \yii2vn\payment\BaseMerchant $merchant, Data $requestData, HttpClient $httpClient): ?array
    {
        /** @var Merchant $merchant */

        $data = $requestData->get();
        $httpMethod = 'POST';

        if ($command & (self::RC_GET_MERCHANT_DATA | self::RC_QUERY_DR)) {
            if ($command === self::RC_GET_MERCHANT_DATA) {
                $url = self::PRO_SELLER_INFO_URL;
            } else {
                $url = self::QUERY_DR_URL;
            }
            $data[0] = $url;
            $url = $data;
            $data = null;
            $httpMethod = 'GET';
        } elseif ($command === self::RC_PURCHASE) {
            $data[0] = self::PURCHASE_URL;
            return ['redirect_url' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } elseif ($command === self::RC_PURCHASE_PRO) {
            $url = [self::PURCHASE_PRO_URL, 'signature' => ArrayHelper::remove($data, 'signature')];
        } else {
            return null;
        }

        return $httpClient->createRequest()
            ->setUrl($url)
            ->setMethod($httpMethod)
            ->setOptions([CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword])
            ->setData($data)
            ->send()
            ->getData();
    }
}
