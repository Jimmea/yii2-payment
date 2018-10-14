<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use Yii;

use yiiviet\payment\BasePaymentClient;
use yiiviet\payment\DataSignature;

/**
 * Lớp PaymentClient hổ trợ tạo và kiểm tra chữ ký dữ liệu và có các thuộc tính kết nối đến cổng thanh toán VTCPay.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * @var string tài khoản nhận tiền mặc định khi thực hiện giao dịch.
     */
    public $business;

    /**
     * @var string Mã bảo vệ nhận khi đăng ký tích hợp.
     */
    public $secureCode;


    /**
     * @var string Mã website nhận khi đăng ký
     */
    public $merchantId;

    /**
     * @inheritdoc
     * @return object|\yiiviet\payment\HashDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type = null): ?DataSignature
    {
        return Yii::createObject([
            'class' => 'yiiviet\payment\HashDataSignature',
            'hashAlgo' => 'sha256'
        ], [$data]);
    }

}
