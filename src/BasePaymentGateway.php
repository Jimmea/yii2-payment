<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\Request as HttpClientRequest;

/**
 *
 * @property HttpClient $httpClient
 * @property MerchantInterface $defaultMerchant
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentGateway extends Component implements PaymentGatewayInterface
{

    /**
     * @var array
     */
    public $checkoutRequestDataConfig = ['class' => Data::class];

    /**
     * @var array
     */
    public $checkoutResponseDataConfig = ['class' => Data::class];

    /**
     * @var array
     */
    public $merchantClass;

    /**
     * @var array|MerchantInterface[]
     */
    private $_merchants = [];

    /**
     * @param bool $load
     * @return array|MerchantInterface[]
     * @throws InvalidConfigException
     */
    public function getMerchants($load = true): array
    {
        if ($load) {
            $merchants = [];
            foreach ($this->_merchants as $id => $merchant) {
                $merchants[$id] = $this->getMerchant($id);
            }
        } else {
            $merchants = $this->_merchants;
        }

        return $merchants;
    }

    /**
     * @param array|MerchantInterface[] $merchants
     * @return bool
     */
    public function setMerchants(array $merchants): bool
    {
        foreach ($merchants as $id => $merchant) {
            $this->setMerchant($id, $merchant);
        }

        return true;
    }

    /**
     * @param null|string|int $id
     * @return mixed|MerchantInterface
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function getMerchant($id = null): MerchantInterface
    {
        if ($id === null) {
            return $this->getDefaultMerchant();
        } elseif ((is_string($id) || is_int($id))) {
            if (isset($this->_merchants[$id])) {
                $merchant = $this->_merchants[$id];

                if (is_array($merchant) || is_string($merchant)) {
                    if (is_string($merchant)) {
                        $merchant = ['class' => $merchant, 'paymentGateway' => $this];
                    } else {
                        $merchant['paymentGateway'] = $this;
                        if (!isset($merchant['class'])) {
                            $merchant['class'] = $this->merchantClass;
                        }
                    }
                }

                if (!$merchant instanceof MerchantInterface) {
                    return $this->_merchants[$id] = Yii::createObject($merchant);
                } else {
                    return $merchant;
                }

            } else {
                throw new InvalidConfigException(__METHOD__ . ": merchant id: `$id` not found!");
            }
        } else {
            throw new InvalidArgumentException('Only accept get merchant via string or int key type!');
        }
    }

    /**
     * @param int|string $id
     * @param null $merchant
     * @return bool
     */
    public function setMerchant($id, $merchant = null): bool
    {
        if ($merchant === null) {
            $this->_merchants[] = $id;
        } else {
            $this->_merchants[$id] = $merchant;
        }

        return true;
    }


    /**
     * @var null|MerchantInterface
     */
    private $_defaultMerchant;

    /**
     * @return MerchantInterface|BaseMerchant
     * @throws InvalidConfigException
     */
    public function getDefaultMerchant(): MerchantInterface
    {
        if (!$this->_defaultMerchant) {
            $merchantIds = array_keys($this->_merchants);
            if ($merchantId = reset($merchantIds)) {
                return $this->_defaultMerchant = $this->getMerchant($merchantId);
            } else {
                throw new InvalidConfigException('Can not get default merchant on an empty array merchants!');
            }
        } else {
            return $this->_defaultMerchant;
        }
    }

    /**
     * @inheritdoc
     */
    public function checkout(array $data, string $method = self::CHECKOUT_METHOD_INTERNET_BANKING)
    {
        $merchant = ArrayHelper::remove($data, 'merchant', $this->getDefaultMerchant());
        $requestDataConfig = ArrayHelper::merge($this->checkoutRequestDataConfig, ['merchant' => $merchant, 'method' => $method]);
        /** @var Data $data */

        $requestData = Yii::createObject($requestDataConfig, [$data]);

        $event = Yii::createObject([
            'class' => CheckoutEvent::class,
            'requestData' => $requestData,
            'method' => $method,
        ]);

        $this->trigger(self::EVENT_BEFORE_CHECKOUT, $event);

        if ($event->isValid) {
            $responseData = $this->checkoutInternal($data, $method);
            $responseDataConfig = ArrayHelper::merge($this->checkoutResponseDataConfig, ['merchant' => $merchant, 'method' => $method]);
            $responseData = Yii::createObject($responseDataConfig, [$responseData]);
            $event->responseData = $responseData;
            $this->trigger(self::EVENT_AFTER_CHECKOUT, $event);

            return $responseData;
        } else {
            return false;
        }
    }

    /**
     * @param Data $data
     * @param string $method
     * @return array
     */
    abstract protected function checkoutInternal(Data $data, string $method): array;


    /**
     * @var HttpClient|null
     */
    private $_httpClient = ['class' => HttpClient::class, 'transport' => 'yii\httpclient\CurlTransport'];

    /**
     * @return HttpClient
     * @throws InvalidConfigException
     */
    public function getHttpClient(): HttpClient
    {
        if (!$this->_httpClient instanceof HttpClient) {
            $client = $this->_httpClient = Yii::createObject($this->_httpClient);
            $client->baseUrl = static::baseUrl();

            return $client;
        } else {
            return $this->_httpClient;
        }
    }

    /**
     * @param array|string|callable|HttpClient $client
     * @return bool
     */
    public function setHttpClient($client): bool
    {
        if ($client instanceof HttpClient) {
            $client->baseUrl = static::baseUrl();
        }

        $this->_httpClient = $client;

        return true;
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $queryData
     * @param null|MerchantInterface $merchant
     * @return HttpClientRequest
     */
    abstract public function createHttpRequest(string $url, string $method, array $queryData, MerchantInterface $merchant = null): HttpClientRequest;

}