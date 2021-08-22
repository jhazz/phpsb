<?
class forms_PsbForm{
	var $FormScope=false;
	var $LastFormIDNum=1;
	var $Form_ScriptInited=false;
	var $DatasetArrayInited=false;
	var $FormID;
	var $Forms;
	var $Layouts;
	var $InitText="";
	var $InitScriptText="";
	var $_xml_result="";
	var $_xml_stack_pos=-1;
	var $_xml_stack=false;
	var $_xml_parser=false;
	var $_dynamicFormsDetected=false;
	var $_loadedControls=false;

	/*function Render ($LayoutXML,$DataXML) {
	print "\n\n<!-- LAYOUT ".$this->Form['ID']." --><div id='".$this->Form['ID']."'>$LayoutXML</div>";
	print "\n<!-- DATA ".$this->Form['ID']." --><div id='".$this->Form['ID']."_data'>$DataXML</div>";
	print "\n<script>phpsbforms_RenderFromContainer('".$this->Form['ID']."');</script>\n\n";
	}
	*/

	function Init() {
#		$_ENV->InitWindows();
	}
	function AfterRender() {
		global $cfg;
#		print $this->InitText;
		
		
#		foreach ($this->Forms as $id=>$form) {
#			$ss=$form->Serialize();
#			if ($ss) $s.="\n\n//Serialized form $id\nPsbFormDispatcher.AddForm('$id',\{$ss});\n";
		
/*
		foreach ($this->Forms as $id=>$form) {
			$ss=$form->Serialize();
			if ($ss) $s.="\n\n//Serialized form $id\nPsbFormDispatcher.AddForm('$id',\{$ss});\n";
			
		}
		*/
		if ($this->InitScriptText) {
			print "\n\n<script src='$cfg[PublicURL]/forms/PsbForm.js'></script>";
			print "\n<script>$s $this->InitScriptText;</script>";
		}
	}

	function RenderForm($FormID,$LayoutID,$ModelID,$StaticMode=0,$ShowMonitor=1) {
		global $cfg;
		$this->Forms[$FormID]=array(LayoutID=>$LayoutID,ModelID=>$ModelID,StaticMode=>$StaticMode);
		if ($StaticMode) {
			print "Я не знаю что делать в статике";
		} else {
			print "
<table width='100%' border='1'>
<tr valign='top'><td width='70%'><div style='width:100%; height:500; overflow:auto;' id='PsbFormContainer_$FormID'>
Form[$FormID] loading. Please wait...</div>
</td>";
			if ($ShowMonitor) print "<td rowspan='2' style='font-size:10px; background-color:#f0f0f0'>Form[$FormID] data viewer<br>
<div id='PsbFormMonitor_$FormID' style='overflow:auto; width:100%; height:550;'></div>
<div id='PsbFeedMonitor_$FormID' style='overflow:auto; width:100%; height:100;'></div></td>
</tr><tr><td valign='bottom' bgcolor='#f0f0f0'>Debug window <button onClick='document.getElementById(\"debug\").innerHTML=\"<table id=debugTable width=100%></table>\";'>Clear</button>
<div id='debug' style='background-color:#f0f0f0; overflow:auto; width:100%; height:120'>
<table id='debugTable' width='100%'></table></div></td>";
			print "</tr></table>";
			$this->AddInitScript("\nPFD.forms['$FormID']={"."formId:'$FormID',layoutId:'$LayoutID',modelId:'$ModelID'};");
			
		}
	}

	
	function _xml_start_element($parser, $name, $attribs) {
		$this->_xml_stack_pos++;
		$this->_xml_stack[$this->_xml_stack_pos]=array($name, $attribs,"",array());
	}
	function _xml_char ($parser,$data) {
		$this->_xml_stack[$this->_xml_stack_pos][2].=$data;
	}
	function _xml_end_element($parser, $name) {
		$d=$this->_xml_stack[$this->_xml_stack_pos];
		$this->_xml_stack_pos--;
		$this->_xml_stack[$this->_xml_stack_pos][3][]=$d;
	}
	function _to_javascript($s) {
		return str_replace("\n","\\\n",addslashes($s));
	}
	function _get_xml_stack_result (&$item,$l=0) {
		$s="";
		# КРАСИВО ФОРМАТИРУЕТ
		$s.="\n".str_repeat("  ",$l)."'$item[0]'";
		#$s.="'$item[0]'";
		$s2="";
		foreach($item[1] as $k=>$v) {$s2.=(($s2)?",":"")."'$k':'".$this->_to_javascript(trim($v))."'";}
		if ($s2) $s.=",{"."$s2}"; else $s.=",false";
		if ($item[2]) {$s.=",'".$this->_to_javascript(trim($item[2]))."'";} else $s.=",''";
		$c=count($item[3]);
		$s1="";
		if ($c) {
			for ($i=0;$i<$c;$i++) {
				$s2=$this->_get_xml_stack_result($item[3][$i],$l+1);
				if ($s2) $s1.=(($s1)?",":"")."[".$s2."]";
			}
			$s.=",[$s1]";
		}
		return $s;
	}
	
