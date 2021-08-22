<?
class Forms
{
	
var $EvalValues=false;
var $InframeSubmitScriptInited=false;
var $FieldsByName; # array of references to qff(Form fields with class fields description)
var $ListFields;   #array of marked DocFieldID's that has enum or check type
var $FormTypeClass,$FormTypeSubClass;
var $OrderByDescending;
var $GroupByFields;
var $OrderByFields;
var $TabPages,$OpenedTabPage;
var $FieldGroups;
var $Caption;

var $qform;
var $qdoc;
var $qfg;         # Form field groups
var $qlistvalues; # List values by [DocFieldID,Value]
var $qff;         # form fields by FormFieldID
var $qsearch;     # saved search parameters
var $fieldDefaults;

var $xml_stack;
var $xml_key;
var $xml_tablestyle;
var $xml_rowno;
var $xml_groups;
var $xml_opendocformid;
var $xml_taggable;
var $xml_openSysContext;
var $_openModalScripRequired;
var $Items; # current and linked/child forms
var $FormFields;

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
	foreach ($this->FormFields as $FieldID=>$Field) {
		if (($Field->FieldType=='enum')||($Field->FieldType=='set')) {
			$valueSetFields[$Field->DocFieldID]==1;
		}
	}
	if ($valueSetFields) {
		DBQuery2Array("SELECT * FROM doc_ListValues WHERE DocFieldID IN (".implode (",",array_keys($valueSetFields)).")",
		$this->ListValues,array("DocFieldID","Value"));
	}
	
#	print"<pre>";
#	print_r($this->FormFields);
#	$this->DisplayFormat=$this->Forms[$FormID]['DisplayFormat'];
	
#	$this->qfg=DBQuery ("SELECT * FROM doc_FormGroups WHERE FormID=$FormID ORDER BY Seq","GroupID");
	$d=getdate();
	$this->EvalValues=array(CurrentYear=>$d["year"],CurrentDate=>time());
	$this->ListFields=false;
	$this->TableFields=array();
	$this->OrderByDescending=0;
	$this->GroupByFields=$this->OrderByFields=false;
#	$this->Caption=langstr_get($this->qform->Top->Caption);
}
function AssignFrom($existForms) {
	$this->FormFields=&$existForms->FormFields;
	$this->Forms=&$existForms->Forms;
	$this->ListValues=&$existForms->ListValues;
	$this->EvalValues=&$existForms->EvalValues;
	
}

