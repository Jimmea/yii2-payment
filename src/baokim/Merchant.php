<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\helpers\ArrayHelper;

use yii2vn\payment\BaseMerchant;
use yii2vn\payment\BaseDataSignature;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $id;

    public $apiUser;

    public $apiPassword;

    public $businessEmail;

    public $securePassword;

    public $privateCertificate;

    public $publicCertificate;

    public $hmacDataSignatureConfig = ['class' => 'yii2vn\payment\HmacDataSignature'];

    public $rsaDataSignatureConfig = ['class' => 'yii2vn\payment\RsaDataSignature'];

    public function setPublicCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->publicCertificate = file_get_contents($file);

        return true;
    }

    public function setPrivateCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->privateCertificate = file_get_contents($file);

        return true;
    }

    /**
     * @inheritdoc
     *
     * @return null|object|BaseDataSignature
     */
    protected function createDataSignature(string $data, string $type): ?BaseDataSignature
    {
        if ($type === self::SIGNATURE_RSA) {
            return Yii::createObject(ArrayHelper::merge($this->rsaDataSignatureConfig, [
                'data' => $data,
                'publicCertificate' => $this->publicCertificate,
                'privateCertificate' => $this->privateCertificate,
                'openSSLAlgo' => OPENSSL_ALGO_SHA1
            ]));
        } elseif ($type === self::SIGNATURE_HMAC) {
            return Yii::createObject(ArrayHelper::merge($this->hmacDataSignatureConfig, [
                'data' => $data,
                'key' => $this->securePassword,
                'hmacAlgo' => 'md5'
            ]));
        } else {
            return null;
        }
    }
}