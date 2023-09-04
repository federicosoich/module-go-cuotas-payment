<?php
namespace FS\GoCuotas\Block;
class GoCuotas extends \Magento\Framework\View\Element\Template
{        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        array $data = []
    )
    {        
        parent::__construct($context, $data);
    }
    
    public function getGoCuotas()
    {
        return 'Hello World';
    }
    
}
?>