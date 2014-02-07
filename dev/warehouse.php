<?php

require "../app/Mage.php";

Mage::getModel('orderdispatch/observer')->sendReminders();
