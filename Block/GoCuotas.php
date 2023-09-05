<?php

namespace FS\GoCuotas\Block;

use \FS\GoCuotas\Helper\Data;

class GoCuotas extends \Magento\Framework\View\Element\Template
{        
    protected $helper;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,   
        Data $helper,
        array $data = []
    )
    {        
        parent::__construct($context, $data);
        $this->helper = $helper;
    }
    
    public function getGoCuotasUrl()
    {
        return $this->helper->generatePopupUrl();
    }
    
}
?>