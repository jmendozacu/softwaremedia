<?php
/**
 * File path:
 * magento_root/app/code/local/Youramespace/Yourextensionname/Block/Adminhtml/Entityname/Edit/Form/Renderer/Fieldset/Customtype.php
 */
class Mmsmods_Serialcodes_Block_Adminhtml_Serialcodes_Edit_Renderer_Webcam extends Varien_Data_Form_Element_Abstract
{
 protected $_element;
 
 public function getElementHtml()
 {


$camHTML = '<div class="cam" style="position: relative;"><div id="my_camera" style="width: 320px; margin-bottom: 8px;"></div><input style="position: absolute; top: 5px; right: 5px" type=button value="Take Snapshot" onClick="take_snapshot()"><div id="results" style="width: 320px; ">';
if ($this->getData('value')) {
	$camHTML .= '<img width="320" src="' . Mage::getBaseUrl('media') . DS . $this->getData('value') . '" />';
	$camHTML .= '<span style="padding-left: 0;" class="delete-image"><input type="checkbox" name="webcam_delete" value="1" class="checkbox" id="image_delete"><label for="webcam_delete"> Delete Image</label></span>';
}
$camHTML .= '</div></div><div style="clear: both;"></div>';

$camHTML .= <<<EOT
<script language="JavaScript">
	Webcam.set({
		width: 320,
		height: 240,
		dest_width: 640,
		dest_height: 480,
		image_format: 'jpeg',
		jpeg_quality: 90
	});
	Webcam.attach( '#my_camera' );
</script>



<script language="JavaScript">
		function take_snapshot() {
			// take snapshot and get image data
			Webcam.snap( function(data_uri) {
				// display results in page
				document.getElementById('results').innerHTML = 
					'<img width="320" src="'+data_uri+'"/>' +
					'<input type="hidden" name="webcam" value="'+data_uri+'"/>'
					;
			} );
		}
	</script>
EOT;

 	return $camHTML;
 }
 
}