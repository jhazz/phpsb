<?
class forms_fcontrol_table{
	var $CopyrightText="(c)2009 PHP Site Builder. Forms";
	var $CopyrightURL="http://www.jhazz.com/forms";
	var $ComponentVersion="1.0";
	
	function InitControl() {
	  $_=&$GLOBALS['_STRINGS']['forms'];
		$this->Caption="Повторение содержимого";
		$this->Description="Отображает содержимое блока столько раз сколько будет узлов";
		$this->Attrs=array(
		  "nodeset"=>array(Caption=>"Повторяющийся узел",Type=>"modelpath"),
		  );
	}
	
	function OnDynamicInit() {
		return array (EmbedJScript=>true);
	}

	function GetStaticHTML (&$cInstance) {
		# $cInstance->ModelScope  ref to model scope
		# $cInstance->FormScope   ref to form scope
		# $cInstance->DesignMode  0/1
		# $cInstance->ReadMode    0/1
		# $cInstance->Attrs       array
		# $cInstance->ControlID   array
		extract(param_extract(array(
			headerBgColor=>"string=#fff0f0",
			panelBgColor=>"string=#f0f0f0",
			label=>"string=this is a field group"
		),$Control->Attrs));
		
		$cInstance->innerHTML="";
		array_walk ($cInstance->FormScope->Controls,array(&$this,"_collect_childrens"),$cInstance);
		return "<table width='100%'><tr><td bgcolor='$panelBgColor'><table width='100%'><tr><td bgcolor='$headerBgColor'>"
		  .$cInstance->innerHTML
		  ."</td></tr></table>";
	}
	function _collect_childrens (&$aControl,&$ParentInstance) {
		$ParentInstance->innerHTML.=$aControl->GetStaticHTML();
	}
}
?>