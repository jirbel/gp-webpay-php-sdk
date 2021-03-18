<?php

namespace AdamStipak\Webpay;

class Signer
{

    /** @var string */
    private $privateKey;

    /** @var resource */
    private $privateKeyResource;

    /** @var string */
    private $privateKeyPassword;

    /** @var string */
    private $publicKey;

    /** @var resource */
    private $publicKeyResource;

    /**
     * Request Signer Class
     *
     * @param string $privateKey path to file
     * @param string $privateKeyPassword in plaintext
     * @param string $publicKey path to file
     *
     * @throws SignerException
     */
    public function __construct(string $privateKey, string $privateKeyPassword, string $publicKey)
    {
        if (!file_exists($privateKey) || !is_readable($privateKey)) {
            throw new SignerException("Private key ({$privateKey}) not exists or not readable!");
        }

        if (!file_exists($publicKey) || !is_readable($publicKey)) {
            throw new SignerException("Public key ({$publicKey}) not exists or not readable!");
        }

        $this->privateKey = $privateKey;
        $this->privateKeyPassword = $privateKeyPassword;
        $this->publicKey = $publicKey;
    }

    /**
     * Obtain private key as resource
     *
     * @return resource
     *
     * @throws SignerException
     */
    private function getPrivateKeyResource()
    {
        if ($this->privateKeyResource) {
            return $this->privateKeyResource;
        }

        $key = file_get_contents($this->privateKey);

        if (!($this->privateKeyResource = openssl_pkey_get_private($key, $this->privateKeyPassword))) {
            throw new SignerException("'{$this->privateKey}' is not valid PEM private key (or passphrase is incorrect).");
        }

        return $this->privateKeyResource;
    }

    /**
     * Reorder parmas as required for digest calculation
     *
     * @param array $params for request
     * @param boolean $checkRequied check for requied columns
     *
     * @return array params in proper order
     *
     * @throws SignerException An required field  is not present in params
     */
    public static function reorderParams(array $params, $checkRequired = false)
    {
        $paramsOrdered = [];
        foreach (Api::PAYMENT_PARAMS as $panme => $pprps) {
            if (array_key_exists($panme, $params)) {
                $paramsOrdered[$panme] = $params[$panme];
            }
            if (($checkRequired == true) && (Api::PAYMENT_PARAMS[$panme]['required'] === true)) {
                if (($panme != 'DIGEST') && !array_key_exists($panme, $params)) {
                    throw new SignerException('Required field ' . $panme . ' is not present in params to sign');
                }
            }
        }
        return $paramsOrdered;
    }

    /**
     * Reorder params to proper order and sign to make DIGEST value
     *
     * @param array $params
     *
     * @return string
     */
    public function sign(array $params): string
    {
        // $digestText = implode('|', self::reorderParams($params, true));
        $digestText = implode('|', $params);
        openssl_sign($digestText, $digest, $this->getPrivateKeyResource());
        $digest = base64_encode($digest);
        return $digest;
    }

    /**
     * Verify digest
     *
     * @param array $params
     * @param string $digest
     *
     * @return bool
     *
     * @throws SignerException
     */
    public function verify(array $params, $digest)
    {
        // $data = implode('|', self::reorderParams($params, true));
        $data = implode('|', $params);
        $digest = base64_decode($digest);

        $ok = openssl_verify($data, $digest, $this->getPublicKeyResource());

        if ($ok !== 1) {
            throw new SignerException("Digest is not correct!");
        }

        return true;
    }

    /**
     * Obtain public key resource
     *
     * @return resource
     *
     * @throws SignerException
     */
    private function getPublicKeyResource()
    {
        if ($this->publicKeyResource) {
            return $this->publicKeyResource;
        }

        $fp = fopen($this->publicKey, "r");
        $key = fread($fp, filesize($this->publicKey));
        fclose($fp);

        if (!($this->publicKeyResource = openssl_pkey_get_public($key))) {
            throw new SignerException("'{$this->publicKey}' is not valid PEM public key.");
        }

        return $this->publicKeyResource;
    }

}
