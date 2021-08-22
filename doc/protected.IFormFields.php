<?
class doc_IFormFields
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentDesigner=>"BrowseFields");

	var $DocTableFields;
	var $SystemFields;
	var $FormTypeClass,$FormTypeSubClass;
	

	function BrowseFields($args)
	{
	  extract(param_extract(array(
			FormID=>'int',
			OpenedGroupID=>'int',
	  ),$args));
	    		
	  $qform=DBQuery ("SELECT * FROM doc_Forms WHERE FormID=$FormID");
	  if (!$qform) return (array(Error=>"Document Form not found",Details=>"FormID=$FormID"));

	  extract(param_extract(array(
	    FormType=>"string",
	    Caption=>"string",
	    DocClassID=>"int",
	  ),$qform->Top));

	  list ($this->FormTypeClass,$this->FormTypeSubClass)=explode (".",$FormType);
	  $qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if ($qclass) {
	  	$class=$qclass->Top;
		  $ClassCaption=langstr_get($class->Caption);
      $qdocfields=DBQuery ("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID ORDER BY Seq","DocFieldID");
			$this->TableExists=DBQuery("SHOW TABLES LIKE '$class->DocTable'");
			if ($this->TableExists) {
		    $this->IndexInfo=DBQuery ("SHOW INDEX FROM `$class->DocTable`",array("Key_name","Column_name"));
			}
	  } else {$ClassCaption="без класса";}

	  include ("inc.docutils.php");
    _putMenu("FormBrowseFields",&$qclass,$FormID);

	  $Caption=langstr_get($Caption);
	  
	  print "<h1>Редактирование формы #$FormID</h1><h2>$Caption ($ClassCaption)</h2>";
	  $qg=DBQuery ("SELECT * FROM doc_FormGroups WHERE FormID=$FormID ORDER BY Seq","GroupID");
    $_ENV->PutValueSet(array(ValueSetName=>"groups", Recordset=>$qg,CaptionField=>"Caption"));
    $qf=DBQuery ("SELECT * FROM doc_FormFields WHERE FormID=$FormID ORDER BY GroupID,Seq","FormFieldID");
		$this->DetectedDocFieldID=false;
    if ($qf) foreach ($qf->Rows as $FormFieldID=>$FormField) {
    	if (($qg) && (!isset($qg->Rows[$FormField->GroupID]))) $qf->Rows[$FormFieldID]->GroupID=0;
    	$this->DetectedDocFieldID[$FormField->DocFieldID]=true;
    }

    global $_THEME;
   	$ts=$_THEME['TableStyles'][0];
   	list($ts['te'],$ts['ce'])=get_css_pair($ts['Even'],"td");
   	list($ts['to'],$ts['co'])=get_css_pair($ts['Odd'],"td");
   	list($ts['th'],$ts['ch'])=get_css_pair($ts['Top'],"th");

	  $FormTypes=&$_ENV->Cartridges['doc']->Data->FormTypes;
		
   	print "<table border='0' cellpadding='5'><tr valign='top'><td class='bgdown' align='center'><table>";
   	if ($class) print "<tr class='bgup'><td align='right'><b>Класс документов:</b></td><td><a href='".ActionURL("doc.IDocFields.BrowseFields.bm",array(DocClassID=>$DocClassID))."'>$ClassCaption</a>&nbsp;$class->ClassName</td></tr>";
		print "<tr class='bgup'><td align='right'><b>Название формы:</b></td><td>$Caption</td></tr>";
		print "<tr class='bgup'><td align='right'><b>Тип формы:</b></td><td>".$FormTypes[$FormType]."</td></tr>";
		print "<tr class='bgup'><td></td><td><a href='".ActionURL("doc.IForms.Edit.bm",array(FormID=>$FormID))."'>Изменить параметры формы</a></td></tr>";
		print "<tr class='bgup'><td></td><td><a href='javascript:' onClick=\"W.openModal({url:'".
		  ActionURL("doc.IFormFields.EditTemplate.b",array(FormID=>$FormID))."',w:700,h:550})\">Редактировать шаблон отображения</a></td></tr>
			</table>";

		 
		if ($qf) {
			$_ENV->OpenForm(array(ReloadOnOk=>1,Modal=>1,ShowDelete=>1,
			  Action=>ActionURL("doc.IFormFields.UpdateFieldList.b"),Align=>"center", Width=>"100%"));
		  print "<tr><td>";

		 
		 $_ENV->PutFormField(array(Type=>'hidden',Name=>'FormID',Value=>$FormID));
			print "<table width='100%' border='0'><tr>";
			print "<$ts[th] $ts[ch]></$ts[th]>";
			if ($this->FormTypeClass=="list") print " <$ts[th] $ts[ch]>+</$ts[th]> <$ts[th] $ts[ch]>G</$ts[th]>";
			print "<$ts[th] $ts[ch]>Надпись поля</$ts[th]>
			  <$ts[th] $ts[ch]>Тип</$ts[th]>
			  <$ts[th] $ts[ch]>Поле документа</$ts[th]>
			  <$ts[th] $ts[ch]>Порядк ном</$ts[th]></tr>";
			print $this->_getFieldGroup(0,$qg,$qf,$qdocfields,$ts);
			
			# page tabs
			$s2="";
			$OpenedGroupCaption="";
			if ($qg->Rows) foreach ($qg->Rows as $aGroupID=>$FieldGroup) {
				if ($FieldGroup->ParentID!=0) continue;
				if (!$OpenedGroupID) $OpenedGroupID=$aGroupID;
				if ($aGroupID==$OpenedGroupID) {
					$OpenedGroupCaption=langstr_get($FieldGroup->Caption);
					$s2.="<td class='bgupup'><img src='$_THEME[SkinURL]/tri2_off.gif'> $OpenedGroupCaption</td>";
				}
				else $s2.="<td class='bgdown'><img src='$_THEME[SkinURL]/tri1_off.gif'><a href='".ActionURL("doc.IFormFields.BrowseFields.bm",array(FormID=>$FormID,OpenedGroupID=>$aGroupID))."'>".langstr_get($FieldGroup->Caption)."</a></td>";
				$s2.="<td>&nbsp;</td>";
			}
			
			if ($s2) {
				print "<tr><td colspan='10'><br><table border='0' cellspacing='0' cellpadding='4'><tr valign='top'>$s2</tr></table></td></tr>";
			}
			
			if ($OpenedGroupID) {
				print "<tr><td colspan='10' class='bgupup'><table width='100%'><tr><td><br><b>$OpenedGroupCaption</b><br></td><td align='right'><a href='javascript:;' onClick='"
				   ."W.openModal({url:\"".ActionURL("doc.IFormFields.EditGroup.b",array(FormID=>$FormID,GroupID=>$OpenedGroupID))
				   ."\",w:600,h:500,reloadOnOk:1})'>[редактировать]</a></td></tr></table></td></tr>"
				   .$this->_getFieldGroup($OpenedGroupID,$qg,$qf,$qdocfields,$ts);

				 foreach ($qg->Rows as $aGroupID=>$FieldGroup) {
					if ($FieldGroup->ParentID!=$OpenedGroupID) continue;
					print "<tr><td></td><td colspan='10' class='bgdown'><table width='100%'><tr><td><b>".langstr_get($FieldGroup->Caption)."</b></td><td align='right'><a href='javascript:;' onClick='"
					   ."W.openModal({url:\"".ActionURL("doc.IFormFields.EditGroup.b",array(FormID=>$FormID,GroupID=>$aGroupID))
					   ."\",w:600,h:500,reloadOnOk:1})'>[редактировать]</a></td></tr></table></td></tr>"
					   .$this->_getFieldGroup($aGroupID,$qg,$qf,$qdocfields,$ts);
				}
			}
			print "</table>";
			if ($qg) {
				$r=$this->_getGroupsValues($qg,"move_","Переместить в [%s]");
				}
			if ($this->FormTypeClass=="list") {
				$r['group']="G: Сгруппировать поля";
				$r['ungroup']="G: Снять группировку с полей";
				$r['sort']="+: Включить сортировку по полю";
				$r['sortdesc']="-: Включить сортировку по полю (в обратном порядке)";
				$r['unsort']="+: Снять сортировку с полей";
			}
			
#			$r['exclude']="Исключить поля из формы";
	   	print "</td></tr>";$_ENV->CloseForm(array(SubactionNulCaption=>"Обновить порядк ном",SubactionList=>$r));
		} else {
				print "<h2>Нет описания полей</h2>
			<p>Выберите в правой таблице поля из класса документа для регистрации в форме, выберите Зарегистрировать и нажмите Ok</p>";
		}

    print "<table><tr><td>";
    $_ENV->PutButton(array(Kind=>'add',Caption=>"Добавить параметр",OnClick=>"W.openModal({url:'".
      ActionURL("doc.IFormFields.EditField.b",array(FormID=>$FormID))."',w:500,h:500,reloadOnOk:1})"));
    print "</td><td>";
    $_ENV->PutButton(array(Kind=>'add',Caption=>"Добавить группу",OnClick=>"W.openModal({url:'".
      ActionURL("doc.IFormFields.EditGroup.b",array(FormID=>$FormID))."',w:500,h:500,reloadOnOk:1})"));
    print "</td></tr></table>";
	  print "</td><td>";
	  
	  
	  if ($qdocfields) {
	  	
	  	# Check if all fields are registered
	  	$atleastone=false;
	  	foreach($qdocfields->Rows as $DocFieldID=>$DocField) {
				if (!isset($this->DetectedDocFieldID[$DocFieldID])) {$atleastone=true; break;}
	  	}
	  	
	  	if ($atleastone) {
			  $Subactions=array("reg_0"=>"Включить в форму");
			  if ($qg) foreach ($qg->Rows as $GroupID=>$Group) {
			  	$Subactions["reg_$GroupID"]=" &lt;= ".langstr_get($Group->Caption);
			  }
			  print "<h5>Поля документа $ClassCaption</h5>";
			  $_ENV->PrintTable($qdocfields,array(
			    Action=>ActionURL("doc.IFormFields.UpdateFormFields.b"),
			    FormName=>"tabfields",
			    Modal=>1,
			    HiddenFields=>array(reloadOnOk=>1,FormID=>$FormID),
			    Fields=>array(
			      Required=>"*",
			      Caption=>"Поле документа",
			      FieldType=>"Тип",
			      IsProperty=>"Индекс"
			      ),
			    FieldHooks=>array(IsProperty=>tab_IsProperty,Required=>tab_Required),
			    Width=>'100%',
			    ShowCheckers=>true,
			    ShowOk=>true,
			    ColAligns=>array(IsProperty=>'center'),
			    SubactionList=>$Subactions,
			    OnRowFilter=>tabf_TabColumnFilter,
			    OnGetCellStyle=>tabf_TabColumnStyle,
			    ThisObject=>&$this));
			  print "T - текстовая индексация<br>
			   <font face='wingdings'>u</font> - уникальный индекс<br>
			   <font face='wingdings'>v</font> - множественный индекс<br>
			   <font face='wingdings'>Ö</font> - свойство<br>
			   
			   <font color='red'>* - требуемое поле<br>";
	  	}
	  } else {
	  	print "<p class='warning'>Класс документа отсутствует.";
	  }
	  print "</td></tr></table>";
	}

	function tab_IsProperty($DocFieldID,&$row,$f,$a) {
		if ($row->IsProperty) print "<font face='wingdings'>Ö</font>"; else {
			if ($this->IndexInfo) { 
				foreach($this->IndexInfo->Rows as $KeyName=>$data) {
					foreach ($data as $ColumnName=>$data2) {
						if ($ColumnName==$row->FieldName) {
							if ($data2->Non_unique){
								if ($data2->Index_type=='FULLTEXT') print "T"; else print "<font face='wingdings'>v</font>";
							}	else {print "<font face='wingdings'>u</font>";}
						}
					}
				}
			}
		}
	}
	function tab_Required($DocFieldID,&$row,$f,$a) {
		if ($row->Required) print "*";
	}
	function tabf_TabColumnFilter(&$row) {
		if (isset($this->DetectedDocFieldID[$row->DocFieldID])) return false; else return true;
	}
	function tabf_TabColumnStyle($TabFieldName,&$row,$f,$a) {
		if ($row->Required) {return "style='color:#f00000';";}
	}
	function _getFieldGroup ($GroupID,&$qg,&$qf,&$qdocfields,&$ts,$Level=0) {
		$i=0;
		if ($Level>3) return "<tr><td colspan='10'>Ошибка. В дереве групп полей обнаружен цикл</td></tr>"; 
		$s="";
		
		if ($qf)foreach ($qf->Rows as $FormFieldID=>$FormField) {
			$i++;
			if ($i&1) {$td=$ts['te'];$cd=$ts['ce'];} else {$td=$ts['to'];$cd=$ts['co'];}
			$TabFieldType="";
			if ($FormField->GroupID==$GroupID) {
				$FieldTypeText=$FormField->RepresentType;
				if ($FormField->Size) $FieldTypeText.="($FormField->Size)";
				$req=($FormField->Required)?"<font color='red'>*</font>":"";
				if ($qdocfields) {
					$dr=$qdocfields->Rows[$FormField->DocFieldID];
					$s2=langstr_get($dr->Caption);
					if ($dr->FieldName) $s2.="<br><span class='tiny'>$dr->FieldName</span>";
				}
				$s.="<tr>";
				$s.="<$td $cd><input type='checkbox' name='check[$FormFieldID]' value='1'></$td>";
				if ($this->FormTypeClass=="list") {
					$s.="<$td $cd>".(($FormField->OrderBy)?"+":"")."</$td>
				  <$td $cd>".(($FormField->GroupBy)?"G":"")."</$td>";
				}
				
				$s.="<$td $cd><a href='#' onClick=\"W.openModal({url:'".ActionURL("doc.IFormFields.EditField.b",
				array(FormFieldID=>$FormFieldID,FormID=>$FormField->FormID))."',w:500,h:500,reloadOnOk:1});\">".langstr_get($FormField->Caption)."</a>
				<br><span class='tiny'>{"."$FormFieldID}</span></$td>
				<$td $cd>$req$FieldTypeText</$td>
				<$td $cd>$s2</$td>
				<$td $cd>".$_ENV->PutFormField(array(Size=>6,Style=>"clear",ToString=>1,Type=>'int',Name=>"Seq[$FormFieldID]",Value=>$FormField->Seq))."</$td></tr>";
			}
		}
		return $s;
	}

	function _getGroupsValues(&$qg,$valueprefix="",$captionformat="%s") {
		$result=false;
		$prefix=""; for ($i=0;$i<$Level;$i++) $prefix.="- ";
		
		foreach ($qg->Rows as $GroupID=>$Group) {
			if ($Group->ParentID==0) {
				$d[$GroupID][]=array($GroupID,"",$prefix.langstr_get($Group->Caption));
			}
		}
		foreach ($qg->Rows as $GroupID=>$Group) {
			if ($Group->ParentID!=0) {
				$d[$Group->ParentID][]=array($GroupID,"- ",$prefix.langstr_get($Group->Caption));
			}
		}

		foreach ($d as $GroupID=>$stack) {
			foreach ($stack as $item) {
				$result[$valueprefix.$item[0]]=sprintf($captionformat,$item[1].$item[2]);
			}
		}
		return $result;
	}
	
	function EditField($args)
	{
	  extract(param_extract(array(
	    FormFieldID=>'int',
	    FormID=>'int'
	  ),$args));
		global $cfg;
		
		$qdocfield=$qf=false;
		if (!$FormID)
		{
			return array(Error=>"Undefilned argument FormID");
		}
		$qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		if (!$qform) {
			return array(Error=>"Form not found",Details=>"FormID=$FormID");
		}
		
		if ($FormFieldID)
		{
			$qf=DBQuery ("SELECT * FROM doc_FormFields WHERE FormFieldID=$FormFieldID AND FormID=$FormID");
			if (!$qf)
			{
				return array (Error=>"Field not found in the form",Details=>"FormFieldID=$FormFieldID and FormID=$FormID");
			}
			$FormField=&$qf->Top;
		}

		$Form=&$qform->Top;
		
		
		if ($Form->DocClassID) {
			$qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$Form->DocClassID");
			$qclassfields=DBQuery("SELECT DocFieldID,Caption FROM doc_Fields WHERE DocClassID=$Form->DocClassID","DocFieldID");
			if ($qclassfields) {
		    $_ENV->PutValueSet(array(ValueSetName=>"docfields", Recordset=>$qclassfields,CaptionField=>"Caption"));
				
			}
		}

/*		if (!$this->TableExists) {
			$this->TableExists=DBQuery("SHOW TABLES LIKE '$DocTable'");
			if ($this->TableExists) {
				$this->ColumnInfo=DBQuery ("SHOW COLUMNS FROM $DocTable","Field");
			} else {
				print "Таблица '$DocTable' не найдена";
				return;
			}
		}*/
    $FormTypes=&$_ENV->Cartridges['doc']->Data->FormTypes;
		print "<h2>".$FormTypes[$Form->FormType]." [".langstr_get($Form->Caption)."]</h2>";
    if ($FormFieldID) {
    	$Caption=langstr_get($qf->Top->Caption);
			$doc=param_extract(array(
			  Caption=>'string',
			  RepresentType=>'string',
			  Required=>'int',
			  Seq=>'int',
			  DocFieldID=>'int',
			  Size=>'int',
			  Notice=>'string',
			  Parameters=>'string',
			  DependOn=>'string',
			  GroupID=>'int',
			  DefaultValue=>'string',
			  ),$qf->Top);
    } else {
    	print "<h2>Новый параметр</h2>";
    	$IsProperty=1;
    	$q=DBQuery("SELECT MAX(Seq) AS MaxSeq FROM doc_FormFields WHERE FormID=$FormID");
    	$doc=array(Size=>0);
    	if ($q) $doc['Seq']=intval($q->Top->MaxSeq)+10;
    }
    
		if (!$doc['FieldType']) $doc['FieldType']='string';

		if (!$qg) {
		  $qg=DBQuery ("SELECT GroupID,ParentID,Caption FROM doc_FormGroups WHERE FormID=$FormID ORDER BY Seq","GroupID");
		  if ($qg) {
			  $v=$this->_getGroupsValues($qg);
		    $_ENV->PutValueSet(array(ValueSetName=>"groups",Values=>$v));
		  	
		  }
    }
    
    $FieldTypes=$_ENV->Cartridges['doc']->Data->FieldTypes;
    
#    if ($IsProperty) {unset($FieldTypes['bindto']);}
#    else {unset($FieldTypes['bindfld']); unset($FieldTypes['bindto']); unset($FieldTypes['collection']);}
    $FieldTypeSet=false;
    foreach ($_ENV->Cartridges['doc']->Data->FieldTypes as $k=>$v) {
    	$FieldTypeSet[$k]=(is_array($v))?$v['Caption']:$v;
	    if ($Form->FormType=="search"){
	    	foreach ($_ENV->Cartridges['doc']->Data->SearchTypes as $k2=>$v2) {
    			list ($c2,$s2)=explode(".",$k2);
	    		if ($c2==$k) {
	    			$s=(is_array($v2))?$v2['Caption']:$v2;
						$FieldTypeSet[$k2]="- ".$s;
	    		}
	    	}
	    }
    }
    $_ENV->PutValueSet(array(ValueSetName=>"fieldtypes", Values=>$FieldTypeSet));
		$_ENV->OpenForm(array(ModalOkOnOk=>1,Modal=>1,ShowCancel=>1,
		  Action=>ActionURL("doc.IFormFields.SaveField"),Align=>"center", Width=>"100%"));
		$_ENV->PutFormField(array(Name=>"FormID",Value=>$FormID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"FormFieldID",Value=>$FormFieldID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Caption=>"Надпись",Type=>"langstring",Value=>$doc['Caption'],Required=>1));
		$_ENV->PutFormField(array(Name=>"Notice",Size=>40,Caption=>"Вспомогательный текст",Type=>"langtext",Value=>$doc['Notice']));

		if ($qclassfields) {
	  	$_ENV->PutFormField(array(Name=>"DocFieldID",Size=>40,
	  	  Caption=>"Поле документа",
	  	  Value=>$doc['DocFieldID'],
	  	  ValueSetName=>"docfields",
	  	  NullCaption=>"[это поле не связано с документом]",
	  	  Type=>"droplist"));
    	
    }
		$_ENV->PutFormField(array(Name=>"GroupID",Size=>40,Caption=>"Группа полей",
  		NullCaption=>"[Главная группа вне страниц]",Type=>"droplist",ValueSetName=>"groups",Value=>$doc['GroupID']));
		$_ENV->PutFormField(array(Name=>"RepresentType",Size=>40,Caption=>"Отображать как тип",Type=>"droplist",ValueSetName=>"fieldtypes",Value=>$doc['RepresentType'],Required=>1,DefaultValue=>"string"));
		$_ENV->PutFormField(array(Name=>"Required",Caption=>"Обязательное",Type=>"checkbox",Value=>$doc['Required']));
		$_ENV->PutFormField(array(Name=>"Size",Size=>10,Caption=>"Размер поля (в символах)",Notice=>"При вводе данных поле будет ограничивать ввод указанным размером. Введите 0 для снятия ограничения",Type=>"int",Value=>$doc['Size']));
		$_ENV->PutFormField(array(Name=>"Parameters",Size=>40,Caption=>"Параметры",Type=>"string",Value=>$doc['Parameters']));
		$_ENV->PutFormField(array(Name=>"DefaultValue",Size=>20,Caption=>"Значение по-умолчанию",Type=>"string",Value=>$doc['DefaultValue']));
		$_ENV->PutFormField(array(Name=>"DependOn",Size=>40,Caption=>"Зависит от",Type=>"string",Value=>$doc['DependOn']));
		$_ENV->PutFormField(array(Name=>"Seq",Size=>10,Caption=>"Порядковый номер",Type=>"int",Value=>$doc['Seq']));
		$_ENV->CloseForm();
	}

	function SaveField($args)	{
	  extract(param_extract(array(
	    FormID=>'int',
	    FormFieldID=>'int',
	  ),$args));

	  if (!$FormID) {
			return array(Error=>"Undefined argument FormID");
	  }
	  
	  $doc=param_extract(array(
		  Caption=>'string',
		  RepresentType=>'string',
		  Required=>'int',
		  Seq=>'int',
		  DocFieldID=>'int',
		  Size=>'int',
		  Notice=>'string',
		  Parameters=>'string',
		  DependOn=>'string',
		  GroupID=>'int',
		  DefaultValue=>'string',
	  ),$args);
	  
		if (!$FormFieldID)
		{
			$FormFieldID=DBGetID("doc.FormField");
			if (DBInsert(array(Table=>"doc_FormFields",Values=>$doc+array(FormID=>$FormID,FormFieldID=>$FormFieldID)))) return array(ModalResult=>true);
		} else {
			if (DBUpdate(array(Table=>"doc_FormFields",Values=>$doc,Keys=>array(FormFieldID=>$FormFieldID,FormID=>$FormID)))) return array(ModalResult=>true);
		}
	}
	
	function UpdateFormFields($args) {
		extract(param_extract(array(
		action=>'string',
		check=>'int_checkboxes',
		subaction=>'string',
		FormID=>'int'
		),$args));
		global $cfg;
		
		if ($check && ($action=='ok') && (substr($subaction,0,4)=="reg_"))
		{
			$GroupID=intval(substr($subaction,4));
			$r=$this->_registerDocFields($FormID,array_keys($check),$GroupID);
		}
		if ($r['Error']) return $r;
		return array(ModalResult=>true);
	}

	function _registerDocFields($FormID,$DocFields,$GroupID) {
		$qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		if (!$qform) {
			return array(Error=>"Form not found",Details=>"FormID=$FormID");
		}
		$DocClassID=$qform->Top->DocClassID;
	  $qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
		if (!$qclass) {
			return array(Error=>"Class not found",Details=>"DocClassID=$DocClassID");
		}
    $qdocfields=DBQuery ("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID ORDER BY Seq","DocFieldID");
		$qmax=DBQuery("SELECT MAX(Seq) AS MaxSeq FROM doc_FormFields WHERE FormID=$FormID");
		$MaxSeq=intval($qmax->Top->MaxSeq);
		$cnt=count($DocFields);
		$FormFieldID=DBGetID("doc.Form","","",$cnt); #reserve id for $cnt fields
		foreach ($DocFields as $DocFieldID) {
			$r=&$qdocfields->Rows[$DocFieldID];
			$MaxSeq+=10;
			
			if (!DBInsert(array(Debug=>0,Table=>"doc_FormFields",Values=>array(
				FormFieldID=>$FormFieldID,
				DocFieldID=>$DocFieldID,
				FormID=>$FormID,
			  Caption=>$r->Caption,
			  GroupID=>$GroupID,
			  Required=>($qform->Top->FormType=='edit')?$r->Required:0,
			  RepresentType=>$r->FieldType,
			  Seq=>$MaxSeq,
			  Size=>$r->Size,
			  )))) return array(Error=>"Registration failed");
			$FormFieldID++;
		}
		return true;
	}
	
	function EditGroup($args) {
		extract(param_extract(array(
	    GroupID=>'int',
	    FormID=>'int',
	  ),$args));
		global $cfg;

		if (!$FormID)
		{
			return array(Error=>"Undefined argument FormID");
		}
		
		if ($GroupID)
		{
			$qg=DBQuery ("SELECT * FROM doc_FormGroups WHERE GroupID=$GroupID AND FormID=$FormID");
			if (!$qg)
			{
				return array (Error=>"Field group not found",Details=>"GroupID=$GroupID");
			}
			$Group=&$qg->Top;
		}
		$qform=DBQuery("SELECT * FROM doc_Forms WHERE FormID=$FormID");
		if (!$qform) {
			return array(Error=>"Form not found",Details=>"FormID=$FormID");
		}
		$Form=&$qform->Top;
		print "<h1>".langstr_get($Form->Caption)."</h1>";
    if ($GroupID) {
    	$Caption=langstr_get($Group->Caption);
    	print "<h2>Редактирование группы '$Caption'</h2>";
    	
			$doc=param_extract(array(
			  ParentID=>'int',
			  Caption=>'string',
			  Header=>'string',
			  Footer=>'string',
			  Seq=>'int',
			  Closable=>'int',
			  CloseIfEmpty=>'int',
			  TriggerEnable=>'string',
			  Seq=>'int',
			  ),$Group);
    } else {print "<h2>Добавление группы</h2>";
			$qmax=DBQuery("SELECT MAX(Seq) AS MaxSeq FROM doc_FormGroups WHERE FormID=$FormID");
			$MaxSeq=intval($qmax->Top->MaxSeq)+10;
      $doc=array(Seq=>$MaxSeq);
    }
		
		if ($GroupID) $qchilds=DBQuery("SELECT COUNT(*) AS Counter FROM doc_FormGroups WHERE ParentID=$GroupID");
		if (!$GroupID || ($qchilds->Top->Counter==0)) {
			$qplist=DBQuery ("SELECT GroupID,Caption
			  FROM doc_FormGroups WHERE ParentID=0 AND GroupID<>$GroupID AND FormID=$FormID ORDER BY Seq","GroupID");
		}
    $_ENV->PutValueSet(array(ValueSetName=>"pages", Recordset=>$qplist,CaptionField=>"Caption"));
		$_ENV->OpenForm(array(ModalOkOnOk=>1,Modal=>1,ShowCancel=>1,
		  Action=>ActionURL("doc.IFormFields.SaveGroup"),Align=>"center", Width=>"100%"));
		$_ENV->PutFormField(array(Name=>"FormID",Value=>$FormID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"GroupID",Value=>$GroupID,Type=>"hidden"));
  	$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Caption=>"Заголовок группы",Type=>"langstring",Value=>$doc['Caption'],Required=>1));
  	$_ENV->PutFormField(array(Name=>"Header",Size=>40,Caption=>"Текст в начале группы",Type=>"langtext",Value=>$doc['Header']));
  	$_ENV->PutFormField(array(Name=>"Footer",Size=>40,Caption=>"Текст в конце группы (примечания)",Type=>"langtext",Value=>$doc['Footer']));
		if ($qplist) {
			$_ENV->PutFormField(array(Name=>"ParentID",NullCaption=>"[Сделать отдельной страницей]",Size=>40,Caption=>"Страница",Notice=>"Группа-страница, в которую входит данная",Type=>"droplist",ValueSetName=>"pages",Value=>$doc['ParentID']));
		} else {
			$_ENV->PutFormField(array(Name=>"ParentID",Value=>$doc['ParentID'],Type=>"hidden"));
		}
		$_ENV->PutFormField(array(Name=>"Seq",Size=>10,Caption=>"Порядковый номер",Type=>"int",Value=>$doc['Seq']));
		$_ENV->PutFormField(array(Name=>"TriggerEnable",Size=>20,Caption=>"Триггер показа группы",Notice=>"Если результат вычислений будет истина, то группа откроется",Type=>"string",Value=>$doc['TriggerEnable']));
		$_ENV->PutFormField(array(Name=>"Closable",Caption=>"Закрываемая группа",Notice=>"Рядом с названием группы будет галочка, раскрывающая все поля в группе",Type=>"checkbox",Value=>$doc['Closable']));
		$_ENV->PutFormField(array(Name=>"CloseIfEmpty",Caption=>"Группа закрыта по-умолчанию",Notice=>"Если поля группы пусты, то группа будет закрыта",Type=>"checkbox",Value=>$doc['CloseIfEmpty']));
		$args2=false;
		if ($GroupID) {$args2=array(
		Buttons=>array(array(
			Kind=>'delete',
			Caption=>"Удалить группу",
			OnClick=>"W.openModal({url:'".ActionURL("doc.IFormFields.DeleteGroup.b",array(FormID=>$FormID,GroupID=>$GroupID))."',w:300,h:200,modalOkOnOk:1})")
			));
			}
		$_ENV->CloseForm($args2);
	}
	
	function SaveGroup($args) {
	  extract(param_extract(array(
	    FormID=>'int',
	    GroupID=>'int',
	  ),$args));

	  if (!$FormID) {
			return array(Error=>"Undefined argument FormID");
	  }
	  $d=param_extract(array(
	    Caption=>'string',
	    Header=>'string',
	    Footer=>'string',
	    ParentID=>'int',
		  Closable=>'int',
		  CloseIfEmpty=>'int',
		  TriggerEnable=>'string',
	    Seq=>'int'
	  ),$args);
	  
	  if (!$GroupID)
		{
			$GroupID=DBGetID("doc.FormGroup");
			if (DBInsert(array(Table=>"doc_FormGroups",Values=>$d+array(FormID=>$FormID,GroupID=>$GroupID)))) 
				return array(ModalResult=>true);
		} else {
			if (DBUpdate(array(Table=>"doc_FormGroups",Values=>$d,Keys=>array(FormID=>$FormID,GroupID=>$GroupID)))) 
				return array(ModalResult=>true);
		}
		return false;
	}
	
	function UpdateFieldList($args) {
		extract(param_extract(array(
			action=>'string',
			check=>'int_checkboxes',
			subaction=>'string',
			FormID=>'int'
		),$args));

		
#		print_r($args);
		#return;
#		if (is_array($check)) 
		  $keys=implode(",",array_keys($check));
		if (substr($subaction,0,5)=='move_') {
			if (!$check) return array(ModalResult=>'cancel');
			$NewGroupID=intval(substr($subaction,5));
			if (!DBExec("UPDATE doc_FormFields SET GroupID=$NewGroupID WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($action=='delete') {
			$keys=implode(",",array_keys($check));
			if (!$check) return array(ModalResult=>'cancel');
			if (!DBExec("DELETE FROM doc_FormFields WHERE FormID=$FormID AND FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=="group") {
			if (!DBExec("UPDATE doc_FormFields SET GroupBy=1 WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=="ungroup") {
			if (!DBExec("UPDATE doc_FormFields SET GroupBy=0 WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=="sort") {
			if (!DBExec("UPDATE doc_FormFields SET OrderBy=1 WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=="sortdesc") {
			if (!DBExec("UPDATE doc_FormFields SET OrderBy=2 WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=="unsort") {
			if (!DBExec("UPDATE doc_FormFields SET OrderBy=0 WHERE FormFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} else {
			if (is_array($args['Seq'])) foreach ($args['Seq'] as $aFormFieldID=>$NewNo) {
				$NewNo=intval($NewNo);
				DBUpdate (array(
				Table=>"doc_FormFields",Keys=>array(FormFieldID=>$aFormFieldID,FormID=>$FormID),
				Values=>array(Seq=>$NewNo)));
			}
			return array(ModalResult=>true);
		}
		
	}
	
	function DeleteGroup ($args) {
		extract(param_extract(array(
			GroupID=>'int',
			FormID=>'int'
		),$args));
		if (!DBUpdate(array(Table=>'doc_FormFields',Keys=>array(FormID=>$FormID,GroupID=>$GroupID),Values=>array(GroupID=>0)))) return false;
		if (!DBUpdate(array(Table=>'doc_FormGroups',Keys=>array(FormID=>$FormID,ParentID=>$GroupID),Values=>array(ParentID=>0)))) return false;
		if (!DBExec ("DELETE FROM doc_FormGroups WHERE FormID=$FormID AND GroupID=$GroupID")) return false;
		return array(ModalResult=>true);
	}

	function EditTemplate($args) {
		extract(param_extract(array(
			FormID=>'*int'
		),$args));

		$qform=DBQuery ("SELECT FormType,DisplayFormat FROM doc_Forms WHERE FormID=$FormID");
		if (!$qform) return (array(Error=>"Document Form not found",Details=>"FormID=$FormID"));
		list($type,$subtype)=explode ('.',$qform->Top-> FormType);
	  $gentype=2;
	  if ($type=='list') $gentype=1;
		$_ENV->OpenForm(array(ModalOkOnOk=>1,ShowCancel=>0,Name=>"form1",
		  Action=>ActionURL("doc.IFormFields.UpdateTemplate.b"),Align=>"center", Width=>"100%",Style=>"vertical",
		  Buttons=>array(
		  	array(Kind=>"generate",Caption=>"Сгенерировать",OnClick=>"generate($gentype)"),
		    array(Kind=>"preview",Caption=>"Предпросмотр",OnClick=>"preview()"),
		  )));
	  $_ENV->PutFormField(array(Type=>'hidden',Name=>'FormID',Value=>$FormID));
		$_ENV->PutFormField(array(Type=>'text',Caption=>"Текст шаблона",Size=>100,Width=>'100%',Rows=>20,Name=>'DisplayFormat',Value=>$qform->Top->DisplayFormat));
		$_ENV->CloseForm(array(ShowCancel=>1));
		print "<form method='post' name='tools' target='iframe1' action='".ActionURL("doc.IFormFields.TemplateTools.b")."'>
		<input type='hidden' name='FormID' value='$FormID'>
		<input type='hidden' name='templatetext2' value=''>
		<input type='hidden' name='action' id='toolsAction' value=''></form>";
		?><iframe name='iframe1' width='100%' height='200'>Error: IFRAME not supported</iframe><script>
		function generate(mode) {
			
			var f=document.getElementById('tools');
			var a=document.getElementById('toolsAction');
      a.value='generate'+mode;
      f.submit();
		}
		function preview() {
			var f=document.getElementById('tools');
			var a=document.getElementById('toolsAction');
			var t1=document.getElementById('lfv_form1DisplayFormat');
			var t2=document.getElementById('templatetext2');
			t2.value=t1.value;
      a.value='preview';
      f.submit();
		}
		</script><?
  }
	function TemplateTools($args) {
		extract(param_extract(array(
			FormID=>'*int',
			templatetext2=>'nonesc_string',
			action=>'string'
		),$args));
		if ($action=='preview') {
			print "<table border='1'><tr valign='top'>";
			print $templatetext2;
			print "</tr></table>";
			return;
		}
		
# 'generate'
		$qff=DBQuery ("SELECT ff.FormFieldID, ff.RepresentType, ff.Size, ff.Required, 
			ff.GroupID, ff.GroupBy, ff.OrderBy, ff.Caption, ff.Notice, ff.DocFieldID, ff.Parameters, ff.DependOn,
			df.FieldType,df.IsProperty, df.TargetDocClass, df.FieldName
		  FROM doc_FormFields AS ff LEFT JOIN doc_Fields AS df ON ff.DocFieldID = df.DocFieldID
			WHERE FormID=$FormID ORDER BY ff.Seq","FormFieldID");
		#$qff->Dump();
		$qfg=DBQuery("SELECT * FROM doc_FormGroups WHERE FormID=$FormID","GroupID");
		#$qfg->Dump();
		
		$s="";
		if ($action=='generate1') { # LIST OF DOCUMENTS
			if ($qfg) {
				$co1="";
				foreach ($qff->Rows as $FormFieldID=>$ffield) {
					if ($ffield->GroupID==0) {
						$sz=$ffield->Size; if ($sz) $sz=":$sz"; else $sz="";
						$col.="<if exists='{"."$ffield->FieldName}'>{"."$ffield->FieldName:$ffield->RepresentType$sz}<br/></if>\n";
					}
				}
				if ($col) $s.="<td title='".langstr_get($ffield->Caption)."'>$col</td>\n\n";
				foreach ($qfg->Rows as $GroupID=>$group) {
					$col="";
					foreach ($qff->Rows as $FormFieldID=>$ffield) {
						if ($ffield->GroupID==$GroupID) {
							$sz=$ffield->Size; if ($sz) $sz=":$sz"; else $sz="";
							$col.="<if exists='{"."$ffield->FieldName}'>{"."$ffield->FieldName:$ffield->RepresentType$sz}<br/></if>\n";
						}
					}
					if ($col) $s.="<td title='".langstr_get($group->Caption)."'>$col</td>\n\n"; 
				}
				
			} else {
				foreach ($qff->Rows as $FormFieldID=>$ffield) {
					$sz=$ffield->Size; if ($sz) $sz=":$sz"; else $sz="";
					$s.="<td title='".langstr_get($ffield->Caption)."'><if exists='{"."$ffield->FieldName}'>{"."$ffield->FieldName:$ffield->RepresentType$sz}</if></td>\n";
				}
			}
		} elseif ($action=='generate2') { # ONE DOCUMENT
			$c="";
			foreach ($qff->Rows as $FormFieldID=>$ffield) {
				$sz=$ffield->Size; if ($sz) $sz=":$sz"; else $sz="";
				$c.="$ffield->Caption<br/>\n{"."$ffield->FieldName:$ffield->RepresentType$sz}<br/><br/>\n\n";
				
			}
			$s="<tr><td>$c</td></tr>";
		}
		
		print "<pre>";
		print htmlspecialchars($s);
		
	}
	function UpdateTemplate($args) {
		extract(param_extract(array(
			FormID=>'*int',
			DisplayFormat=>'nonesc_string',
			action=>'string'
		),$args));

		if (!DBUpdate(array(Debug=>0,Table=>'doc_Forms',
		  Values=>array(DisplayFormat=>$DisplayFormat),
		  Keys=>array(FormID=>$FormID)))) return;
		return array(ModalResult=>true);
		
	}
}
?>