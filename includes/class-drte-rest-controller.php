	<?php
class DRTE_REST_Controller extends WP_REST_Controller {

	protected $cordial;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {
    $this->cordial 	 = new CI_Cordial();
		$this->namespace = get_option( 'drte_endpoint_namespace' );
		$this->rest_base = get_option( 'drte_endpoint_restbase' );
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route($this->namespace, "/".$this->rest_base , array(
				'methods' 	=> 'POST',
				'callback'  => array( $this, 'cordial_confirmation' )
		));
	}
  /**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function cordial_confirmation( $request ) {
		$response					= [];
		$params 					= $request->get_params();
    $webhook_type			= $params["type"];
    $orderId          = (isset($params["data"]["object"]["id"]) ? $params["data"]["object"]["id"] : $params["data"]["object"]["orderId"]);
    $data             = $params["data"]["object"];

		try {
			switch ($webhook_type) {
				case "fulfillment.created":
					$reponse = $this->postFulfillmentCreatedCordialNotification( $data );
					break;
				case "order.created":
					$reponse = $this->postOrderCreatedCordialNotification(  $data );
					break;
				case "order.refunded":
					$reponse = $this->postOrderRefundedCordialNotification( $data );
					break;
				default:
					$reponse = ["webhook not registered"];
			}
		} catch (Exception $exception) {//
				$responseBody = (string)$exception->getResponse()->getBody();
				$log = [
						'webhook'    => $webhook_type,
						'orderId'    => $orderId,
		        'response'  => $responseBody
					];
				echo json_encode($log);
 				error_log(json_encode($log));
		}
		return $reponse;
	}
	private function postFulfillmentCreatedCordialNotification ( $data ) {

		$channel	= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : "amazon";
		$orderId	= $data["orderId"];
		$email		= (!empty($data['metadata']) && !empty($data['metadata']['email'])) ? $data['metadata']['email'] : null;
		$name			= (!empty($data['metadata']) && !empty($data['metadata']['name'])) ? $data['metadata']['name'] : null;
		$address	= (!empty($data['metadata']) && !empty($data['metadata']['address'])) ? $data['metadata']['address'] : null;

		$isCanceled = false;
		$items	= array();
		if(array_key_exists('items', $data)) {
				foreach($data['items'] as $item) {
						$isCanceled = ($isCanceled === false && !empty($item['cancelQuantity'])) ? true : false;
	          $items[] = array (
	              'sku'							=> $item['skuId'],
	              'quantity'  			=> !empty($item['quantity']) ? $item['quantity'] : null,
	              'cancelQuantity'  => !empty($item['cancelQuantity']) ? $item['cancelQuantity'] : null,
	              'name'  					=> (!empty($data['metadata']) && !empty($data['metadata'][$item['skuId']])) ? $data['metadata'][$item['skuId']]:$item['skuId'],
	          );
	      }
		}

		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'         => $email,
            ],
            'extVars'            		 => [
                'dr_orderId'     		 => $orderId,
                'dr_name'       		 => $name,
                'dr_address'     		 => $address,
		            'dr_trackingCompany' => $data["trackingCompany"],
				        'dr_trackingNumber'  => $data["trackingNumber"],
				        'dr_trackingUrl'     => $data["trackingUrl"],
								'dr_items'     	   	 => $items
            ],
        ],
    ];

		$postId 		= get_option( $channel );
		if (empty($postId)) $postId 		= get_option( "amazon" );

		$apiKey 		= get_field( "cordial_api_key", $postId );
		$messageKey = get_field( "cordial_email_message_keys", $postId )[($isCanceled) ? "order_cancelled" : "order_shipped"];

		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}

	private function postOrderRefundedCordialNotification(  $data ) {

		$channel				= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : "amazon";
		$orderId				= $data["id"];
    $email					= $data["email"];
    $name						= $data["shipTo"]["name"];
    $refoundAmount	= $data["refundedAmount"];

    $items	= array();
		if(array_key_exists('items', $data)) {
				foreach($data['items'] as $item) {
	          $items[] = array (
	              'sku'					=> $item['skuId'],
	              'quantity'		=> !empty($item['quantity']) ? $item['quantity'] : null,
	              'name'				=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
	          );
	      }
		}
		$cordialBody =  [
        'identifyBy'		=> 'email',
        'to'	=> [
            'contact'		=> [
                'email'     => $email,
            ],
            'extVars'		=> [
                'dr_orderId'				=> $orderId,
                'dr_name'   				=> $data["shipTo"]["name"],
                'dr_refundedAmount' => floatval($refoundAmount),
								'dr_items'  				=> $items
            ],
        ],
    ];

		$postId 		= get_option( $channel );
		if (empty($postId)) $postId 		= get_option( "amazon" );
		$apiKey 		= get_field( "cordial_api_key", $postId );
		$messageKey = get_field( "cordial_email_message_keys", $postId )["order_refunded"];

		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}
	private function postOrderCreatedCordialNotification(  $data ) {

		$channel				= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : "amazon";
		$orderId				= $data["id"];
    $email					= $data["email"];
	  $name						= $data["shipTo"]["name"];
    $totalAmount		= $data["totalAmount"];
		$subtotal				= $data["subtotal"];
		$totalTax				= $data["totalTax"];
		$totalShipping	= $data["totalShipping"];

    $items	= array();
		if(array_key_exists('items', $data)) {
				foreach($data['items'] as $item) {
	          $items[] = array (
	              'sku'					=> $item['skuId'],
	              'quantity'		=> !empty($item['quantity']) ? $item['quantity'] : 0,
	              'amount'			=> (!empty($item['amount']) && !empty($item['quantity']))  ? $item['amount']/$item['quantity'] : 0.00,
								'totalAmount'	=> !empty($item['amount']) ? $item['amount'] : 0.00,
	              'name'				=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
	          );
	      }
		}

		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'	=> [
            'contact'		=> [
                'email'        			=> $email,
            ],
            'extVars'		=> [
                'dr_orderId'				=> $orderId,
                'dr_name'						=> $name,
                'dr_shipTo'			=> [
		                //'email'     		=> $shipTo_email,
										'address'				=> $data["shipTo"]["address"],
										'name'					=> $data["shipTo"]["name"],
			           ],
								 'dr_items'					=> $items,
								 'dr_totalAmount'		=> $data["totalAmount"],
								 'dr_subtotal'			=> $data["subtotal"],
								 'dr_totalTax'			=> $data["totalTax"],
								 'dr_totalShipping'	=> $data["totalShipping"]
            ],
        ],
    ];

		$postId 		= get_option( $channel );

		if (empty($postId)) $postId 		= get_option( "amazon" );

		$apiKey 		= get_field( "cordial_api_key", $postId );

		$messageKey = get_field( "cordial_email_message_keys", $postId )["order_confirmation"];

		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}
}
 ?>
