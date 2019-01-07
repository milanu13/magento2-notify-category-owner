<?php
namespace MilanDev\NotifyCatOwner\Observer\Sales;

use MilanDev\NotifyCatOwner\Helper\Configs;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Configs
     */
    private $configsHelper;
    
    /**
     * @var Product
     */
    private $productModel;
    
    /**
     * @var Category
     */
    private $categoryModel;

    public function __construct(
        Configs $moduleHelper,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Category $categoryModel
    ) {
        $this->configsHelper = $moduleHelper;
        $this->productModel = $productModel;
        $this->categoryModel = $categoryModel;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
    
        if(!$this->configsHelper->isEnabled()){
          return '';
        }
            
        $order = $observer->getEvent()->getOrder();
        $orderIncrementId = $order->getIncrementId();
        $email = $order->getCustomerEmail();

        $itemsCollection = $order->getAllItems();

        $customEmailPath = 'notifycatowner/options/email_template';
        $customEmailSenderPath = 'notifycatowner/options/email_sender';

        /* Sender Detail */
        $senderInfo = $this->configsHelper->emailSender($customEmailSenderPath,$order->getStoreId());

        $catData = array();

        foreach ($itemsCollection as $item) {

          $dataToCat = array(
            'customer_email' => $order->getCustomerEmail(),
            'customer_name' => $order->getCustomerName(),
            'prod_name' => $item->getName(),
            'prod_sku' => $item->getSku(),
            'prod_qty_ordered' => $item->getQtyOrdered(),
            'prod_price_ordered' => $this->configsHelper->getStoreCurrencySymbol() . number_format($item->getPrice(),2),
            'prod_total_price_ordered' => $this->configsHelper->getStoreCurrencySymbol() . number_format($item->getRowTotal(),2)
          );

          $product = $this->productModel->load($item->getProductId());
          $catIds = $product->getCategoryIds();

          foreach ($catIds as $catId) {
            $catData[$catId] = $dataToCat;
          }

        }
    
        foreach ($catData as $catId => $catOrderDetails) {
          $catOwners = $this->categoryModel->load($catId)->getCategoryOwner();
          $catOwners = $catOwners ? explode(',', $catOwners) : [];
              
          foreach ($catOwners as $catOwner) {
            
            /* Receiver Detail */
            $receiverInfo = [
            'name' => 'Mr. X',
            'email' => $catOwner
            ];

            /* To assign the values to template variables */

            $templateVarParams = array();

            $templateVarParams['order_id'] = $orderIncrementId;

            $templateVarParams['cust_name'] = $catOrderDetails['customer_name'];
            $templateVarParams['cust_email'] = $catOrderDetails['customer_email'];
            
            $templateVarParams['prod_name'] = $catOrderDetails['prod_name'];
            $templateVarParams['prod_sku'] = $catOrderDetails['prod_sku'];
            $templateVarParams['prod_qty_ordered'] = $catOrderDetails['prod_qty_ordered'];
            $templateVarParams['prod_price_ordered'] = $catOrderDetails['prod_price_ordered'];
            $templateVarParams['prod_total_price_ordered'] = $catOrderDetails['prod_total_price_ordered'];

            $this->configsHelper->sendMailToCatOwner(
                $customEmailPath,
                $senderInfo,
                $receiverInfo,
                $templateVarParams
            );

          }
        }
    }
}
