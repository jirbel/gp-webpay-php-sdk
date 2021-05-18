<?php

namespace AdamStipak\Webpay;

class Api
{

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
        'MERCHANTNUMBER' => ['type' => 'string', 'size' => 10, 'required' => true, 'digest' => true],
        'OPERATION' => ['type' => 'string', 'size' => 20, 'required' => true, 'digest' => true],
        'ORDERNUMBER' => ['type' => 'numeric', 'size' => 15, 'required' => true, 'digest' => true],
        'AMOUNT' => ['type' => 'numeric', 'size' => 15, 'required' => true, 'digest' => true],
        'CURRENCY' => ['type' => 'numeric', 'size' => 3, 'required' => null, 'digest' => true], // pokud není uvedeno, použije se default z obchodníka nebo banky
        'DEPOSITFLAG' => ['type' => 'numeric', 'size' => 1, 'required' => true, 'digest' => true],
        'MERORDERNUM' => ['type' => 'numeric', 'size' => 30, 'required' => false, 'digest' => true],
        'URL' => ['type' => 'string', 'size' => 300, 'required' => true, 'digest' => true],
        'DESCRIPTION' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'MD' => ['type' => 'string', 'size' => 255, 'required' => null, 'digest' => true],
        // 'USERPARAM1' => ['type' => 'string', 'size' => 255, 'required' => null, 'digest' => false], // povinné pro registrační platbu pro funkci Opakovaná platba, Uložená karta, Uložená karta 3D, jinak nepovinné
        // 'VRCODE' => ['type' => 'string', 'size' => 48, 'required' => null, 'digest' => false], //pole povinné pro zaslání ověřovacího kódu prostřednictvím názvu obchodníka do AC
        // 'FASTPAYID' => ['type' => 'numeric', 'size' => 15, 'required' => null, 'digest' => false], //povinné, pokud je využita služba Fastpay
        'PAYMETHOD' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'DISABLEPAYMETHOD' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'PAYMETHODS' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'EMAIL' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'REFERENCENUMBER' => ['type' => 'string', 'size' => 20, 'required' => false, 'digest' => true],
        'ADDINFO' => ['type' => 'xml', 'size' => 24000, 'required' => false, 'digest' => true], // schéma
        // 'PANPATTERN' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => false],
        // 'TOKEN' => ['type' => 'string', 'size' => 64, 'required' => false, 'digest' => false],
        // 'FASTTOKEN' => ['type' => 'string', 'size' => 64, 'required' => null, 'digest' => false], //povinné, pokud je využita služba Fasttoken
        'DIGEST' => ['type' => 'string', 'size' => 2000, 'required' => true, 'digest' => false],
        'LANG' => ['type' => 'string', 'size' => 2, 'required' => false, 'digest' => false],
        // 'PRCODE' => ['type' => 'string', 'size' => 2, 'required' => false, 'digest' => false],
        // 'SRCODE' => ['type' => 'string', 'size' => 2, 'required' => false, 'digest' => false],
        // 'RESULTTEXT' => ['type' => 'string', 'size' => 2, 'required' => false, 'digest' => false],
    ];

    const PAYMENT_RESPONSE_PARAMS = [
        'OPERATION' => ['type' => 'string', 'size' => 20, 'required' => true, 'digest' => true],
        'ORDERNUMBER' => ['type' => 'numeric', 'size' => 15, 'required' => true, 'digest' => true],
        'MERORDERNUM' => ['type' => 'numeric', 'size' => 30, 'required' => false, 'digest' => true],
        'MD' => ['type' => 'string', 'size' => 255, 'required' => null, 'digest' => true],
        'PRCODE' => ['type' => 'numeric', 'size' => null, 'required' => true, 'digest' => true],
        'SRCODE' => ['type' => 'numeric', 'size' => null, 'required' => true, 'digest' => true],
        'RESULTTEXT' => ['type' => 'string', 'size' => 255, 'required' => false, 'digest' => true],
        'USERPARAM1' => ['type' => 'string', 'size' => 64, 'required' => null, 'digest' => true], // povinné pro registrační platbu pro funkci Opakovaná platba, Uložená karta, Uložená karta 3D, jinak nepovinné
        'ADDINFO' => ['type' => 'xml', 'size' => null, 'required' => false, 'digest' => true], // schéma
        'TOKEN' => ['type' => 'string', 'size' => 64, 'required' => false, 'digest' => true],
        'EXPIRY' => ['type' => 'string', 'size' => 4, 'required' => false, 'digest' => true], // Expirace použité platební karty ve formátu YYMM
        'ACSRES' => ['type' => 'string', 'size' => 1, 'required' => false, 'digest' => true], // N = nebyl proveden pokus o ověření, A = byl proveden pokus, F = držitel se plně autentikoval, D = držitel nebyl úspěšně ověřen, E = technický problém
        'ACCODE' => ['type' => 'string', 'size' => 6, 'required' => false, 'digest' => true],
        'PANPATTERN' => ['type' => 'string', 'size' => 19, 'required' => false, 'digest' => true],
        'DAYTOCAPTURE' => ['type' => 'string', 'size' => 8, 'required' => false, 'digest' => true],
        'TOKENREGSTATUS' => ['type' => 'string', 'size' => 10, 'required' => false, 'digest' => true],
        'ACRC' => ['type' => 'string', 'size' => 2, 'required' => false, 'digest' => true],
        'RRN' => ['type' => 'string', 'size' => 12, 'required' => false, 'digest' => true],
        'PAR' => ['type' => 'string', 'size' => 29, 'required' => false, 'digest' => true],
        'TRACEID' => ['type' => 'string', 'size' => 15, 'required' => false, 'digest' => true],
        'DIGEST' => ['type' => 'string', 'size' => 2000, 'required' => true, 'digest' => false],
        'DIGEST1' => ['type' => 'string', 'size' => 2000, 'required' => true, 'digest' => false],
        'MERCHANTNUMBER' => ['type' => 'string', 'size' => 10, 'required' => false, 'digest' => true],
    ];

    /**
     * @param $merchantNumber
     * @param $webPayUrl
     * @param Signer $signer
     */
    public function __construct(string $merchantNumber, string $webPayUrl, Signer $signer)
    {
        $this->merchantNumber = $merchantNumber;
        $this->webPayUrl = $webPayUrl;
        $this->signer = $signer;
    }

    /**
     * @param PaymentRequest $request
     * @return string
     */
    public function createPaymentRequestUrl(PaymentRequest $request): string
    {
        // build request URL based on PaymentRequest
        $paymentUrl = $this->webPayUrl . '?' . http_build_query($this->createPaymentParam($request));

        return $paymentUrl;
    }

    /**
     * @param \AdamStipak\Webpay\PaymentRequest $request
     * @return array
     */
    public function createPaymentParam(PaymentRequest $request): array
    {
        // digest request
        $request->setMerchantNumber($this->merchantNumber);
        $params = $request->getParams();
        $request->setDigest($this->signer->sign($params));

        return $request->getParams();
    }

    /**
     * @param PaymentResponse $response
     * @throws Exception
     * @throws PaymentResponseException
     */
    public function verifyPaymentResponse(PaymentResponse $response)
    {
        // verify digest & digest1
        try {
            $responseParams = $response->getParams();
            $this->signer->verify($responseParams, $response->getDigest());

            $this->signer->verify($responseParams, $response->getDigest1(), ['MERCHANTNUMBER' => $this->merchantNumber]);
        } catch (SignerException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        // verify PRCODE and SRCODE
        if (false !== $response->hasError()) {
            $prcode = $response->getParams()['PRCODE'];
            $srcode = $response->getParams()['SRCODE'];
            throw new PaymentResponseException(
                $prcode,
                $srcode,
                "Response has an error. {$prcode}:{$srcode}"
            );
        }
    }
}
