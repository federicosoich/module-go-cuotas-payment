<?php
namespace FS\GoCuotas\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Order;
use \Magento\Framework\Exception\NotFoundException;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Framework\DB\Transaction; 
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Request\InvalidRequestException;
use \FS\GoCuotas\Helper\Data;

class Notification extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    public $context;
    protected $order;
    protected $invoiceService;
    protected $invoiceSender;
    protected $transaction;
    protected $scopeConfig;
    protected $helper;
        /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function __construct(
        Context $context,
        Order $order,
        Data $helper,
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
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
   
    
        try {
            $code = $this->getRequest()->getParams()["code"];
            echo $this->helper->decodeUrl($code);
            $postData = json_decode($this->getRequest()->getContent(), true);
            $increm = $postData['order_reference_id'];
            if (!$this->helper->decodeUrl($code)==$increm)
                throw new \Exception('Validation postcode fails');
            $status = $postData['status'];
            $order = $this->order->loadByIncrementId($increm);

            if ($status=='approved')
                {
                    $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
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
                    $message =  __('Notificacion Automatica de Go Cuotas: el pago fue aprobado');
                    $message .= __('<br/> Orden GoCuotas:'.$postData['order_id']);
                    $message .= __('<br/> Status: '.$postData['status']);
                    $message .= __('<br/> Cuotas:'.$postData['number_of_installments']);
                    $message .= __('<br/> Total:'.($postData['amount_in_cents']/100));
                    $order->addStatusToHistory(\Magento\Sales\Model\Order::STATE_PROCESSING, $message, true);
                    $order->save();
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
            $logger->info($e->getMessage());
        }
    }
}
