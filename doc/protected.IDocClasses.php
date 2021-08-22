<?
class doc_IDocClasses
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentDesigner=>"Browse,Edit,Update,Save");

	function Browse($args)
	{
	  extract(param_extract(array(
	    PageNo=>'int=1',RowsPerPage=>'int=20',
	  ),$args));
	    		
		$qc=DBQuery("SELECT COUNT(*) AS RowCount FROM doc_Classes ");
		$q=DBQuery ("SELECT * FROM doc_Classes ORDER BY ClassName LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","DocClassID");
	  $_ENV->PrintTable($q,array(
	    Action=>ActionURL("doc.IDocClasses.Update.b"),
	    ReloadOnOk=>1,
	    Fields=>array(
	      ClassName=>"Класс объектов",
	      Caption=>"Описание класса",
	      DocTable=>"Таблица",
	      SysContext=>"Контекст сайта",
	      DocList=>"Обзор",
	      ),
      Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
      FieldTypes=>array(
        ClassName=>array(Action=>"doc.IDocFields.BrowseFields.bm",KeyName=>"DocClassID"),
        DocList=>array(Action=>"doc.IDocFields.BrowseContent.bm",KeyName=>"DocClassID",NullCaption=>"Обзор"),
        ),
	    Width=>'100%',
	    ShowDelete=>true,
	    ShowCheckers=>true,
	    ShowOk=>true,
	    ButtonAdd=>array(ModalWindowURL=>ActionURL("doc.IDocClasses.Edit.b"),Width=>680,Height=>550),
	    ThisObject=>&$this));
	    
	}

	function tab_List (&$DocClassID,&$row,$fname,$args)
	{
	print "<a href='".ActionURL("doc.IDocFields.BrowseContent.bm",array(DocClassID=>$DocClassID))."'>Данные</a>";	
	}

	function Update ($args)
	{
  extract(param_extract(array(
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    ),$args));
  global $cfg;

  if ($check && ($action=="delete"))
    {
    $checkids=implode (",",array_keys($check));
  	DBExec ("DELETE FROM doc_Classes WHERE DocClassID IN ($checkids)");
  	DBExec ("DELETE FROM doc_Fields WHERE DocClassID IN ($checkids)");
    }
  return array(ModalResult=>true);
	}
	
	function Edit($args)
	{
	  extract(param_extract(array(
	    DocClassID=>'int',
	  ),$args));
		global $cfg;
		if ($DocClassID)
		{
			$q=DBQuery ("SELECT * FROM doc_Classes WHERE DocClassID=$DocClassID");
			if (!$q)
			{
				return array (Error=>"Class not found",Details=>"DocClassID=$DocClassID");
			}
		}
		$q1=DBQuery("SHOW TABLES");
    $_ENV->PutValueSet(array(ValueSetName=>"tables", Recordset=>$q1,CaptionField=>$q1->Fields[0]));
    $qctx=DBQuery ("SELECT SysContext,Caption FROM sys_Contexts ORDER BY OrderNo","SysContext");
    $_ENV->PutValueSet(array(ValueSetName=>"syscontexts", Recordset=>$qctx,CaptionField=>"Caption"));
    $_ENV->PutValueSet(array(ValueSetName=>"IdentityMethods", Values=>$_ENV->Cartridges['doc']->Data->IdentityMethods));

    $doc=param_extract(array(ClassName=>'string',
    	Caption=>'string',
    	DocTable=>'string',
    	SysContext=>'string',
    	IDField=>'string',
    	IdentityMethod=>'int',
    	CaptionField=>'string',
	    UserIDField=>"string",
	    LangField=>"string",
	    ModifyTimeField=>"string",
	    UpdateTimeField=>"string",
	    BindToField=>"string",
	    HistoryField=>"string",
	    StatusField=>"string",
    	),$q->Top);

    if ($DocClassID) {
    	print "<h1>Редактирование класса документов</h1>";
      $ColumnInfo=DBQuery ("SHOW COLUMNS FROM `$doc[DocTable]`","Field");
      $Fields=$IntFields=$StringFields=false;
      if ($ColumnInfo) foreach ($ColumnInfo->Rows as $FieldName=>$Field) {
      	if (preg_match("/varchar|char|text/i",$Field->Type)) $StringFields[$FieldName]=$FieldName;
      	if (preg_match("/int|integer|bigint|numeric|decimal/i",$Field->Type)) $IntFields[$FieldName]=$FieldName;
      	$Fields[$FieldName]=$FieldName;
      }
      $_ENV->PutValueSet(array(ValueSetName=>"fields", Values=>$Fields));
      $_ENV->PutValueSet(array(ValueSetName=>"intfields", Values=>$IntFields));
      $_ENV->PutValueSet(array(ValueSetName=>"stringfields", Values=>$StringFields));
    } else {
    	print "<h1>Добавление класса документов</h1>";
    	$doc=array();
    }
		$_ENV->OpenForm(array(ModalOkOnOk=>1,Modal=>1,ShowCancel=>1,Action=>ActionURL("doc.IDocClasses.Save"),Align=>"center", Width=>650));
		$_ENV->PutFormField(array(Name=>"DocClassID",Value=>$DocClassID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"ClassName",Size=>40,Caption=>"Класс объекта",Notice=>"Например: shop.Product",Type=>"string",Value=>$doc['ClassName'],Required=>1));
		$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Caption=>"Описание объекта",Notice=>"Например: Товар ",Type=>"langstring",Value=>$doc['Caption']));
		$_ENV->PutFormField(array(Name=>"DocTable",Size=>40,Caption=>"Таблица документов",Editable=>1,Type=>"droplist",ValueSetName=>"tables",Value=>$doc['DocTable'],Required=>1));
		$_ENV->PutFormField(array(Name=>"IdentityMethod",Size=>60,Caption=>"Метод идентификации документов",Notice=>"Каждому новому документу присваивается уникальный код разными методами",Type=>"droplist",ValueSetName=>"IdentityMethods",Value=>$doc['IdentityMethod']));
		if ($DocClassID) {
			$_ENV->PutFormField(array(Name=>"SysContext",Size=>40,Caption=>"Контекст сайта для отображения объектов",DoEditValue=>1,Editable=>1,Type=>"droplist",ValueSetName=>"syscontexts",Value=>$doc['SysContext']));
			$_ENV->PutFormField(array(Type=>"label",Value=>"Таблица, используемая для хранения документов может содержать ряд системных полей, описывающих документ. Эти поля могут содержаться в базовом классе документов. Эти поля заполняются системой при добавлении или изменении объектов"));

  		$_ENV->PutFormField(array(Name=>"IDField",Size=>40,Caption=>"Поле индекса",Notice=>"Какое поле таблицы является уникальным идентификатором",Type=>"droplist",ValueSetName=>"fields",Value=>$doc['IDField']));
			$_ENV->PutFormField(array(Name=>"CaptionField",Size=>40,Caption=>"Поле названия объекта",Notice=>"В каком поле таблицы содержится название объекта",Type=>"droplist",ValueSetName=>"stringfields",Value=>$doc['CaptionField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"LangField",Size=>40,Caption=>"Поле языка",Notice=>"Какое поле таблицы является идентификатором языка",Type=>"droplist",ValueSetName=>"stringfields",Value=>$doc['LangField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"UserIDField",Size=>40,Caption=>"Поле редактора",Notice=>"В каком поле будет сохраняться ID пользователя изменившего документ",Type=>"droplist",ValueSetName=>"intfields",Value=>$doc['UserIDField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"ModifyTimeField",Size=>40,Caption=>"Поле времени ручных изменений",Notice=>"В каком поле будет сохраняться время модификации",Type=>"droplist",ValueSetName=>"intfields",Value=>$doc['ModifyTimeField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"UpdateTimeField",Size=>40,Caption=>"Поле времени автоматического обновления",Notice=>"В какое поле будет вноситься время обновления",Type=>"droplist",ValueSetName=>"intfields",Value=>$doc['UpdateTimeField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"HistoryField",Size=>40,Caption=>"Поле истории",Notice=>"При модификации объекта его старые значения отражаются в истории. Это поле будет указывать на идентификатор Текущего документа",Type=>"droplist",ValueSetName=>"intfields",Value=>$doc['HistoryField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"BindToField",Size=>40,Caption=>"Поле для прикрепления к папкам",Notice=>"Документ может прикрепляться к папкам разных документов, посредством этого поля",Type=>"droplist",ValueSetName=>"stringfields",Value=>$doc['BindToField'],NullCaption=>"[не указано]"));
			$_ENV->PutFormField(array(Name=>"StatusField",Size=>40,Caption=>"Поле для хранения статуса документа",Notice=>"",Type=>"droplist",ValueSetName=>"intfields",Value=>$doc['StatusField'],NullCaption=>"[не указано]"));
		}
		
		$_ENV->CloseForm();
	}
	function Save($args)
	{
	  extract(param_extract(array(
	    DocClassID=>'int',
	  ),$args));

	  $doc=param_extract(array(
	    ClassName=>'string',
	    Caption=>'string',
	    DocTable=>'string',
	    IDField=>'string',
	    CaptionField=>'string',
	    SysContext=>'string',
	    IdentityMethod=>'int',
	    UserIDField=>"string",
	    LangField=>"string",
	    ModifyTimeField=>"string",
	    UpdateTimeField=>"string",
	    HistoryField=>"string",
	    BindToField=>"string",
	    StatusField=>"string",
	  ),$args);
	  
		if (!$DocClassID)
		{
			$DocClassID=DBGetID("doc.DocClass");
			if (DBInsert(array(Table=>"doc_Classes",Values=>$doc+array(DocClassID=>$DocClassID)))) return array(ModalResult=>true);
		} else {
			if (DBUpdate(array(Table=>"doc_Classes",Values=>$doc,Keys=>array(DocClassID=>$DocClassID)))) return array(ModalResult=>true);
		}
	}

}
?>
