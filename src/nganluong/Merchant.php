<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\nganluong;

use yii\base\NotSupportedException;

use yii2vn\payment\BaseDataSignature;
use yii2vn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $id;

    public $email;

    public $password;

    public $receiverEmail;
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    protected function createDataSignature(string $data, string $type): ?BaseDataSignature
    {
        throw new NotSupportedException('Data signature is not supported in Ngan Luong Payment Gateway');
    }

}