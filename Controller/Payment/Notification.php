<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Order;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Framework\DB\Transaction; 

class Notification extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $order;
    protected $invoiceService;
    protected $transaction;

    public function __construct(
        Context $context,
        Order $order,
        InvoiceService $invoiceService,
        Transaction $transaction
    ) {
        $this->context = $context;
        $this->order = $order;
        $this->invoiceService = $invoiceService;
        $this->transaction    = $transaction;
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
