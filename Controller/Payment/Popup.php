<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Sales\Model\OrderFactory as Order;
use \Magento\Checkout\Model\Cart;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Url\DecoderInterface;
use \Magento\Framework\Url\EncoderInterface;
use \Magento\Customer\Model\Session as CustomerSession; 
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \FS\GoCuotas\Helper\Data;
use Magento\Framework\View\Result\PageFactory;


class Popup extends \Magento\Framework\App\Action\Action
{
    public $scopeConfig;
    public $context;
    protected $order;
    protected $cart;
    protected $session;
    protected $result;
    protected $helper;
    protected $customerSession;
    /**
     * @var PageFactory $pageFactory
     */
    protected $pageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */

    public function __construct(
        ResultFactory $result,
        ScopeConfigInterface $scopeConfig,
        Order $order,
        Cart $cart,
        Data $helper,
        Session $session,
        Context $context,
        CustomerSession $customerSession,
        PageFactory $pageFactory
    ) {
        $this->context = $context;
        $this->result = $result;
        $this->order = $order;
        $this->helper = $helper;
        $this->cart = $cart;
        $this->session = $session;
        $this->customerSession   = $customerSession;
        $this->scopeConfig       = $scopeConfig;
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);    }

    public function execute()
    {
        //echo "FEDE";
        $this->_redirect($this->helper->generateCheckoutUrl());
    }

}
