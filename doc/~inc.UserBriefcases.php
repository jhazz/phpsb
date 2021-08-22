<?
class Briefcase {
	
	var $qbc;  # Список всех портфелей данного посетителя
	var $qbct; # Данные о типе портфеля BriefcaseTypeID и о маршруте документа, который выйдет при окончании заполнения портфеля
	var $BriefcaseTypeID; # текущий тип портфеля
	var $OpenedBriefcaseID; # текущий потфель
	var $DocClassID; # ID Класса документа, являющегося тикетом маршрута
	var $qdocclasses; # классы тикета и его связанных документов второго уровня

	var $allMyBriefcases;

	var $vars;
	
/*	function loadRow2Vars(&$row,$varName) {
		$this->$vars[$varName]=
	}
	*/
function readBriefcase ($args) {
	  extract(param_extract(array(
	  	UserID=>'int',
	  	BriefcaseID=>'int',
	    OpenBriefcaseID=>'int',
	    BriefcaseTypeID=>'*int',
	    LoadOtherTypes=>'int',
	    LoadOtherBriefcases=>'int',
	  ),$args));
  global $_SESSION,$_USER;
	
  
 	$this->BriefcaseTypeID=$BriefcaseTypeID;
  if (!$UserID) {
	  $wh="SessionKey='$_SESSION->SessionKey'";
	  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
  } else $wh="UserID=$UserID";
  
	if (!$ShowOtherTypes) $wh="BriefcaseTypeID=$BriefcaseTypeID AND ($wh)";
	
#  $this->qbc=DBQuery ("SELECT * FROM doc_Briefcases WHERE $wh","BriefcaseID");
  $r=DBQuery2Vars ("SELECT * FROM doc_Briefcases WHERE $wh","BriefcaseID",&$this->vars);
  
  exit;
  #if (!$this->qbc) return;
#  array_walk($this->qbc->Rows,array(&$this,loadRow2Vars),"briefcases");
	
  if (!$OpenBriefcaseID) return;
	if (!$this->qbc->Rows[$OpenBriefcaseID]) {
		$this->OpenedBriefcaseID=0;
		return;
	} 
	
	elseif ($this->OpenedBriefcaseID) {
		$this->Briefcase=&$this->qbc->Rows[$this->OpenedBriefcaseID];
		$this->qbct=DBQuery("SELECT bct.RouteID, bct.SingleForUser, r.TicketDocClassID, bct.AskUser, bct.AskGuest,bct.DisplayFields
FROM doc_BriefcaseTypes AS bct INNER JOIN doc_FlowRoutes AS r ON bct.RouteID = r.RouteID
WHERE bct.BriefcaseTypeID=$this->BriefcaseTypeID");
#		$this->qbct->Dump();

		$this->DocClassID=$this->qbct->Top->TicketDocClassID;
	
		# qdf1 - (1) Поля класса документа - тикета (gtrf_Request)
		$this->qdf1=DBQuery("SELECT DocClassID,DocFieldID,Size,Caption,FieldName,FieldType,TargetDocClass FROM
		   doc_Fields WHERE DocClassID=$this->DocClassID ORDER BY Seq","FieldName");
		# Загружаем в $LoadingClasses классы, которые составляют коллекции данного класса или классами, на которые указывает поля тикета
		$LoadingClasses=false; 
		foreach ($this->qdf1->Rows as $FieldName=>$DocField) {
			if (($DocField->FieldType=="collection")||($DocField->FieldType=="document")) {
				$LoadingClasses[$DocField->TargetDocClass]=$LoadingFields[$DocField->TargetDocClass]=$FieldName;
			}
		}
		#$this->qdf1->Dump();
		
		if ($LoadingClasses) {
			$lc=implode(",",array_keys($LoadingClasses));
			
			# qdf2 (2) - промежуточный запрос, чтобы узнать на какие классы ссылаются требущиеся классы
			#            То есть, мы узнали в qdf1, что надо грузить gtrf.RequestDetails, а здесь узнаем, что 
			#            gtrf.RequestDetails ссылется на gtrf_Request и gtrf_Data. 
			$this->qdf2=DBQuery("SELECT TargetDocClass,FieldName
			  FROM doc_Fields WHERE (FieldType='document') AND (DocClassID IN ($lc))","TargetDocClass");
			if ($this->qdf2) {
				foreach ($this->qdf2->Rows as $aDocClassID=>$afields) {
					$LoadingClasses[$aDocClassID]=1;
					if ($this->DocClassID==$aDocClassID) continue; # игнорируем обратную ссылку с gtrf_RequestDetails на gtrf_Request
					$LoadingFields[$aDocClassID]=1;
				}
				if ($LoadingFields) {
					$lc=implode(",",array_keys($LoadingFields));
					# qdf3 (3) - Поля всех документов[Класс][Поле], которые связаны с тикетом, то есть поля gtrf.RequestDetails и gtrf.Data
					$this->qdf3=DBQuery("SELECT DocClassID,DocFieldID,Caption,FieldName,FieldType,TargetDocClass,AutoCalc FROM doc_Fields
					   WHERE DocClassID IN ($lc) ORDER By Seq",array("DocClassID","DocFieldID"));
				}
				
				$lc=implode(",",array_keys($LoadingClasses));
				$this->qdocclasses=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID IN ($lc)","DocClassID");
			}
		}
/*		$this->bcdoc=DBQuery("SELECT bct.RouteID, bct.SingleForUser, c.DocClassID, c.Caption, c.DocTable, c.IDField, c.CaptionField
FROM (doc_BriefcaseTypes AS bct INNER JOIN doc_FlowRoutes AS r ON bct.RouteID = r.RouteID) INNER JOIN doc_Classes AS c ON r.TicketDocClassID = c.DocClassID
WHERE bct.BriefcaseTypeID=$this->BriefcaseTypeID");*/

	}	
}

?>