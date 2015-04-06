<?php

class AW_Advancedreports_Model_Additional_Reports extends Varien_Object
{
    const UNIT_NAME_PREFIX = 'AW_ARUnit';

    protected $_reports = array();

    public function getReports()
    {
        if (!$this->_reports) {
            $reports = array();
            # Collect additional reports data here
            $searchDir = Mage::getModuleDir('etc','AW_Advancedreports')
                . DS . "additional"
            ;
            if (!is_dir($searchDir)) {
                return array();
            }
            $files = scandir($searchDir);
            foreach ($files as $file) {
                $fileName = $searchDir . DS . $file;
                if (is_file($fileName)) {
                    $info = pathinfo($fileName);
                    if (isset($info['extension']) && strtolower($info['extension']) == "xml") {
                        $name = basename($fileName, ".xml");
                        try {
                            $element = simplexml_load_file($fileName);
                        } catch (Exception $e) {
                            //TODO Catch same error
                            continue;
                        }
                        if ($element) {
                            if (strtolower($element->$name->active) == "true") {
                                $sysName = self::UNIT_NAME_PREFIX . uc_words($name);
                                $item = Mage::getModel('advancedreports/additional_item');
                                $item->setName($name);
                                $item->setTitle((string)$element->$name->title);
                                $item->setVersion((string)Mage::getConfig()->getNode("modules/{$sysName}/version"));
                                $item->setRequiredVersion((string)$element->$name->required_version);
                                $item->setSortOrder((string)$element->$name->sort_order);
                                $reports[] = $item;
                            }

                        }
                    }
                }
            }
            $this->_reports = $reports;
        }
        return $this->_reports;
    }

    public function getCount()
    {
        return count($this->getReports());
    }

    public function getTitle($name)
    {
        if ($this->getCount()) {
            foreach ($this->getReports() as $report) {
                if ($report->getName() == $name) {
                    return $report->getTitle();
                }
            }
        }
        return '';
    }
}