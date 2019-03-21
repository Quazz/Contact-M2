<?php
/**
 * Contact
 * 
 * @author Slava Yurthev
 */
namespace SY\Contact\Helper;

use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\ObjectManager;

class Email extends \SY\Contact\Helper\Data {
	const EMAIL_TYPE = 'email';
	protected $json;
	protected $translation;
	protected $transportBuilder;
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Serialize\Serializer\Json $json,
		\Magento\Framework\Translate\Inline\StateInterface $translation,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
	){
		$this->_json = $json;
		$this->translate = $translation;
		$this->transport = $transportBuilder;
		parent::__construct($context);
	}
	public function recive(\SY\Contact\Model\Request $request, $storeId = 0){
		$to = $this->getConfig('general/send_to');
		if((bool)$to !== false){
			$info = $request->getData('info');
			$info = $this->_json->unserialize($info);
			if(is_array($info) && count($info)>0){
				foreach ($info as $field) {
					if(@$field['type'] == self::EMAIL_TYPE){
						$this->send($field['value'], $to, $this->toVars($info), $storeId);
					}
				}
			}
		}
	}
	public function toVars($array){
		$vars = [];
		if(is_array($array) && count($array) > 0){
			foreach ($array as $field) {
				$vars[$field['key']] = $field['value'];
			}
		}
		return $vars;
	}
	public function send($from, $to, $vars, $storeId = 0){
		$translator = $this->translate;
		$transport = $this->transport;
		try {
			$translator->suspend();
			$transport->setTemplateIdentifier(
				$this->getConfig('general/email_template', $storeId)
			);
			echo $this->getConfig('general/email_template', $storeId);
			$transport->setTemplateOptions([
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
					'store' => $storeId
				]);
			$transport->addTo([$to]);
			$transport->setFrom(['name'=>__('Customer'), 'email' => $from]);
			$transport->setTemplateVars($vars);
			$transport->getTransport()->sendMessage();
			$translator->resume();
		} catch (\Exception $e) {}
	}
}
