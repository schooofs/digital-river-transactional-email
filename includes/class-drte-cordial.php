<?php
class CI_Cordial
{

    protected $httpClient;

    protected $endPoint = 'https://api.cordial.io/v2/automationtemplates/';

    public function __construct() {

        /*$this->apiKey = get_option("drte_cordial_api_key");

        $this->templateKeyPrefix = get_option( 'drte_cordial_email_template_key_prefix' );

        if (is_null($this->httpClient)) {
            $this->httpClient = new \GuzzleHttp\Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey.':'),
                ]
            ]);
        }*/
    }
    /*public function postNotification( $templateKeyPostfix , $data ) {

        $response = $this->httpClient->request('POST',
          $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send' ,  [
            'form_params' => $data,
        ] );

        //    error_log(  $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix);
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send';

        return $response;
    }*/
    public function postNotification( $messageKey , $data, $apiKey ) {
        $this->httpClient = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($apiKey.':'),
            ]
        ]);
        $response = $this->httpClient->request('POST',
          $this->endPoint . $messageKey .'/send' ,  [
            'form_params' => $data,
        ] );

        //    error_log(  $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix);
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint . $messageKey .'/send';

        return $response;
    }
}
