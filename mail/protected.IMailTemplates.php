<?
class mail_IMailTemplates {
var $CopyrightText="(c)2007 PHP Systems builder. Mail";
var $CopyrightURL="http://www.phpsb.com/mail";
var $RoleAccess=array(QueueManager=>"Browse,Edit,Save");

function _tab_Status (&$QueueID,&$row,$fname,$args) {
	$s="";
	if ($row->CountWait) $s.="$row->CountWait";
	if ($row->CountSending) $s.=" <font color='#ff6600'>sending:$row->CountSending</font>";
	if ($row->CountErr) $s.=" <font color='red'>errors:$row->CountErr</font>";
	if ($row->CountOk) {if ($s) $s="($s)"; else $s=" done"; $s="<b>$row->CountOk</b>$s";}
	if ($row->Stopped) $s="<b>[STOP]</b> ".$s;
	print $s;
}

function Browse($args) {
	extract(param_extract(array(
		PageNo=>'int=1', 
		RowsPerPage=>'int=20',
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
	global $_USER;
  

  $qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM mail_Templates");
  $RowCount=$qc->Top->RowCount;
  
	$s="SELECT TemplateID,CONCAT(Cartridge,'.',Name) AS TemplateName,Lang,Subject
	 FROM mail_Templates ORDER BY Cartridge,Name LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
  $q=DBQuery($s,"TemplateID");

  $args=array(
    Action=>ActionURL("mail.IMailTemplates.BrowseAction.b"),
    Modal=>1,
    Fields=>array(
      Lang=>"..",
      TemplateName=>$_['MAILTEMPLATE_NAME'],
      Subject=>$_['MAILTEMPLATE_SUBJECT'],
      ),
/*    FieldHooks=>array(
      CreateByUserID=>_tab_User,
      Status=>_tab_Status,
      Priority=>_tab_Priority,
      Name=>_tab_Name,
      ),
      */
    ShowCheckers=>1,
    ShowDelete=>1,
    ShowOk=>1,
    TableStyle=>1,
    Width=>'100%',
    ButtonAdd=>array(ModalWindowURL=>ActionURL("mail.IMailTemplates.Edit.b")),
		ButtonEdit=>array(ModalWindowAction=>"mail.IMailTemplates.Edit.b",KeyName=>"TemplateID"),
    Pages=>array(RowCount=>$RowCount,RowsPerPage=>$RowsPerPage),
#    HiddenAutoFields=>array(CatalogID=>$CatalogID,MemberIDs=>$MemberIDs),
#    ColAligns=>array(Edit=>'center',ShowIt=>'center',Removed=>'center'),
    ThisObject=>&$this);
    
  $_ENV->PrintTable($q,$args);
	  
	
}

function Edit ($args) {
	extract(param_extract(array(
		TemplateID=>'int', 
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
  if ($TemplateID) {
	  $q=DBQuery("SELECT * FROM mail_Templates WHERE TemplateID=$TemplateID");
	  if ($q) extract(param_extract(array(
	  	Subject=>'string',
	  	Name=>'string',
	  	Cartridge=>'string',
	  	PlainBody=>'string',
	  	Encoding=>'string',
	  	AutoFields=>'string',
	  	Lang=>'string'
	  	),$q->Top));
	  $AutoFields=str_replace(',',"\n",$AutoFields);
  }
  $_ENV->SetWindowOptions(array(Width=>700,Height=>600));
 	$_ENV->OpenForm(array(Action=>ActionURL('mail.IMailTemplates.Save'),ModalOkOnOk=>1));
 	$_ENV->PutFormField(array(Type=>"hidden",Name=>"TemplateID",Value=>$TemplateID));
 	print "<tr><td colspan='2'>";
 	print "<table><tr><td>Cartridge:";
 	$_ENV->PutFormField(array(Type=>"string",Name=>"Cartridge",Style=>'clear',Value=>$Cartridge,Size=>12,MaxLength=>12,Required=>1));
 	print "</td><td>Name:</td><td>";
 	$_ENV->PutFormField(array(Type=>"string",Name=>"Name",Style=>'clear',Value=>$Name,Required=>1));
 	print "</td><td>Language:";
 	$_ENV->PutFormField(array(Type=>"string",Name=>"Lang",Style=>'clear',Value=>$Lang,Size=>2,MaxLength=>2,Required=>1));
 	print "</td></tr></table></td></tr>";
 	$_ENV->PutFormField(array(Type=>"string",Size=>60,Name=>"Subject",Value=>$Subject,Caption=>$_['MAILTEMPLATE_SUBJECT'],Required=>1));
 	print "<tr><td colspan='2'>";
 	$_ENV->PutFormField(array(Type=>"text",Size=>80,Rows=>20,Style=>'vertical',Name=>"PlainBody",Value=>$PlainBody,Caption=>$_['MAILTEMPLATE_PLAINTEXT']));
 	print "</td></tr>";
 	print "<tr><td colspan='2'><a href='javascript:;' onClick='document.getElementById(\"div_fieldlist\").style.display=\"block\";'>Show AutoFields</a><div style='display:none' id='div_fieldlist'>";

 	print "<table><tr valign='top'><td>";
 	$_ENV->PutFormField(array(Type=>"text",Size=>30,Rows=>10,Style=>'vertical',Name=>"AutoFields",Value=>$AutoFields,Caption=>$_['MAILTEMPLATE_AutoFields']));
 	print "</td><td><b>Additional AutoFields:</b><br>
 	  MAIL_FROM - sender email address<br>
 	  MAIL_TO -recipient email address<br>
 	  SITE_NAME - short site name<br>
 	  SITE_TITLE - full site name from settings<br>
 	  SITE_URL - url to the homepage of the site<br>
 	  FOOTER - standard mail footer<br>
 	  HELP_EMAIL - site help emaill
 	  </td></tr></table>";
 	print "</div></td></tr>";
 	
 	$_ENV->CloseForm();
}
function Save ($args) {
	extract(param_extract(array(
		TemplateID=>'int', 
		Subject=>'string',
		Name=>'string',
		Cartridge=>'string',
		PlainBody=>'string',
		AutoFields=>'string',
		Lang=>'string',
	  ),$args));
	  
	print_r($args);
	$f=explode ("\n",$AutoFields);
	if (is_array($f)) {
		$AutoFields="";
		foreach ($f as $s) {$s=trim($s); if ($s) $AutoFields.=((empty($AutoFields))?"":",").$s;}
	}
	if ($TemplateID) {
		$r=DBUpdate(array(
			Debug=>0,
			Table=>"mail_Templates",
			Keys=>array(TemplateID=>$TemplateID),
			Values=>array(Lang=>$Lang, AutoFields=>$AutoFields, Name=>$Name, Cartridge=>$Cartridge, Subject=>$Subject, PlainBody=>$PlainBody)));
	} else {
		$r=DBInsert(array(
			Debug=>0,
			Table=>"mail_Templates",
			Values=>array(Lang=>$Lang, AutoFields=>$AutoFields, Name=>$Name, Cartridge=>$Cartridge, Encoding=>'utf-8', Subject=>$Subject, PlainBody=>$PlainBody)));
	}
	if ($r) return array(ModalResult=>true);
}
}


?>