function DisplayViewingFields($FormID) {
	
	/*
	foreach ($this->FormFields[$FormID] as $FormFieldID=>$Field) {
		list($type,$subtype)=explode('.',$Field->RepresentType);
		if ($DocClassID) {
			if ((!$Field->IsProperty)&&($Field->FieldName)) {
				$this->TableFields[$Field->FieldName]=1;
				if ($Field->GroupBy) $this->GroupByFields[$Field->FieldName]=$Field->GroupBy;
				if ($Field->OrderBy)  {$this->OrderByDescending=$Field->OrderBy; $this->OrderByFields[$Field->FieldName]=$Field->OrderBy;}
			}
		}
		$this->FieldsByName[$Field->FieldName]=&$this->qff->Rows[$FormFieldID];
		if (($type=='enum')||($Field->RepresentType=='set')) {
			$this->ListFields[$Field->DocFieldID]=true;
		}
	}
	if (is_array($this->GroupByFields)) asort($this->GroupByFields,SORT_NUMERIC);
	
	if ($this->ListFields) {
		$s=implode(",",array_keys($this->ListFields));
		$this->qlistvalues=DBQuery("SELECT * FROM doc_ListValues WHERE DocFieldID IN ($s)",array('DocFieldID','Value'));
	}
*/
	/*$this->TabPages=false;
	foreach ($this->qfg->Rows as $GroupID=>$Group) {
		if ($Group->ParentID==0) {
			$this->TabPages[$GroupID]=&$this->qfg->Rows[$GroupID];
			if (!$OpenedTabPage) $OpenedTabPage=$GroupID;
		} else {
			$this->FieldGroups[$Group->ParentID][$GroupID]=&$this->qfg->Rows[$GroupID];
		}
	}
	$this->OpenedTabPage=$OpenedTabPage;
	*/
	
	$this->MainFormID=$FormID;
	
}

	function Display($FormID,&$Document,&$DocClasses,&$DocClassFields,$taggable=false,$isMultipleDocument=false,$DocumentID=0) {
		
		$form=&$this->Forms[$FormID];
		$ffields=&$this->FormFields[$FormID];
		if ((!$form)||(!$ffields)) return array(Error=>"FormID not found",Details=>$FormID);
		$DisplayFormat=$form['DisplayFormat'];
		list($formType,$formSubtype)=explode (".",$form['FormType']);
		$FormXMLParser=new FormXMLParser($formType,$taggable);
		$this->xmls[]=&$FormXMLParser;
	
		if ($formType=='list') {
			print "<table border=0 cellspacing='1' cellpadding='2'>";
			$DocClassID=$form['DocClassID'];
			$DocClass=$DocClasses['ByID'][$DocClassID];
			$DocumentIDs=false;
			
			$IDField=$DocClass['IDField'];
			if (!$IDField) {
				print "<tr><td><font color=red>Warning. Document has no IDField</font></td></tr>";
			}
			$FormXMLParser->xml_fields=array();
			$FormXMLParser->xml_docclassid=$DocClassID;
			$FormXMLParser->DocClasses=&$DocClasses;
			$FormXMLParser->DocClassFields=&$DocClassFields;
			$FormXMLParser->Forms=&$this;
			if ($isMultipleDocument) {
				$FormXMLParser->Parse("<body><header>$DisplayFormat</header>");
				foreach ($Document as $DocumentID=>$doc) {
					$FormXMLParser->xml_key=$DocumentID;
					$DocumentIDs[$DocumentID]=1;
					foreach ($ffields as $FormFieldID=>$formfield) {
						$FieldName=$formfield['FieldName'];
						$FormXMLParser->xml_fields[$FieldName]=$formfield;
						if (isset($doc[$FieldName])) {$FormXMLParser->xml_fields[$FieldName]+=$doc[$FieldName];}
					}
					$FormXMLParser->Parse("<row id='$DocumentID'>$DisplayFormat</row>");
					$this->xml_rowno++;
				}
			} else {
#				$DocumentID=$Document[$IDField]['Value'];
				# DOCUMENTID=NULL!!! Document='Author,Cast,Concert'
				foreach ($ffields as $FormFieldID=>$formfield) {
					$FieldName=$formfield['FieldName'];
					$FormXMLParser->xml_fields[$FieldName]=$formfield;
					if (isset($Document[$FieldName])) {$FormXMLParser->xml_fields[$FieldName]+=$Document[$FieldName];}
				}
				$FormXMLParser->Parse("<row id='$DocumentID'>$DisplayFormat</row>");
			}
	
			print "</table>";
		} else {
	
		}
	
		if ($taggable) {
			print "<script>var ids$FormID=[".implode (",",array_keys($DocumentIDs))."];
				function checkAllRows(cbox) {for(var i in ids) {id='cbox_'+ids$FormID[i];
					var c=document.getElementById(id); if (c!=undefined) c.checked=cbox.checked;}}</script>";
		}
	}
}

