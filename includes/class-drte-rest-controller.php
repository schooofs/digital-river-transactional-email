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
		$template_type    = str_replace(".","-",$webhook_type);
    $data             = $params["data"]["object"];
/*
		if ( $webhook_type === "fulfillment.created" ) {
				$reponse = $this->postFulfillmentCreatedCordialNotification( $template_type, $data);
		} else if ($webhook_type === "order.created") {
				$reponse = $this->postOrderCreatedCordialNotification( $template_type, $data );
		} else if ($webhook_type === "order.refunded") {
				$reponse = $this->postOrderRefundedCordialNotification( $template_type, $data);
		}*/

		switch ($webhook_type) {
		  case "fulfillment.created":
		    $reponse = $this->postFulfillmentCreatedCordialNotification( $template_type, $data);
		    break;
		  case "order.created":
		    $reponse = $this->postOrderCreatedCordialNotification( $template_type, $data );
		    break;
		  case "order.refunded":
		    $reponse = $this->postOrderRefundedCordialNotification( $template_type, $data );
		    break;
		  default:
				$reponse = ["webhook not registered"];
		}

    //file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'.json', json_encode($params));

		//$reponse = $this->cordial->postNotification($template_type,$cordialBody);

		//file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'_reponse.json', json_encode($reponse));

		return $reponse;
	}
	private function postFulfillmentCreatedCordialNotification ( $template_type, $data ) {

		$channel	= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : null;
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
		$apiKey 		= get_field( "cordial_api_key", $postId );
		$messageKey = get_field( "cordial_email_message_keys", $postId )[($isCanceled) ? "order_cancelled" : "order_shipped"];

		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}

	private function postOrderRefundedCordialNotification( $template_type, $data ) {

		$channel	= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : null;
		$orderId	= $data["id"];
    $email		= $data["email"];
    $name			= $data["shipTo"]["name"];

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
                'dr_orderId'=> $orderId,
                'dr_name'   => $data["shipTo"]["name"],
								'dr_items'  => $items
            ],
        ],
    ];

		$postId 		= get_option( $channel );
		$apiKey 		= get_field( "cordial_api_key", $postId );
		$messageKey = get_field( "cordial_email_message_keys", $postId )["order_refunded"];

		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}
	private function postOrderCreatedCordialNotification( $template_type, $data ) {

		$channel				= (!empty($data['metadata']) && !empty($data['metadata']['channel'])) ? $data['metadata']['channel'] : null;
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
	              'amount'			=> !empty($item['amount']) ? $item['amount'] : 0.00,
								'totalAmount'	=> (!empty($item['amount']) && !empty($item['quantity']))  ? $item['amount']*$item['quantity'] : 0.00,
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

		$apiKey 		= get_field( "cordial_api_key", $postId );

		$messageKey = get_field( "cordial_email_message_keys", $postId )["order_confirmation"];
//return $messageKey;
		$reponse 		= $this->cordial->postNotification( $messageKey, $cordialBody, $apiKey);

		return $reponse;
	}
}
 ?>
