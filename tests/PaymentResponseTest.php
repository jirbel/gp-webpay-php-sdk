<?php

namespace AdamStipak\Webpay;

use PHPUnit\Framework\TestCase;

class PaymentResponseTest extends TestCase {

    public function errorCodesProvider() {
        return [
            [
                [
                    'PRCODE' => 0,
                    'SRCODE' => 0,
                ],
                false,
            ],
            [
                [
                    'PRCODE' => 97,
                    'SRCODE' => 0,
                ],
                true,
            ],
            [
                [
                    'PRCODE' => 12,
                    'SRCODE' => 32,
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider errorCodesProvider
     */
    public function testHasError($codes, $result) {
        $response = new PaymentResponse(
                'OPERATION',
                'ORDERNUMBER',
                'MERORDERNUM',
                $codes['PRCODE'],
                $codes['SRCODE'],
                'RESULTTEXT',
                'DIGEST',
                'DIGEST1'
        );

        $this->assertEquals($result, $response->hasError());
    }

}
