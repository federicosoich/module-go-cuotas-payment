<?php

namespace FS\GoCuotas\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Sales\Model\OrderFactory as Order;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Checkout\Model\Type\Onepage;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Url\DecoderInterface;
use \Magento\Framework\Url\EncoderInterface;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Customer\Model\Session as CustomerSession; 


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENCRYPT = 1;
    const DECRYPT = 2;
    protected $urlEncoder;
    protected $urlDecoder;
    public $scopeConfig;
    public $order;
    public $store;
    protected $checkout;
    protected $curl;
    protected $messageManager;
    protected $customerSession;
    protected $session;
    

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Order $order,
        StoreManagerInterface $store,
        Onepage $checkout,
        Curl $curl,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        Session $session
    ) {
        $this->order             = $order;
        $this->store             = $store;
        $this->scopeConfig       = $scopeConfig;
        $this->checkout          = $checkout;
        $this->curl              = $curl;
        $this->urlEncode         = $urlEncoder;
        $this->urlDecode         = $urlDecoder;
        $this->messageManager    = $messageManager;
        $this->customerSession   = $customerSession;
        $this->Session           = $session;
    }


    public function generateCheckoutUrl()
	{
        try {
            $email = $this->scopeConfig->getValue('payment/gocuotas/email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $password = $this->scopeConfig->getValue('payment/gocuotas/password',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //get token
            $url = "https://www.gocuotas.com/api_redirect/v1/authentication";
       
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array("Content-Type: application/json");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $data = '{"email":"'.$email.'","password":"'.$password.'"}';

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);
            $arre = json_decode($resp, true);
            $customer = $this->customerSession->getCustomer()->getEmail();
            $increment_id = $this->Session->getLastRealOrder()->getIncrementId();
            $order = $this->order
            ->create()
            ->loadByIncrementId($increment_id);
            //echo $order->getGrandTotal();
            $base = $this->scopeConfig->getValue('web/secure/base_url',\Magento\Store\Model\ScopeInterface::SCOPE_STORE); 

            $url = "https://www.gocuotas.com/api_redirect/v1/checkouts?amount_in_cents=".($order->getGrandTotal()*100)."&order_reference_id=".$increment_id."&url_success=".$base."gocuotas/payment/success/&webhook_url=".$base."gocuotas/payment/notification/&url_failure=".$base."gocuotas/payment/failure/&email=".$customer;

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $headers = array(
            "Authorization: Bearer ".$arre['token']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $data = '';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            //for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $resp = curl_exec($curl);
            curl_close($curl);
            $arre=json_decode($resp, true);
            return $arre['url_init'];
        }
        catch(Exception $e) {
            echo $e->getMessage();
        }
	}

    public function getMessage()
    {
        return $this->scopeConfig->getValue(
            'payment/gocuotas/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
