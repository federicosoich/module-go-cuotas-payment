<?php

namespace FS\GoCuotas\Model;

class Vpos extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'gocuotas';
    protected $_isOffline = true;
    protected $_isInitializeNeeded = true;
}