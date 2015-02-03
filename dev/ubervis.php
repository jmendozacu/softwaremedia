<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";


?>

<html>
	<head>
	
	<title>Test</title>
	</head>
	<body>
	
<script type="text/javascript">
var _rrES = {
    seller_id: 3677,
    email: "jeff@jaldev.com",
    invoice: "100101113"};
(function() {
    var s=document.createElement('script');s.type='text/javascript';s.async=true;
    s.src="https://www.resellerratings.com/popup/include/popup.js";var ss=document.getElementsByTagName('script')[0];
    ss.parentNode.insertBefore(s,ss);
})();
</script>

	</body>
</html>