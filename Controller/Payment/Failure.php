<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Sales\Model\Order;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Checkout\Model\Cart;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\Action\Context;

class Failure extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $order;
    protected $cart;
    protected $session;
    protected $manager;

    public function __construct(
        ManagerInterface $manager,
        Order $order,
        Cart $cart,
        Session $session,
        Context $context
    ) {
        $this->context = $context;
        $this->manager = $manager;
        $this->order = $order;
        $this->cart = $cart;
        $this->session = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $id = $this->session->getLastRealOrder()->getId();
            $order = $this->order->load($id);    
            $cart = $this->cart;
            if ($order->getStatus()=='pending')
                {
                $id = $this->session->getLastRealOrder()->getId();
                $order = $this->order->load($id);    
                $cart = $this->cart;
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                foreach ($order->getAllItems() as $item) { // Cancel order items
                    try {
                            $cart->addOrderItem($item);
                            $item->cancel();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    echo $e->getMessage();
                    }    
                }    
            
            } else {
                foreach ($order->getAllItems() as $item) { // Cancel order items
                    try {
                            $item->cancel();
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    echo $e->getMessage();
                    }    
                }   
            }    
            $order->save();
            $cart->save();    
            $this->_redirect('checkout/');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
