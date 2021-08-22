<?
class forms
{
function forms()
  {
  $_=&$GLOBALS['_STRINGS']['forms'];
  $this->Title=$_['FORMS_CARTRIDGE'];
  }
  
	function Menu()
	{
		$_=&$GLOBALS['_STRINGS']['forms'];
		return array (
		array
			(
			PutToCategory=>"admin",
			Caption=>$_['Test forms'],
			Items=>array(
				array(Caption=>'Test the form',Call=>"forms.IFormEditor.Browse.bm"),
				)
			
			),
			);
	}
}
?>

