<?
class doc
{
	var $FieldTypes;
	
	function doc()
	{
		$_=&$GLOBALS['_STRINGS']['doc'];
		$this->Title=$_['ABOUT'];
		$this->Roles=array(
		  DocumentDesigner=>$_['ROLE_DOCUMENTDESIGNER'],
		  BriefcaseManager=>"Менеджер заявок",
		);
		
		$this->FieldTypes=array(
	  'int'=>$_['FTYPE_INT'],
	  'string'=>$_['FTYPE_STRING'],
	  'text'=>$_['FTYPE_TEXT'],
	  'checker'=>$_['FTYPE_CHECKER'],
	  'decimal'=>$_['FTYPE_DECIMAL'],
	  'float'=>$_['FTYPE_FLOAT'],
	  'set'=>$_['FTYPE_SET'], # UNUSED
	  'enum'=>$_['FTYPE_ENUM'],
	  'time'=>$_['FTYPE_TIME'],
	  'datetime'=>$_['FTYPE_DATETIME'],
	  'date'=>$_['FTYPE_DATE'],
	  'inttime'=>$_['FTYPE_INT_TIMESTAMP'],
	  'email'=>$_['FTYPE_EMAIL'],
	  'langstring'=>$_['FTYPE_LANG_STRING'],
	  'langtext'=>$_['FTYPE_LANG_TEXT'],
	  'document'=>$_['FTYPE_DOCUMENT'],
	  'collection'=>$_['FTYPE_CONTAINER'],
	  'bindfld'=>$_['FTYPE_BINDABLE_FOLDER'],
	  'user' =>$_['FTYPE_USER'],
	  'page' =>$_['FTYPE_PAGE'],
	  );
	  $this->SearchTypes=array(
	    'int.gt'=>array(Caption=>'Должно быть больше чем','from'=>'От какого значения',to=>'До какого',step=>'С шагом'),
	    'int.lt'=>array(Caption=>'Должно быть меньше чем','from'=>'От какого значения',to=>'До какого',step=>'С шагом'),
	    'int.bt'=>array(Caption=>'Должно быть между двумя значениями','from'=>'От какого значения',to=>'До какого',step=>'С шагом'),
	    'enum.checks'=>array(Caption=>'Галочки выбора возможных значений',columns=>'Количество колонок'),
	  );
	  
	  $this->FormTypes=array(
	  'search'=>"ПОИСК",
	  'list'=>"СПИСОК",
	  #'list.tag'=>"Список документов (с галочками)",
	  'list.preview'=>"СПИСОК(предпросмотр)",
	  'edit'=>"РЕДАКТОР",
	  'display'=>"ДОКУМЕНТ",
	  'display.print'=>"ПЕЧАТЬ ДОКУМЕНТА",
	  );
	  
	  $this->MYSQL2FieldTypes=array(
	  char=>"string",varchar=>"string",
	  text=>"text",blob=>"text",
	  decimal=>"decimal",real=>"float",double=>"float",
	  int=>"int",integer=>"int",bigint=>"int",tinyint=>"int",smallint=>"int",mediumint=>"int",
	  datetime=>"datetime",
	  time=>"time",
	  date=>"date",
	  boolean=>"checker");
	  
	  $this->IdentityMethods=array(
	  0=>$_['IDENTMETH_0_CLASSIDREGISTRY'],
	  1=>$_['IDENTMETH_1_MAX_NUMBER_PLUS_ONE'],
	  2=>$_['IDENTMETH_2_AUTOINC'],
	  3=>$_['IDENTMETH_3_BASEDOCUMENT_REGISTRY'],
	  );
	}

	function Controls()
	{
		$_=&$GLOBALS['_STRINGS']['doc'];
		return array(
		TSearchForm  =>array(Caption=>$_['TSEARCHFORM_CAPTION'], Description=>$_['TSEARCHFORM_DESCRIPTION'] ,Icon=>''),
		TSearchResult=>array(Caption=>$_['TSEARCHRESULT_CAPTION'], Description=>$_['TSEARCHRESULT_DESCRIPTION'] ,Icon=>''),
		TDisplayForm=>array(Caption=>$_['TDISPLAYFORM_CAPTION'],Icon=>''),
		TBriefcase=>array(Caption=>$_['TBRIEFCASE_CAPTION'],Icon=>''),
		);
	}
	function Menu()
	{
		$_=&$GLOBALS['_STRINGS']['doc'];
		return array (
		array
			(
			PutToCategory=>"admin",
			CreateCategory=>"admin.doc",
			Caption=>$_['ABOUT'],
			Items=>array(
#				array(Caption=>$_['MENUITEM_EDIT_DOCUMENT_DIAGRAMS'],Call=>"doc.IDiagrams.Edit.b"),
#				array(Caption=>$_['MENUITEM_EDIT_DOCUMENT_FORMS'],Call=>"doc.IDocStructure.Edit.bm"),

				array(Caption=>$_['MENUITEM_EDIT_DOCUMENTS_LIST'],Call=>"doc.IDocClasses.Browse.bm"),
				array(Caption=>$_['MENUITEM_EDIT_DOCUMENT_FORMS'],Call=>"doc.IForms.Browse.bm"),
				array(Caption=>$_['MENUITEM_BROWSE_UPDATE_GONFIGS'],Call=>"doc.IUpdateConfigs.Browse.bm"),
				)

			
			),
		array
			(
			PutToCategory=>"content",
#			CreateCategory=>"admin.doc",
#			Caption=>$_['ABOUT'],
			Items=>array(
#				array(Caption=>$_['MENUITEM_EDIT_DOCUMENT_DIAGRAMS'],Call=>"doc.IDiagrams.Edit.b"),
#				array(Caption=>$_['MENUITEM_EDIT_DOCUMENT_FORMS'],Call=>"doc.IDocStructure.Edit.bm"),

#				array(Caption=>$_['MENUITEM_EDIT_DOCUMENTS_LIST'],Call=>"doc.IDocClasses.Browse.bm"),
				array(Caption=>$_['MENUITEM_UPDATE_PANEL'],Call=>"doc.IUpdating.Panel.bm"),
				)

			
			),
		);
	}
	
	function ObjectClasses()
  {
  return array
    (
    "doc.Document"=>array(Caption=>"The document"),
    );
  }
	
	function Settings()
	  {
	  $_=&$GLOBALS['_STRINGS']['img'];
	  return array
	    (
	    BriefcaseManagerEmail=>array(Caption=>"Email менеджера по приему заявок",Type=>'string'),
	    SearchCacheLifetimeInMinutes=>array(Caption=>"Продолжительность хранения кэша результатов поиска (мин)",Type=>'Integer',DefaultValue=>30),
	    );
	  }
	

}
?>