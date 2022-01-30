<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Sales\Model\Order;
use \Magento\Framework\DB\Transaction; 
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Checkout\Model\Session;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Success extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $invoiceService;
    protected $order;
    protected $transaction;
    protected $session;
    protected $invoiceSender;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        InvoiceService $invoiceService,
        Order $order,
        Transaction $transaction,
        Session $session,
        InvoiceSender $invoiceSender,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction    = $transaction;
        $this->order          = $order;
        $this->context        = $context;
        $this->session        = $session;
        $this->invoiceSender = $invoiceSender;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $id = $this->session->getLastRealOrder()->getId();
            $order = $this->order->load($id); 
            if ($order->getStatus()=='pending')
                {               
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                $this->order->save();
                if ($this->order->canInvoice()) {
                    $invoice = $this->invoiceService->prepareInvoice($this->order);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    $this->order->addStatusHistoryComment(__('Invoiced', $invoice->getId()))->setIsCustomerNotified(false)->save();
                    if ($this->scopeConfig->getValue('payment/gocuotas/email_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                            $this->invoiceSender->send($invoice);
                    }
                }    
            $this->_redirect('checkout/onepage/success');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
