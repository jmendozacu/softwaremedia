var manufacturerList = {
	"Adobe"		:	[
	{
		"ManufacturerID" 	: "10297",
		"ManufacturerCode"	: "AD"
	
	}
	],
	"Acronis"		:	[
	{
		"ManufacturerID" 	: "1020785",
		"ManufacturerCode"	: "AC"
	
	}
	]
};

document.observe("dom:loaded", function() {

	$('brand').observe('change', function () {
      alert(manufacturerList[$('brand').value].ManufacturerID);
  });
  
});