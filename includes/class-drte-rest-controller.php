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
		if ( $webhook_type === "fulfillment.created" ) {
			/*
			order.created = OrderConfirmation email
			fulfillment.created (with cancelqty) = cancel email
			fulfillment.created (with shipped quantity) = shipped email
			order.refunded = refund email
			*/
			// && $item['cancelQuantity'] > 0
			echo $item['trackingNumber'];
			if (empty($item['trackingNumber'])) {
				$webhook_type = $webhook_type.".canceled";
				$cordialBody = $this->getFulfillmentCreatedCanceledCordialBody($data);
			} else {
				$webhook_type = $webhook_type.".shipped";
				$cordialBody = $this->getFulfillmentCreatedShippedCordialBody($data);
			}
		} else if ($webhook_type === "order.created") {
		    $cordialBody = $this->getOrderCreatedCordialBody($data);
		} else if ($webhook_type === "order.refunded") {
		    $cordialBody = $this->getOrderRefundedCordialBody($data);
		}
		$template_type    = str_replace(".","_",$webhook_type);

    file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'.json', json_encode($params));

		$reponse = $this->cordial->postNotification("",$cordialBody);

		file_put_contents(plugin_dir_path( dirname( __FILE__ ) ).$webhook_type.'_reponse.json', json_encode($reponse));

		return $reponse;
	}
	private function getFulfillmentCreatedShippedCordialBody($data) {
		$orderId          = $data["id"];
		$email          	= $data["metadata"]["email"];
		$name	          	= $data["metadata"]["name"];
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'         => "shelly.lan@gmail.com",
            ],
            'extVars'           => [
                'dr_name'       => "Shelly Chan",
                'dr_address'     => $orderId
            ],
        ],
    ];
		return $cordialBody;
	}
	private function getFulfillmentCreatedCanceledCordialBody($data) {
		$orderId          = $data["id"];
		$email          	= $data["metadata"]["email"];
		$name	          	= $data["metadata"]["name"];
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'         => $email,
            ],
            'extVars'           => [
                'dr_name'       => $name,
                'dr_address'     => $orderId
            ],
        ],
    ];
		return $cordialBody;
	}
	private function getOrderRefundedCordialBody($data) {
		$orderId          = $data["id"];
		$email          	= $data["metadata"]["email"];
		$name	          	= $data["metadata"]["name"];
		$reason						= $data["reason"];
		$refundedAmount		= $data["refundedAmount"];
		
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'         => $email,
            ],
            'extVars'           => [
                'dr_name'       => $name,
                'dr_address'     => $orderId." ".$reason." ".$refundedAmount." "
            ],
        ],
    ];
		return $cordialBody;
	}
	private function getOrderCreatedCordialBody($data) {
		$orderId          = $data["id"];
    $email            = $data["email"];
    $shipto_name      = $data["shipTo"]["name"];
    $shipto_address   = $data["shipTo"]["address"]["line1"]." ".
                        $data["shipTo"]["address"]["city"]." ".
                        $data["shipTo"]["address"]["postalCode"]." ".
                        $data["shipTo"]["address"]["state"]." ".
                        $data["shipTo"]["address"]["country"]." ";
    $shipFrom_address = $data["shipFrom"]["address"]["line1"]." ".
                        $data["shipFrom"]["address"]["city"]." ".
                        $data["shipFrom"]["address"]["postalCode"]." ".
                        $data["shipFrom"]["address"]["state"]." ".
                        $data["shipFrom"]["address"]["country"]." ";
    $totalAmount      = $data["totalAmount"];
		$subtotal		      = $data["subtotal"];
		$totalTax 		    = $data["totalTax"];
		$totalShipping    = $data["totalShipping"];
    $items = array();
		if(array_key_exists('items', $data)) {
			foreach($data['items'] as $item) {
          $items[] = array (
              'sku'       			=> $item['skuId'] == 'SKU' ? $item['skuId'] : null,
              'quantity'  			=> !empty($item['quantity']) ? $item['quantity'] : null,
              'amount'  				=> !empty($item['amount']) ? $item['amount'] : null,
              'descriptions'  	=> (!empty($item['metadata']) && !empty($item['metadata']['lineCustomAttribute'])) ? $item['metadata']['lineCustomAttribute'] : null,
          );
      }
		}
		$cordialBody =  [
        'identifyBy'    => 'email',
        'to'            => [
            'contact'       => [
                'email'         => $email, //"shelly.lan@gmail.com",
            ],
            'extVars'           => [
                'dr_name'       => $shipto_name, //"Shelly Chan",
                'dr_address'     => $shipto_address
            ],
        ],
    ];
		return $cordialBody;
	}
}
 ?>
