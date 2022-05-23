<?php


namespace Aoxiang\FengQiao\Requests;


class Customer
{
//{
//        "address": "广东省广州市白云区湖北大厦",
//      "company": "顺丰速运",
//      "contact": "小邱",
//      "contactType": 2,
//      "country": "CN",
//      "postCode": "580058",
//      "tel": "18688806057"
//    }

    public $address = "";

    public $contact = "";

    public $contactType = "";

    public $tel = "";

    public $country = "CN";

    public $postCode = "";

    public $company = "";


    public function __construct(string $contact, string $address, string $tel, $contactType = 1)
    {
        $this->contact     = $contact;
        $this->address     = $address;
        $this->tel         = $tel;
        $this->contactType = $contactType;
    }
}