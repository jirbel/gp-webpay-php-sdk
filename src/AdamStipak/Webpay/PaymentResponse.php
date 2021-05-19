<?php

namespace AdamStipak\Webpay;

class PaymentResponse
{

    /** @var array */
    private $params = [];

    /** @var string */
    private $digest;

    /** @var string */
    private $digest1;

    /**
     * @param string $operation
     * @param int $ordernumber
     * @param string $merordernum
     * @param int $prcode
     * @param int $srcode
     * @param string $resulttext
     * @param string $digest
     * @param string $digest1
     */
    public function __construct(string $operation, int $ordernumber, string $merordernum = null, int $prcode, int $srcode, string $resulttext, string $digest, string $digest1)
    {
        $this->params['OPERATION'] = $operation;
        $this->params['ORDERNUMBER'] = $ordernumber;
        if ($merordernum !== null) {
            $this->params['MERORDERNUM'] = $merordernum;
        }
        $this->params['PRCODE'] = $prcode;
        $this->params['SRCODE'] = $srcode;
        $this->params['RESULTTEXT'] = $resulttext;
        $this->digest = $digest;
        $this->digest1 = $digest1;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return mixed
     */
    public function getDigest(): string
    {
        return $this->digest;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return (bool)$this->params['PRCODE'] || (bool)$this->params['SRCODE'];
    }

    /**
     * @return string
     */
    public function getDigest1(): string
    {
        return $this->digest1;
    }

    /**
     * Known Primary response codde states
     *
     * @return array<string> PRCODE meaning codes
     */
    public static function prcode_meanings()
    {
        return [
            0 => __('OK'),
            1 => __('Field too long'),
            2 => __('Field too short'),
            3 => __('Incorrect content of field'),
            4 => __('Field is null'),
            5 => __('Missing required field'),
            6 => __('Missing field'),
            11 => __('Unknown merchant'),
            14 => __('Duplicate order number'),
            15 => __('Object not found'),
            16 => __('Amount to approve exceeds payment amount'),
            17 => __('Amount to deposit exceeds approved amount'),
            18 => __('Total sum of credited amounts exceeded deposited amount'),
            20 => __('Object not in valid state for operation'),
            25 => __('Operation not allowed for user'),
            26 => __('Technical problem in connection to authorization center'),
            27 => __('Incorrect payment type'),
            28 => __('Declined in 3D'),
            30 => __('Declined in AC'),
            31 => __('Wrong digest'),
            32 => __('Expired card'),
            33 => __('Original/Master order was not authorized'),
            34 => __('Original/Master order is not valid for subsequent payment'),
            35 => __('Session expired'),
            38 => __('Card not supported'),
            40 => __('Declined in Fraud detection system'),
            50 => __('The cardholder canceled the payment'),
            80 => __('Duplicate MessageId'),
            82 => __('HSM key label missing'),
            83 => __('Canceled by issuer'),
            84 => __('Duplicate value'),
            85 => __('Declined due to merchant’s rules'),
            200 => __('Additional info request'),
            300 => __('Soft decline – issuer requires SCA'),
            1000 => __('Technical problem')];
    }

    /**
     * Secondary response code menanigs
     *
     * @return array<string> SRCODE meaning
     */
    public static function srcode_meanings()
    {

        return [
            0 => null,
            1 => 'ORDERNUMBER',
            2 => 'MERCHANTNUMBER',
            3 => 'PAN',
            4 => 'EXPIRY',
            5 => 'CVV',
            6 => 'AMOUNT',
            7 => 'CURRENCY',
            8 => 'DEPOSITFLAG',
            10 => 'MERORDERNUM',
            11 => 'CREDITNUMBER',
            12 => 'OPERATION',
            14 => 'ECI',
            18 => 'BATCH',
        ];

//V případě PRCODE 1 až 5, 15 a 20 se mohou vrátit následující SRCODE
    }

    /**
     * full response meang for given PRCODE and SRCODE
     *
     * @param int $prcode
     * @param int $srcode
     *
     * @return string response meaning
     */
    public static function response_meaning($prcode, $srcode)
    {
        switch ($prcode) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 15:
            case 20:
                return self::prcode_meanings()[$prcode] . ': ' . self::srcode_meanings()[$srcode];

            default:
                return self::prcode_meanings()[$prcode];
        }
    }
}
