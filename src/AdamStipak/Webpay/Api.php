<?php

namespace AdamStipak\Webpay;

class Api {

    /** @var string */
    private $webPayUrl;

    /** @var string */
    private $merchantNumber;

    /** @var Signer */
    private $signer;

    /**
     * Payment request parameters in requied order
     */
    const PAYMENT_PARAMS = [
        'MERCHANTNUMBER' =>  ['type' => 'string',  'size' => 10,    'required' => true],
        'OPERATION' =>       ['type' => 'string',  'size' => 20,    'required' => true],
        'ORDERNUMBER' =>     ['type' => 'numeric', 'size' => 15,    'required' => true],
        'AMOUNT' =>          ['type' => 'numeric', 'size' => 15,    'required' => true],
        'CURRENCY' =>        ['type' => 'numeric', 'size' => 3,     'required' => null], // pokud není uvedeno, použije se default z obchodníka nebo banky
        'DEPOSITFLAG' =>     ['type' => 'numeric', 'size' => 1,     'required' => true],
        'MERORDERNUM' =>     ['type' => 'numeric', 'size' => 30,    'required' => false],
        'URL' =>             ['type' => 'string',  'size' => 300,   'required' => true],
        'DESCRIPTION' =>     ['type' => 'string',  'size' => 255,   'required' => false],
        'MD' =>              ['type' => 'string',  'size' => 255,   'required' => null],
        'USERPARAM1' =>      ['type' => 'string',  'size' => 255,   'required' => null], // povinné pro registrační platbu pro funkci Opakovaná platba, Uložená karta, Uložená karta 3D, jinak nepovinné
        'VRCODE' =>          ['type' => 'string',  'size' => 48,    'required' => null], //pole povinné pro zaslání ověřovacího kódu prostřednictvím názvu obchodníka do AC
        'FASTPAYID' =>       ['type' => 'numeric', 'size' => 15,    'required' => null], //povinné, pokud je využita služba Fastpay
        'PAYMETHOD' =>       ['type' => 'string',  'size' => 255,   'required' => false],
        'DISABLEPAYMETHOD'=> ['type' => 'string',  'size' => 255,   'required' => false],
        'PAYMETHODS' =>      ['type' => 'string',  'size' => 255,   'required' => false],
        'EMAIL' =>           ['type' => 'string',  'size' => 255,   'required' => false],
        'REFERENCENUMBER' => ['type' => 'string',  'size' => 20,    'required' => false],
        'ADDINFO' =>         ['type' => 'xml',     'size' => 24000, 'required' => false], // schéma
        'PANPATTERN' =>      ['type' => 'string',  'size' => 255,   'required' => false],
        'TOKEN' =>           ['type' => 'string',  'size' => 64,    'required' => false],
        'FASTTOKEN' =>       ['type' => 'string',  'size' => 64,    'required' => null], //povinné, pokud je využita služba Fasttoken
        'DIGEST' =>          ['type' => 'string',  'size' => 2000,  'required' => true],
        'LANG' =>            ['type' => 'string',  'size' => 2,     'required' => false],
    ];

    /**
     * API Client Class
     * 
     * @param $merchantNumber
     * @param $webPayUrl
     * @param Signer $signer
     */
    public function __construct(string $merchantNumber, string $webPayUrl, Signer $signer) {
        $this->merchantNumber = $merchantNumber;
        $this->webPayUrl = $webPayUrl;
        $this->signer = $signer;
    }

    /**
     * Generate Request URL from object content
     * 
     * @param PaymentRequest $request
     * 
     * @return string
     */
    public function createPaymentRequestUrl(PaymentRequest $request): string {
// build request URL based on PaymentRequest
        $paymentUrl = $this->webPayUrl . '?' . http_build_query($this->createPaymentParam($request));

        return $paymentUrl;
    }

    /**
     * @param \AdamStipak\Webpay\PaymentRequest $request
     * 
     * @return array
     */
    public function createPaymentParam(PaymentRequest $request): array {
// digest request
        $request->setMerchantNumber($this->merchantNumber);
        $params = $request->getParams();
        $request->setDigest($this->signer->sign($params));

        return $request->getParams();
    }

    /**
     * Payment Response verification
     * 
     * @param PaymentResponse $response
     * @throws Exception
     * @throws PaymentResponseException
     */
    public function verifyPaymentResponse(PaymentResponse $response) {
// verify digest & digest1
        try {
            $responseParams = $response->getParams();
            $this->signer->verify($responseParams, $response->getDigest());

            $responseParams['MERCHANTNUMBER'] = $this->merchantNumber;

            $this->signer->verify($responseParams, $response->getDigest1());
        } catch (SignerException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

// verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            throw new PaymentResponseException(
                    $response->getParams()['prcode'],
                    $response->getParams()['srcode'],
                    "Response has an error."
            );
        }
    }

}
