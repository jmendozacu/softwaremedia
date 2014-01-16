<?php

class TBT_Rewards_Model_Mysql4_Setup extends TBT_Common_Model_Resource_Mysql4_Setup
{
    protected $_exceptionStack = array();
    protected $_setup = null;
    protected $_firstInstall = false;

    /**
     * alter table for each column update and ignore duplicate column errors
     * This is used since "if column not exists" function does not exist
     * for MYSQL
     *
     * @param unknown_type $installer
     * @param string $tableName
     * @param array $columns
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function addColumns($tableName, $columns)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }

        foreach ($columns as $column) {
            $sql = "ALTER TABLE {$tableName} ADD COLUMN ( {$column} );";
            // run SQL and ignore any errors including (Duplicate column errors)
            try {
                $this->run($sql);
            } catch (Exception $ex) {
                $this->addInstallProblem($ex);
            }
        }

        return $this;
    }

    /**
     * @deprecated Use addForeignKey instead
     */
    public function addFKey($keyName, $table1Name, $column1, $table2Name, $column2=null, $onDelete='NO ACTION', $onUpdate='NO ACTION')
    {
        return $this->addForeignKey($keyName, $table1Name, $column1, $table2Name, $column2, $onDelete, $onUpdate);
    }

    /**
     * Attempt to add a foreign key constraint between two tables
     *
     * @param unknown_type $installer
     * @param string $table1Name
     * @param string $column1
     * @param string $table2Name
     * @param string $column2=null (uses column1 if null)
     * @param string $onDelete='NO ACTION'
     * @param string $onUpdate='NO ACTION'
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function addForeignKey($keyName, $tableName, $columnName, $refTableName, $refColumnName = null, $onDelete = 'NO ACTION', $onUpdate = 'NO ACTION')
    {
        try {
            if (empty($refColumnName)) {
                $refColumnName = $columnName;
            }

            $connection = $this->getConnection();
            if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.0.1')) {
                $connection->addForeignKey(
                    $keyName,
                    $tableName,
                    $columnName,
                    $refTableName,
                    $refColumnName,
                    $onDelete,
                    $onUpdate
                );
            } else {
                $connection->addConstraint(
                    $keyName,
                    $tableName,
                    $columnName,
                    $refTableName,
                    $refColumnName,
                    $onDelete,
                    $onUpdate
                );
            }
        } catch (Exception $ex) {
            $this->addInstallProblem($ex);
        }

        return $this;
    }

    /**
     * Adds an exception problem to the stack of problems that may
     * have occured during installation.
     * Ignores duplicate column name errors; ignore if the msg starts with "SQLSTATE[42S21]: Column already exists"
     * @param Exception $ex
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function addInstallProblem(Exception $ex)
    {
        if (strpos($ex->getMessage(), "SQLSTATE[42S21]: Column already exists") !== false) {
            return $this;
        }

        if (strpos($ex->getMessage(), "SQLSTATE[42000]: Syntax error or access violation: 1091 Can't DROP") !== false
                && strpos($ex->getMessage(), "check that column/key exists") !== false) {

            return $this;
        }

        $this->_exceptionStack[] = $ex;

        return $this;
    }

    /**
     * Returns true if any problems occured after installation
     * @return boolean
     */
    public function hasProblems()
    {
        return sizeof($this->_exceptionStack) > 0;
    }

    /**
     * Returns a string of problems that occured after any installation scripts were run through this helper
     * @return string message to display to the user
     */
    public function getProblemsString()
    {
        $msg = $this->__("The following errors occured while trying to install the module.");
        $msg .= "\n<br>";
        foreach ($this->_exceptionStack as $i => $ex) {
            $msg .= "<b>#{$i}: </b>";
            if (Mage::getIsDeveloperMode()) {
                $msg .= nl2br($ex);
            } else {
                $msg .= $ex->getMessage ();
            }
            $msg .= "\n<br>";
        }
        $msg .= "\n<br>";
        $msg .= $this->__("If any of these problems were unexpected, I recommend that you contact the module support team to avoid problems in the future.");

        return $msg;
    }

