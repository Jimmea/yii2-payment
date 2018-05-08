<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\nganluong;

use yii\base\NotSupportedException;

use yiiviet\payment\DataSignature;
use yiiviet\payment\BasePaymentClient;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BasePaymentClient
{

    public $id;

    public $email;

    public $password;

    /**
     * @inheritdoc
     */
    protected function initDataSignature(string $data, string $type): ?\yiiviet\payment\DataSignature
    {
        return null;
    }

}
