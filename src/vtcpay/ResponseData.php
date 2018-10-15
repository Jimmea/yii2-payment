<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu phản hồi từ VTCPay sau khi thực hiện hàm [[request()]] ở [[PaymentGateway]].
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property string redirect_url
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class ResponseData extends BaseResponseData
{

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        return true;
    }

}
