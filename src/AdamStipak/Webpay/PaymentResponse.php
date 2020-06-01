<?php

namespace AdamStipak\Webpay;

class PaymentResponse {

  /** @var array */
  private $params = [];

  /** @var string */
  private $digest;

  /** @var string */
  private $digest1;

  /**
   * @param string $operation
   * @param string $ordernumber
   * @param string $merordernum
   * @param int $prcode
   * @param int $srcode
   * @param string $resulttext
   * @param string $digest
   * @param string $digest1
   */
  public function __construct (string $operation, string $ordernumber, string $merordernum = null, int $prcode, int $srcode, string $resulttext, string $digest, string $digest1) {
    $this->params['operation'] = $operation;
    $this->params['ordermumber'] = $ordernumber;
    if ($merordernum !== null) {
      $this->params['merordernum'] = $merordernum;
    }
    $this->params['prcode'] = $prcode;
    $this->params['srcode'] = $srcode;
    $this->params['resulttext'] = $resulttext;
    $this->digest = $digest;
    $this->digest1 = $digest1;
  }

  /**
   * @return array
   */
  public function getParams (): array {
    return $this->params;
  }

  /**
   * @return mixed
   */
  public function getDigest (): string {
    return $this->digest;
  }

  /**
   * @return bool
   */
  public function hasError (): bool {
    return (bool) $this->params['prcode'] || (bool) $this->params['srcode'];
  }

  /**
   * @return string
   */
  public function getDigest1 (): string {
    return $this->digest1;
  }
  
    /**
     * Known Primary response codde states
     *  
     * @return array<string> PRCODE meaning codes
     */
    public static function prcode_meanings() {
        return [
            0 => 'OK',
            1 => _('Field too long'),
            2 => _('Field too short'),
            3 => _('Incorrect content of field'),
            4 => ('Field is null'),
            5 => _('Missing required field'),
            6 => _('Missing field'),
            11 => _('Unknown merchant'),
            14 => _('Duplicate order number'),
            15 => _('Object not found'),
            16 => _('Amount to approve exceeds payment amount'),
            17 => _('Amount to deposit exceeds approved amount'),
            18 => _('Total sum of credited amounts exceeded deposited amount'),
            20 => _('Object not in valid state for operation'),
            25 => _('Operation not allowed for user'),
            26 => _('Technical problem in connection to authorization center'),
            27 => _('Incorrect payment type'),
            28 => _('Declined in 3D'),
            30 => _('Declined in AC'),
            31 => _('Wrong digest'),
            32 => _('Expired card'),
            33 => _('Original/Master order was not authorized'),
            34 => _('Original/Master order is not valid for subsequent payment'),
            35 => _('Session expired'),
            38 => _('Card not supported'),
            40 => _('Declined in Fraud detection systém'),
            50 => _('The cardholder canceled the payment'),
            80 => _('Duplicate MessageId'),
            82 => _('HSM key label missing'),
            83 => _('Canceled by issuer'),
            84 => _('Duplikate value'),
            85 => _('Declined due to merchant’s rules'),
            200 => _('Additional info request'),
            300 => _('Soft decline – issuer requires SCA'),
            1000 => _('Technical problem')];
    }

    /**
     * Secondary response code menanigs
     * 
     * @return array<string> SRCODE meaning
     */
    public static function srcode_meanings() {

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
    public static function response_meaning($prcode, $srcode) {
        switch ($prcode) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 15:
            case 20:
                $meaning = self::prcode_meanings()[$prcode] . ': ' . self::srcode_meanings()[$srcode];
                break;

            default:
                $meaning = self::prcode_meanings()[$prcode];
                break;
        }
    }
  
}
