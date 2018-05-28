<?php

namespace App\Goodreads;


class ApiRequest
{
    protected $key = '';

    public function __construct($apiKey)
    {
        $this->key = (string)$apiKey;
    }

    //shelf=read
    //key=******************
    //v=2
    //
    //sort=date_read
    //per_page=200
    //page=
    public function getShelfRead($userId, $page){

        $request = "https://www.goodreads.com/review/list/"
        . $userId
        . ".xml?shelf=read&key="
        . $this->key
        . "&v=2&sort=date_read&per_page=200&page="
        . $page;
        $xmlResponse = $this->curlRequest($request);
        return $xmlResponse;
    }

    public function getUserInfo($userId){

        $request =  "https://www.goodreads.com/user/show/"
            . $userId
            . ".xml"
            . "?"
            . "key="
            . $this->key;
        $xmlResponse = $this->curlRequest($request);
        return $xmlResponse;
    }

    public function curlRequest($requestString){

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
        usleep(1000);

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

        // return xml
        $xml = new \SimpleXMLElement($body);

        return $xml;
    }
}
