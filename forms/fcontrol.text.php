<?
class forms_fcontrol_text{
	var $CopyrightText="(c)2008 PHP Site Builder. Forms";
	var $CopyrightURL="http://www.jhazz.com/forms";
	var $ComponentVersion="1.0";

	function InitControl() {
	  $_=&$GLOBALS['_STRINGS']['forms'];
		$this->Caption="Поле ввода текста";
		$this->Description="Позволяет вводить однострочный и многострочный текст";
		$this->Attrs=array(
		  "label"=>array(Caption=>"Надпись поля",Type=>"string",Bindable=>1,DefaultValue=>"Группа полей"),
		  "ref"=>array(Caption=>"Ссылка на поле модели данных",Type=>"modelpath"),
		  );
	}
	function OnDynamicInit() {return array (EmbedJScript=>1);}
	function OnStaticInit() {}
	
	function GetStaticHTML (&$cInstance) {
		return "<input type='text'/>";
	}
}
?>