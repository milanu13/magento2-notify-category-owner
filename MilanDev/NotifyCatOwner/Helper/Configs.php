<?php
namespace MilanDev\NotifyCatOwner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Area;

class Configs extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
    * Store manager
    *
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_storeManager;
    
    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;
    
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;

        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;

        // $state->setAreaCode('frontend');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return ($this->moduleManager->isEnabled('MilanDev_NotifyCatOwner') && $this->scopeConfig->getValue('notifycatowner/options/enabled')) ? 1 : 0;
    }


    /**
    * @param string $path
    * @param int $storeId
    * @return mixed
    */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
            );
    }

    /**
     * get email sender from backend
     */
    public function emailSender($path,$scopeId)
    {
        $result = [];

        $sender = $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
        
        $result['name'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $sender . '/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );
        $result['email'] = $this->scopeConfig->getValue(
            'trans_email/ident_' . $sender . '/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        );

        return $result;
    }


    /**
    * Return store
    *
    * @return Store
    */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
    * Return currency symbol
    *
    * @return currency symbol
    */
    public function getStoreCurrencySymbol()
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }

    /**
    * send mail to category owner
    *
    * @return object
    */

    public function sendMailToCatOwner(
        $template,
        $senderInfo,
        $receiverInfo,
        $templateParams = []
        ) {
        $this->inlineTranslation->suspend();
        $templateId = $this->getConfigValue($template, $this->getStore()->getStoreId());

        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
        ->setTemplateOptions(
            [
            'area' => Area::AREA_FRONTEND,
            'store' => $this->getStore()->getId(),
            ]
            )
        ->setTemplateVars($templateParams)
        ->setFrom($senderInfo)
        ->addTo($receiverInfo['email'],$receiverInfo['name'])
        ->getTransport();

        $transport->sendMessage();
        $this->inlineTranslation->resume();

        return $this;
    }

}
