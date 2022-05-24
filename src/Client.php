<?php

namespace Aoxiang\FengQiao;

use Aoxiang\FengQiao\Requests\CargoDetails;
use Aoxiang\FengQiao\Requests\Customer;
use Aoxiang\FengQiao\Requests\Route;
use phpDocumentor\Reflection\Types\Boolean;

class Client
{
    protected $partnerId;
    protected $checkWord;
    protected $serviceCode;
    protected $timestamp;
    protected $debug = false;
    protected $result = null;
    //沙箱环境的地址
    protected $sandBoxUrl = "http://sfapi-sbox.sf-express.com/std/service";
    //生产环境的地址
    protected $url = "https://sfapi.sf-express.com/std/service";

    /**
     * Client constructor.
     *
     * @param  string  $partnerId
     * @param  string  $checkWord
     * @param  bool    $debug
     */
    public function __construct(string $partnerId, string $checkWord, $debug = false)
    {
        $this->partnerId = $partnerId;
        $this->checkWord = $checkWord;
        $this->setDebug($debug);
    }

    

    /**
     *
     * @param                $orderId
     * @param  CargoDetails  货物信息 $cargoDetails
     * @param  Customer      发件人 $sendCustomer
     * @param  Customer      收件人 $recoverCustomer
     */
    public function createOrder($orderId, CargoDetails $cargoDetails, Customer $sendCustomer, Customer $recoverCustomer)
    {
        $data = [
            'orderId'         => $orderId,
            'cargoDetails'    => $cargoDetails,
            'contactInfoList' => [
                $sendCustomer, $recoverCustomer,
            ],
        ];
        $this->setServiceCode('EXP_RECE_CREATE_ORDER')->request($data);

        return $this;
    }


    /**
     * @param $route
     *
     * @return $this
     * @throws FengQiaoException
     */
    public function getRoute($route)
    {
        if( !$route instanceof Route ){
            $route = new Route($route);
        }
        $this->setServiceCode('EXP_RECE_SEARCH_ROUTES')->request($route);

        return $this;
    }


    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->result->apiResultData->msgData;
    }

    /**
     * @return null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param  string  $code
     *
     * @return $this
     */
    protected function setServiceCode(string $code)
    {
        $this->serviceCode = $code;

        return $this;
    }

    /**
     * @param $debug
     *
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param $data
     *
     * @return mixed
     * @throws FengQiaoException
     */
    protected function request($data)
    {
        $data            = json_encode($data);
        $this->timestamp = time();
        //发送参数
        $post_data = array(
            'partnerID'   => $this->partnerId,
            'requestID'   => $this->createUuid(),
            'serviceCode' => $this->serviceCode,
            'timestamp'   => $this->timestamp,
            'msgDigest'   => $this->createDigest($data),
            'msgData'     => $data,
        );


        $postdata = http_build_query($post_data);
        $options  = array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            ),
        );
        $context  = stream_context_create($options);
        if( $this->debug ){
            $url = $this->sandBoxUrl;
        } else {
            $url = $this->url;
        }
        $result = file_get_contents($url, false, $context);
        $result = json_decode($result);
        if( is_object($result) ){
            $result->apiResultData = json_decode($result->apiResultData);

            if( $result->apiResultData->success ){
                $this->result = $result;

                return $this->result;
            } else {
                throw new FengQiaoException($result->apiResultData->errorMsg);
            }

        } else {
            throw new FengQiaoException('数据解析失败');
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return string
     */
    protected function createDigest($data)
    {
        return base64_encode(md5((urlencode($data . $this->timestamp . $this->checkWord)), true));
    }

    /**
     * @return string
     */
    protected function createUuid()
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid  = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);

        return $uuid;
    }
}