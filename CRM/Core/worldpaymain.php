<?php



 
require_once 'CRM/Core/Payment.php';

 
class CRM_Core_worldpaymain extends CRM_Core_Payment {

	 //const  CHARSET  = 'iso-8859-1';
	
	static protected $_params = array();
	
  /**
   * We only need one instance of this object. So we use the singleton
   * pattern and cache the instance in this variable
   *
   * @var object
   * @static
   */
  static private $_singleton = null;
 
  /**
   * mode of operation: live or test
   *
   * @var object
   * @static
   */
   protected $_mode = null;
 
  /**
   * service_key provided by the web site - worldpay 
   *
   * @var object
   * @static
   */
 
	protected $_service_key = null;
 
	/**
   * client_key provided by the web site - worldpay 
   *
   * @var object
   * @static
   */
  
	 protected $_client_key = null;	
	
	/**
	* timeout - when this time existing then end the process 
	*
	* @var object
	* @static
	*/
	 protected $_timeout = null;
	
	/**
	* disable_ssl - 
	*
	* @var object
	* @static
	*/
     protected $_disable_ssl = null;
	
    /**
	* endpoint - the API link used by worldpay 
	*
	* @var object
	* @static
	*/
	 protected $_endpoint = null;
	
    /**
	* use_external_JSON - external JSON flag
	*
	* @var object
	* @static
	*/
	 protected $_use_external_JSON = null;
	
    /**
	* order_types - type of the order palced against Worldpay
	*
	* @var object
	* @static
	*/
	 protected $_order_types = null;

 
  /**
   * Constructor
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return void
   */
  function __construct( $mode, &$paymentProcessor ) {
	
	if ( empty($paymentProcessor)) {
		CRM_Core_Error::fatal( ts( 'Could not find user name for payment processor' ) );
    }
	
	if ( $paymentProcessor['name'] == 'Worldpay' ) {
            return;
    }
	
	//select the key depending on the mode from the payment processor type into the DB
	$sql = "select user_name,password,url_site from civicrm_payment_processor Where name = '".$paymentProcessor['name']."'";
	
	if ($mode == 'test'){
		$sql .= " and is_test = 0" ;
	} else {
		$sql .= " and is_test = 1" ;
	}
		
	$dao = CRM_Core_DAO::executeQuery($sql);	
	
	if ($dao->fetch()){
		//variable declaration 
		$this->_service_key = $dao->user_name;
		$this->_client_key = $dao->password;	
		$this->_endpoint = $dao->url_site;		
	}
	
	$this->_timeout = 65;
    $this->_disable_ssl = false;
    
    $this->_use_external_JSON = false;
    $this->_order_types = array('ECOM', 'MOTO', 'RECURRING');
	
	
	$this->_mode             = $mode;
    $this->_paymentProcessor = $paymentProcessor;
    $this->_processorName    = ts('world pay payment processor');
	
	
  }
 
  /**
   * singleton function used to manage this object
   *
   * @param string $mode the mode of operation: live or test
   *
   * @return object
   * @static
   *
   */
   public static function &singleton($mode = 'test', &$paymentProcessor, &$paymentForm = null, $force = false) {
    
      $processorName = $paymentProcessor['name'];
	  if (is_null(self::$_singleton[$processorName]))
          self::$_singleton[$processorName] = new CRM_Core_worldpaymain( $mode, $paymentProcessor );
	  
	  //echo ( self::$_singleton[$processorName] );	  
	  return self::$_singleton[$processorName];
  }
 
 
 
  /**
   * This function checks to see if we have the right config values
   *
   * @return string the error message if any
   * @public
   */
  
   function checkConfig( ) {
        $error = array( );
         
   if ( empty( $this->_paymentProcessor['user_name'] ) ) {
           $error[] = ts( 'Username is not set for this payment processor' );
       }
  
  if ( empty( $this->_paymentProcessor['url_site'] ) ) {
           $error[] = ts( 'Site URL is not set for this payment processor' );
       }

  
   if ( $this->_paymentProcessor['payment_processor_type'] == 'Worldpay' ||
             $this->_paymentProcessor['payment_processor_type'] == 'Worldpay' ) {
            if ( empty( $this->_paymentProcessor['user_name'] ) ) {
                $error[] = ts( 'User Name is not set in the Administer CiviCRM &raquo; Payment Processor.' );
            }
        } 
     

        if ( ! empty( $error ) ) {
            return implode( '<p>', $error );
        } else {
            return null;
        }
    }
  
  
  
	
	 function initialize( &$args, $method ) {
  
        $args['user'     ] = $this->_paymentProcessor['user_name' ];
       // $args['pwd'      ] = $this->_paymentProcessor['password'  ];
        $args['version'  ] = 3.0;
        $args['signature'] = $this->_paymentProcessor['signature' ];
        $args['subject'  ] = $this->_paymentProcessor['subject'   ];
        $args['method'   ] = $method;
    }
 