	# Возвращает элементы типа [tagName,{attr1:v1,attr2:v2,...},contentStr,arrayOfChild[], EMPTY_SLOT_FOR_PARENT, EMPTY_SLOT]
	# после подготовки данных для каждой инстанции модели будет вызываться initModelInstance, которая будет 
	# в EMPTY_SLOT_FOR_PARENT ставить указатель на родительский узел
	# [0] - tagName: string
	# [1] - attrs: object
	# [2] - contentStr: string
	# [3] - childs: array
	# [4] - parent: object ref
	# [5] - object for future use
	function _get_xml_as_javascript(&$xmlText) {
		$this->_xml_result="";
		$this->_xml_stack_pos=0;
		$this->_xml_stack=array("_c",array(),"",array());
		$this->_xml_parser=xml_parser_create("UTF-8");
		xml_set_object($this->_xml_parser, $this);
    xml_parser_set_option($this->_xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($this->_xml_parser, XML_OPTION_SKIP_WHITE, 1);
    xml_set_element_handler($this->_xml_parser, "_xml_start_element","_xml_end_element");
    xml_set_character_data_handler($this->_xml_parser, "_xml_char");
    xml_parse($this->_xml_parser,$xmlText,true);
    xml_parser_free($this->_xml_parser);
    if ($this->_xml_stack[1]) {
			return "[".$this->_get_xml_stack_result($this->_xml_stack[1])."]";
    } else return "undefined";
	}
	

	function SetModel ($ModelID,$xmlText) {
		$s=$this->_get_xml_as_javascript($xmlText);
		$this->AddInitScript("\nPFD.model_js['$ModelID']=$s;");
	}
	function SetLayout ($LayoutID,$xmlText) {
#		$xmlText="<forms:group>".$xmlText."</forms:group>";
		$xml_parser=xml_parser_create("UTF-8");
		xml_set_object($xml_parser, $this);
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($xml_parser, XML_OPTION_SKIP_WHITE, 1);
    xml_set_element_handler($xml_parser, "_xml_layout_dynamic_starttag","_xml_layout_dynamic_endtag");
    xml_set_character_data_handler($xml_parser, "_xml_layout_dynamic_text");
    $this->layoutDynamicData=new PsbFormControl("layout",$LayoutID); 
    $this->_layoutScope=&$this->layoutDynamicData;
    xml_parse($xml_parser,$xmlText,true);
    xml_parser_free($xml_parser);
    $s=$this->layoutDynamicData->js_serialize();
    $this->AddInitScript("\nPFD.layout_js['$LayoutID']=[$s];\n");
    }

  function _xml_layout_dynamic_pushtext($s) {
		$prevIsStr=false;
		$cnt=count($this->_layoutScope->childs);
		if (($cnt>0) && (is_string($this->_layoutScope->childs[$cnt-1])))
		  $this->_layoutScope->childs[$cnt-1].=$s; else $this->_layoutScope->childs[]=$s;
  }
  function _xml_layout_dynamic_starttag($parser,$tag,$attrs) {
		$p=strpos($tag,':');
  	if ($p===false) {
  		$s=""; foreach ($attrs as $k=>$v) $s.=" $k='$v'";
  		$this->_xml_layout_dynamic_pushtext("<$tag$s>");
  		return;
  	}
  	
  	$tag=strtolower($tag);
  	$control=&$this->LoadFormControl($tag);
		if (!$control) {print "Tag module not found: &lt;$tag&gt;"; return;}
  	$id=$attrs['id'];
  	list ($cart,$mod)=explode (":",$tag);
  	
		$cInstance=new PsbFormControl($tag,$attrs);
		if (!$control->dynamicInited) {
			$control->dynamicInited=1;
			$r=$control->OnDynamicInit();
			global $cfg;
			if ($r['IncludeJScript']) print "\n<script src='$cfg[PublicURL]/$cart/fcontrol.$mod.js'></script>\n";
			if ($r['EmbedJScript']) {
				$f=$cfg['PHPSBScriptsPath']."/$cart/public/fcontrol.$mod.js";
				if (file_exists($f)) {$this->AddInitScript("\n".implode("",file($f)));}
			}
			if (isset($r['Script'])) $this->AddInitScript($r['Script']);
		}
		$this->_layoutScope->childs[]=&$cInstance;
		$cInstance->parent=&$this->_layoutScope;
		$this->_layoutScope=&$cInstance;
		$cInstance->childs=array();
	}
	function _xml_layout_dynamic_endtag($parset,$tag) {
		$p=strpos($tag,':');
		if ($p===false) {
  		$this->_xml_layout_dynamic_pushtext("</$tag>");
  		return;
  	}
		$this->_layoutScope=&$this->_layoutScope->parent;
	}
	function _xml_layout_dynamic_text ($parser,$s) {
		$s=trim($s);
		if ($s) $this->_xml_layout_dynamic_pushtext($s);
	}
	
	function AddInitScript ($s) {
		if ($s) $this->InitScriptText.=$s;
	}
	function &LoadFormControl ($ControlClassName) {
		global $cfg;
		if (isset($this->_loadedControls[$ControlClassName])) {
			return $this->_loadedControls[$ControlClassName];
		} else {
			$ControlClassName=basename($ControlClassName);
			list ($cartridge,$className)=explode (':',$ControlClassName,2);
			if (!$_ENV->LoadCartridge($cartridge)) return false;
	    $dir="$cfg[ScriptsPath]/$cartridge";
	    if (!is_dir($dir)) $dir="$cfg[PHPSBScriptsPath]/$cartridge";
	    $f="$dir/fcontrol.$className.php";
	    if (!is_file($f)) {
	      print_developer_warning("Form control file not found",$f);
	    	return false;
	    }
			include_once ($f);
	    $class=$cartridge.'_fcontrol_'.$className;
	    if (!class_exists ($class))
	      {
	      print_developer_warning("Class declaration not found in the form control file","class $class{} in '$f'");
	      return false;
	      }
			$control=&new $class();
			$this->_loadedControls[$ControlClassName]=&$control;
			return $control;
		}
	}
}

/*-----------------------------------------------------------------
*  
*   PsbFormControl
*
*/
class PsbFormControl {
	var $tag;
	var $parent=false;
	var $childs=false; # for group controls
	var $attrs=false;

