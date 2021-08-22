<?
class doc_IForms
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
	    		
		$qc=DBQuery("SELECT COUNT(*) AS RowCount FROM doc_Forms");
		$q=DBQuery ("SELECT * FROM doc_Forms ORDER BY FormID LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","FormID");
    $qcl=DBQuery ("SELECT DocClassID,Caption,ClassName FROM doc_Classes ORDER BY ClassName","DocClassID");
	
	  $_ENV->PrintTable($q,array(
	    Action=>ActionURL("doc.IForms.Update.b"),
	    ReloadOnOk=>1,
	    Fields=>array(
	    	FormID=>"##",
	      Caption=>"Название формы",
	      FormType=>"Тип формы",
	      DocClassID=>"Базовый класс документа",
	      ),
	    FieldTypes=>array(
        Caption=>array(Type=>"langstring",Action=>"doc.IFormFields.BrowseFields.bm",KeyName=>"FormID"),
	      DocClassID=>array(Type=>"lookup",Recordset=>&$qcl,LookupCaption=>"Caption"),
	      FormType=>array(Type=>"lookupvalue",Values=>$FormTypes=$_ENV->Cartridges['doc']->Data->FormTypes),
	      ),
	    FieldHooks=>array(DocClassID=>tab_DocClass),
	    FieldHookArgs=>array(DocClasses=>&$qcl),
      Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
	    Width=>'100%',
	    ShowDelete=>true,
	    ShowCheckers=>true,
	    HideSubmit=>true,
	    Buttons=>array(array(Kind=>'add',Href=>ActionURL("doc.IForms.Edit.bm"),Width=>680,Height=>550)),
	    ThisObject=>&$this));
	}
	function tab_DocClass($FormID,&$Form,$fName,$a) {
		if ($a['DocClasses']) {
			
			$d=$a['DocClasses']->Rows[$Form->DocClassID];
			print "<a href='".ActionURL("doc.IDocFields.BrowseFields.bm",array(DocClassID=>$Form->DocClassID))."'>$d->ClassName</a><br>".langstr_get($d->Caption);
		}
		
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
  	DBExec ("DELETE FROM doc_Forms WHERE FormID IN ($checkids)");
  	DBExec ("DELETE FROM doc_FormGroups WHERE DocClassID IN ($checkids)");
  	DBExec ("DELETE FROM doc_FormGroups WHERE DocClassID IN ($checkids)");
    }
    return array(ModalResult=>true);
	}
	
	function Edit($args)
	{
	  extract(param_extract(array(
	    FormID=>'int',
	  ),$args));
		global $cfg;
		if ($FormID)
		{
			$q=DBQuery ("SELECT * FROM doc_Forms WHERE FormID=$FormID");
			if (!$q)
			{
				return array (Error=>"Form not found",Details=>"FormID=$FormID");
			}
		}
    $qcl=DBQuery ("SELECT DocClassID,ClassName,Caption FROM doc_Classes ORDER BY ClassName","DocClassID");
    global $_ENVIRONMENT;
	  if ($_ENVIRONMENT=='bm') {
	    include ("inc.docutils.php");
		  _putMenu("FormBrowseFields",&$qcl,$FormID);
	  }
    
    
    $_ENV->PutValueSet(array(ValueSetName=>"classes", Recordset=>$qcl,CaptionField=>"Caption"));
	  $FormTypes=$_ENV->Cartridges['doc']->Data->FormTypes;
    $_ENV->PutValueSet(array(ValueSetName=>"formtypes", Values=>$FormTypes));

    if ($FormID) {
    	print "<h1>Редактирование формы документов</h1>";
	    $doc=param_extract(array(
	      FormType=>'string',
	    	Caption=>'string',
	    	DocClassID=>'int',
	    	),$q->Top);
    } else {
    	print "<h1>Добавление формы документов</h1>";
    	$doc=array(IsDefault=>1);
    }
		$_ENV->OpenForm(array(ShowCancel=>0,Action=>ActionURL("doc.IForms.Save.b"),Align=>"center", Width=>650));
		$_ENV->PutFormField(array(Name=>"FormID",Value=>$FormID,Type=>"hidden"));
		$_ENV->PutFormField(array(Name=>"Caption",Size=>40,Required=>1,Caption=>"Название формы",Type=>"langstring",Value=>$doc['Caption']));
		$_ENV->PutFormField(array(Name=>"FormType",Size=>60,Caption=>"Тип формы",Notice=>"Каждому новому документу присваивается уникальный код разными методами",Type=>"droplist",ValueSetName=>"formtypes",Value=>$doc['FormType']));
		$_ENV->PutFormField(array(Name=>"DocClassID",Size=>40,Caption=>"Класс документа",ValueSetName=>"classes",Type=>"droplist",Value=>$doc['DocClassID'],NullCaption=>"[Самостоятельная форма]"));
		$_ENV->PutFormField(array(Name=>"IsDefault",Type=>"checkbox",Caption=>"Использовать эту форму по-умолчанию для данного типа форм",Value=>$doc['IsDefault']));
		$_ENV->CloseForm();
		
		
	}
	function Save($args)
	{
	  extract(param_extract(array(
	    FormID=>'int',
	    modal=>'int',
	  ),$args));

	  $doc=param_extract(array(
      FormType=>'string',
    	Caption=>'string',
    	DocClassID=>'int',
	    IsDefault=>'int',
	  ),$args);
	  
		if (!$FormID)
		{
			$FormID=DBGetID("doc.Forms");
			if (!DBInsert(array(Table=>"doc_Forms",Values=>$doc+array(FormID=>$FormID)))) return false;
		} else {
			if (!DBUpdate(array(Table=>"doc_Forms",Values=>$doc,Keys=>array(FormID=>$FormID)))) return false;
		}
		if ($doc['IsDefault']) {
			DBExec ("UPDATE doc_Forms SET IsDefault=0 WHERE FormType='$doc[FormType]' AND DocClassID=$doc[DocClassID] AND FormID<>$FormID");
		}
		if ($modal) return array (ModalResult=>true);
		else return array(ForwardTo=>ActionURL("doc.IFormFields.BrowseFields.bm",array(FormID=>$FormID)));
	}

}
?>