<?php

namespace FS\GoCuotas\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \FS\GoCuotas\Helper\Data;

class CustomConfigProvider implements ConfigProviderInterface
{

    protected $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'gocuotas' => [
                    'message' => $this->helper->getMessage()
                ]
            ]
        ];
        return $config;
    }
}