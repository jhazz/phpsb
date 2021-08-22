<?
class forms_DropList extends PsbFormControl{
	function CreateFormControl($tag,$id,&$PsbForm,&$attrs) {
		extract(param_extract(array(
		Caption=>"string",
		Name=>"string",
		DatasetName=>"string",
		Value=>"string",
		BtnImg=>"string=btn-dropdown.gif",
		Size=>'int',
		NullCaption=>'string',
		Editable=>'int',
		DoEditValue=>'int',
		ToString=>'int',
		Form=>'string', # not required
		OnChange=>'',
		OnSelect=>'',
		DefaultValue=>"string",
		Required=>'int',
		ViewCaption=>'int',
		),$attrs));

		$this->PsbFormControl($tag,$id,$PsbForm,$args);

		global $cfg,$_SYSSKIN_NAME,$_THEME_NAME,$_ENVIRONMENT,$_THEME;

		if (!$Editable) $ViewCaption=1;
		/*
		if (!$this->PutDropListInited)
		{
		$this->PutDropListInited=1;
		#$this->InitScriptText.=implode("\n",file($cfg['PHPSBScriptsPath']."/forms/public/droplist.js"));

		}*/

		$SkinPath=$_THEME['SkinPath'];
		$SkinURL=$_THEME['SkinURL'];
		$GlyphURL ="$SkinURL/$BtnImg";
		$GlyphFile="$SkinPath/$BtnImg";

		list($w,$h,$t,$imgwh)=@getimagesize($GlyphFile);
		$idName=$Form.preg_replace(array('/\[/','/\]/','/\//'),array('_','','_'),$Name);

		if ($DefaultValue) $dv=",\"$DefaultValue\""; else $dv=",false";
		$OnClickBtn="PsbDropList.Open(event,\"$idName\"$dv)";

		if ($Editable)
		{
			$result.="<table cellspacing=0 cellpadding=0 border=0><tr valign='top'><td>";
			$onch=" onKeyUp='dropFieldID=\"$idName\"; dropFieldValue=this.value; $OnChange' onChange='
	      dropFieldID=\"$idName\";
	      dropFieldValue=this.value;
	      $OnChange'";
			$result.="<input size='$Size' class='inputarea' name='$Name' id='lfv_$idName' type='text' $onch value='$Value'>";
			if ($DoEditValue) $result.="<div id='lfc_$idName' class='notice'></div>";
		}
		else
		{
			$s1=($Size)?"":"width='100%'";
			$s2=($Size)?$Size*5:"100%";
			$result.="<table $s1 cellspacing=0 cellpadding=0 border=0><tr valign='top'><td>";
			$result.="<div nowrap class='inputarea' style='overflow:hidden; border:2px groove; height:18px; width:$s2; padding:1; cursor:hand;color:#000000'  id='lfc_$idName' onClick='$OnClickBtn' title='".addslashes($Caption)."'>$Caption</div>
	    <input type='hidden' id='lfv_$idName' name='$Name' value='$Value'>";
		}

		$result.="</td><td align='right' width='1'><img id='drop_$idName' src='$GlyphURL' $imgwh onClick='$OnClickBtn'></td></tr>";
		$sc="";
		$field=array(
		idName=>"'$idName'",
		fValue=>"document.getElementById(\"lfv_$idName\")",
		DropImg=>"document.getElementById(\"drop_$idName\")",
		ValueSetName=>"'$ValueSetName'",
		Editable=>intval($Editable),
		DoEditValue=>intval($DoEditValue));
		if ((!$Editable)||($DoEditValue)) $field['fCaption']="document.getElementById(\"lfc_$idName\")";

		if ($NullCaption) $field['NullCaption']="'$NullCaption'";
		if ($OnChange) $field['OnChange']="'$OnChange'";
		if ($OnSelect) $field['OnSelect']="'$OnSelect'";

		foreach ($field as $k=>$v)
		{
			if ($v) $sc.=(($sc)?",":"")."$k:$v";
		}
		$result.="<script>dropFields['$idName']={"."$sc}; DropDown_Update('$idName',1)</script></table>";
		$PsbForm->Text.=$result;
	}


}
?>