<?php
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

        //    error_log(  $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix);
        $response = json_decode($response->getBody(), true);
        $response['url'] = $this->endPoint . $this->templateKeyPrefix . $templateKeyPostfix .'/send';

        return $response;
    }
}
