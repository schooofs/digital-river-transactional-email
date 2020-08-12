<?php
//if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CI_Cordial
{

    protected $httpClient;

    protected $apiKey = "";//'''5f2281b217d4e556434de5e2-K0xb8quGmR1iga8Erkbw1U4t7eqy9L0s';

    protected $templateKeyPrefix = "";//'''5f2281b217d4e556434de5e2-K0xb8quGmR1iga8Erkbw1U4t7eqy9L0s';

    protected $endPoint = 'https://api.cordial.io/v2/automationtemplates/';

    public function __construct() {

        $this->apiKey = get_option("drte_cordial_api_key");

        $this->templateKeyPrefix = get_option( 'drte_cordial_email_template_key_prefix' );

        if (is_null($this->httpClient)) {
            $this->httpClient = new \GuzzleHttp\Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey.':'),
                ]
            ]);
        }
    }
    public function postNotification( $templateKeyPostfix , $data ) {

        $response = $this->httpClient->request('POST',
        $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send' ,  [
            'form_params' => $data,
        ] );
        /*
        $response = $this->httpClient->request('POST',
        $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send' ,  [
            'form_params' => $data,
        ] );
        */
        //$this->endPoint . $this->templateKeyPrefix .'dr-webhook-order-confirmation/send' ,  [
        //    'form_params' => $data,
        //] );

            error_log(  $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix);
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send';
        return $response;
    }
/*
    public function post_DR_API_TEST2($cordialBody) {
        $response = $this->httpClient->request('POST', $this->endPoint . $this->templateKey. '/send' ,  [
            'form_params' => $cordialBody,
        ] );
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint;

        return $response;
    }
    public function post_DR_API_TEST($data) {
        $cordialBody =  [
                'identifyBy'    => 'email',
                'to'            => [
                    'contact'       => [
                        'email'         => "shelly.lan@gmail.com",
                    ],
                    'extVars'           => [
                        'dr_name'       => "Shelly Chan",
                        'dr_address'     => "somewhere in Tokyo"
                    ],
                ],
            ];
        $response = $this->httpClient->request('POST', $this->endPoint . $this->templateKey. '/send' ,  [
            'form_params' => $cordialBody,
        ] );
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint;

        return $response;
    }
    */

    // public function postPasswordReset($data) {
    //     $response = $this->httpClient->request('POST', $this->endPoint . 'test/send' ,  [
    //         'form_params' => $data,
    //     ] );
    //     $response = json_decode($response->getBody(), true);
    //     $response['url'] = $this->endPoint;
    //     return $response;
    // }
}
