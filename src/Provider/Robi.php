<?php
/*
 *  Last Modified: 6/29/21, 12:06 AM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

class Robi extends AbstractProvider
{
    /**
     * Robi constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Api and Send Message
     * @throws GuzzleException
     * @throws RenderException
     */
    public function sendRequest()
    {
        $number = $this->senderObject->getMobile();
        $text = $this->senderObject->getMessage();
        $config = $this->senderObject->getConfig();

        $client = new Client([
            'base_uri' => 'https://bmpws.robi.com.bd/ApacheGearWS/SendTextMessage',
            'timeout' => 10.0,
            'verify' => false,
        ]);

        /**
         * SendTextMessage
         * SendTextMultiMessage
         */
        try {
            $response = $client->request('POST', '', [
                'form_params' => [
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'To' => $number,
                    'Message' => $text,
                ]
            ]);
            $body = $response->getBody();
            $smsResult = $body->getContents();
        } catch (ConnectException|ClientException|RequestException $e) {
            throw new RenderException($e->getMessage());
        }

        $data['number'] = $number;
        $data['message'] = $text;
        return $this->generateReport($smsResult, $data)->getContent();
    }

    /**
     * @throws ParameterException
     */
    public function errorException()
    {
        if (!array_key_exists('username', $this->senderObject->getConfig())) {
            throw new ParameterException('username key is absent in configuration');
        }
        if (!array_key_exists('password', $this->senderObject->getConfig())) {
            throw new ParameterException('password key is absent in configuration');
        }
        if (!array_key_exists('telcom_from', $this->senderObject->getConfig())) {
            throw new ParameterException('telcom_from key is absent in configuration');
        }

    }
}
