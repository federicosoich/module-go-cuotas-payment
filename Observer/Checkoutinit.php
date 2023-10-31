<?php

namespace FS\GoCuotas\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\InputException;
use Magento\Framework\App\ResponseFactory;
use Magento\Catalog\Model\Product;

class Checkoutinit implements \Magento\Framework\Event\ObserverInterface
{
    protected $order;
    protected $cart;
    protected $responseFactory;
    protected $prod;
    
    public function __construct(
    Order $order,
    Cart $cart,
    Session $session,
    ResponseFactory $responseFactory,
    Product $prod
    ) {
    $this->order          = $order;
    $this->cart           = $cart;
    $this->session        = $session;
    $this->responseFactory= $responseFactory;
    $this->product        = $prod;
    
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $id = $this->session->getLastRealOrder()->getId();
            $order = $this->order->load($id);    
            $cart = $this->cart;
            $quote = $this->session->getQuote();
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $code = $method->getCode();
            if ($order->getStatus()=="pending" && $code=="gocuotas") {
                $this->order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                $this->order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                foreach ($this->order->getAllItems() as $item) { // Cancel order items
                    $prod = $this->product->loadByAttribute("sku",$item->getSku());
                    $item->cancel();
                    $quote->addProduct($prod, $item->getQty());
                }
                $this->order->save();
                $cart->save();
                $quote->setCustomerId($order->getCustomerId());
                $quote->getBillingAddress()->addData($order->getBillingAddress()->toArray());
                $quote->getShippingAddress()->addData($order->getShippingAddress()->toArray());
                $shippingAddress=$quote->getShippingAddress();
                $shippingAddress->setCollectShippingRates(true)
                                ->collectShippingRates()
                                ->setShippingMethod($order->getShippingMethod()); 
                $quote->save();     
                $quote->collectTotals();   
                $this->responseFactory->create()->setRedirect("/checkout/#payment")->sendResponse();    
                }
            return $this;
        } catch (InputException $e) {
        echo $e->getMessage();
        }
    }
}
