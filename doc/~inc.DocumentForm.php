<?
class DocumentForm
{
	var $EvalValues=false;
	var $InframeSubmitScriptInited=false;
	var $FieldsByName; # array of references to qff(Form fields with class fields description)
	var $ListFields;   #array of marked DocFieldID's that has enum or check type
	var $FormTypeClass,$FormTypeSubClass;
	var $OrderByDescending;
	var $GroupByFields;
	var $OrderByFields;
	var $TabPages;
	
	var $qform;
	var $qdoc;
	var $qfg;         # Form field groups
	var $qlistvalues; # List values by [DocFieldID,Value]
	var $qff;         # form fields by FormFieldID

function Load($args) {
	extract(param_extract(array(
    FormID=>'int',
    FormType=>'string', # uses for optional call. FormID determines from default form for doc_Class
    DocClassID=>'int',
    OpenedGroupID=>'int'
	  ),$args));
	
	$this->FormID=0;
	
	if (!$FormID) {
	  list ($this->FormTypeClass,$this->FormTypeSubClass)=explode (".",$FormType);
		if ($this->FormTypeClass && $DocClassID) {
			$this->qdoc=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
			if (!$this->qdoc) return array(Error=>"Document class of requested form not found",Details=>"DocClassID=$DocClassID");
			switch ($FormType) {
				case 'list.preview': $FormID=intval($this->qdoc->Top->DefaultPreviewForm); break;
				case 'list': $FormID=intval($this->qdoc->Top->DefaultListForm); break;
				case 'item': $FormID=intval($this->qdoc->Top->DefaultItemForm); break;
				case 'edit': case 'search': $FormID=0; break;
				default: return array(Error=>"Unknown form type",Details=>$FormType);
			}
			
			if (!$FormID) {
				print "Не найдена форма по-умолчанию для режима '$FormType'";
				# Try to get any form for the DocClassID
				$this->qform=DBQuery("SELECT * FROM doc_Forms WHERE FormType='$FormType' AND DocClassID=$DocClassID");
				if (!$this->qform) return array(Error=>"No one form found for needed type",Details=>"FormType='$FormType' DocClassID=$DocClassID");
			} else {
				$this->FormID=$FormID;
				$this->qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
				if (!$this->qform) return array(Error=>"Missing default '$FormType' form for class",Details=>"DocClassID=$DocClassID");
			}
		} else {
			return array(Error=>"You have neither select FormID nor FormType with DocClassID");
		}
	} else {
		$this->qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		if (!$this->qform) return array(Error=>"Form not found",Details=>"FormID=$FormID");
	  list ($this->FormTypeClass,$this->FormTypeSubClass)=explode (".",$this->qform->Top->FormType);
		$DocClassID=$this->qform->Top->DocClassID;
		if ($DocClassID) {
			$this->qdoc=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
			if (!$this->qdoc) return array(Error=>"Document class of requested form not found",Details=>"DocClassID=$DocClassID");
		}
	}

	$this->DisplayFormat=$this->qf->Top->DisplayFormat;
	$this->qfg=DBQuery ("SELECT * FROM doc_FormGroups WHERE FormID=$FormID ORDER BY Seq","GroupID");
	$d=getdate();
	$this->EvalValues=array(CurrentYear=>$d["year"],CurrentDate=>time());
	$this->ListFields=false;
	$this->TableFields=array();
	$this->OrderByDescending=1;
	$this->GroupByFields=$this->OrderByFields=false;
	
	if (($this->FormTypeClass=='search')||(!$DocClassID)) {
		$this->qff=DBQuery ("SELECT * FROM doc_FormFields WHERE FormID=$FormID ORDER BY Seq","FormFieldID");
	} else {
	  $this->TableFields[$this->qdoc->Top->IDField]=1;
	  $this->qff=DBQuery ("SELECT ff.FormFieldID, ff.RepresentType, ff.Size, ff.Required, 
		ff.GroupID, ff.GroupBy, ff.OrderBy, ff.Caption, ff.Notice, ff.DocFieldID, ff.Parameters, ff.DependOn,
		df.FieldType,df.IsProperty, df.TargetClass, df.FieldName
	  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
		WHERE FormID=$FormID ORDER BY ff.Seq","FormFieldID");
	}

	foreach ($this->qff->Rows as $FormFieldID=>$Field) {
		list($type,$subtype)=explode('.',$Field->RepresentType);
		if ($DocClassID) {
			if ((!$Field->IsProperty)&&($Field->FieldName)) {
				$this->TableFields[$Field->FieldName]=1;
				if ($Field->GroupBy) $this->GroupByFields[$Field->FieldName]=$Field->GroupBy;
				if ($Field->OrderBy)  {$this->OrderByDescending=$Field->OrderBy; $this->OrderByFields[$Field->FieldName]=$Field->OrderBy;}
			}
		}
		$this->FieldsByName[$Field->FieldName]=&$this->qformf->Rows[$FormFieldID];
		if (($type=='enum')||($Field->RepresentType=='set')) {
			$this->ListFields[$Field->DocFieldID]=true;
		}
	}
	if (is_array($this->GroupByFields)) asort($this->GroupByFields,SORT_NUMERIC);
	
	$s=implode(",",array_keys($this->ListFields));
	$this->qlistvalues=DBQuery("SELECT * FROM doc_ListValues WHERE DocFieldID IN ($s)",array('DocFieldID','Value'));

	$this->TabPages=false;
	foreach ($this->qfg->Rows as $GroupID=>$Group) {
		if ($Group->ParentID==0) {
			$this->TabPages[$GroupID][0]=&$this->qfg->Rows[$GroupID];
			if (!$OpenedGroupID) $OpenedGroupID=$GroupID;
		} else {
			$this->TabPages[$Group->ParentID][$GroupID]=&$this->qfg->Rows[$GroupID];
		}
	}
	$this->OpenedGroupID=$OpenedGroupID;
	$this->FormID=$FormID;
	$this->DocClassID=$DocClassID;
}

function DisplaySearchForm($args) {
  extract(param_extract(array(
    TargetPage=>'string',
    Text_ListIsEmpty=>'string',
    Width=>'string',
    ShowPageTabs=>'int',
    SubmitCaption=>'string',
    SubmitType=>'string', #inframe/totargetpage
    Style=>'string',  #??/vertical/clear
    TableStyle=>'int=1',
    Mode=>"string", # 'preview' for form editor previewing
  ),$args));
	$_ENV->InitWindows();

	$FormTriggers=false;
	$formargs=array(
	  Width=>$Width,
	  SubmitCaption=>$SubmitCaption,
	  Action=>ActionURL('doc.PForm.AcceptSearchFormData.f',array(SubmitType=>$SubmitType))
	  );
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
			$formargs['OnSubmit']="onFormSubmit($FormID,this)";
			break;
			default:
			$formargs['Modal']=0;
	}
		
	$_ENV->OpenForm($formargs);
	$_ENV->PutFormField(array(Type=>'hidden',Name=>'FormID',Value=>$FormID));
	print $this->_putFormFields(0);
	print "<tr><td class='bgdowndown' colspan='2'>".langstr_get($qfg->Rows[$OpenedGroupID]->Caption)."</td></tr>";
	print $this->_putFormFields($OpenedGroupID);
	foreach ($Groups[$OpenedGroupID] as $i=>$Group) {
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
		print "</td><td style='display:none' id='TdFormTarget_$FormID'><iframe name='FormTarget_$FormID' width='100%' height='300' src='".ActionURL("doc.PForm.ShowWait")."'></iframe></td></tr></table>";
	}
}

function GetCount($args) {
  extract(param_extract(array(
  	SearchID=>'int',
  	WhereClause=>'string',
  ),$args));

	if ($SearchID) {
		$sc="SELECT COUNT(*) AS RowCount FROM doc_SearchTags WHERE st.SearchID=$SearchID";
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
	
	print "загружаю список результатов поиска<hr>";
	$fnames="tt.".implode(",tt.",array_keys($this->TableFields));
	$IDField=$this->qdoc->Top->IDField;
	if ($SearchID) {
		$s="SELECT st.DocumentID,$fnames 
		FROM ".$qdoc->Top->DocTable." AS tt LEFT JOIN doc_SearchTags AS st ON tt.$IDField=st.DocumentID";
		$s.=" WHERE st.SearchID=$SearchID";
	}
	
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
  	Mode
  ),$args));

  if (!$DisplayFormat) $DisplayFormat=$this->qform->DisplayFormat;
  
	if (!$DisplayFormat) {
		print "Формат отображения отсутствует";
		return;
	} else {
		$displayMapper=xml_parser_create("utf-8");
		xml_parser_set_option ($displayMapper,XML_OPTION_SKIP_WHITE,1);
		$this->xml_stack=array();
		$this->xml_vars=array();
		$this->xml_formtype=$FormType;
		$this->xml_tablestyle=&$_ENV->ParseTableStyle($TableStyle);
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
		
		$this->xml_groups=array();
		foreach ($this->Documents as $DocumentID=>$doc) {
			$this->xml_vars=array();
			foreach ($this->qff->Rows as $FormFieldID=>$formfield) {
				if (($formfield->IsProperty)||(!$formfield->FieldName)) continue;
				$FieldName=$formfield->FieldName;
				if (isset($doc->$FieldName)) $this->xml_vars[$FieldName]=$doc->$FieldName;
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
	}	
}
/*
function DisplayForm($args)
	{
		$_ENV->InitWindows();
		
	  extract(param_extract(array(
	    FormID=>'int',
	    FormType=>'string', # uses for optional call. FormID determines from default form for doc_Class
	    DocClassID=>'int',
	    
	    TargetPage=>'string',
	    Text_ListIsEmpty=>'string',
	    Width=>'string',
	    ShowPageTabs=>'int',
	    SubmitCaption=>'string',
	    PageNo=>'int=1',
	    RowsPerPage=>'int=20',
	    SubmitType=>'string', #inframe/totargetpage
	    Style=>'string',  #??/vertical/clear
	    TableStyle=>'int=1',
	    Mode=>"string", # 'preview' for form editor previewing
	    
	    SearchID=>'int', # optional parameter for 'preview/list'
	  ),$args));
	 
	if (!$FormID) {
	  list ($this->FormTypeClass,$this->FormTypeSubClass)=explode (".",$FormType);
		if ($this->FormTypeClass && $DocClassID) {
			$qpf=DBQuery ("SELECT DefaultPreviewForm,DefaultListForm,DefaultItemForm FROM doc_Classes WHERE DocClassID=$DocClassID");
			switch ($FormType) {
				case 'list.preview': $FormID=intval($qpf->Top->DefaultPreviewForm); break;
				case 'list': $FormID=intval($qpf->Top->DefaultListForm); break;
				case 'item': $FormID=intval($qpf->Top->DefaultItemForm); break;
				case 'edit': case 'search': $FormID=0; break;
				default: return array(Error=>"Unknown form type",Details=>$FormType);
			}
			
			if (!$FormID) {
				print "Не найдена форма по-умолчанию для режима '$FormType'";
				# Try to get any form for the DocClassID
				$qf=DBQuery("SELECT * FROM doc_Forms WHERE FormType='$FormType' AND DocClassID=$DocClassID");
				if (!$qf) return array(Error=>"No one form found for needed type",Details=>"FormType='$FormType' DocClassID=$DocClassID");
			} else {
				$qf=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
				if (!$qf) return array(Error=>"Missing default '$FormType' form for class",Details=>"DocClassID=$DocClassID");
			}
		} else {
			return array(Error=>"You have neither select FormID nor FormType with DocClassID");
		}
	} else {
		$qf=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		$FormType=$qf->Top->FormType;
	  list ($this->FormTypeClass,$this->FormTypeSubClass)=explode (".",$FormType);
		$DocClassID=$qf->Top->DocClassID;
	}
	if (!$qf) return array(Error=>"Form not found");

	$DisplayFormat=$qf->Top->DisplayFormat;
	$qfg=DBQuery ("SELECT * FROM doc_FormGroups WHERE FormID=$FormID ORDER BY Seq","GroupID");
	$d=getdate();
	$this->EvalValues['CurrentYear']=$d["year"];
	$this->ListFields=false;
	$this->TableFields=array();
	$this->OrderByDescending=1;
	$this->GroupByFields=$this->OrderByFields=false;
	
	if ($this->FormTypeClass=='search') {
		$this->qff=DBQuery ("SELECT * FROM doc_FormFields WHERE FormID=$FormID ORDER BY Seq","FormFieldID");
	} else {
	  $qdoc=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qdoc) {
	  	return array (Error=>"Document class not found for '$Caption'",Details=>"DocClassID=$DocClassID");
	  }
	  $this->TableFields[$qdoc->Top->IDField]=1;
	  $this->qff=DBQuery ("SELECT ff.FormFieldID, ff.RepresentType, ff.Size, ff.Required, 
		ff.GroupID, ff.GroupBy, ff.OrderBy, ff.Caption, ff.Notice, ff.DocFieldID, ff.Parameters, ff.DependOn,
		df.FieldType,df.IsProperty, df.TargetClass, df.FieldName
	  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
		WHERE FormID=$FormID ORDER BY ff.Seq","FormFieldID");
	}

	foreach ($this->qff->Rows as $FormFieldID=>$Field) {
		list($type,$subtype)=explode('.',$Field->RepresentType);
		if ($qdoc) {
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
	
	$s=implode(",",array_keys($this->ListFields));
	$this->qlistvalues=DBQuery("SELECT * FROM doc_ListValues WHERE DocFieldID IN ($s)",array('DocFieldID','Value'));

	$Pages=false;
	foreach ($qfg->Rows as $GroupID=>$Group) {
		if ($Group->ParentID==0) {
			$Pages[$GroupID]=&$qfg->Rows[$GroupID];
			if (!$OpenedGroupID) $OpenedGroupID=$GroupID;
		} else {
			$Groups[$Group->ParentID][$GroupID]=&$qfg->Rows[$GroupID];
		}
	}
	
	switch ($this->FormTypeClass) {
		case 'search':
		$FormTriggers=false;
		$formargs=array(
		  Width=>$Width,
		  SubmitCaption=>$SubmitCaption,
		  Action=>ActionURL('doc.PForm.AcceptSearchFormData.f',array(SubmitType=>$SubmitType))
		  );
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
				$formargs['OnSubmit']="onFormSubmit($FormID,this)";
				break;
				default:
				$formargs['Modal']=0;
			}
			
			$_ENV->OpenForm($formargs);
			$_ENV->PutFormField(array(Type=>'hidden',Name=>'FormID',Value=>$FormID));
			print $this->_putFormFields(0);
			print "<tr><td class='bgdowndown' colspan='2'>".langstr_get($qfg->Rows[$OpenedGroupID]->Caption)."</td></tr>";
			print $this->_putFormFields($OpenedGroupID);
			foreach ($Groups[$OpenedGroupID] as $i=>$Group) {
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
				print "</td><td style='display:none' id='TdFormTarget_$FormID'><iframe name='FormTarget_$FormID' width='100%' height='300' src='".ActionURL("doc.PForm.ShowWait")."'></iframe></td></tr></table>";
			}
		break;
		
		
	case 'list':
		print "Показываю список<hr>";
		$fnames="tt.".implode(",tt.",array_keys($this->TableFields));
		
		if ($SearchID) {
			$sc="SELECT COUNT(*) AS RowCount FROM doc_SearchTags WHERE st.SearchID=$SearchID";
			$qc=DBQuery($sc);
			$RowCount-$qc->Top->RowCount;
			$key=$DocumentID;
			$s="SELECT st.DocumentID,$fnames 
			FROM ".$qdoc->Top->DocTable." AS tt LEFT JOIN doc_SearchTags AS st ON tt.".$qdoc->Top->IDField." = st.DocumentID";
			$s.=" WHERE st.SearchID=$SearchID";
		} else {
			$key=$qdoc->Top->IDField;
			$WhereClause="";
			$sc="SELECT COUNT(*) AS RowCount FROM doc_SearchTags $WhereClause";
			$qc=DBQuery($sc);
			$RowCount-$qc->Top->RowCount;
			$s="SELECT $fnames FROM ".$qdoc->Top->DocTable." ".$WhereClause;
		}
		
    if ($this->GroupByFields) $s.=" GROUP BY ".implode (",",array_keys($this->GroupByFields));
    if ($this->OrderByFields) {
    	$s.=" ORDER BY ".implode (",",array_keys($this->OrderByFields));
    	if ($this->OrderByDescending==2) $s.=" DESC";
    }
		if ($this->FormTypeSubClass=="preview")	$s.=" LIMIT 0,10"; 
		elseif ($this->FormTypeSubClass=="list") {
			$s.=" LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage"; 
		}
		$q=DBQuery($s,$key);
		if (!$DisplayFormat) {
			print "Формат отображения отсутствует";
			return;
		} else {
			$displayMapper=xml_parser_create("utf-8");
			xml_parser_set_option ($displayMapper,XML_OPTION_SKIP_WHITE,1);
			$this->xml_stack=array();
			$this->xml_vars=array();
			$this->xml_formtype=$FormType;
			$this->xml_tablestyle=&$_ENV->ParseTableStyle($TableStyle);
			$this->xml_rowno=1;
 			xml_set_object($displayMapper,$this);
			xml_set_element_handler($displayMapper,"tag_open","tag_close");
			xml_set_character_data_handler($displayMapper,"tag_text");
		}

		
		if ($q) {
			print "<table border=0 cellspacing='1' cellpadding='2'>";
			
			if (!xml_parse ($displayMapper,"<body><header>$DisplayFormat</header>")) {
				print "<td>Error in ".xml_get_current_line_number($displayMapper)
				.":".xml_get_current_column_number($displayMapper)
				." <b>".xml_error_string(xml_get_error_code($displayMapper))."</b></td>";
				exit;
			}
			
			$this->xml_groups=array();
			foreach ($q->Rows as $DocumentID=>$doc) {
				$this->xml_vars=array();
				foreach ($this->qff->Rows as $FormFieldID=>$formfield) {
					if (($formfield->IsProperty)||(!$formfield->FieldName)) continue;
					$FieldName=$formfield->FieldName;
					if (isset($doc->$FieldName)) $this->xml_vars[$FieldName]=$doc->$FieldName;
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
		}
		break;
	}
}
*/
function tag_open($parser, $name, $attrs) {
	$ts=&$this->xml_tablestyle;
	if ($name=='BODY') return;
	$after="";
	$c=count($this->xml_stack);
	if ($c) {$disabled=$this->xml_stack[$c-1]['disabled']; } else $disabled=0;

	if ((!$disabled)&&($name=="IF")) {
		if (isset($attrs['EXISTS'])) {
			$expr=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_vars_boolean_callback"),$attrs['EXISTS']);
			eval("\$disabled=!($expr);");				
		} elseif (isset($attrs['EXPR'])) {
			$expr=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_vars_callback"),$attrs['EXPR']);
			eval("\$disabled=!($expr);");				
		}
	}

	$headerMode=($this->xml_stack[0]['name']=='HEADER');
	if ($headerMode &&(!$disabled)) {
		if ($name=="TD") {
			$name="TH";
			$disabled=1;
			$after=$attrs['TITLE'];
      print "<$name"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">$after";
		}
	}

	if (($name=="GROUP")&&(!$disabled)) {
		if ($headerMode) {
			$disabled=1;
		} else {
			$by=preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_vars_callback"),$attrs['BY']);
			eval ("\$v='$by';");
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
	if (($name=="HEADER")||($name=="ROW")) {$this->xml_groupIndex=0; print "\n<tr valign='top'>";return;}
	if ($name=="BR") {print "<br/>"; return;}
	if ($name=="GROUP") {
		$tg=$ts["tg$this->xml_groupIndex"];
		$cg=$ts["cg$this->xml_groupIndex"];
		print "<$tg colspan='20' $cg>"; return;
	}
	if (($name=="TD")&&(!$headermode)) {
		if ($this->xml_rowno %2) {$name=$ts['to']." ".$ts['co'];} else {$name=$ts['te']." ".$ts['ce'];}
	}
		
	print "<$name"; foreach ($attrs as $k=>$v) print " $k=\"$v\""; print ">";
	
}

function tag_close($parser,$name) {
	if ($name=='BODY') return;
#	$c=count($this->xml_stack);
#	if ($c) {$disabled=$this->xml_stack[$c]['disabled'];} else $disabled=0;
	$top=array_pop($this->xml_stack);
	if ($top) if ($top['disabled']) return;
	$name=$top['name'];
	if (($name=="HEADER")||($name=="ROW")) {print "</tr>";return;}
	if ($name=="BR") {return;}
	if ($name=="GROUP") {
		$tg=$ts["tg$this->xml_groupIndex"];
		print "</$tg></tr><tr>"; return;
	}

	print "</$name>";
}

function tag_text($parser,$data) {
	$c=count($this->xml_stack);
	if ($c) if ($this->xml_stack[$c-1]['disabled']) return;
	$data=trim($data);
	if ($data) print preg_replace_callback("|\{([^}]*?)}|",array(&$this,"xml_vars_callback"),$data);
}

function xml_vars_callback($matches) {
  $s=$matches[1]; 
  list($fieldname,$type,$size)=explode (":",$s);
  if ($this->PreviewMode) {return "Prints $fieldname of type $type";}
  
  if (isset($this->xml_vars[$fieldname])) {
  	$v=$this->xml_vars[$fieldname];
  	$field=&$this->FieldsByName[$fieldname];
  	if (!$field) {
  		return "<font color='red'>Unknown field: $fieldname</font>";
  	}
  	switch($type) {
  		case 'enum':
  			if ($this->qlistvalues) {
  				$v=langstr_get($this->qlistvalues->Rows[$field->DocFieldID][$v]->Caption);
  			}
  			break;
  		default: $v=langstr_get($v); 
  		  if ($size) if (mb_strlen($v>$size)) $v=mb_substr($v,0,$size-3)."...";
  		  break;
  	}
  } else {return "";}
  return $v;
}

function xml_vars_boolean_callback($matches) {
  $s=$matches[1]; 
  list($fieldname,$type,$size)=explode (":",$s);
  
  if (isset($this->xml_vars[$fieldname])) {
  	$v=$this->xml_vars[$fieldname];
    return ($v)?1:0;
  } else {return 0;}
}


function _evaluate($s) {
	#preg_replace("/\{(/w)\}/",)
	
	return str_replace(array('{CurrentYear}'),array($this->EvalValues['CurrentYear']),$s);
}
function _putFormFields ($GroupID) {
	$print="";
	foreach ($this->qff->Rows as $FormFieldID=>$FormField) {
		if ($FormField->GroupID!=$GroupID) continue;
		
		$FieldParameters=$_ENV->Unserialize($this->_evaluate($FormField->Parameters));
	  $Type=$FormField->RepresentType;
	  
		$data=array(
		  Name=>"Field$FormFieldID",
		  Type=>$Type,
		  ToString=>1,
		  Value=>$FormField->DefaultValue,
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


}



?>