 /**
     * This function collects all the information from a web/api form and invokes
     * the relevant payment processor specific functions to perform the transaction
     *
     * @param  array $params assoc array of input parameters for this transaction
     *
     * @return array the result in an nice formatted array (or an error object)
     * @public
     */
    function doDirectPayment( &$params ) { 
		
	
        $args = array( );
		
		$this->initialize( $args, 'DoDirectPayment' );

		//create array to generate Tokens 
		$obj['reusable'] = false;
		$obj['paymentMethod']['name'] = $params['first_name'];
		$obj['paymentMethod']['expiryMonth'] = $params['credit_card_exp_date']['M'];
		$obj['paymentMethod']['expiryYear'] =  $params['credit_card_exp_date']['Y'];
		$obj['paymentMethod']['cardNumber'] = $params['credit_card_number'];
		$obj['paymentMethod']['type'] = 'Card';
		$obj['paymentMethod']['cvc'] = $params['cvv2'];
		$obj['clientKey'] = $this->_client_key;
		
		//create token against Worldpay using API
		$token = $this->worldpay_api_using_curl_tokens($obj,'tokens');
		
		$get_token = json_decode($token);
		
		//get the token from the output 
		$vars = get_object_vars ( $get_token );
		
		$obj1['token'] 				= $vars['token'];
		
		$obj1['orderDescription'] 	= $params['description'];
		$obj1['amount'] 			= $params['amount'] * 100 ;
		$obj1['currencyCode'] 		=  'GBP';
		//$obj1['authorizeOnly'] 		= true;
		
		//create orders againt Worldpay  	
		$output =  $this->worldpay_api_using_curl_order($obj1,'orders' );
			
		$get_result = json_decode($output);
		
		$output = get_object_vars( $get_result );
		
		if ($output['paymentStatus'] != 'SUCCESS')
		{
			//Error handling 
			$result = new CRM_Core_Error();
			//passing Worldpay error to CiviCRM 
			foreach ($output as $key => $value )
			{
				if ($key != 'originalRequest')
				{
					$e1['code']=$key;
					$e1['message'] = $value;
					$result->_errors[] = $e1;
				}
			}
		}
		
		if ( is_a( $result, 'CRM_Core_Error' ) ) {
            return $result;
        }
        /* Success */
        
		return $params;
    }
	
	 function cancelSubscriptionURL( ) {
  
        if ( $this->_paymentProcessor['payment_processor_type'] == 'Worldpay' ) {
            return "{$this->_paymentProcessor['url_site']}cgi-bin/webscr?cmd=_subscr-find&alias=" .
                urlencode( $this->_paymentProcessor['user_name'] );
        } else {
            return null;
        }
    }

   
	
/*
*
*World pay api using Curl for createing Token against their system 
*
*/
function worldpay_api_using_curl_tokens($obj,$action,$method='POST'){
	
	
	   $json = json_encode($obj);
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_endpoint.$action);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		
		curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
				"Authorization: $this->_client_key",
                "Content-Type: application/json",
				"-X POST",
                "Content-Length: " . strlen($json)
            )
        );
		
		// Disabling SSL used for localhost testing
        if ($this->_disable_ssl === true) {
            if (substr($this->_service_key, 0, 1) != 'T') {
                self::onError('ssl');
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
		
        $result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$err = curl_error($ch);
		$errno = curl_errno($ch);
		
		return $result;
}


/*
*
*World pay api using Curl for creating Orders against their system 
*
*/

function worldpay_api_using_curl_order($obj,$action,$method='POST'){
	
	    $json = json_encode($obj);
		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_endpoint.$action);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
		
		curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
				"Authorization: $this->_service_key",
                "Content-Type: application/json",
				"-X POST",
                "Content-Length: " . strlen($json)
            )
        );
		
		
        // Disabling SSL used for localhost testing
        if ($this->_disable_ssl === true) {
            if (substr($this->_service_key, 0, 1) != 'T') {
                self::onError('ssl');
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
		
        $result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$err = curl_error($ch);
		$errno = curl_errno($ch);
		
		return $result;
}

  
}

