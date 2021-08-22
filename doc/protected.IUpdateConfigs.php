<?
class doc_IUpdateConfigs
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";
	var $RoleAccess=array(DocumentDesigner=>"Browse");

	function Browse($args) {
	  extract(param_extract(array(
	    PageNo=>'int=1',RowsPerPage=>'int=20',
	  ),$args));
	    		
		$qc=DBQuery("SELECT COUNT(*) AS RowCount FROM doc_UpdateConfigs");
		$qcfgs=DBQuery ("SELECT * FROM doc_UpdateConfigs ORDER BY OrderNo LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","UpdateCfgID");
		$DocClassIDs=false;
		
		if ($qcfgs) {
			foreach ($qcfgs->Rows as $UpdateCfgID=>$cfg) {
				$DocClassIDs[$cfg->DocClassID]=true;
			}
			if ($DocClassIDs) {
				$s=implode (",",array_keys($DocClassIDs));
			}
			$this->qd=DBQuery("SELECT DocClassID,ClassName,Caption FROM doc_Classes WHERE DocClassID IN ($s)","DocClassID");
		  $_ENV->PrintTable($qcfgs,array(
	    ModalWindowURL=>ActionURL("doc.IDocClasses.Update.b"),
	    Fields=>array(
	      Caption=>"Название конфигурации",
	      DocClass=>"Класс документа",
	      Encoding=>"Кодировка файла",
	      CSVFieldNames=>"Структура CSV колонок",
	      UpdatingSegment=>"Обновляемый сегмент"
	      ),
      Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
	    Width=>'100%',
#	    ShowDelete=>true,
#	    ShowCheckers=>true,
	    #ButtonEdit=>array(KeyName=>"DocClassID",ModalWindowAction=>"doc.IDocClasses.Edit.b",Width=>680,Height=>550),
	    FieldHooks=>array(DocClass=>"tab_DocClass",Caption=>"tab_Caption"),
#	    ShowOk=>true,
#	    ButtonAdd=>array(ModalWindowURL=>ActionURL("doc.IDocClasses.Edit.b"),Width=>680,Height=>550),
	    ThisObject=>&$this));
			
		}
	}

	function tab_DocClass(&$ConfigID,&$row,$fname,$args)
	{
		if ($this->qd) {
			$d=$this->qd->Rows[$row->DocClassID];
			if (!$d) print "<font color='red'>Ошибка: Класс [$row->DocClassID] удален</font>";
			else print "<b>".langstr_get($d->Caption)."</b><br>$d->ClassName";
		} else print "[$row->DocClassID]";	
	
	}

	function tab_Caption(&$ConfigID,&$cfg,$fname,$args)
	{
		print "<b>".langstr_get($cfg->Caption)."</b>".(($cfg->FileToUpload)?"<br>$cfg->FileToUpload":"");
	}
}
?>