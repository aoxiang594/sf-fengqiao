<?php


namespace Aoxiang\FengQiao\Requests;


class Route
{
    public $trackingNumber = [];
    public $language = 0;
    public $trackingType = 1;
    public $methodType = 1;
    public $checkPhoneNo = '';

    public function __construct($trackingNumber, $checkPhoneNo = '')
    {
        if( is_array($trackingNumber) ){
            $this->trackingNumber = $trackingNumber;
        } else {
            $this->trackingNumber = [$trackingNumber];
        }
        $this->checkPhoneNo = $checkPhoneNo;
    }
}