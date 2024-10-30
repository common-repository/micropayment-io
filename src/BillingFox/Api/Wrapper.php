<?php

class BillingFox_Api_Wrapper
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $debug;

    public function __construct($token, $debug = false, $test_endpoint = false)
    {
        $this->token = $token;
        $this->url = 'https://'.($test_endpoint?'test':'live').'.billingfox.com/api';
        $this->debug = $debug;
    }

    /**
     * check if there is a connection
     *
     * @return bool
     */
    public function ping()
    {
        try {
            $result = $this->getRequest('/ping');

            return $result['status'] == 'success';
        } catch (BillingFox_Api_Exception $e) {
            return false;
        }
    }

    /**
     * @param string $id
     * @param string $email
     *
     * @return boolean
     */
    public function setIdentity($id, $email)
    {
        try {
            $result = $this->postRequest('/identify', [
                'user' => $id,
                'email' => $email,
            ]);

            return $result['status'] == 'success';
        } catch (BillingFox_Api_Exception $e) {
            return false;
        }
    }

    /**
     * @param string $id
     *
     * @return boolean
     */
    public function hasIdentity($id)
    {
        try {
            $this->getRequest('/identify?user=' . $id);

            return true;
        } catch (BillingFox_Api_Exception $e) {
            return false;
        }
    }

    /**
     * @param string $id
     * @return array
     *
     * @throws BillingFox_Api_Exception
     */
    public function getIdentity($id)
    {
        $result = $this->getRequest('/identify?user=' . $id);

        return $result['user'];
    }

    /**
     * @param string $id
     * @param float $amount
     * @param string $description
     *
     * @return array
     *
     * @throws BillingFox_Api_Exception
     * @throws BillingFox_Api_InsufficientCoins
     */
    public function spend($id, $amount, $description = null)
    {
        return $this->postRequest('/spend', array_filter([
            'user' => $id,
            'amount' => (float)$amount,
            'description' => $description,
        ]));
    }

    /**
     * @param string $id
     * @param float $amount
     *
     * @return array
     *
     * @throws BillingFox_Api_Exception
     */
    public function recharge($id, $amount)
    {
        return $this->postRequest('/recharge', array_filter([
            'user' => $id,
            'amount' => (float)$amount,
        ]));
    }

    /**
     * @param string $id
     * @param DateTime $gte
     * @param DateTime $lte
     *
     * @return array
     *
     * @throws BillingFox_Api_Exception
     */
    public function listSpend($id, DateTime $gte = null, DateTime $lte = null)
    {
        $params = build_query(array_filter([
            'user' => $id,
            'gte' => $gte?$gte->format('Y-m-d'):null,
            'lte' => $lte?$lte->format('Y-m-d'):null,
        ]));
        $result = $this->getRequest('/spend?'.$params);

        return $result['spends'];
    }


    /**
     * @param string $path
     *
     * @return mixed
     *
     * @throws BillingFox_Api_Exception
     */
    private function getRequest($path)
    {
        $this->log('GET '.$this->url.$path);

        $result = wp_remote_get(
            $this->url.$path,
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->token,
                ],
            ]
        );

        return $this->prepareResult($result);
    }


    /**
     * @param string $path
     * @param array $payload
     *
     * @return mixed
     *
     * @throws BillingFox_Api_Exception
     * @throws BillingFox_Api_InsufficientCoins
     */
    private function postRequest($path, $payload = [])
    {
        $this->log('POST '.$this->url.$path.' '.json_encode($payload));

        $result = wp_remote_post(
            $this->url.$path,
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->token,
                    'Content-Type' => 'application/json; charset=UTF-8'
                ],
                'body' => json_encode($payload),
            ]
        );

        return $this->prepareResult($result);
    }

    /**
     * @param mixed $result
     *
     * @return array
     *
     * @throws BillingFox_Api_Exception
     * @throws BillingFox_Api_InsufficientCoins
     */
    private function prepareResult($result)
    {
        if (is_wp_error($result)) {
            $this->log('WP-ERROR: (%s) %s', [$result->get_error_message(), $result->get_error_code()]);
            $e = new BillingFox_Api_Exception($result->get_error_message());

            throw $e;
        }

        $body = @json_decode($result['body'], true);

        if (empty($body)) {
            $this->log('ERROR: failed to decode response');

            $e = new BillingFox_Api_Exception(
                'failed to decode response'
            );

            $e->setResponse($result);

            throw $e;
        }

        if ($result['response']['code'] > 299) {
            $this->log('ERROR: (%s) %s', [$result['response']['code'], $body['message']]);

            if ($result['response']['code'] == 402) {
                $e = new BillingFox_Api_InsufficientCoins(
                    $body['message'],
                    $result['response']['code']
                );

                $e->setInvoiceLink($body['link']);
            } else {
                $e = new BillingFox_Api_Exception(
                    $body['message'],
                    $result['response']['code']
                );
            }

            $e->setResponse($result);

            throw $e;
        }

        return $body;
    }

    /**
     * @param string $message
     * @param array $args
     */
    private function log($message, $args = [])
    {
        if (!$this->debug) {
            return;
        }

        array_unshift($args, "<p>$message</p>\n");

        echo call_user_func_array('sprintf', $args);
    }
}