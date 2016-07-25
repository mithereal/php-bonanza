<?php
namespace Bonanza;

class Boneapitit {

    protected $dev_name = null;
    protected $cert_name = null;
    public $token = null;
    public $authenticationurl = null;
    public $authorized = null;
    

    public function __construct($dev_name = null, $cert_name = null, $token = null) {

        $this->cert_name = $cert_name;
        $this->dev_name = $dev_name;
        $this->token = $token;
        
        if (empty($token)) {
            $token = $this->fetchtoken();
            $this->token = $token['authToken'];
            $this->authenticationurl = $token['authenticationURL'];
            $this->authorized = false;
        }
        
    }

    public function secureapicall($api_call_and_args) {
        $url = "https://api.bonanza.com/api_requests/secure_request";
        $headers = array("X-BONANZLE-API-DEV-NAME: " . $this->dev_name, "X-BONANZLE-API-CERT-NAME: " . $this->cert_name);
        $resp = $this->sendhttprequest($url, $headers, $api_call_and_args);
        return $resp;
    }

    public function getOrders() {
        
        $args["requesterCredentials"] = array('bonanzleAuthToken' => $this->token);
        $api_call_and_args = $this->buildrequest('getOrders', $args);
        return $this->secureapicall($api_call_and_args);
    }

    public function getProfile($userid) {
        $args = array("userId" => $userid);
        $api_call_and_args = $this->buildrequest('getUserProfile', $args);
        return $this->standardapicall($api_call_and_args);
    }

    public function standardapicall($api_call_and_args = "") {

        $url = "http://api.bonanza.com/api_requests/standard_request";
        $headers = array("X-BONANZLE-API-DEV-NAME: " . $this->dev_name);
        $resp = $this->sendhttprequest($url, $headers, $api_call_and_args);
        return $resp;
    }

    private function sendhttprequest($url, $headers, $post_fields) {
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $url);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);  //stop CURL from verifying the peer's certificate
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers); //set the headers using the array of headers
        curl_setopt($connection, CURLOPT_POST, 1);  //set method as POST
        curl_setopt($connection, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);  //set it to return the transfer as a string from curl_exec
        $response = curl_exec($connection);
        curl_close($connection);
        return $response;
    }

    public function buildrequest($api_call_name, $assoc_array) {
        $request_name = $api_call_name . "Request";
        $json = json_encode($assoc_array, JSON_HEX_AMP);
        $request = $request_name . "=" . $json;
        return $request;
    }

    public function fetchtoken() {
        $request_name = "fetchTokenRequest";
        $url = "https://api.bonanza.com/api_requests/secure_request";
        $headers = array("X-BONANZLE-API-DEV-NAME: " . $this->dev_name, "X-BONANZLE-API-CERT-NAME: " . $this->cert_name);
        $connection = curl_init($url);

        $curl_options = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => "fetchTokenRequest",
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1
        );  # data will be returned as a string

        curl_setopt_array($connection, $curl_options);

        $json_response = curl_exec($connection);

        if (curl_errno($connection) > 0) {
            echo curl_error($connection) . "\n";
            return 0;
        }

        curl_close($connection);

        $response = json_decode($json_response, true);
        $token = $response['fetchTokenResponse'];

        return $token;
    }

}
