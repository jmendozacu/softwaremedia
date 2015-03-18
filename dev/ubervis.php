<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

$noteList = Mage::getModel('customernotes/notes')->getCollection();
$noteList->setOrder('created_time','DESC');
$notes = array();

foreach($noteList as $note) {
	if(!array_key_exists($note->getCustomerId(), $notes))
		$notes[$note->getCustomerId()] = array();
		
	$notes[$note->getCustomerId()][] = $note;
}
 
foreach($notes as $customerNote) {
	if (count($customerNote) > 1) {
		$time = null;
		echo "New" . "<br />";
		foreach($customerNote as $note) {
				if ($time)
					$note->setUpdateTime($time);
				else
					$note->setUpdateTime(NULL);
				$note->save();
			
			$time = $note->getCreatedTime();
		}
	}
}