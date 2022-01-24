<?php

namespace FS\GoCuotas\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \FS\GoCuotas\Helper\Data;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    public function __construct(
         Context $context,
         Data $helper
    ) {
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_redirect($this->helper->generateCheckoutUrl());
    }
}