	function PsbFormControl($tag,$attrs=false) {
		$this->tag=$tag;
		if (is_array($attrs)) foreach($attrs as $k=>$v) $this->attrs[$k]=$v;
	}
	
	function js_serialize($level=0) {
		$s="'$this->tag'";
		$s2="";
		if (is_array($this->attrs)) foreach ($this->attrs as $k=>$v) {$s2.=(($s2)?",":"")."$k:'".addslashes($v)."'";}
		$s.=",{"."$s2}";
		$s2="";
		if (is_array($this->childs)) {
			foreach($this->childs as $c) {
			$s3="";
			if (is_object($c)) { 
				#КРАСИВО ФОРМАТИРУЕТ
				$s3="\n".str_repeat('  ',$level).'['.$c->js_serialize($level+1).']';
			} else $s3="'".addslashes($c)."'";
			if ($s3) $s2.=(($s2)?",":"").$s3;
			}
		}
		$s.=",[$s2]";
		return $s;
	}
	
}
/*
class PsbForm {
	var $TargetURL;			#
	var $IsTargetModal;	# boolean - TargetURL opening in modal window
	var $FormStyle;			# string - one of THEME[FormStyles]
	var $FormStyleData; # array - data from FormStyle
	var $Text="";				# resulting text for Render()
	var $Closed=0;
	var $ContentScope;
	var $id;				# string - ID of the form
	var $_content; #array
	
	function PsbForm() {$this->_content=array(); $this->ContentScope=&$this; return $this;}
	function Serialize() {
		$s=$s2=$s3="";
		$a=array(id=>$this->id);
		foreach ($a as $k=>$v) {$s.=(($s)?",":"")."'$k':'".addslashes($v)."'";}
		if (is_array($this->_content)) {
			foreach($this->_content as $c) {
				$s3="";
				if (is_object($c)) {
					$s3='{'.$c->Serialize().'}'; 
				} elseif (is_string($c) && $c) {
					$s3="'".addslashes($c)."'";
				}
				if ($s3) $s2.=(($s2)?",":"").$s3;
			}
		}
		if ($s2) $s.=",_content:[$s2]";
		return $s;
	}
	
}
*/
/*
	function PutData ($args) {
		extract(param_extract(array(
			Name=>"string",
			Values=>"&array",       # using Values or Recordset->Rows[]->CaptionField
			Recordset=>"&object",   #
			Columns=>"&array", # not used yet : array[0..n]=array(Field,Caption,Width) instead CaptionField
			EnumFrom=>'int',
			EnumTo=>'int',
			LoadURL=>'string',
			RowsPerPage=>'int=40',
		),$args));

		$MaxSize=0;

		$defs="";
		if ($LoadPageFromURL) {
			$defs.="loadURL:'$LoadURL',rowsPerPage:$RowsPerPage,";
		}
		$s="";
		if ($Values) {
			foreach($Values as $k=>$v) {
				$v=langstr_get($v);
				$size=strlen($v);
				if ($size>$MaxSize) $MaxSize=$size;
				$v=addslashes ($v);
				$s.=(($s)?",":"")."'$k':'$v'";
			}
			$defs.="type:2,maxsize:$MaxSize";
			if ($s) $s="\{$s}";
		} elseif ($Recordset) {
			$kk=array_keys($Recordset->Rows);
			if (!$Columns) {
				for ($i=0;$i<$Recordset->FieldCount;$i++) $Columns[$i]=array('Field'=>$Recordset->Fields[$i]);
			}
			$columnsCount=count($Columns);
			foreach($kk as $k)
			{
				$vv="";
				for ($i=0;$i<$columnsCount;$i++) {
					$c=&$Columns[$i];
					$v=langstr_get($Recordset->Rows[$k]->$c['Field']);
					$v=addslashes ($v);
					$vv.=(($i)?",":"")."'$v'";
				}
				$s.=(($s)?",":"")."'$k':[$vv]";
			}
			if ($s) $s="\{$s}";
			$cs="";
			for ($i=0;$i<$columnsCount;$i++) {
				$c=&$Columns[$i];

				$cs.=(($cs)?",":"")."{c:'".((isset($c['Caption']))?addslashes($c['Caption']):$c['Field'])."'";
				if ($c['Width']) $cs.=",w:'".$c['Width']."'";
				$cs.="}";
			}
			$defs.="type:3,size:$MaxSize";
			if ($cs) $defs.=",columns:[$cs]";
		} elseif (($EnumFrom)||($EnumTo)) {
			if (!$EnumStep) $EnumStep=1;
			if ($EnumTo<$EnumFrom) $EnumStep=-abs($EnumStep);
			$defs.="type:4,size:$MaxSize,enumf:$EnumFrom,enumt:$EnumTo,step:$EnumStep";

		}
		#if (!$this->DatasetArrayInited) {$this->DatasetArrayInited=1;}
		if ($s) $this->AddInitScript("\n PsbFormDispatcher.AddDataset('$Name',\{$defs,data:$s});");
	}

*/


?>

