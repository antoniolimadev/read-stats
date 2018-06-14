<?php

namespace App\Goodreads;
use App\UserRequest;
use Carbon\Carbon;

class ApiRequest
{
    protected $key = '';
    protected $waitTime;

    public function __construct($apiKey)
    {
        $this->key = (string)$apiKey;
        $this->waitTime = config('goodreads.wait_time');
    }

    public function getUserInfo($userId){
        sleep($this->waitTime);
        $request =  "https://www.goodreads.com/user/show/"
            . $userId
            . ".xml"
            . "?"
            . "key="
            . $this->key;
        $xmlResponse = $this->curlRequest($request);
        return $xmlResponse;
    }

    //shelf=read
    //key=******************
    //v=2
    //
    //sort=date_read
    //per_page=200
    //page=
    public function getShelfRead($userId, $page){
        sleep($this->waitTime);
        $request = "https://www.goodreads.com/review/list/"
        . $userId
        . ".xml?shelf=read&key="
        . $this->key
        . "&v=2&sort=date_read&per_page=200&page="
        . $page;
        $xmlResponse = $this->curlRequest($request);
        return $xmlResponse;
    }

    public function canRequestBeMade(){
        sleep($this->waitTime);
        $lastRequestTime = UserRequest::latest()->first()->created_at; // last request to be made
        if (!$lastRequestTime){
            return true;
        }
        $seconds = Carbon::now()->diffInSeconds($lastRequestTime); // difference in seconds between last request and now
        if($seconds > $this->waitTime){
            return true;
        }
        dd($seconds);
        return false;
    }

    public function curlRequest($requestString){
        //check if request can be made
        if (!$this->canRequestBeMade()){
            return null;
        }
        $headers = array(
            'Accept: application/xml',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        // subtract the header from response so you can get a full xml file
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        //print_r($body);
        // convert xml to array
        // $json = json_encode($xml);
        // $array = json_decode($json,TRUE);
        // return $array;

        //log the request
        UserRequest::create();
        // return xml
        $xml = new \SimpleXMLElement($body);
        return $xml;
    }
}
