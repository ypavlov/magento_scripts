<?php

require_once 'app/Mage.php';
Mage::app();

// loads the proper email template
$emailTemplate  = Mage::getModel('core/email_template')
//    ->loadDefault('sales_email_order_template');
    ->loadByCode('New Order');

// All variables your error log tells you that are missing can be placed like this:

$emailTemplateVars = array();
$emailTemplateVars['usermessage'] = "blub";
$emailTemplateVars['store'] = Mage::app()->getStore();
$emailTemplateVars['sendername'] = 'sender name';
$emailTemplateVars['receivername'] = 'receiver name';

// order you want to load by ID

$emailTemplateVars['order'] = Mage::getModel('sales/order')->load(12);

// load payment details:
// usually rendered by this template:
// web/app/design/frontend/base/default/template/payment/info/default.phtml
$order = $emailTemplateVars['order'];
$paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
    ->setIsSecureMode(true);
$paymentBlock->getMethod()->setStore(Mage::app()->getStore());

$emailTemplateVars['payment_html'] = $paymentBlock->toHtml();

//displays the rendered email template
echo $emailTemplate->getProcessedTemplate($emailTemplateVars);