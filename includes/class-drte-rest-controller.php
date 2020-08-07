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
    register_rest_route($this->namespace, '/' . $this->rest_base , array(
        'methods' => 'POST',
        'callback'  => array( $this, 'cordial_confirmation' )//'my_awesome_func'//
    ));
		//print_r( $this->get_collection_params() );
	}

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	 /*
	public function my_awesome_func( $request ) {
    $cordial = new CI_Cordial();
    $reponse = $cordial->post_DR_API_TEST($data);
    return $reponse;
	}
*/
  /**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function cordial_confirmation( $request ) {
		//get parameters from request
		$params = $request->get_params();
    $webhook_type     = $params["type"];
    $data             = $params["data"]["object"];
		$cordialBody			= [];
		$cordialEmailKey	= "shelly_dr_api_test";
		if ( $webhook_type === "fulfillment.created" ) {
			/*
			order.created = OrderConfirmation email
			fulfillment.created (with cancelqty) = cancel email
			fulfillment.created (with shipped quantity) = shipped email
			order.refunded = refund email
			*/
			// && $item['cancelQuantity'] > 0
			//echo $item['trackingNumber'];
			//return $data['trackingNumber'];
			if (empty($data['trackingNumber']) || null === $data['trackingNumber']) {
				$webhook_type = $webhook_type.".canceled";
				$cordialBody = $this->getFulfillmentCreatedCanceledCordialBody($data);
			} else {
				$webhook_type = $webhook_type.".shipped";
				$cordialBody = $this->getFulfillmentCreatedShippedCordialBody($data);
				$cordialEmailKey = "dr-webhook-fulfillment-created-shipped";
			}
		} else if ($webhook_type === "order.created") {
		    $cordialBody = $this->getOrderCreatedCordialBody($data);
				$cordialEmailKey = "dr-webhook-order-confirmation";
		} else if ($webhook_type === "order.refunded") {
		    $cordialBody = $this->getOrderRefundedCordialBody($data);
		}
		$template_type    = str_replace(".","_",$webhook_type);

    file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'.json', json_encode($params));

		$reponse = $this->cordial->postNotification($cordialEmailKey,$cordialBody);

		file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'_reponse.json', json_encode($reponse));

		return $reponse;
	}
	private function getFulfillmentCreatedShippedCordialBody($data) {
		$orderId          = $data["orderId"];
		$email    			  = (!empty($data['metadata']) && !empty($data['metadata']['email'])) ? $data['metadata']['email'] : null;
		$name					    = (!empty($data['metadata']) && !empty($data['metadata']['name'])) ? $data['metadata']['name'] : null;
		$address			    = (!empty($data['metadata']) && !empty($data['metadata']['address'])) ? $data['metadata']['address'] : null;
		$items = array();
		if(array_key_exists('items', $data)) {
			foreach($data['items'] as $item) {
          $items[] = array (
              'sku'       			=> $item['skuId'],
              'quantity'  			=> !empty($item['quantity']) ? $item['quantity'] : null,
              'name'  					=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
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
		return $cordialBody;
	}
	private function getFulfillmentCreatedCanceledCordialBody($data) {
		$orderId          = $data["orderId"];
		$email    			  = (!empty($data['metadata']) && !empty($data['metadata']['email'])) ? $data['metadata']['email'] : null;
		$name					    = (!empty($data['metadata']) && !empty($data['metadata']['name'])) ? $data['metadata']['name'] : null;
		$items = array();
		if(array_key_exists('items', $data)) {
			foreach($data['items'] as $item) {
          $items[] = array (
              'sku'       			=> $item['skuId'],
              'quantity'  => !empty($item['cancelQuantity']) ? $item['cancelQuantity'] : null,
              'name'  					=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
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
								'dr_items'     	   	 => $items
            ],
        ],
    ];
		return $cordialBody;
	}
	private function getOrderRefundedCordialBody($data) {
		$orderId           = $data["id"];
    $email             = $data["email"];
    $name 		         = $data["shipTo"]["name"];
    $items = array();
		if(array_key_exists('items', $data)) {
			foreach($data['items'] as $item) {
          $items[] = array (
              'sku'       			=> $item['skuId'],
              'quantity'  			=> !empty($item['quantity']) ? $item['quantity'] : null,
              'name'  					=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
          );
      }
		}
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'        			=> $email,
            ],
            'extVars'           => [
                'dr_orderId'     		=> $orderId,
                'dr_name'       		=> $data["shipTo"]["name"],
								'dr_items'     	  	=> $items
            ],
        ],
    ];
		return $cordialBody;
	}
	private function getOrderCreatedCordialBody($data) {
		$orderId           = $data["id"];
    $email             = $data["email"];
    $name 		         = $data["shipTo"]["name"];
    $shipTo_email      = (!empty($data["shipTo"]['metadata']) && !empty($data["shipTo"]['metadata']['email'])) ? $data["shipTo"]['metadata']['email'] : null;
		$shipFrom_name     = (!empty($data["shipFrom"]['metadata']) && !empty($data["shipFrom"]['metadata']['name'])) ? $data["shipFrom"]['metadata']['name'] : null;
    $totalAmount       = $data["totalAmount"];
		$subtotal		       = $data["subtotal"];
		$totalTax 		     = $data["totalTax"];
		$totalShipping     = $data["totalShipping"];
    $items = array();
		if(array_key_exists('items', $data)) {
			foreach($data['items'] as $item) {
          $items[] = array (
              'sku'       			=> $item['skuId'],
              'quantity'  			=> !empty($item['quantity']) ? $item['quantity'] : null,
              'amount'  				=> !empty($item['amount']) ? $item['amount'] : null,
              'name'  					=> (!empty($item['metadata']) && !empty($item['metadata']['name'])) ? $item['metadata']['name'] : null,
          );
      }
		}
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'        			=> $email,
            ],
            'extVars'           => [
                'dr_orderId'     		=> $orderId,
                'dr_name'       		=> $name,
                'dr_shipTo'   			=> [
		                'email'     		=> $shipTo_email,
										'address'   		=> $data["shipTo"]["address"],
										'name'      		=> $data["shipTo"]["name"],
			           ],
							 	 'dr_shipFrom'   		=> [
										'address'   		=> $data["shipFrom"]["address"],
										'name'      		=> $shipFrom_name,
			           ],
								 'dr_items'     	  => $items,
								 'dr_totalAmount'  	=> $data["totalAmount"],
								 'dr_subtotal'     	=> $data["subtotal"],
								 'dr_totalTax'     	=> $data["totalTax"],
								 'dr_totalShipping'	=> $data["totalShipping"]
            ],
        ],
    ];
		return $cordialBody;
	}
}
 ?>
