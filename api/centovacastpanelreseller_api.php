<?php
/**
 * CentovaCastPanel API.
 *
 * @copyright Gigaion LLC
 * @license MIT License
 * @see license.md (MIT License)
 */
class CentovacastpanelresellerApi
{
    private $adminusername;
    private $adminapikey;
	private $ipaddress;
	private $usessl;
	private $useproxy;
	
    /**
     * Initializes the class.
     *
     * @param mixed $password
     * @param mixed $ipaddress
     * @param mixed $usessl
     */
    public function __construct($adminapikey, $ipaddress, $usessl, $adminusername, $useproxy)
    {
		$this->adminapikey = $adminapikey;
        $this->ipaddress = $ipaddress;
        $this->usessl = $usessl;
		$this->adminusername = $adminusername;
		$this->useproxy = $useproxy;
    }

    /**
     * Return a string containing the last error for the current session.
     *
     * @param string $command the CentovaCastPanel API command to call
     * @param array $params the parameters to include in the API request
     * @return mixed string|Array the curl error message or an array representing the API response
     */
    private function apiRequest($command, array $params)
    {
		$curl = curl_init();
		
		$query = array(
			'xm' => $command,
			'f' => 'json'
		);
		$query = http_build_query($query);
		
		if(!isset($params['password'])) {
			$params['password'] = $this->adminapikey;
		}
		$params = $this->buildQuery($params);
		
		//$params['ip'] = $this->ipaddress;
		//$params['owner'] = $this->adminusername;
		//$params['key'] = $this->adminapikey;
		//$params = http_build_query($params);
		
		//Use http. As hostname is not being used for API request. (IP does not support https on centovacastpanel. Only hostname)
		$url = 'http://';
		$port = '2199';
		
		$url .= $this->ipaddress . ':' . $port . '/api.php';
		
		$proxyEnabled = false;
		if($this->useproxy == 'true' && $proxyEnabled) {
			$urlproxy = 'https://example.proxy.lan/proxy.php';
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Proxy-Auth: CENSORED-AUTH-KEY',
				'Proxy-Target-URL: '.$url,
				//'Proxy-Debug: 1'
			));
			curl_setopt($curl, CURLOPT_URL, $urlproxy);
		}
		else {
			curl_setopt($curl, CURLOPT_URL, $url);
		}
		
		
		if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
		   curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		}
		//curl_setopt($curl, CURLAUTH_BASIC, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $query.'&'.$params);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$curl_output = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);
		
		//var_dump($query);
		//var_dump($params);
		//var_dump($curl_output);
		//die();
		
		if (empty($error)) {
			return $curl_output;
		}
		
		return $error;
    }
	
	function buildQuery($dataQuery=array()) {
		if(is_array($dataQuery)) {
			$buildCentovaQuery = '';
			foreach($dataQuery as $key => $value) {
				$buildCentovaQuery .= '&a['.$key.']='.($value);
			}
			return $buildCentovaQuery;
		}
		else {
			return false;
		}
	}


    /**
     * Get packages from root/reseller account.
     *
     * @param string $username the account's username to suspend
     * @return array an array representing the status of the operation
     */
    public function getPackagesList()
    {
		return;
		/*
        $status = false;
		
        $response = $this->apiRequest('reseller_packs', array());
		$json = json_decode($response, true);
		$return = $response;
		
        if ($return != '') {
            $status = true;
        }

        return [
            'status' => $status,
            'response' => $return,
        ];
		*/
    }

    /**
     * Creates a user account.
     *
     * @param array $params an array of parameters
     * @return array an array representing the status of the operation
     */
    public function createRadio(array $params)
    {
        $status = false;
        
		/*
		$parameters = [];
		$parameters['rad_username'] = $params['radiousername'];
        if (isset($params['radiopassword'])) {
            $parameters['panel_pass'] = $params['radiopassword'];
        }
        if (isset($params['radioemail'])) {
            $parameters['client_email'] = $params['radioemail'];
        }
        if (isset($params['package'])) {
            $parameters['package'] = $params['package'];
        }
		*/
		
		$parameters = array(
			'template' => $params['package'],
			
			//Tab [Basic Configuration]
			'username' => $params['radiousername'],
			'adminpassword' => $params['radiopassword'],
			'email' => $params['radioemail'],
			'organization' => 'Reseller Username: '.$params['radiousername'],
		);

		if (isset($params['maxbitrate'])) {
			$parameters['maxbitrate'] = $params['maxbitrate'];
		}

		if (isset($params['resellerusers'])) {
			$parameters['resellerusers'] = $params['resellerusers'];
		}

		if (isset($params['maxclients'])) {
			$parameters['maxclients'] = $params['maxclients'];
		}

		if (isset($params['transferlimit'])) {
			$parameters['transferlimit'] = $params['transferlimit'];
		}

		if (isset($params['resellerbandwidth'])) {
			$parameters['resellerbandwidth'] = $params['resellerbandwidth'];
		}

		if (isset($params['diskquota'])) {
			$parameters['diskquota'] = $params['diskquota'];
		}
		

		//var_dump($parameters);
		//die();
		
        $response = $this->apiRequest('system.provision', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
		if($return == 'Account created') {
			$status = true;
			
			/*
			//Do seperate query to configurate the radio for Disk Quota. As it defaults to the templates value
			if (isset($params['diskquota'])) {
				$configoptions = array(
					'diskquota' => $params['diskquota']
				);
				$this->changePackage($params['radiousername'], $configoptions);
			}
			*/
		}
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }

    /**
     * Change a user account password.
     *
     * @param string $radiousername the account's username to change password
     * @return array an array representing the status of the operation
     */
    public function changePassword($radiousername, $newradiopassword)
    {
        $status = false;
		
		$parameters = array(
			'username' => ($radiousername),
			'adminpassword' => ($newradiopassword),
			
			//Admin radio password
			'password' => 'admin|'.$this->adminapikey,
		);
		
        $response = $this->apiRequest('server.reconfigure', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if ($return == 'Account updated') {
            $status = true;
        }
		elseif($return == 'Invalid username or password') {
			//Password contains symbols which are not allowed!
		}
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }

    /**
     * Suspends a user account.
     *
     * @param string $radiousername the account's username to suspend
     * @return array an array representing the status of the operation
     */
    public function suspendRadio($radiousername)
    {
        $status = false;
		
		$parameters = array(
			'username' => ($radiousername),
			'status' => 'disabled',
		);
		
        $response = $this->apiRequest('system.setstatus', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if ($return == 'Account status updated' || $return == 'Account is already in specified state') {
            $status = true;
        }
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }

    /**
     * Un-suspends a user account.
     *
     * @param string $radiousername the account's username to un-suspend
     * @return array an array representing the status of the operation
     */
    public function unSuspendRadio($radiousername)
    {
        $status = false;
		
		$parameters = array(
			'username' => ($radiousername),
			'status' => 'enabled',
		);
		
        $response = $this->apiRequest('system.setstatus', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if ($return == 'Account status updated' || $return == 'Account is already in specified state') {
            $status = true;
        }
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }

    /**
     * Terminates a user account.
     *
     * @param string $radiousername the account's username to terminate
     * @return array an array representing the status of the operation
     */
    public function terminateRadio($radiousername)
    {
        $status = false;
		
		$parameters = array(
			'username' => ($radiousername),
			'clientaction' => 'delete',
		);
		
        $response = $this->apiRequest('system.terminate', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if ($return == 'Account removed') {
            $status = true;
        }
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }
	
	/**
     * Validate an API connection a user account.
     *
     * @return array an array representing the status of the operation
     */
    public function validateConnectionAPI()
    {
        $status = false;
		
		$parameters = array(
			'username' => ('_#$%^3456#$%^#456[{g'),
			'clientaction' => 'delete',
		);
		
        $response = $this->apiRequest('system.terminate', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if (stristr($return, 'Unable to authorize terminate call: Unable to access account for')) {
            $status = true;
        }
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }

    /**
     * Change a user account package.
     *
     * @param string $radiousername the account's username
     * @param string $package the account's package to change
     * @return array an array representing the status of the operation
     */
    public function changePackage($radiousername, $configoptions)
    {
        $status = false;
		
		$parameters = array(
			'username' => ($radiousername),
			
			//Admin radio password
			'password' => 'admin|'.$this->adminapikey,
		);
		
		//[Basic Configuration] Tab
		if($configoptions['email'] != '') {
			$parameters['email'] = $email;
		}
		if($configoptions['organization'] != '') {
			$parameters['organization'] = $email;
		}
		
		//EVENTUALLY NEED TO CODE it to detect 0 as unlimited. Currently though using templates for that
		
		if (isset($configoptions['maxbitrate'])) {
			$parameters['maxbitrate'] = $configoptions['maxbitrate'];
		}

		if (isset($configoptions['resellerusers'])) {
			$parameters['resellerusers'] = $configoptions['resellerusers'];
		}

		if (isset($configoptions['maxclients'])) {
			$parameters['maxclients'] = $configoptions['maxclients'];
		}

		if (isset($configoptions['transferlimit'])) {
			$parameters['transferlimit'] = $configoptions['transferlimit'];
		}

		if (isset($configoptions['resellerbandwidth'])) {
			$parameters['resellerbandwidth'] = $configoptions['resellerbandwidth'];
		}

		if (isset($configoptions['diskquota'])) {
			$parameters['diskquota'] = $configoptions['diskquota'];
		}
		
        $response = $this->apiRequest('server.reconfigure', $parameters);
		$json = json_decode($response);
		$return = $json->response->message;
		
        if ($return == 'Account updated') {
            $status = true;
        }
		
        return [
            'status' => $status,
            'response' => $return,
        ];
    }
	
}


?>
