<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Order;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Framework\DB\Transaction; 
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Notification extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $order;
    protected $invoiceService;
    protected $invoiceSender;
    protected $transaction;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Order $order,
        InvoiceService $invoiceService,
        InvoiceSender $invoiceSender,
        Transaction $transaction,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->context = $context;
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->invoiceSender = $invoiceSender;
        $this->transaction    = $transaction;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $postData = $this->getRequest()->getPost();
            $increm = $postData['order_reference_id'];
            $status = $postData['status'];
            $order = $this->order->loadByIncrementId($increm);

            if ($status=='approved')
                {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->order->save();
                    if ($order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($this->order);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();
                        $order->addStatusHistoryComment(__('Invoiced', $invoice->getId()))->setIsCustomerNotified(false)->save();
                        if ($this->scopeConfig->getValue('payment/gocuotas/email_invoice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                            $this->invoiceSender->send($invoice);
                    }
                } else
                {
                    if ($order->getStatus()=='pending')
                        {
                            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                            foreach ($order->getAllItems() as $item) { // Cancel order items
                                $item->cancel();
                            }
                            $order->save();
                        }  
                }        
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
