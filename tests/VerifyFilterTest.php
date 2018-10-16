<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

use yii\base\Action;

use yiiviet\payment\VerifyFilter;

/**
 * Lớp VerifyFilterTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class VerifyFilterTest extends TestCase
{

    public static function gatewayId(): string
    {
        return 'VTC';
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage `commands` property must be set!
     */
    public function testMissingCommand()
    {
        return new VerifyFilter([
            'gateway' => $this->gateway
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage `gateway` property must be set!
     */
    public function testMissingGateway()
    {
        return new VerifyFilter([
            'commands' => [
                'ipn' => 'IPN'
            ]
        ]);
    }

    /**
     * @expectedException  \yii\web\ForbiddenHttpException
     */
    public function testInvalid()
    {
        $_POST = [
            '_method' => 'POST',
            'data' => '100000|VND|0963465816|1|74132',
            'signature' => '1d31c0779f47e2bc3bfe40becf1fda0d7e881aeb90d8efb0341e258692cf896aa'
        ];

        return (new VerifyFilter([
            'gateway' => $this->gateway,
            'commands' => [
                'ipn' => 'IPN'
            ]
        ]))->beforeAction(new Action('ipn', null));
    }

    /**
     * @depends testMissingCommand
     * @depends testMissingGateway
     * @depends testInvalid
     */
    public function test()
    {
        $_POST = [
            '_method' => 'POST',
            'data' => '100000|VND|0963465816|1|74132',
            'signature' => '1d31c0779f47e2bc3bfe40becf1fda0d7e881aeb90d8efb0341e258692cf896a'
        ];

        $behavior = new VerifyFilter([
            'gateway' => $this->gateway,
            'commands' => [
                'ipn' => 'IPN'
            ]
        ]);

        $this->assertTrue($behavior->beforeAction(new Action('ipn', null)));
        $this->assertInstanceOf('\GatewayClients\DataInterface', $behavior->getVerifiedData());

    }


}