/*
function Display2($args) {
  extract(param_extract(array(
    TargetURL=>'string',
    Text_ListIsEmpty=>'string',
    Width=>'string',
    ShowPageTabs=>'int',
    SubmitCaption=>'string',
    SubmitType=>'string', #inframe/totargetpage
    Style=>'string',  #??/vertical/clear
    TableStyle=>'int=1',
    Mode=>"string", # 'preview' for form editor previewing
    SearchID=>"int", # uses for stored user's searches
    AutoHide=>'int',
  ),$args));
	$_ENV->InitWindows();

	
	$FormID=$this->FormID;
	$FormName="Search".rand(10,10000);
	
	$FormTriggers=false;
	$formargs=array(
	  Name=>$FormName,
	  Width=>$Width,
	  SubmitCaption=>$SubmitCaption,
	  Action=>ActionURL('doc.PForm.AcceptSearchFormData.'.(($SubmitType=='inframe')?"f":"n"))
	  );

	$this->fieldDefaults=$this->qsearch=false;
	if ($SearchID) {
		$this->qsearch=DBQuery ("SELECT * FROM doc_Searches WHERE SearchID=$SearchID");
		if ($this->qsearch) {
			$this->fieldDefaults=unserialize($this->qsearch->Top->QueryParameters);
		}
	}
	
	if (!$SearchID) $AutoHide=false;
	
	$disp=($AutoHide)?"none":"block";
	if ($SubmitType=='inframe') $ww="width='100%'";
	print "<table cellpadding='2' $ww><tr><td class='bgup'>
	  <div id='hide_$FormName' style='display:$disp'>
	  <a href='javascript:;' onClick='document.getElementById(\"hide_$FormName\").style.display=\"none\"; document.getElementById(\"show_$FormName\").style.display=\"block\"; '>[-] Свернуть параметры поиска</a>";
	switch($SubmitType) {
		case 'inframe':
			if (!$this->InframeSubmitScriptInited) {
				$this->InframeSubmitScriptInited=true;
			?>
			<script>
			function onFormSubmit(FormID,form) {
				var idiframe=document.getElementById("TdFormTarget_"+FormID);
				idiframe.style.display='block';
				form.target="FormTarget_"+FormID;
				return true;
			}	
			</script>
			<?
			}
			print "<table width='100%'><tr valign='top'><td width='1%'>";
			$formargs['OnSubmit']="onFormSubmit($this->FormID,this)";
			break;
		default:
			$formargs['Modal']=0;
	}
		
	$_ENV->OpenForm($formargs);
	$_ENV->PutFormField(array(Type=>'hidden',Name=>'FormID',Value=>$FormID));
	$_ENV->PutFormField(array(Type=>'hidden',Name=>'TargetURL',Value=>$TargetURL));
	$_ENV->PutFormField(array(Type=>'hidden',Name=>'SubmitType',Value=>$SubmitType));
	
	if (!$OpenedTabPage) $OpenedTabPage=$this->OpenedTabPage;

	print $this->_putFormFields($OpenedTabPage);
	foreach ($this->FieldGroups[$OpenedTabPage] as $GroupID=>$Group) {
		$s=$this->_putFormFields($Group->GroupID);
		if (!$s) continue;
		$_ENV->PutFormOpenGroup(array(
		  Name=>$Group->GroupID,
		  Closable=>$Group->Closable,
		  Caption=>langstr_get($Group->Caption),
		  Header=>langstr_get($Group->Header),
		  TriggerEnable=>$Group->TriggerEnable));
		print $s;
		$_ENV->PutFormCloseGroup(array(Footer=>langstr_get($Group->Footer)));
	}
	$_ENV->CloseForm();
	if ($SubmitType=='inframe') {
		print "</td><td style='display:none' id='TdFormTarget_$FormID'><iframe name='FormTarget_$FormID' width='100%' height='500' src='".ActionURL("doc.PForm.ShowWait")."'></iframe></td></tr></table>";
	}

	$disp=(!$AutoHide)?"none":"block";
	print "</div>
    <div id='show_$FormName' style='display:$disp'>
	  <a href='javascript:;' onClick='document.getElementById(\"show_$FormName\").style.display=\"none\"; document.getElementById(\"hide_$FormName\").style.display=\"block\"; '>[+] Развернуть параметры поиска</a>
		</div>	
	</td></tr></table>";
}

function GetCount($args) {
  extract(param_extract(array(
  	SearchID=>'int',
  	WhereClause=>'string',
  ),$args));

	if ($SearchID) {
		$sc="SELECT COUNT(*) AS RowCount FROM doc_SearchTags WHERE SearchID=$SearchID";
		$qc=DBQuery($sc);
		$this->RowCount=$qc->Top->RowCount;
	} else {
		$sc="SELECT COUNT(*) AS RowCount FROM ".$this->qdoc->Top->DocTable." $WhereClause";
		$qc=DBQuery($sc);
		$this->RowCount-$qc->Top->RowCount;
	}
	return $this->RowCount;
}

function LoadDocumentsBySearchResult($args) {
  extract(param_extract(array(
  	SearchID=>'*int',
  	Multiclass=>'int', # TODO
    PageNo=>'int=1',
    RowsPerPage=>'int=20',
  ),$args));
  
	$fnames="tt.".implode(",tt.",array_keys($this->TableFields));
	$IDField=$this->qdoc->Top->IDField;
	if ($SearchID) {
		$s="SELECT st.DocumentID,st.DocClassID,$fnames 
		FROM ".$this->qdoc->Top->DocTable." AS tt LEFT JOIN doc_SearchTags AS st ON tt.$IDField=st.DocumentID";
		$s.=" WHERE st.SearchID=$SearchID";
	}
	
  if ($this->GroupByFields) $s.=" GROUP BY ".implode (",",array_keys($this->GroupByFields));
  if ($this->OrderByFields) {
  	$s.=" ORDER BY ".implode (",",array_keys($this->OrderByFields));
  	if ($this->OrderByDescending==2) $s.=" DESC";
  }
	if ($this->FormTypeSubClass=="preview")	$s.=" LIMIT 0,10"; 
	elseif ($this->FormTypeClass=="list") {
		$s.=" LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage"; 
	}
	$this->Documents=DBQuery($s,$IDField);	
}

function LoadDocumentList($args) {
  extract(param_extract(array(
  	WhereClause=>'string',
    PageNo=>'int=1',
    RowsPerPage=>'int=20',
  ),$args));
	
  if ($WhereClause) $WhereClause=" WHERE $WhereClause";
	$fnames=implode(",",array_keys($this->TableFields));
	$IDField=$this->qdoc->Top->IDField;
	$s="SELECT $fnames FROM ".$this->qdoc->Top->DocTable.$WhereClause;
  if ($this->GroupByFields) $s.=" GROUP BY ".implode (",",array_keys($this->GroupByFields));
  if ($this->OrderByFields) {
  	$s.=" ORDER BY ".implode (",",array_keys($this->OrderByFields));
  	if ($this->OrderByDescending==2) $s.=" DESC";
  }
	if ($this->FormTypeSubClass=="preview")	$s.=" LIMIT 0,10"; 
	elseif ($this->FormTypeSubClass=="list") {
		$s.=" LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage"; 
	}
	$this->Documents=DBQuery($s,$IDField);	
}


function DisplayList($args) {
  extract(param_extract(array(
  	DisplayFormat=>'string',
  	TableStyle=>'int=1',
  	PreviewDataMode=>'int',
  	OpenDocumentFormID=>'int',
    Taggable=>'int',
    AlreadySelected=>'array',
    SysContext=>'string'
  ),$args));

  $this->xml_taggable=$Taggable;
  $this->xml_opendocformid=$OpenDocumentFormID;
  $this->AlreadySelected=$AlreadySelected;
  if (!$OpenDocumentFormID) {
  	if (!$SysContext) $SysContext=$this->qdoc->Top->SysContext;
  	$this->xml_openSysContext=$SysContext;
  }
  
  
	$this->xml_previewdata=$PreviewDataMode;
	if (!$DisplayFormat) $DisplayFormat=$this->DisplayFormat;
  
	if (!$DisplayFormat) {
		print "Формат отображения списка документов отсутствует";
		return;
	} else {
		$displayMapper=xml_parser_create("utf-8");
		xml_parser_set_option ($displayMapper,XML_OPTION_SKIP_WHITE,1);
		$this->xml_stack=array();
		$this->xml_vars=array();
		$this->xml_tablestyle=&$_ENV->ParseTableStyle(1);
		
		$this->xml_rowno=1;
		xml_set_object($displayMapper,$this);
		xml_set_element_handler($displayMapper,"tag_open","tag_close");
		xml_set_character_data_handler($displayMapper,"tag_text");
	}

	if ($this->Documents) {
		print "<table border=0 cellspacing='1' cellpadding='2'>";
		
		if (!xml_parse ($displayMapper,"<body><header>$DisplayFormat</header>")) {
			print "<td>Error in ".xml_get_current_line_number($displayMapper)
			.":".xml_get_current_column_number($displayMapper)
			." <b>".xml_error_string(xml_get_error_code($displayMapper))."</b></td>";
			exit;
		}
		
		$IDField=$this->qdoc->Top->IDField;
		$this->xml_groups=array();
		
		foreach ($this->Documents->Rows as $DocumentID=>$doc) {
			$this->xml_vars=array();
			$this->xml_key=$doc->$IDField;
			$this->xml_docclassid=$doc->DocClassID;
			if (!$this->xml_key) {
				print "<tr><td><font color=red>Warning. Document has no IDField</font></td></tr>";
			}
		
			foreach ($this->qff->Rows as $FormFieldID=>$formfield) {
				if (($formfield->IsProperty)||(!$formfield->FieldName)) continue;
				$FieldName=$formfield->FieldName;
				if (isset($doc->$FieldName)) {
					$this->xml_vars[$FieldName]=$doc->$FieldName;
				}
			}
			
			if (!xml_parse($displayMapper,"<row id='$DocumentID'>$DisplayFormat</row>")) {
				print "<td>Error in ".xml_get_current_line_number($displayMapper)
				.":".xml_get_current_column_number($displayMapper)
				." <b>".xml_error_string(xml_get_error_code($displayMapper))."</b><br><p class='tiny'><pre>$DisplayFormat</pre></p></td>";
				exit;
				
			}
			$this->xml_rowno++;
		}
		
		print "</table>";
		if ($this->xml_taggable) {
			?>
			<script>
			var ids=[<? print implode (",",array_keys($this->Documents->Rows)); ?>];
			function checkAllRows(cbox) {
				for(var i in ids) {
				  id="cbox_"+ids[i];
				  var c=document.getElementById(id);
				  if (c!=undefined) c.checked=cbox.checked;
				}
			}
			</script>
			<?
		}
		if ($this->_openModalScripRequired) {
			$_ENV->InitWindows();
			?>
			<script>
			function op(id,formid,ctx) {
				OpenModal({url:"<? print ActionURL("doc.PForm.DisplayItem.f"); ?>"+"?FormID="+formid+"&ID="+id,w:550,h:550})
			};
			</script>
			<?
		}
	}	
}
*/

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

	


