<?
class doc_IDocFields
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentDesigner=>"BrowseFields,BrowseContent,EditField,Save,RegisterTableFields");

	var $DocTableFields;
	var $SystemFields;
	
	
	
	function BrowseContent($args) {
	  extract(param_extract(array(
			DocClassID=>'int',
	  	PageNo=>'int=1',
	  	RowsPerPage=>'int=10',
	  ),$args));

	  $qcl=DBQuery ("SELECT Caption,DocTable,IDField,ClassName FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qcl) return (array(Error=>"Document Class not found",Details=>"DocClassID=$DocClassID"));
	  $DocTable=$qcl->Top->DocTable;
	  $DocCaption=langstr_get($qcl->Top->Caption);
	  $IDField=$qcl->Top->IDField;
	  $qf=DBQuery("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID ORDER BY Seq",$DocFieldID);
	  print "<h1>Список документов класса ".$qcl->Top->ClassName."</h1>";
	  print "<h2>$DocCaption</h2>";
    
    include ("inc.docutils.php");
    _putMenu("BrowseContent",&$qcl);
    
    $idfound=false;
    foreach ($qf->Rows as $DocFieldID=>$DocField) {
    	if (!$DocField->IsProperty) 
    	{
    		if ($IDField==$DocField->FieldName) $idfound=true;
    		$fields[$DocField->FieldName]=langstr_get($DocField->Caption);
    		$qstr.=(($qstr)?",":"").$DocField->FieldName;
    	}
		}
    if (!$idfound) $qstr.=(($qstr)?",":"")."$IDField";

		$qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM $DocTable");
		$s="SELECT $qstr FROM $DocTable LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
		$qdata=DBQuery ("SELECT $qstr FROM $DocTable LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage",$IDField);
		
    $_ENV->PrintTable($qdata,array(
	    Fields=>$fields,
      Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
	    Width=>'100%',
	    #ShowDelete=>true,
	    ShowCheckers=>false,
	    #ButtonEdit=>array(KeyName=>$IDField,ModalWindowAction=>"doc.IDocData.Edit.b",Width=>680,Height=>500),
	    ThisObject=>&$this));
		
	}

	function BrowseFields($args)
	{
	  extract(param_extract(array(
			DocClassID=>'int',
	  ),$args));
	    		
	  $qcl=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
	  if (!$qcl) return (array(Error=>"Document Class not found",Details=>"DocClassID=$DocClassID"));
	  extract(param_extract(array(
	    Caption=>"string",
	    DocTable=>"string",
	    IDField=>"string",
	    ClassName=>"string",
	    IdentityMethod=>"int",
	    UserIDField=>"string",
	    LangField=>"string",
	    ModifyTimeField=>"string",
	    UpdateTimeField=>"string",
	    BindToField=>"string",
	    HistoryField=>"string",
	    StatusField=>"string",
	  ),$qcl->Top));

	  $Caption=langstr_get($Caption);
	  $IdentityMethods=$_ENV->Cartridges['doc']->Data->IdentityMethods;
    
	  $this->SystemFields=array(
	    "Поле идентификации"=>$IDField,
	    "Поле указания языка"=>$LangField,
	    "Поле времени обновления"=>$UpdateTimeField,
	    "Поле времени редактирования"=>$ModifyTimeField,
	    "Пользователь редактор"=>$UserIDField,
	    "Поле прикрепления к папкам"=>$BindToField,
	    "Поле истории"=>$HistoryField,
	    "Поле статуса"=>$StatusField);
	  
	  if (!$DocTable) {
	  	return array(Message=>"Сначала укажите таблицу документов",Details=>$qcl->Top->ClassName);
	  }
	  print "<h1>Структура документа ".$qcl->Top->ClassName."</h1>";
	  print "<h2>$Caption</h2>";
    include ("inc.docutils.php");
    _putMenu("BrowseFields",&$qcl);
		$qf=DBQuery ("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID ORDER BY Seq","DocFieldID");
		$this->DocTableFields=false;
		
		$this->TableExists=DBQuery("SHOW TABLES LIKE '$DocTable'");
		if ($this->TableExists) {
			$this->ColumnInfo=DBQuery ("SHOW COLUMNS FROM $DocTable","Field");
	    $this->IndexInfo=DBQuery ("SHOW INDEX FROM $DocTable",array("Key_name","Column_name"));
		}
		
    if ($qf) foreach ($qf->Rows as $DocFieldID=>$DocField) {
    	if (!$DocField->IsProperty) $this->DocTableFields[strtoupper($DocField->FieldName)]=true;
    }

    global $_THEME;
    
   	$ts=$_THEME['TableStyles'][0];
   	list($ts['te'],$ts['ce'])=get_css_pair($ts['Even'],"td");
   	list($ts['to'],$ts['co'])=get_css_pair($ts['Odd'],"td");
   	list($ts['th'],$ts['ch'])=get_css_pair($ts['Top'],"th");

		$s="";
		foreach ($this->SystemFields as $FieldCaption=>$Field) {
			if ($Field) {
				$c=$Field;
				if ($Field==$IDField) {$c="<font color='blue'><b>$c</b></font>";}
				$s.="<tr class='bgup'><td align='right'><b>$FieldCaption:</b></td><td>$c</td></tr>";
			}
		}
			
		
   	print "<table border='0' cellpadding='5'><tr valign='top'><td class='bgdown' align='center'>
		<table>
			<tr class='bgup'><td align='right'><b>Класс:</b></td><td>".$qcl->Top->ClassName."</td></tr>
			<tr class='bgup'><td align='right'><b>Название:</b></td><td>$Caption</td></tr>
			<tr class='bgup'><td align='right'><b>Метод идентификации:</b></td><td>".$IdentityMethods[$qcl->Top->IdentityMethod]."</td></tr>";
    if ($s) print $s; #print "<table><tr><th>Системное поля</th><th>Поле</th></tr>$s</table>";			
		print "<tr class='bgup'><td></td><td><a href='javascript:' onClick=\"W.openModal({url:'".
		  ActionURL("doc.IDocClasses.Edit.b",array(DocClassID=>$DocClassID))."',w:680,h:450,reloadOnOk:1})\">Изменить</a></td></tr>
			</table>";

		 
		if ($qf) {
			$Subactions['update']="Обновить порядок";
			$Subactions['exclude']="Исключить поля из класса";
		  $_ENV->PrintTable($qf,array(
		    Action=>ActionURL("doc.IDocFields.UpdateFieldList.b"),
		    Modal=>1,
		    HiddenFields=>array(reloadOnOk=>1,DocClassID=>$DocClassID),
		    Fields=>array(
		      Caption=>"Надпись поля",
		      FieldType=>"Тип поля",
		      FieldName=>"Поле таблицы",
		      Seq=>"Порядк.ном",
		      ),
		    FieldHooks=>array(FieldType=>tab_DocFieldType,FieldName=>tab_DocFieldName),
		    Width=>'100%',
		    FieldTypes=>array(
		      Seq=>'inputint:6',
		      Caption=>array(Type=>'langstring',Modal=>1,Action=>"doc.IDocFields.EditField.b",Width=>500,Height=>500,KeyName=>"DocFieldID")
		      ),
		    ColWidths=>array(FieldType=>'30%'),

		    ShowCheckers=>true,
		    ShowOk=>true,
		    SubactionList=>$Subactions,
		    SubactionDefault=>'update',
		    ThisObject=>&$this));
		} else {
				print "<h2>Нет описания полей</h2>Для документа не определены поля</h6>
			<p>Выберите в правой таблице поля из таблицы для регистрации в классе документа, выберите Зарегистрировать и нажмите Ok</p>";
		}

    print "<table><tr><td>";
    $_ENV->PutButton(array(Kind=>'add',Caption=>"Добавить свойство",OnClick=>"W.openModal({url:'".ActionURL("doc.IDocFields.EditField.b",array(DocClassID=>$DocClassID))."',w:500,h:500,reloadOnOk:1})"));
    print "</td><td>";
    $_ENV->PutButton(array(Kind=>'add',Caption=>"Добавить группу",OnClick=>"W.openModal({url:'".
      ActionURL("doc.IDocFields.EditGroup.b",array(DocClassID=>$DocClassID))."',w:500,h:500,reloadOnOk:1})"));
    print "</td></tr></table>";
	  print "</td><td>";
	  
	  
	  if ($this->TableExists) {
	  	# Check if all fields are registered
	  	$atleastone=false;
	  	foreach($this->ColumnInfo->Rows as $FieldName=>$Field) {
	  		if (array_search($FieldName,$this->SystemFields)!==false) continue;
				if (!isset($this->DocTableFields[strtoupper($FieldName)])) {$atleastone=true; break;}
	  	}
	  	
	  	if ($atleastone) {
			  $Subactions=array("reg_0"=>"Зарегистрировать ");
			  if ($this->qg) foreach ($this->qg->Rows as $GroupID=>$Group) {
			  	$Subactions["reg_$GroupID"]=" &lt;= ".langstr_get($Group->Caption);
			  }
			  print "<h6>Таблица-хранитель документов: '$DocTable'</h6>";
			  $_ENV->PrintTable($this->ColumnInfo,array(
			    Action=>ActionURL("doc.IDocFields.RegisterTableFields.b"),
			    Modal=>1,
			    HiddenFields=>array(reloadOnOk=>1,DocClassID=>$DocClassID),
			    Fields=>array(
			      Primary=>"PK",
			      Required=>"*",
			      Field=>"Поле таблицы",
			      Type=>"Тип",
			      ),
			    FieldHooks=>array(Primary=>tab_Primary,Required=>tab_Required),
			    Width=>'100%',
			    ShowCheckers=>true,
			    HideSubmit=>1,
			    Buttons=>array(array(Caption=>"Зарегистрировать",FormAction=>'register',Kind=>'ok')),
			    OnRowFilter=>tabf_TabColumnFilter,
			    OnGetCellStyle=>tabf_TabColumnStyle,
			    ThisObject=>&$this));
	  	}
	  } else {
	  	print "<p class='warning'>Таблица документа '$DocTable' отсутствует. Укажите другую таблицу или зарегистрируйте её</p>";
	  }
	  print "</td></tr></table>";
	}
	function tab_DocFieldName($DocFieldID,&$DocField,$f,$a) {
		$FieldName=$FieldCaption=$DocField->FieldName;
		$FieldType=$DocField->FieldType;
		
		if ($DocField->IsProperty) {
			$FieldCaption="<font color='green'>$TabFieldType</font>";
		
		} else {
			if (!isset($this->ColumnInfo->Rows[$FieldName])) $FieldCaption="<font color='red'>Error: $FieldName is absent</font>";
			else {
				$TabFieldType=$this->ColumnInfo->Rows[$FieldName]->Type;
				$TabFieldKey=$this->ColumnInfo->Rows[$FieldName]->Key;
				if ($TabFieldKey=="PRI") $FieldCaption="<font color='#0000e0'><b>$FieldName</b></font>";
				$FieldCaption.="<br><span class='notice'>$TabFieldType</span>";
			}
		}
		
		if ($DocField->TargetDocClass) {
			if (!$this->Classes[$DocField->TargetDocClass]){
				$this->Classes[$DocField->TargetDocClass]=DBQuery("SELECT ClassName,Caption FROM doc_Classes WHERE DocClassID=$DocField->TargetDocClass");
			}
			$FieldCaption.="<br><font color='green'>@".$this->Classes[$DocField->TargetDocClass]->Top->ClassName."</font>";
		}

		print $FieldCaption;
	}

	function tab_DocFieldType($DocFieldID,&$DocField,$f,$a) {
		$FieldType=$DocField->FieldType;
		$req=($DocField->Required)?"<font color='red'>*</font>":"";
		$FieldTypeText=$FieldType;
		switch ($FieldType) {
			case 'int': case 'string': if ($DocField->Size) $FieldTypeText.="($DocField->Size)";break;
			case 'float': case 'decimal': if ($DocField->Size) $FieldTypeText.="($DocField->Size".(($DocField->Decimals)?",".$DocField->Decimals:"").")";break;
			case 'enum': $FieldTypeText="<a href='#' onClick='W.openModal({url:\"".ActionURL("doc.IDocFields.EditEnum.b",array(DocFieldID=>$DocFieldID))."\",w:450,h:400});'>Список</a>";break;
			case 'collection':	$FieldTypeText="collection[]"; break;
			case 'document':	$FieldTypeText="●-->"; break;
			case 'page':	$FieldTypeText="-->jsb.Page/$DocField->TargetPage"; break;
		}
		if ($DocField->TargetDocClass) {
			if (!$this->Classes[$DocField->TargetDocClass]){
				$this->Classes[$DocField->TargetDocClass]=DBQuery("SELECT ClassName,Caption FROM doc_Classes WHERE DocClassID=$DocField->TargetDocClass");
			}
			$FieldTypeText.="<a class='p' href='".ActionURL("doc.IDocFields.BrowseFields.bm",array(DocClassID=>$DocField->TargetDocClass))."'>".langstr_get($this->Classes[$DocField->TargetDocClass]->Top->Caption)."</a>";
		}

		if ($DocField->AutoCalc) {
			$c2=$DocField->AutoCalc; $c="";
			if (mb_strlen($c2,"UTF-8")>80) {$c="title='".htmlspecialchars ($c2)."'"; $c2=mb_substr($c2,0,77).".."; }
			$c2=htmlspecialchars ($c2);
			$FieldTypeText.="<br><span class='mini' $c><font color='#800080'>$c2</font></span>";
		}
		
		print $FieldTypeText;
	}

	function tab_Primary($TabFieldName,&$row,$f,$a) {
		if ($row->Key=='PRI') print "[!]";
	}
	function tab_Required($TabFieldName,&$row,$f,$a) {
		if (!$row->Null) print "*";
	}
	function tabf_TabColumnFilter(&$row) {
		if (array_search($row->Field,$this->SystemFields)!==false) return false;
		if (isset($this->DocTableFields[strtoupper($row->Field)])) return false; else return true;
	}
	function tabf_TabColumnStyle($TabFieldName,&$row,$f,$a) {
		if ($row->Null != "YES") {return "style='color:#f00000';";}
	}

	function EditField($args)
	{
	  extract(param_extract(array(
	    DocFieldID=>'int',
	    DocClassID=>'int'
	  ),$args));
		global $cfg;
		
		if (!$DocClassID)
		{
			return array(Error=>"Undefilned argument DocClassID");
		}
		
		if ($DocFieldID)
		{
			$qf=DBQuery ("SELECT * FROM doc_Fields WHERE DocFieldID=$DocFieldID");
			if (!$qf)
			{
				return array (Error=>"Field not found",Details=>"DocFieldID=$DocFieldID");
			}
			$DocField=&$qf->Top;
		}
		$qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
		if (!$qclass) {
			return array(Error=>"Class not found",Details=>"DocClassID=$DocClassID");
		}
		$DocClass=&$qclass->Top;
		$DocTable=$DocClass->DocTable;

		if (!$this->TableExists) {
			$this->TableExists=DBQuery("SHOW TABLES LIKE '$DocTable'");
			if ($this->TableExists) {
				$this->ColumnInfo=DBQuery ("SHOW COLUMNS FROM $DocTable","Field");
			} else {
				print "Таблица '$DocTable' не найдена";
				return;
			}
		}
		print "<h1>".langstr_get($DocClass->Caption)."</h1>";
		$IsProperty=1;
    if ($DocFieldID) {
    	$Caption=langstr_get($qf->Top->Caption);
    	$IsProperty=$DocField->IsProperty;
    	if ($IsProperty) print "<h2>Редактирование свойства '$Caption'</h2>";
    		else print "<h2>Редактирование поля '$Caption'</h2>";
    	
			$doc=param_extract(array(
			  FieldName=>'string',
			  Caption=>'string',
			  FieldType=>'string',
			  Required=>'int',
			  Seq=>'int',
			  Size=>'int',
			  Decimals=>'int',
			  TargetDocClass=>'string',
			  AutoCalc=>'string',
			  TargetPage=>'string'
			  ),$qf->Top);
    } else {
    	print "<h2>Новое свойство</h2>";
    	$IsProperty=1;
    	$q=DBQuery("SELECT MAX(Seq) AS MaxSeq FROM doc_Fields WHERE DocClassID=$DocClassID");
    	$doc=array(Size=>0,Decimals=>0);
    	if ($q) $doc['Seq']=intval($q->Top->MaxSeq)+10;
    }
    
		if (!$doc['FieldType']) $doc['FieldType']='string';

    $FieldTypes=$_ENV->Cartridges['doc']->Data->FieldTypes;
    if ($IsProperty) {unset($FieldTypes['bindto']);}
    else {unset($FieldTypes['bindfld']); unset($FieldTypes['bindto']); unset($FieldTypes['collection']);}
    $_ENV->PutValueSet(array(ValueSetName=>"fieldtypes", Values=>$FieldTypes));
		$_ENV->OpenForm(array(ModalOkOnOk=>1,Modal=>1,ShowCancel=>1,
		  Action=>ActionURL("doc.IDocFields.SaveField"),Align=>"center", Width=>"100%"));
		$_ENV->PutFormField(array(Name=>"DocClassID",Value=>$DocClassID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"DocFieldID",Value=>$DocFieldID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Caption=>"Надпись",Type=>"langstring",Value=>$doc['Caption'],Required=>1));
    if ($IsProperty) {
    	# Если это дополнительный, то указывается лишь символьное значение поля
	  	$_ENV->PutFormField(array(Name=>"FieldName",Size=>20,
	  	  Caption=>"Название свойства",
	  	  Value=>$doc['FieldName'],
	  	  Notice=>"Символьное обозначение свойства, которое потом будет использоваться при импорте/экспорте",
	  	  Type=>"identifier",Required=>1));
    } else {
    	# Если это поле таблицы, то предлагаем выбрать другие поля
	    if ($this->ColumnInfo) {
	    	$_ENV->PutValueSet(array(ValueSetName=>"tablefields", Recordset=>$this->ColumnInfo,CaptionField=>"Field"));
	    }
	    	
	  	$_ENV->PutFormField(array(Name=>"FieldName",Size=>20,
	  	  Caption=>"Поле таблицы",
	  	  Value=>$doc['FieldName'],
	  	  ValueSetName=>"tablefields",
	  	  Notice=>"Поле таблицы, которое содержит значение",
	  	  Type=>"droplist",
	  	  Required=>1));
    	
    }
  	$_ENV->PutFormField(array(Name=>"IsProperty",Type=>"hidden",Value=>$IsProperty));
		$_ENV->PutFormField(array(Name=>"FieldType",Size=>40,Caption=>"Тип данных",Type=>"droplist",ValueSetName=>"fieldtypes",Value=>$doc['FieldType'],Required=>1,DefaultValue=>"string"));
		$_ENV->PutFormField(array(Name=>"Required",Caption=>"Обязательное",Type=>"checkbox",Value=>$doc['Required']));
		$_ENV->PutFormField(array(Name=>"Size",Size=>10,Caption=>"Размер поля (в символах)",Notice=>"При вводе данных поле будет ограничивать ввод указанным размером. Введите 0 для снятия ограничения",Type=>"int",Value=>$doc['Size']));
		$_ENV->PutFormField(array(Name=>"Decimals",Size=>4,Caption=>"Количество цифр после запятой",Notice=>"Для вещественных полей",Type=>"int",Value=>$doc['Decimals']));
		$_ENV->PutFormField(array(Name=>"Seq",Size=>10,Caption=>"Порядковый номер",Type=>"int",Value=>$doc['Seq']));
		$_ENV->PutFormField(array(Name=>"AutoCalc",Size=>40,Caption=>"Выражение для автовычислений",Type=>"string",MaxLength=>250,Value=>$doc['AutoCalc']));
		
		
		$qd=DBQuery ("SELECT DocClassID,ClassName FROM doc_Classes ORDER By ClassName","DocClassID");
		$_ENV->PutValueSet(array(ValueSetName=>"targetclasses",Recordset=>$qd,CaptionField=>"ClassName"));
		$_ENV->PutFormField(array(
		  Name=>"TargetDocClass",Size=>40,
		  Caption=>"Класс документа",
		  NullCaption=>"[Не используется]",
		  Notice=>"Для полей типа коллекция/документ указывается класс документов",
		  Type=>"droplist",ValueSetName=>"targetclasses",Value=>$doc['TargetDocClass']));

		$_ENV->PutFormField(array(Type=>"inputmodal",
			ModalCall=>"jsb.IPage.Select",
			InitCall=>"jsb.IPage.GetPageNameByValue",
      ModalArgs=>array(ContextSelectable=>1),
      Editable=>1,
      Value=>$doc['TargetPage'],
      Size=>40,
      Name=>'TargetPage',
      Caption=>'Корневая страница дерева',
      Notice=>'Используется только для полей типа страница'));
		 
		$_ENV->CloseForm();
	}

	function SaveField($args)	{
	  extract(param_extract(array(
	    DocClassID=>'int',
	    DocFieldID=>'int',
	    IsProperty=>'int',
	  ),$args));

	  if (!$DocClassID) {
			return array(Error=>"Undefined argument DocClassID");
	  }
	  
	  $doc=param_extract(array(
	    FieldName=>'string',
	    Caption=>'string',
	    FieldType=>'string',
	    Required=>'int',
	    Size=>'int',
	    Decimals=>'int',
	    Seq=>'int',
	    TargetDocClass=>'int',
	    AutoCalc=>'nonesc_string',
	    TargetPage=>'string'
	  ),$args);
	  
		if (!$DocFieldID)
		{
			$DocFieldID=DBGetID("doc.DocField");
			if (DBInsert(array(Table=>"doc_Fields",Values=>$doc+array(DocClassID=>$DocClassID,IsProperty=>$IsProperty,DocFieldID=>$DocFieldID)))) return array(ModalResult=>true);
		} else {
			if (DBUpdate(array(Table=>"doc_Fields",Values=>$doc,
			Keys=>array(DocFieldID=>$DocFieldID,DocClassID=>$DocClassID)))) return array(ModalResult=>true);
		}
	return array(ModalResult=>true);
	}
	
	function RegisterTableFields($args) {
		extract(param_extract(array(
		action=>'string',
		check=>'int_checkboxes',
		subaction=>'string',
		DocClassID=>'int'
		),$args));
		global $cfg;

		if ($check && ($action=='register'))
		{
			$r=$this->_registerTableFields($DocClassID,array_keys($check));
		}
		if ($r['Error']) return $r;
		return array(ModalResult=>true);
	}

	function _registerTableFields($DocClassID,$FieldNames) {
		$qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
		if (!$qclass) {
			return array(Error=>"Class not found",Details=>"DocClassID=$DocClassID");
		}
		$MYSQL2FieldTypes=&$_ENV->Cartridges["doc"]->Data->MYSQL2FieldTypes;
		$DocTable=$qclass->Top->DocTable;
		$this->TableExists=DBQuery("SHOW TABLES LIKE '$DocTable'");
		if ($this->TableExists) {
			$this->ColumnInfo=DBQuery ("SHOW COLUMNS FROM $DocTable","Field");
		} else {return array(Error=>"Table not found",Details=>"$DocTable");}
		
		$cnt=count($FieldNames);
		$DocFieldID=DBGetID("doc.DocField","","",$cnt); #reserve id for $cnt fields
		foreach ($FieldNames as $FieldName) {
			$r=&$this->ColumnInfo->Rows[$FieldName];
			preg_match("/^((\w+)\((\d+),?(\d+)?\)|^\w+)\s?(\w+)?\s?(\w+)?$/",$r->Type,$t);
			$TabFieldType="";
			$Size=$Decimals=0;
			$Additional=false;
			if (count($t)>0) {
				if (count($t)==2) {$TabFieldType=$t[1];} 
				else {
					$TabFieldType=$t[2];
					$Size=$t[3];
					$Decimals=intval($t[4]);
				}
				if (isset($t[5])) $Additional[]=$t[5]; 
				if (isset($t[6])) $Additional[]=$t[6];
				$FieldType=$MYSQL2FieldTypes[$TabFieldType];
				if (!$FieldType) $FieldType="string";
				DBInsert(array(Debug=>0,Table=>"doc_Fields",Values=>array(
					DocFieldID=>$DocFieldID,
					DocClassID=>$DocClassID,
				  Caption=>$FieldName,
				  FieldName=>$FieldName,
				  FieldType=>$FieldType,
				  Seq=>$Seq,
				  Size=>$Size,
				  Decimals=>$Decimals,
				  Required=>($r->Null=='YES')?0:1,
				  IsProperty=>0,
				  
				  )));
				$DocFieldID++;
			}
		}
		return true;
	}
	/*
	function EditGroup($args) {
		extract(param_extract(array(
	    FieldGroupID=>'int',
	    DocClassID=>'int',
	  ),$args));
		global $cfg;
		
		if (!$DocClassID)
		{
			return array(Error=>"Undefilned argument DocClassID");
		}
		
		if ($FieldGroupID)
		{
			$qg=DBQuery ("SELECT * FROM doc_FieldGroups WHERE FieldGroupID=$FieldGroupID AND DocClassID=$DocClassID");
			if (!$qg)
			{
				return array (Error=>"Field group not found",Details=>"FieldGroupID=$FieldGroupID");
			}
			$FieldGroup=&$qg->Top;
		}
		$qclass=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
		if (!$qclass) {
			return array(Error=>"Class not found",Details=>"DocClassID=$DocClassID");
		}
		$DocClass=&$qclass->Top;
		print "<h1>".langstr_get($DocClass->Caption)."</h1>";
    if ($FieldGroupID) {
    	$Caption=langstr_get($FieldGroup->Caption);
    	print "<h2>Редактирование группы '$Caption'</h2>";
    	
			$doc=param_extract(array(
			  ParentID=>'int',
			  Caption=>'string',
			  Notice=>'string',
			  Seq=>'int',
			  Closable=>'int',
			  CloseIfEmpty=>'int',
			  Seq=>'int',
			  DependOn=>'string'),$FieldGroup);
    } else {print "<h2>Добавление группы</h2>";
    	$doc=array();
    }
		
		if ($FieldGroupID) $qchilds=DBQuery("SELECT COUNT(*) AS Counter FROM doc_FieldGroups WHERE ParentID=$FieldGroupID");
		if (!$FieldGroupID || ($qchilds->Top->Counter==0)) {
			$qplist=DBQuery ("SELECT FieldGroupID,Caption
			  FROM doc_FieldGroups WHERE ParentID=0 AND FieldGroupID<>$FieldGroupID AND DocClassID=$DocClassID ORDER BY Seq","FieldGroupID");
		}
    $_ENV->PutValueSet(array(ValueSetName=>"pages", Recordset=>$qplist,CaptionField=>"Caption"));
    
		$_ENV->OpenForm(array(ModalOkOnOk=>1,Modal=>1,ShowCancel=>1,
		  Action=>ActionURL("doc.IDocFields.SaveGroup"),Align=>"center", Width=>"100%"));
		$_ENV->PutFormField(array(Name=>"DocClassID",Value=>$DocClassID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"FieldGroupID",Value=>$FieldGroupID,Type=>"hidden"));
  	$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Caption=>"Заголовок группы",Type=>"langstring",Value=>$doc['Caption'],Required=>1));
  	$_ENV->PutFormField(array(Name=>"Notice",Size=>40,Caption=>"Текстовое описание для того кто вводит данные",Type=>"langtext",Value=>$doc['Notice']));
		if ($qplist) {
			$_ENV->PutFormField(array(Name=>"ParentID",NullCaption=>"[Сделать отдельной страницей]",Size=>40,Caption=>"Страница",Notice=>"Группа-страница, в которую входит данная",Type=>"droplist",ValueSetName=>"pages",Value=>$doc['ParentID']));
		} else {
			$_ENV->PutFormField(array(Name=>"ParentID",Value=>$doc['ParentID'],Type=>"hidden"));
		}
		$_ENV->PutFormField(array(Name=>"Seq",Size=>10,Caption=>"Порядковый номер",Type=>"int",Value=>$doc['Seq']));
		$_ENV->PutFormField(array(Name=>"DependOn",Size=>20,Caption=>"Поле-триггер",Notice=>"Если указано поле-триггер, то группа не будет вводиться до тех пор пока пользователь не введет данные в поле-триггер",Type=>"string",Value=>$doc['DependOn']));
		$_ENV->PutFormField(array(Name=>"Closable",Caption=>"Закрываемая группа",Notice=>"Рядом с названием группы будет галочка, раскрывающая все поля в группе",Type=>"checkbox",Value=>$doc['Closable']));
		$_ENV->PutFormField(array(Name=>"CloseIfEmpty",Caption=>"Закрывать если пусто",Notice=>"Если поля группы пусты, то группа будет закрыта",Type=>"checkbox",Value=>$doc['Closable']));
		$args2=false;
		if ($FieldGroupID) {$args2=array(
		Buttons=>array(array(
			Kind=>'delete',
			Caption=>"Удалить группу",
			OnClick=>"W.openModal({url:'".ActionURL("doc.IDocFields.DeleteGroup.b",array(DocClassID=>$DocClassID,FieldGroupID=>$FieldGroupID))."',w:300,h:200,modalOkOnOk:1})")
			));
			}
		$_ENV->CloseForm($args2);
	}
	*/
	function SaveGroup($args) {
	  extract(param_extract(array(
	    DocClassID=>'int',
	    FieldGroupID=>'int',
	  ),$args));

	  if (!$DocClassID) {
			return array(Error=>"Undefined argument DocClassID");
	  }
	  $d=param_extract(array(
	    Caption=>'string',
	    Notice=>'string',
	    ParentID=>'int',
	    DependOn=>'int',
	    Closable=>'int',
	    CloseIfEmpty=>'int',
	    Seq=>'int'
	  ),$args);
	  
	  if (!$FieldGroupID)
		{
			$FieldGroupID=DBGetID("doc.FieldGroup");
			if (DBInsert(array(Table=>"doc_FieldGroups",Values=>$d+array(DocClassID=>$DocClassID,FieldGroupID=>$FieldGroupID)))) 
				return array(ModalResult=>true);
		} else {
			if (DBUpdate(array(Table=>"doc_FieldGroups",Values=>$d,Keys=>array(DocClassID=>$DocClassID,FieldGroupID=>$FieldGroupID)))) 
				return array(ModalResult=>true);
		}
		return false;
	}
	
	function UpdateFieldList($args) {
		extract(param_extract(array(
			action=>'string',
			check=>'int_checkboxes',
			subaction=>'string',
			DocClassID=>'int'
		),$args));

		if (substr($subaction,0,5)=='move_') {
			$keys=implode(",",array_keys($check));
			if (!$check) return array(ModalResult=>'cancel');
			$NewGroupID=intval(substr($subaction,5));
			if (!DBExec("UPDATE doc_Fields SET FieldGroupID=$NewGroupID WHERE DocFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} elseif ($subaction=='exclude') {
			$keys=implode(",",array_keys($check));
			if (!$check) return array(ModalResult=>'cancel');
			if (!DBExec("DELETE FROM doc_Fields WHERE DocClassID=$DocClassID AND DocFieldID IN ($keys)")) return false;
			if (!DBExec("DELETE FROM doc_ValuesInt  WHERE DocClassID=$DocClassID AND DocFieldID IN ($keys)")) return false;
			if (!DBExec("DELETE FROM doc_ValuesStr  WHERE DocClassID=$DocClassID AND DocFieldID IN ($keys)")) return false;
			if (!DBExec("DELETE FROM doc_ValuesText WHERE DocClassID=$DocClassID AND DocFieldID IN ($keys)")) return false;
			return array(ModalResult=>true);
		} else {
			if (is_array($args['Seq'])) foreach ($args['Seq'] as $aDocFieldID=>$NewNo) {
				$NewNo=intval($NewNo);
				DBUpdate (array(
				Table=>"doc_Fields",Keys=>array(DocFieldID=>$aDocFieldID,DocClassID=>$DocClassID),
				Values=>array(Seq=>$NewNo)));
			}
			return array(ModalResult=>true);
		}
		
	}
	
	function DeleteGroup ($args) {
		extract(param_extract(array(
			FieldGroupID=>'int',
			DocClassID=>'int'
		),$args));
		if (!DBUpdate(array(Table=>'doc_Fields',Keys=>array(DocClassID=>$DocClassID,FieldGroupID=>$FieldGroupID),Values=>array(FieldGroupID=>0)))) return false;
		if (!DBUpdate(array(Table=>'doc_FieldGroups',Keys=>array(DocClassID=>$DocClassID,ParentID=>$FieldGroupID),Values=>array(ParentID=>0)))) return false;
		if (!DBExec ("DELETE FROM doc_FieldGroups WHERE FieldGroupID=$FieldGroupID")) return false;
		return array(ModalResult=>true);
	}
	
	function EditEnum($args) {
		extract(param_extract(array(
			DocFieldID=>'int',
			DocClassID=>'int'
		),$args));
		
		$_ENV->OpenForm(array(ReloadOnOk=>1,Modal=>1,ShowCancel=>1,
		  Action=>ActionURL("doc.IDocFields.UpdateEnum"),Align=>"center", Width=>"100%"));
		$_ENV->PutFormField(array(Type=>"hidden",Name=>"DocFieldID",Value=>$DocFieldID));
		
		$s="";
		$qlv=DBQuery("SELECT Value,Caption,Seq FROM doc_ListValues WHERE DocFieldID=$DocFieldID ORDER BY Seq,Value","Value");

		print "<tr><td>Числовое значение</td><td>Текстовое представление</td><td>Порядковый номер</td></tr>";
		$MaxValue=0;
		$MaxSeq=0;
		if ($qlv) foreach ($qlv->Rows as $Value=>$row) {
			if ($MaxValue<$Value) $MaxValue=$Value;
			if ($MaxSeq<$row->Seq) $MaxSeq=$row->Seq;
			print "<tr><td>$Value";
			$_ENV->PutFormField(array(Type=>"hidden",Name=>"Values[$Value]",Value=>$Value));
			
			print "</td><td>";
			$_ENV->PutFormField(array(Style=>"clear",Size=>40,Type=>"langstring",Name=>"Captions[$Value]",Value=>$row->Caption));
			print "</td><td>";
			$_ENV->PutFormField(array(Style=>"clear",Size=>7,Type=>"int",Name=>"Seqs[$Value]",Value=>$row->Seq));
			print "</td></tr>";
		}
		$Value=$MaxValue;
		for ($i=0;$i<5;$i++) {
			$Value++; $MaxSeq+=10;
			print "<tr><td>";
			$_ENV->PutFormField(array(Style=>"clear",Size=>8,Type=>"int",Name=>"AddValues[$i]",Value=>$Value));
			print "</td><td>";
			$_ENV->PutFormField(array(Style=>"clear",Size=>40,Type=>"langstring",Name=>"AddCaptions[$i]",Value=>""));
			print "</td><td>";
			$_ENV->PutFormField(array(Style=>"clear",Size=>7,Type=>"int",Name=>"AddSeqs[$i]",Value=>$MaxSeq));
			print "</td></tr>";
		}
		
		$_ENV->CloseForm();
	}
	
	function UpdateEnum($args) {
		extract(param_extract(array(
			DocFieldID=>'int',
			Values=>'array:int',
			Captions=>'array:string',
			Seqs=>'array:int',
			AddValues=>'array:int',
			AddCaptions=>'array:string',
			AddSeqs=>'array:int',
		),$args));
		
		$ok=true;
		foreach ($Values as $i=>$Value) {
			$Caption=$Captions[$i];
			if ($Caption) {
				if (!DBUpdate(array(Table=>"doc_ListValues", 
				  Keys=>array(DocFieldID=>$DocFieldID,Value=>$Value),
				  Values=>array(Caption=>$Caption,Seq=>$Seqs[$i])))) $ok=false;
			} else {
				if (!DBExec ("DELETE FROM doc_ListValues WHERE DocFieldID=$DocFieldID AND Value=$Value")) $ok=false;
			}
		}
		foreach ($AddValues as $i=>$Value) {
			$Caption=$AddCaptions[$i];
			if (!$Caption) continue;
			if (!DBInsert(array(Table=>"doc_ListValues", Values=>array(DocFieldID=>$DocFieldID,Value=>$Value,Caption=>$Caption,Seq=>$AddSeqs[$i])))) $ok=false;
		}
		if ($ok) return array(ModalResult=>true);
	}
	
}
?>