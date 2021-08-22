<?
class Forms
{
	var $Forms;
	var $FormFields;
	var $EvalValues=false;
	var $ListValues;
	var $TableFields;
	var $xmls;
	
function Load($FormIDs,$DocClassID=false,$FormType=false,$OpenedTabPage=false) {
	if (is_array($FormIDs)) {$FormIDs=implode(",",$FormIDs); }
		if (!DBQuery2Array("SELECT * FROM doc_Forms WHERE FormID IN ($FormIDs)",
		$this->Forms,"FormID")) {
			return array(Error=>"Forms not found",Details=>"FormIDs: $FormIDs");
		}
	if (!DBQuery2Array("SELECT ff.FormID,ff.FormFieldID, ff.RepresentType, ff.Size, ff.Required, ff.Parameters,
		ff.GroupID, ff.GroupBy, ff.OrderBy, ff.Caption, ff.Notice, ff.DocFieldID, ff.Parameters, ff.DependOn,
		df.FieldType,df.IsProperty, df.TargetDocClass, df.FieldName
	  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
		WHERE FormID IN ($FormIDs) ORDER BY ff.Seq",$this->FormFields,array("FormID","FormFieldID"))) 
	return array(Error=>"Fields not found");

	$this->ListValues=$valueSetFields=false;
	foreach ($this->FormFields as $aFormID=>$aForm) {
		foreach ($aForm as $FieldID=>$field) {
			if (($field['FieldType']=='enum')||($field['FieldType']=='set')) {
				$valueSetFields[$field['DocFieldID']]=1;
			}
		}
	}
	if ($valueSetFields) {
		DBQuery2Array("SELECT * FROM doc_ListValues WHERE DocFieldID IN (".implode (",",array_keys($valueSetFields)).")",
		$this->ListValues,array("DocFieldID","Value"));
	}
	$d=getdate();
	$this->EvalValues=array(CurrentYear=>$d["year"],CurrentDate=>time());
}

	function Display($args) {
#		$FormID,&$Document,&$DocClasses,&$DocClassFields,$taggable=false,
		extract(param_extract(array(
			FormID=>'*int',
		  Document=>'&array',  # Document or Documents
		  DocumentID=>'int',   # ID of document
		  Documents=>'&array', #
		  DocClasses=>'*&array',
		  DocClassFields=>'*&array',
		  isTaggable=>'int',
		  isInputMode=>'int',
		  hideColumnTitles=>'int',
		  ParentForm=>'&object',
		  ShowDelete=>'int',
		  TableStyle=>'int',
		  ParentFormFieldPath=>'string',
		  DisplayFieldFilters=>'&array'
		  ),$args));

		  
		if ((!isset($Documents)&&(!isset($Document)))) {
#			print_developer_warning("Neither Document nor Documents set as argument for Forms.Display");
			return;
		}
		
		$form=&$this->Forms[$FormID];
		
		$ffields=&$this->FormFields[$FormID];
		if ((!$form)||(!$ffields)) return array(Error=>"FormID not found",Details=>$FormID);
		$DisplayFormat=$form['DisplayFormat'];
		$DocClassID=$form['DocClassID'];
		$DocClass=$DocClasses['ByID'][$DocClassID];
		
	
		list($formType,$formSubtype)=explode (".",$form['FormType']);
		$formXMLParser=new FormXMLParser($this,$formType,$DocClasses,$DocClassFields,$TableStyle);
		$this->xmls[]=&$formXMLParser;
		$formXMLParser->xml_isInputMode=$isInputMode;
		$formXMLParser->xml_isTaggable=$isTaggable;
		$formXMLParser->ParentFormFieldPath=$ParentFormFieldPath;
		$formXMLParser->DisplayFieldFilters=&$DisplayFieldFilters;
		$formXMLParser->ParentForm=$ParentForm; # не используется пока
		$formXMLParser->DocClassID=$DocClassID;
		$IDField=$DocClass['IDField'];
		if (!$IDField) {
			print "<tr><td><font color=red>Warning. Document has no IDField</font></td></tr>";
		}
		$formXMLParser->xml_fields=array();
		
		print "\n\n<table border=0 cellspacing='1' cellpadding='2'>";
		if (isset($Documents)) {
			$s="<body>";
			if (!$hideColumnTitles) $s.="<header>$DisplayFormat</header>";
			$formXMLParser->Parse($s);
			foreach ($Documents as $DocumentID=>$doc) {
				foreach ($ffields as $FormFieldID=>$formField) {
					$fieldName=$formField['FieldName'];
					$formXMLParser->xml_fields[$fieldName]=$formField;
					if (isset($doc[$fieldName])) {
						$formXMLParser->xml_fields[$fieldName]+=$doc[$fieldName];
						if ($formField['FieldType']=='enum') {
						}
					}
				}
				$formXMLParser->Parse("<document id='$DocumentID'>$DisplayFormat</document>");
				$formXMLParser->xml_rowno++;
			}
		} else {
			$s="<body>";
			if (!$hideColumnTitles) $s.="<header>$DisplayFormat</header>";
			foreach ($ffields as $formFieldID=>$formField) {
				$fieldName=$formField['FieldName'];
				$formXMLParser->xml_fields[$fieldName]=$formField;
				if (isset($Document[$fieldName])) {
					$formXMLParser->xml_fields[$fieldName]+=$Document[$fieldName];
				}
			}
			$formXMLParser->Parse("<document id='$DocumentID'>$DisplayFormat</document>");
			
		}
		print "</table>"; 

	}
}
/*
function _putFormFields ($GroupID) {
	$print="";
	foreach ($this->qff->Rows as $FormFieldID=>$FormField) {
		if ($FormField->GroupID!=$GroupID) continue;
		
		$FieldParameters=$_ENV->Unserialize($this->_evaluate($FormField->Parameters));
	  $Type=$FormField->RepresentType;
	  
	  
	  $defaultValue=$FormField->DefaultValue;
	  if ($this->fieldDefaults) $defaultValue=$this->fieldDefaults[$FormFieldID];

	  $data=array(
		  Name=>"Field$FormFieldID",
		  Type=>$Type,
		  ToString=>1,
		  Value=>$defaultValue,
	    Caption=>langstr_get($FormField->Caption),
		  Notice=>langstr_get($FormField->Notice),
		  Required=>$FormField->Required,
		  MaxLength=>$FormField->Size,
		  Size=>$FormField->Size);
		if (is_array($FieldParameters)) $data=$FieldParameters+$data;
		switch ($Type) {
			case 'enum.checks':
				if (!$FormField->DocFieldID) {
					return "Ошибка: Тип enum.checks работает только с полями связанными с документами";
				}
				$d=&$this->qlistvalues->Rows[$FormField->DocFieldID];
				$Values=false;
				if ($d) foreach ($d as $v=>$row) {
					$Values[$v]=langstr_get($row->Caption);
				}
				if ($Values) $data['DocFieldValues']=$Values;
				break;
		}
		
		if ($data['Size']>40) $data['Size']=40;
		$print.=$_ENV->PutFormField ($data);
	}
	#if ($print) $print="<table width='100%' border=0 cellpadding='3'>$print</table>";
	return $print;
}
*/
class FormXMLParser {
	var $xml; # xml parser
	var $xml_stack;
	var $xml_key;
	var $xml_groups;
	var $xml_fields;
	var $xml_tablestyle;
	var $xml_isInputMode;
	var $xml_isTaggable;
	var $xml_opendocformid; # open instead of SysContext
	var $rowno;
	var $xml_formType;
	var $Forms;
	var $DocClasses;
	var $DocClassFields;
	var $DocClassID;
	var $ParentFormFieldPath;
	var $DisplayFieldFilters;
	
	function FormXMLParser(&$Forms,$formType,&$DocClasses,&$DocClassFields,$tableStyleID=1) {
		$this->$rowno=1;
		$this->xml_tablestyle=$_ENV->ParseTableStyle($tableStyleID);
		$this->xml_stack=array();
		$this->xml_groups=false;
		$this->xml_key=0;
		$this->showChecks=$showChecks;
		$this->Forms=&$Forms;
		$this->DocClasses=&$DocClasses;
		$this->DocClassFields=&$DocClassFields;
		
		$this->xml_formType=$formType;
		$this->xml=xml_parser_create("utf-8");
		xml_parser_set_option ($this->xml,XML_OPTION_SKIP_WHITE,1);
		xml_set_object($this->xml,$this);
		xml_set_element_handler($this->xml,"tag_open","tag_close");
		xml_set_character_data_handler($this->xml,"tag_text");
		 
	}
	
	function Parse($s) {
		if (!xml_parse ($this->xml,$s)) {
			print "<td>Ошибка в XML-шаблоне формы. Строка:".xml_get_current_line_number($this->xml)
			.", символ:".xml_get_current_column_number($this->xml)
			." <b>".xml_error_string(xml_get_error_code($this->xml))."</b></td>";
			exit;
		}
	}
	
	function tag_open($parser, $name, $attrs) {
		$ts=&$this->xml_tablestyle;
		if ($name=='BODY') return;
		$after="";
		$c=count($this->xml_stack);
		if ($c) {$disabled=$this->xml_stack[$c-1]['disabled']; } else $disabled=0;
	
		if ((!$disabled)&&($name=="IF")) {
			if (isset($attrs['EXISTS'])) {
				$expr=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_fields_boolean_callback"),$attrs['EXISTS']);
				eval("\$disabled=!($expr);");				
			} elseif (isset($attrs['EXPR'])) {
				$expr=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_fields_callback"),$attrs['EXPR']);
				eval("\$disabled=!($expr);");
			} elseif (isset($attrs['NOTNULL'])) {
				$expr=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_fields_boolean_callback"),$attrs['NOTNULL']);
				$disabled=!intval($expr);
			} elseif (isset($attrs['DISPLAYFILTERSHOW'])) {
				if ($this->DisplayFieldFilters[$attrs['DISPLAYFILTERSHOW']]) $disabled=1; else $disabled=0;
			}
		}
	
		$headerMode=($this->xml_stack[0]['name']=='HEADER');
		if ($headerMode &&(!$disabled)) {
			if ($name=="TD") {
				$t=$ts['tt']; $c=$ts['ct'];
				$name=$t;
				$disabled=1;
				$after=$attrs['TITLE'];
				if ((!$this->xml_columnNo)&&($this->xml_isTaggable && $this->xml_isInputMode)) print "<$name></$name>";
				$this->xml_columnNo++;
	      print "<$t$c"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">$after";
			}
		}
	
		if (($name=="GROUP")&&(!$disabled)) {
			if ($headerMode) {
				$disabled=1;
			} else {
				if (!$attrs['BY']) {
					print_developer_warning("Tag GROUP should has attribute BY");
					exit;
				}
				$v="";
				$by=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_fields_callback"),$attrs['BY']);
				if ($by) {
					$s="\$v='$by';";
					eval ($s);
				}
				if ($this->xml_groups[$this->xml_groupIndex]!=$v) {
					$this->xml_groups[$this->xml_groupIndex]=$v;
					$disabled=0;
				} else $disabled=1;
				$this->xml_groupIndex++;
			}
		}
		array_push($this->xml_stack,array(name=>$name,attrs=>$attrs,disabled=>$disabled));
		if ($name=="IF") return;
		if ($disabled) {return;}
		if (($name=="HEADER")||($name=="DOCUMENT")) {
			$this->xml_groupIndex=0; 
			$this->xml_columnNo=0; 
			print "\n<tr valign='top'>";
			$this->xml_key=$attrs['ID'];
			return;
		}
		if ($name=="BR") {print "<br/>"; return;}
		if ($name=="GROUP") {
			$tg=$ts["tg$this->xml_groupIndex"];
			$cg=$ts["cg$this->xml_groupIndex"];
			print "<$tg colspan='20' $cg>"; return;
		}
		
		if ($name=="FORM") {
			$fieldname=$attrs['FIELD'];
		  $field=&$this->xml_fields[$fieldname];
		  if (!$field) {return "<font color=red>Unknown field: $fieldname</font>";}
		  $FormFieldPath=$this->ParentFormFieldPath."/".$fieldname;
		  if ($this->DisplayFieldFilters) {
	  		if ($this->DisplayFieldFilters[$FormFieldPath]) return;
	  	}
			if (!empty($field['Fields'])) {
	#			$Params=$_ENV->Unserialize($field['Parameters']);
				$r=$this->Forms->Display(array(
				  Document=>&$field['Fields'],
					FormID=>$attrs['FORMID'],
				  DocumentID=>$this->xml_key,
					ParentFormFieldPath=>$FormFieldPath, #$this->ParentFieldName."/".$field['FieldName']."",
				  DocClasses=>&$this->DocClasses,
				  DocClassFields=>&$this->DocClassFields,
				  ParentForm=>&$this,
				  TableStyle=>$attrs['TABLESTYLE'],
				  hideColumnTitles=>$attrs['HIDECOLUMNTITLES'],
				  isTaggable=>$attrs['ISTAGGABLE'],
				  isInputMode=>$this->xml_isInputMode, #$attrs['ISINPUTMODE'],
				  DisplayFieldFilters=>&$this->DisplayFieldFilters,
				  $this->xml_key)
				);
			}
			return;			
		}
		if ($name=="COLLECTION") {
			$fieldname=$attrs['FIELD'];
		  $field=&$this->xml_fields[$fieldname];
		  if (!$field) {return "<font color=red>Unknown field: $fieldname</font>";}
	  	#$v=$field['Value'];
	  	#if (!$type) $type=$field['FieldType'];
	  	$FormFieldPath=$this->ParentFormFieldPath."/".$fieldname;
	  	if ($this->DisplayFieldFilters) {
	  		if ($this->DisplayFieldFilters[$FormFieldPath]) return;
	  	}
			$args=array(
			  Documents=>&$field['Items'],

			  FormID=>intval($attrs['FORMID']),
			  ParentFormFieldPath=>$FormFieldPath, # $this->ParentFieldName."/".$field['FieldName'],
			  ParentForm=>&$this,
			  DocClasses=>&$this->DocClasses,
			  DocClassFields=>&$this->DocClassFields,
			  isInputMode=>$this->xml_isInputMode, #$attrs['ISINPUTMODE'],
			  DisplayFieldFilters=>&$this->DisplayFieldFilters,
			  TableStyle=>$attrs['TABLESTYLE'],
			  $this->xml_key);
			$args['hideColumnTitles']=$attrs['HIDECOLUMNTITLES'];
			$args['isTaggable']=$attrs['ISTAGGABLE'];
#			$args['isInputMode']=$attrs['ISINPUTMODE'];
			#$Params=$_ENV->Unserialize($field['Parameters']);
			$r=$this->Forms->Display($args);
			
			return;
		}
		if ($name=="INPUT") {
			$fieldName=$attrs['FIELD'];
		  $field=&$this->xml_fields[$fieldName];
		  if (!$field) {return "<font color=red>Unknown field: $fieldName</font>";}
	  	$v=$field['Value'];
	  	#if (!$type) $type=$field['FieldType'];
	  	$FormFieldPath=$this->ParentFormFieldPath."/".$fieldName;
	  	
	  	if ($this->DisplayFieldFilters) {
	  		if ($this->DisplayFieldFilters[$FormFieldPath]) return;
	  	}
			$style=$attrs['STYLE'];
			if (!$style) $style="clear";
			$args=array(
				Type=>$attrs['TYPE'],
				Value=>$v,
				Name=>$field['FormFieldName'],
				Caption=>$field['Caption'],
				Style=>$style);
			if ($attrs['COLS'])	{$args['Size']=$attrs['COLS']; }
			if ($attrs['ROWS'])	{$args['Rows']=$attrs['ROWS']; }
			if ($attrs['SIZE'])	{$args['Size']=$attrs['SIZE']; }
			if ($this->xml_isInputMode) $_ENV->PutFormField($args); else print $v;
			return;
		}
		
		
		if (($name=="TD")&&(!$headermode)) {
			if ($this->xml_rowno %2) {$name=$ts['to']." ".$ts['co'];} else {$name=$ts['te']." ".$ts['ce'];}
			if (isset($this->AlreadySelected)) {
				$ch=false;
				if ($this->AlreadySelected["$this->Forms['DocClassID']:$this->xml_key"]) {
					$ch=true;
					if ($this->xml_rowno %2) {$name=$ts['tho']." ".$ts['cho'];} else {$name=$ts['the']." ".$ts['che'];}
				}
			}
			if ((!$this->xml_columnNo)&&($this->xml_isTaggable && $this->xml_isInputMode)) {
				$cid=$this->ParentFormFieldPath.':'.$this->xml_key;
				if (!$ch) print "<$name width='1%'><input type='checkbox' name='selected[]' value='$cid'/></td>";
				else print "<$name></td>";
			}
			$this->xml_columnNo++;
		}
		
		print "<$name"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">";
		
	}
	
	function tag_close($parser,$name) {
		if ($name=='BODY') return;
		$top=array_pop($this->xml_stack);
		if ($name=='FORM') return;
		if ($name=='COLLECTION') return;

		if ($top) if ($top['disabled']) return;
		$name=$top['name'];
		if (($name=='IF')||($name=="BR")) {return;}
		if (($name=="HEADER")||($name=="DOCUMENT")) {print "</tr>";return;}
		if ($name=="GROUP") {
			$tg=$ts["tg$this->xml_groupIndex"];	
			print "</$tg></tr><tr valign='top'>";
			return;
		}
		print "</$name>";
	}
	
	function tag_text($parser,$data) {
		$c=count($this->xml_stack);
		if ($c) if ($this->xml_stack[$c-1]['disabled']) return;
		$data=trim($data);
		if ($data) print preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_fields_callback"),$data);
	}
	
	function xml_fields_callback($matches) {
	  global $_HOMEURL;
	  $s=$matches[1]; 
	  list($fieldname,$type,$size)=explode (":",$s);
	  if ($this->xml_previewdata) {return "Prints $fieldname of type $type";}
	  
	  $field=&$this->xml_fields[$fieldname];
	  if (!$field) {return "<font color=red>Unknown field: $fieldname</font>";}
  	$v=$field['Value'];
  	if (!$type) $type=$field['FieldType'];
  	$FormFieldPath=$this->ParentFormFieldPath."/".$field['FieldName'];
  	
  	if ($this->DisplayFieldFilters) {
  		if ($this->DisplayFieldFilters[$FormFieldPath]) return;
  	}
  	
  	switch($type) {
  		case 'collection':
  			if (!empty($field['Items'])) {
	  			$Params=$_ENV->Unserialize($field['Parameters']);
	  			$r=$this->Forms->Display(array(
	  				FormID=>$Params['FormID'],
	  			  Documents=>&$field['Items'],
	  			  ParentFormFieldPath=>$FormFieldPath, # $this->ParentFieldName."/".$field['FieldName'],
	  			  ParentForm=>&$this,
	  			  DocClasses=>&$this->DocClasses,
	  			  DocClassFields=>&$this->DocClassFields,
	  			  hideColumnTitles=>$Params['hideHeaders'],
	  			  isTaggable=>$Params['isTaggable'],   #$this->xml_isInputMode,
  				  isInputMode=>$this->xml_isInputMode, #$attrs['ISINPUTMODE'],

#	  			  isInputMode=>$Params['isInputMode'],
	  			  TableStyle=>$Params['TableStyle'],
	  			  DisplayFieldFilters=>&$this->DisplayFieldFilters,
	  			  $this->xml_key));
	  			  break;
  			}
  		case 'document':
  			if (!empty($field['Fields'])) {
  			$Params=$_ENV->Unserialize($field['Parameters']);
  			$r=$this->Forms->Display(array(
  				FormID=>$Params['FormID'],
  				DocumentID=>$this->xml_key,
  				ParentFormFieldPath=>$FormFieldPath, #$this->ParentFieldName."/".$field['FieldName']."",
  			  Document=>&$field['Fields'],
  			  DocClasses=>&$this->DocClasses,
  			  DocClassFields=>&$this->DocClassFields,
  			  ParentForm=>&$this,
  			  hideColumnTitles=>$Params['hideHeaders'],
  			  isTaggable=>0,
  			  isInputMode=>0,
  			  TableStyle=>$Params['TableStyle'],
  			  DisplayFieldFilters=>&$this->DisplayFieldFilters,
  			  $this->xml_key));
  			}
  			break;
  			/*
  		case 'langstring': case 'langtext': case 'string': case 'int': case 'float': case 'text': case 'email':
  			#print $field['FieldName'];
  			if (($this->xml_isInputMode)&&(!$field['AutoCalc'])) {
#					print_r($field);
  				$v=$_ENV->PutFormField(array(
  					Type=>$type,
  					Value=>$v,
  					Name=>$field['FormFieldName'],
  					Caption=>$field['Caption'],
  					ToString=>1,
  					Style=>'clear',
  				));
  			}
  			break;*/
  		case 'enum':
  			if ($this->Forms->ListValues) {
  				$v=langstr_get($this->Forms->ListValues[$field['DocFieldID']][$v]['Caption']);
  			}
  			break;
  		case 'opendocument':
  			$v=langstr_get($v); 
  			if (!$v) {$v="[..]";}
  		  if ($size) if (mb_strlen($v)>$size) $v=mb_substr($v,0,$size-3)."...";
  			if ($this->xml_opendocformid) {
	  		  $v="<a href='javascript:op($this->xml_key,$this->xml_opendocformid);'>$v</a>";
	  		  $this->_openModalScripRequired=1;
  			} else {
  				$v="<a href='$_HOMEURL/".$this->DocClasses['ByID'][$this->DocClassID]['SysContext']."/$this->xml_key' target='_blank'>$v</a>";
  			}
  			break; 
  		default: $v=langstr_get($v); 
  		  if ($size) if (mb_strlen($v)>$size) $v=mb_substr($v,0,$size-3)."...";
  		  break;
  	}
   
	 return $v;
	}
	
	function xml_fields_boolean_callback($matches) {
#		if ($this->xml_previewdata) {return 1;}
	  $s=$matches[1];
	  list($fieldname,$type,$size)=explode (":",$s);
	  
	  if (isset($this->xml_fields[$fieldname])) {
	  	$f=$this->xml_fields[$fieldname];
	  	if ($this->DisplayFieldFilters) {
		  	$FormFieldPath=$this->ParentFormFieldPath."/".$f['FieldName'];
	  		if ($this->DisplayFieldFilters[$FormFieldPath]) return 0;
	  	}
	  	$v=$f['Value'];
	    return ($v)?1:0;
	  } else {return 0;}
	}
	
	
	function _evaluate($s) {
		#preg_replace("/\{(/w)\}/",)
		
		return str_replace(array('{CurrentYear}'),array($this->EvalValues['CurrentYear']),$s);
	}		
	
}

?>