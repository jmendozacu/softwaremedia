<?php

class SoftwareMedia_EmailHistory_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$params = $this->getRequest()->getParams();

		if (!empty($params['image'])) {
			$id = str_replace('.gif', '', $params['image']);

			$email = Mage::getModel('emailhistory/email')->load($id);

			if ($email) {
				$email->setIsRead(1);
				$email->save();
			}
		}

		$img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAIBTAA7');
		header('Content-Type: image/gif');
		header('Content-Length: ' . strlen($img));
		header('Connection: Close');
		print $img;
		exit;
	}

}
