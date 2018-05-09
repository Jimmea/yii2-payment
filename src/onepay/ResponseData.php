<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use Yii;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu nhận được từ OnePay khi gọi [[request()]] ở lớp [[PaymentGateway]].
 *
 * @property PaymentClient $client
 * @property string|null $message
 * @property int|null $responseCode
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class ResponseData extends BaseResponseData
{

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        if (in_array($this->getCommand(), [PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_DR_INTERNATIONAL], true)) {
            return $this->canGetProperty('vpc_DRExists') ? strcasecmp($this->vpc_DRExists, 'Y') === 0 : false;
        } else {
            return true;
        }
    }

    /**
     * Phương thức hổ trợ lấy và phiên dịch message nhận từ OnePay (nếu như bạn có thiết lập i18n).
     *
     * @return null|string Trả về NULL nếu như dữ liệu OnePay gửi về không tồn tại `error_code`,
     * và ngược lại sẽ là câu thông báo đã được phiên dịch.
     */
    public function getMessage(): ?string
    {
        if ($this->canGetProperty('vpc_Message')) {
            return Yii::t('yii2vn/payment/onepay', $this->vpc_Message);
        } else {
            return null;
        }
    }

    /**
     * Phương thức hổ trợ lấy response code từ OnePay,
     * do tùy theo lệnh mà giá trị này nằm ở các attribute phản hồi khác nhau nên phương thức này sẽ tự động xác định.
     *
     * @return int|null Trả về NULL nếu như không có response code và ngược lại
     */
    public function getResponseCode(): ?int
    {
        if ($this->canGetProperty('vpc_TxnResponseCode')) {
            return $this->vpc_TxnResponseCode;
        } elseif ($this->canGetProperty('vpc_ResponseCode')) {
            return $this->vpc_ResponseCode;
        } else {
            return null;
        }
    }

}