    /**
     * Clears any install problems (exceptions) that were in the stack
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function clearProblems()
    {
        $this->_exceptionStack = array();
        return $this;
    }

    /**
     * alter table for each column update and ignore duplicate column errors
     * This is used since "if column not exists" function does not exist
     * for MYSQL
     *
     * @param unknown_type $installer
     * @param string $tableName
     * @param array $columns
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function dropColumns($tableName, $columns)
    {
        foreach ($columns as $column) {
            $sql = "ALTER TABLE {$tableName} DROP COLUMN {$column};";
            // run SQL and ignore any errors including (Duplicate column errors)
            try {
                $this->run($sql);
            } catch (Exception $ex) {
                $this->addInstallProblem($ex);
            }
        }

        return $this;
    }

    /**
     * Runs a SQL query using the install resource provided and
     * remembers any errors that occur.
     *
     * @param unknown_type $installer
     * @param string $sql
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function attemptQuery($sql)
    {
        try {
            $this->run($sql);
        } catch (Exception $ex) {
            $this->addInstallProblem($ex);
        }

        return $this;
    }

    /**
     * Creates an installation message notice in the backend.
     * @param string $msgTitle
     * @param string $msgDesc
     * @param string $url=null if null default Sweet Tooth URL is used.
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function createInstallNotice($msgTitle, $msgDesc, $url = null, $severity = null)
    {
        $message = Mage::getModel('adminnotification/inbox');
        $message->setDateAdded(date("c", time()));

        if ($url == null) {
            $url = "https://support.sweettoothrewards.com/categories/20038603-change-logs";
        }

        if ($severity === null) {
            $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
        }

        // If problems occured increase severity and append logged messages.
        if (Mage::helper('rewards/mysql4_install')->hasProblems()) {
            $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR;
            $msgTitle .= " Problems may have occured during installation.";
            $msgDesc .= " " . Mage::helper('rewards/mysql4_install')->getProblemsString();
            Mage::helper('rewards/mysql4_install')->clearProblems();
        }

        $message->setTitle($msgTitle);
        $message->setDescription($msgDesc);
        $message->setUrl($url);
        $message->setSeverity($severity);
        $message->save();

        return $this;
    }

    /**
     * Add an EAV entity attribute to the database.
     *
     * @param string $entityType        entity type (catalog_product, order, order_item, etc)
     * @param string $attributeCode    attribute code
     * @param array $data                 eav attribute data
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function addAttribute($entityType, $attributeCode, $data)
    {
        try {
            $this->_getSetupSingleton()->addAttribute($entityType, $attributeCode, $data);
        } catch (Exception $ex) {
            $this->addInstallProblem($ex);
        }

        return $this;
    }

    /**
     * Clears cache and prepares anything that needs to generally happen before running DB install scripts.
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function prepareForDb()
    {
        try {
            if (Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.0.0')) {
                Mage::app()->getCacheInstance()->flush();
            } else { // version is 1.3.3 or lower.
                Mage::app()->getCache()->clean();
            }
        } catch (Exception $ex) {
            $this->addInstallProblem("Problem clearing cache:" . $ex);
        }

        return $this;
    }

    /**
     * Dispatches _preApplyData() and _postApplyData before and after it falls back to its
     * parent method, which will:
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function applyDataUpdates()
    {
        $dataVer= $this->_getResource()->getDataVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;

        $updatesApplied = false;
        if ($dataVer !== false) {
             $status = version_compare($configVer, $dataVer);
             if ($status == self::VERSION_COMPARE_GREATER) {
                 $updatesApplied = true;
             }
        } elseif ($configVer) {
            $updatesApplied = true;
        }

        if ($updatesApplied) {
            $this->_preApplyData();
        }

        parent::applyDataUpdates();

        if ($updatesApplied) {
            $this->_postApplyData();
        }

        return $this;
    }

    /**
     * Dispatches _preApply() and _postApply() before and after it falls back to its parent
     * method, which will:
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    public function applyUpdates()
    {
        $dbVer = $this->_getResource()->getDbVersion($this->_resourceName);
        $configVer = (string)$this->_moduleConfig->version;

        $updatesApplied = false;
        if ($dbVer === false) {
            $this->_firstInstall = true;
        }

        if ($dbVer !== false) {
            $status = version_compare($configVer, $dbVer);
            if ($status == self::VERSION_COMPARE_GREATER) {
                $updatesApplied = true;
            }
        } elseif ($configVer) {
            $updatesApplied = true;
        }

        if ($updatesApplied) {
            $this->_preApply();
        }

        parent::applyUpdates();

        if ($updatesApplied) {
            $this->_postApply();
        }

        return $this;
    }

    /**
     * Runs before install/update SQL has been executed
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _preApply()
    {
        return $this;
    }

    /**
     * Runs before additional data update scripts have been executed
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _preApplyData()
    {
        return $this;
    }

    /**
     * Runs after install/update SQL has been executed
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _postApply()
    {
        if ($this->getIsFirstInstall()) {
            $this->_createSuccessfulInstallNotice();
        } else {
            $this->_createSuccessfulUpdateNotice();
        }

        return $this;
    }

    /**
     * Runs after additional data update scripts have been executed
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _postApplyData()
    {
        $this->cleanCache();
        $this->_updateVersionInfo();
        return $this;
    }

    public function cleanCache()
    {
        Mage::getConfig()->cleanCache();
        return $this;
    }

    /**
     * If this store is connected to a Platform account, this method will send the latest
     * version information about Magento and Sweet Tooth up to Platform.
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _updateVersionInfo()
    {
        $apiKey = Mage::getStoreConfig('rewards/platform/apikey');
        if (!$apiKey) {
            return $this;
        }

        $channelData['channel_type'] = 'magento';
        $channelData['channel_version'] = (string) Mage::getConfig()->getNode('modules/TBT_Rewards/version');
        $channelData['platform_version'] = Mage::getVersion();
        $channelData['frontend_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $channelData['backend_url'] = Mage::getUrl('adminhtml');

        try {
            $platform = Mage::getModel('rewards/platform_instance');
            $platform->channel()->update($channelData);
        } catch (Exception $ex) {

        }

        return $this;
    }

    /**
     * This method will create a backend notification regarding a successful
     * Sweet Tooth installation, with the appropriate version number.
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _createSuccessfulUpdateNotice()
    {
        $version = Mage::getConfig()->getNode('modules/TBT_Rewards/version');
        $msgTitle = "Sweet Tooth was successfully updated to v{$version}!";
        $msgDesc = "Sweet Tooth was successfully updated to v{$version} on your store.";
        $this->createInstallNotice($msgTitle, $msgDesc);

        return $this;
    }

    /**
     * This method will create a backend notification regarding a successful
     * Sweet Tooth installation, with the appropriate version number.
     * @return TBT_Rewards_Model_Mysql4_Setup
     */
    protected function _createSuccessfulInstallNotice()
    {
        $version = Mage::getConfig()->getNode('modules/TBT_Rewards/version');
        $msgTitle = "Sweet Tooth v{$version} was successfully installed!";
        $msgDesc = "Sweet Tooth v{$version} was successfully installed on your store.";
        $this->createInstallNotice($msgTitle, $msgDesc);

        return $this;
    }

    /**
     * @return Mage_Eav_Model_Entity_Setup
     */
    protected function _getSetupSingleton()
    {
        if ($this->_setup == null) {
            // TODO: is there any reason we can't just extend this guy instead (from TBT_Common of course)?
            $this->_setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        }

        return $this->_setup;
    }

    /**
     * Returns true if this is a first installation of Sweet Tooth, false otherwise
     * @return bool
     */
    public function getIsFirstInstall()
    {
        return $this->_firstInstall;
    }
}
