<?php
class doc_TBriefcase
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Documents";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Briefcase;
var $Briefcase2;
function InitComponent()
  {
  $_ =&$GLOBALS['_STRINGS']['doc'];
  $__=&$GLOBALS['_STRINGS']['_'];

  $qbt=DBQuery ("SELECT BriefcaseTypeID,Caption FROM doc_BriefcaseTypes ORDER BY BriefcaseTypeID","BriefcaseTypeID");
  $qbf=DBQuery ("SELECT FormID,Caption FROM doc_Forms ORDER BY FormID","FormID");
  $this->Propdefs=array(
    LoadOtherTypes=>array(Type=>'boolean',Caption=>"Загружать другие виды портфелей"),
    ShowBriefcases=>array(Type=>'boolean',Caption=>"Показывать другие портфели для выбора пользователем"),
    Editable=>array(Type=>'boolean',Caption=>"Позволить редактировать"),
    ShowDisplayFilters=>array(Type=>'boolean',Caption=>"Показывать фильтры выборки полей"),
    BriefcaseTypeID=>array(Type=>"list",Caption=>"Какой тип портфеля отражать",NullCaption=>"[--Показывать все--]",
      Recordset=>&$qbt,CaptionField=>"Caption"),
		FormID=>array(Type=>"list",Caption=>"Какую форму использовать",Recordset=>&$qbf,CaptionField=>"Caption"),
		DescendantForms=>array(Type=>"string",Caption=>"Укажите коды вложенных форм через запятую"),
    TableStyle=>array(Type=>"ThemeElement",Section=>"TableStyles",Caption=>"Стиль отображения таблицы"),
    PrintVersionURL=>array(Type=>"InputModal",Caption=>"Страница с версией для печати",Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    );
	$this->Datadefs=array(
		Pages =>array(DataType=>"Pages",Caption=>"Страницы "),
		RowCountText=>array(DataType=>"String",Caption=>"Общее количество документов"),
		BriefcaseCaption=>array(DataType=>"String",Caption=>"Заголовок портфеля"),
		);
  }

  function AfterInit(&$Control) {
#	  	$_ENV->InitWindows();
  	
  }
function Init(&$Control) {
  $__=&$GLOBALS['_STRINGS']['_'];
  $_=&$GLOBALS['_STRINGS']['doc'];
  global $_SESSION,$_USER;

  if ($Control->Properties) extract($Control->Properties);
  if ($_GET['bcasetype']) $BriefcaseTypeID=intval($_GET['bcasetype']);
  if ($_GET['bcaseid']) 	$BriefcaseID=intval($_GET['bcaseid']);
  if (!$BriefcaseID)  $BriefcaseID=$_SESSION->doc_Briefcase[$BriefcaseTypeID];
  

	include ("inc.Briefcase.php");
	
	
	$this->Briefcase=new Briefcase;
	$this->Briefcase->LoadBriefcase(array(
		BriefcaseTypeID=>$BriefcaseTypeID,
		LoadOtherTypes=>$LoadOtherTypes,
		LoadOtherBriefcases=>$ShowBriefcases,
		BriefcaseID=>$BriefcaseID
	));

	$BriefcaseID=$this->Briefcase->vars['BriefcaseID'];
	if ($_GET['setactive']) {	$_SESSION->doc_Briefcase[$BriefcaseTypeID]=$BriefcaseID;}
	
	$CurrentBriefcase=&$this->Briefcase->vars['Briefcases'][$BriefcaseID];
	$DateClose=$CurrentBriefcase['DateClose'];
	if ($_USER->HasRole("doc:BriefcaseManager")) {$Control->IsManager=1;}
	
	if ($DateClose && (!$Control->IsManager)) {
		$Control->Properties['Editable']=0;
	}
	
	include ("inc.Forms.php");
	$this->Forms=new Forms;
	
	$desc=explode (",",$DescendantForms); 
	$r=$this->Forms->Load($desc); #array(1309,1308,1307)
		/*
	if ($Control->Properties['Editable']) {
		$desc=explode (",",$DescendantForms); 
		$r=$this->Forms->Load($desc); #array(1309,1308,1307)
	} else {
		# readonly version
		$desc=explode (",","1311,1312,1314"); 
		$r=$this->Forms->Load($desc); #array(1309,1308,1307)
		$Control->Properties['FormID']=1312;
	}
	*/
	
	if ($r['Error']) return $r;
	$LoadedBriefcase=&$this->Briefcase->vars['Briefcases'][$BriefcaseID];
	
	global $_TITLE;
	if ($LoadedBriefcase['Caption']) {
		$_TITLE="Заявка - $LoadedBriefcase[Caption]";
		$Control->Data['BriefcaseCaption']="$LoadedBriefcase[Caption]";
	}
	
	
	
#	$r=$this->Forms->InitForm(1309);
	
	/*
	global $_SESSION,$_USER;
	 	$this->BriefcaseTypeID=$BriefcaseTypeID;
	  if (!$UserID) {
		  $wh="SessionKey='$_SESSION->SessionKey'";
		  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
	  } else $wh="UserID=$UserID";
		if (!$LoadOtherTypes) $wh="BriefcaseTypeID=$BriefcaseTypeID AND ($wh)";
	 	$this->qbc=DBQuery("SELECT * FROM doc_Briefcases WHERE $wh","BriefcaseID");

*/

  
#  $RowsPerPage=20;
#  $Control->RowCount=$Control->form->GetCount(array(SearchID=>$Control->SearchID));
#  $PageCount=ceil($Control->RowCount/$RowsPerPage);

  
#  $Control->Data['Pages']=array(PageCount=>$PageCount,PageNo=>$Control->PageNo,JSBPageControlID=>$Control->JSBPageControlID);
#  $Control->Data['RowCountText']=$Caption_RowCountText." ".$Control->RowCount;
  return array(DisableCache=>true);

}
	
	function Render(&$Control)
	  {
	  $__=&$GLOBALS['_STRINGS']['_'];
	  $_=&$GLOBALS['_STRINGS']['doc'];
	  if ($Control->Properties) extract($Control->Properties);

	  global $_USER,$_SESSION;
	 	if (($_USER->HasRole("doc:BriefcaseManager"))&&($ShowBriefcases)) {
	 		print "<h3>Вы являетесь менеджером, поэтому можете редактировать другие заявки</h3>";
	 	}
	 	
	 	$PageNo=intval($_GET['PageNo']);
  	if ($ShowBriefcases) {
  		$this->Briefcase->DisplayBriefcases(array(
  			LoadAllBriefcases=>1,
  			Closeable=>($PageNo)?0:1,
  			BriefcaseTypeID=>$BriefcaseTypeID,
  			TableStyle=>$TableStyle,
  			PageNo=>$PageNo));
  	}
	  	
	 	$BriefcaseID=$this->Briefcase->vars['BriefcaseID'];
  	if ($BriefcaseID) {
	  	if ($DateClose) {
	  		print "<h3>Эта заявка закрыта</h3>";
	  	}
  	} else {
  		if ($this->Briefcase->vars['Briefcases']) {
#  			print "Нажмите на заказ, чтобы посмотреть его содержимое";
  		} else {
  			print "Заказ пуст. Для того, чтобы его создать - сделайте поиск по базе ГТРФ и затем добавьте материалы в портфель заказов";
  		}
  	}

		if ($this->Briefcase->vars['Fields']) {
			if ($ShowDisplayFilters) {
				$this->Briefcase->ShowDisplayFilters(!$_SESSION->doc_BriefcaseFieldFilters);
			}

			if ($PrintVersionURL) {
				print "<script>function sendCopyToEmail(){
				var f1=document.getElementById('InputCopyEmail');
				var f2=document.getElementById('FormSendCopyEmail');
				if (!f1.value) {alert ('Вы не указали email на который следует отправить копию заявки'); return;}
				f2.value=f1.value;
				var frm=document.getElementById('FormSendCopy');
				frm.submit();
				} </script>
				<form method='post' action='".ActionURL('doc.PBriefcase.SendCopy.n')."' id='FormSendCopy'>
				<input type='hidden' name='BriefcaseID' value='$BriefcaseID'>
				<input type='hidden'  name='CopyEmail' id='FormSendCopyEmail'></form>";
			}
			
			if ($Editable) {
		  	$_ENV->OpenForm(array(Action=>ActionURL("doc.PBriefcase.Update"),
		  		Buttons=>array(array(Caption=>"Отправить менеджеру",Kind=>'email',FormAction=>'send')),ShowDelete=>1));
		  	print "<tr><td><h2>Детали заявки</h2><input type='hidden' name='BriefcaseID' value='".$this->Briefcase->vars['BriefcaseID']."'/>";
				$_ENV->PutFormField(array(Type=>'string',Name=>'Caption',Style=>'vertical',Value=>$Control->Data['BriefcaseCaption'],Size=>50,MaxLength=>250));
			} else {
				print "<table border='0' width='100%'><tr><td><h2>".$Control->Data['BriefcaseCaption']."</h2>";
			}
			if ($PrintVersionURL) {
				print "<table width='100%' border='0'><tr><td align='right'><table><tr><td>";
	  		$_ENV->PutButton(array(Kind=>'email',Caption=>"Отправить на адрес..",OnClick=>"document.getElementById('divemail').style.display='block';this.style.display='none';"));
	  		print "</td><td id='divemail' style='display:none'>Укажите емайл:<input type='text' class='inputarea' id='InputCopyEmail'>";
				$_ENV->PutButton(array(Kind=>'email',Caption=>"Отправить",OnClick=>"sendCopyToEmail();"));
				print "</td><td>";
				
		  	if ($PrintVersionURL) {
		  		$_ENV->PutButton(array(Kind=>'print',NewWindow=>1,Caption=>"Версия для печати",Href=>$PrintVersionURL."?bcaseid=$BriefcaseID"));
		  	}
		  	print "</td></tr></table></td></tr></table>";
			}
			
			$r=$this->Forms->Display(array(FormID=>$FormID, # 1309
		  		Document=>$this->Briefcase->vars['Fields'],
		  		DocumentID=>$this->Briefcase->vars['BriefcaseID'],
		  		DocClasses=>$this->Briefcase->vars['DocClasses'],
		  		DocClassFields=>$this->Briefcase->vars['DocClassFields'],
		  		isInputMode=>$Editable,
		  		TableStyle=>$TableStyle,
		  		DisplayFieldFilters=>&$_SESSION->doc_BriefcaseFieldFilters,
		  		));
	  	if ($r['Error']) return $r;
	  	
	  	print "</td></tr>";
#	  	<tr>";
#  		print "<td align='right'>";
#  		print "</td></tr>";
	  	
	  	if ($Editable) {
	  		$_ENV->CloseForm();
	  	} else {
	  		
	  		print "</td></tr></table>";
	  	}
		}
	 
  	
  	
/*	  	
  	$this->DocClassByName=false;
  	$this->DocFieldsByName=false;
  	foreach ($this->qdocclasses->Rows as $DocClassID=>$DocClass) $this->DocClassByName[$DocClass->ClassName]=&$this->qdocclasses->Rows[$DocClassID];
  	foreach ($this->qdf3->Rows as $DocClassID=>$tmp) {
  		foreach ($tmp as $DocFieldID=>$DocField) {
  			$DocClass=$this->qdocclasses->Rows[$DocField->DocClassID];
  			$this->DocFieldsByName[$DocClass->ClassName.":".$DocField->FieldName]=&$this->qdf3->Rows[$DocClassID][$DocFieldID];
  		}
*/
  	
  	

	}

}
?>