class FormXMLParser {
	var $xml; # xml parser
	var $xml_stack;
	var $xml_key;
	var $xml_groups;
	var $xml_fields;
	var $xml_tablestyle;
	var $rowno;
	var $taggable;
	var $formType;
	var $Forms;

	function FormXMLParser($formType,$taggable,$tableStyleID=1) {
		$this->$rowno=1;
		$this->xml_tablestyle=$_ENV->ParseTableStyle($tableStyleID);
		$this->xml_stack=array();
		$this->xml_groups=false;
		$this->formType=$formType;
		$this->xml=xml_parser_create("utf-8");
		xml_parser_set_option ($this->xml,XML_OPTION_SKIP_WHITE,1);
		xml_set_object($this->xml,$this);
		if ($formType=='list') {
			xml_set_element_handler($this->xml,"tag_open_list","tag_close_list");
			xml_set_character_data_handler($this->xml,"tag_text_list");
		}
	}
	
	function Parse($s) {
		if (!xml_parse ($this->xml,$s)) {
			print "<td>Error in ".xml_get_current_line_number($this->xml)
			.":".xml_get_current_column_number($this->xml)
			." <b>".xml_error_string(xml_get_error_code($this->xml))."</b></td>";
			exit;
		}
	}
	
	function tag_open_list($parser, $name, $attrs) {
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
			}
		}
	
		$headerMode=($this->xml_stack[0]['name']=='HEADER');
		if ($headerMode &&(!$disabled)) {
			if ($name=="TD") {
				$name="TH";
				$disabled=1;
				$after=$attrs['TITLE'];
				if ((!$this->xml_columnNo)&&($this->xml_taggable)) print "<td><input type='checkbox' id='checkAll' onclick='checkAllRows(this)'></td>";
				$this->xml_columnNo++;
	      print "<$name"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">$after";
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
		if (($name=="HEADER")||($name=="ROW")) {$this->xml_groupIndex=0; $this->xml_columnNo=0; print "\n<tr valign='top'>";}
	#	if (($name=="ROW")&&($this->xml_taggable)) 
		if (($name=="HEADER")||($name=="ROW")) return;
		if ($name=="BR") {print "<br/>"; return;}
		if ($name=="GROUP") {
			$tg=$ts["tg$this->xml_groupIndex"];
			$cg=$ts["cg$this->xml_groupIndex"];
			print "<$tg colspan='20' $cg>"; return;
		}
		if (($name=="TD")&&(!$headermode)) {
			if ($this->xml_rowno %2) {$name=$ts['to']." ".$ts['co'];} else {$name=$ts['te']." ".$ts['ce'];}
			if (isset($this->AlreadySelected)) {
				$ch=false;
				if ($this->AlreadySelected["$this->xml_docclassid:$this->xml_key"]) {
					$ch=true;
					if ($this->xml_rowno %2) {$name=$ts['tho']." ".$ts['cho'];} else {$name=$ts['the']." ".$ts['che'];}
				}
			}
			if ((!$this->xml_columnNo)&&($this->xml_taggable)) {
				if (!$ch) print "<$name><input type='checkbox' id='cbox_$this->xml_key' name='selected[]' value='$this->xml_docclassid:$this->xml_key'/></td>";
				else print "<td></td>";
			}
			$this->xml_columnNo++;
		}
		
		print "<$name"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">";
		
	}
	
	function tag_close_list($parser,$name) {
		if ($name=='BODY') return;
		$top=array_pop($this->xml_stack);
		if ($top) if ($top['disabled']) return;
		$name=$top['name'];
		if (($name=='IF')||($name=="BR")) {return;}
		if (($name=="HEADER")||($name=="ROW")) {print "</tr>";return;}
		if ($name=="GROUP") {
			$tg=$ts["tg$this->xml_groupIndex"];	
			print "</$tg></tr><tr valign='top'>";
			return;
		}
		print "</$name>";
	}
	
	function tag_text_list($parser,$data) {
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
	  
	  if (isset($this->xml_fields[$fieldname])) {
	  	$field=&$this->xml_fields[$fieldname];
	  	if (!$field) {
	  		return "<font color=red>Unknown field: $fieldname</font>";
	  	}
	  	$v=$field['Value'];
	  	if (!$type) $type=$field['type'];
	  	
	  	switch($type) {
	  		case 'document':
	  			$Params=$_ENV->Unserialize($field['Parameters']);
	  			
	  			#TOSTRING
#  				$this->SubForms=new Forms;
#  				$this->SubForms->AssignFrom($this->Forms);

	  			$r=$this->Forms->Display($Params['FormID'],$field['Fields'],$this->DocClasses,$this->DocClassFields,false,false,$this->xml_key);
	  			
#	  			$v="[[OPEN DOC FORM $Params[FormID]]]<pre>";
#	  			$v.=print_r($field['Fields'],true);
#	  			$v.="</pre>";
	  			break;
	  		case 'enum':
	  			if ($this->qlistvalues) {
	  				$v=langstr_get($this->qlistvalues->Rows[$field->DocFieldID][$v]->Caption);
	  			}
	  			break;
	  		case 'opendocument':
	  			$v=langstr_get($v); 
	  		  if ($size) if (mb_strlen($v)>$size) $v=mb_substr($v,0,$size-3)."...";
	  			if ($this->xml_opendocformid) {
		  		  $v="<a href='javascript:op($this->xml_key,$this->xml_opendocformid);'>$v</a>";
		  		  $this->_openModalScripRequired=1;
	  			} else {
	  				$v="<a href='$_HOMEURL/$this->xml_openSysContext/$this->xml_key' target='_blank'>$v</a>";
	  			}
	  			break; 
	  		default: $v=langstr_get($v); 
	  		  if ($size) if (mb_strlen($v)>$size) $v=mb_substr($v,0,$size-3)."...";
	  		  break;
	  	}
	  } else {return "";}
	  return $v;
	}
	
	function xml_fields_boolean_callback($matches) {
		if ($this->xml_previewdata) {return 1;}
	  $s=$matches[1];
	  list($fieldname,$type,$size)=explode (":",$s);
	  
	  if (isset($this->xml_fields[$fieldname])) {
	  	$v=$this->xml_fields[$fieldname];
	    return ($v)?1:0;
	  } else {return 0;}
	}
	
	
	function _evaluate($s) {
		#preg_replace("/\{(/w)\}/",)
		
		return str_replace(array('{CurrentYear}'),array($this->EvalValues['CurrentYear']),$s);
	}		
	
}

?>