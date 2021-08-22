<?
class doc_PBriefcase
{
	var $CopyrightText="(c)2007 PHP Systems builder. Documents";
	var $CopyrightURL="http://www.phpsb.com/doc";
	var $ComponentVersion="1.0";

	function Put($args) {
	  extract(param_extract(array(
	    selected=>'array',
	  	FormID=>'*int',
	  	BriefcaseID=>'int', # optional
	  	BriefcaseTypeID=>'*int',
	  	TargetProperty=>'string',
	  	Parameter=>'array:string',
#	  	ToNewBriefcase=>'int'
	  ),$args));

		global $_SESSION,$_USER;

		if (!$BriefcaseID) {
			$BriefcaseID=$_SESSION->doc_Briefcase[$BriefcaseTypeID];
		}
		
		$qbct=DBQuery("SELECT * FROM doc_BriefcaseTypes WHERE BriefcaseTypeID=$BriefcaseTypeID");
		if (!$qbct) return array(Error=>"Не указан тип портфеля");
		
		$RouteID=$qbct->Top->RouteID;
		
		$qroute=DBQuery("SELECT TicketDocClassID FROM doc_FlowRoutes WHERE RouteID=$RouteID");
		$DocClassID=$qroute->Top->TicketDocClassID;
		$qdocfields=DBQuery("SELECT DocFieldID,FieldName,FieldType,TargetDocClass FROM doc_Fields WHERE DocClassID=$DocClassID","DocFieldID");
		
		$LoadingClasses=false;
		foreach ($qdocfields->Rows as $DocFieldID=>$DocField) {
			if ($DocField->FieldType=="collection") {
				$LoadingClasses[$DocField->TargetDocClass]=$DocField->FieldName;
			}
		}
		
		if (!$LoadingClasses) {
			return array(Error=>"Указанный класс документов портфеля не имеет ни одной коллекции документов, который мог бы принять отобранные документы");
		}
		$Containers=implode(",",array_keys($LoadingClasses));
		$qchilds=DBQuery("SELECT TargetDocClass,DocClassID	FROM doc_Fields WHERE (IsProperty=0) AND (FieldType='document') AND (DocClassID IN ($Containers))","TargetDocClass");

		if ($BriefcaseID) {
			if ($_USER->UserID) {"(SessionKey='$_SESSION->SessionKey' OR UserID=$_USER->UserID)";}
			else {$wh=(($wh)?" AND ":"")."(SessionKey='$_SESSION->SessionKey')";}
			$wh.=" AND BriefcaseID=$BriefcaseID";
			$qbc=DBQuery ("SELECT * FROM doc_Briefcases WHERE BriefcaseTypeID=$BriefcaseTypeID $wh","BriefcaseID");
		}
		if ($qbc) $ParamValues=$_ENV->Unserialize($qbc->Top->ParamValues);
		
		if ($Parameter) foreach ($Parameter as $k=>$v) $ParamValues[$k]=$v;
		
		if ($selected) foreach ($selected as $item) {
			list ($aClassID,$aDocumentID)=explode (":",$item);
			$DestClassID=$qchilds->Rows[$aClassID]->DocClassID; #11
			$TargetDocClass=$qchilds->Rows[$aClassID]->TargetDocClass; #5
			$DestFieldName=$LoadingClasses[$DestClassID];
			if (!$DestFieldName) {return array (Error=>"Container class not found for selected document ($item)");}
			$ParamValues['_collections_']["$DestFieldName:$TargetDocClass"][$aDocumentID]=array(Tag=>$aDocumentID);
		}
		$Params=$_ENV->Serialize($ParamValues);
		if (!$qbc) {
			$AutoCaption=$qbct->Top->Caption." от ".format_date("shortdate",time());  #preg_replace("|\{([^}]*?)}|",array("CurrentDate","Ini").
			$BriefcaseID=DBInsert (array(Debug=>0,Table=>"doc_Briefcases",GetAutoInc=>1,Values=>array(
			  BriefcaseTypeID=>$BriefcaseTypeID,
			  UserID=>$_USER->UserID,
			  SessionKey=>$_SESSION->SessionKey,
			  Caption=>$AutoCaption,
			  DateOpen=>time(),
			  ParamValues=>$Params
			  )));
			if (!$BriefcaseID) return array(Error=>"Не возвращен код новой записи");
		} else {
			
			if (!DBUpdate(array(Debug=>0,Table=>"doc_Briefcases",Values=>array(
			  DateModify=>time(),
			  UserID=>$_USER->UserID,
			  ParamValues=>$Params
			  ),Keys=>array(BriefcaseTypeID=>$BriefcaseTypeID,BriefcaseID=>$BriefcaseID)))) return;
		}
		$_SESSION->doc_Briefcase[$BriefcaseTypeID]=$BriefcaseID;
		return array(Previous=>1);
	}
	/*
	function MakeTask($args) {
		$qroute=DBQuery("SELECT * FROM doc_FlowRoutes WHERE RouteID=$RouteID");
		$DocClassID=$qroute->Top->TicketDocClassID;
		$RouteXML=$qroute->Top->RouteXML;
		$qdocfields=DBQuery("SELECT * FROM doc_Fields WHERE DocClassID=$DocClassID","DocFieldID");
		
		$LoadingClasses=false;
		foreach ($qdocfields->Rows as $DocFieldID=>$DocField) {
			if ($DocField->FieldType=="collection") {
				$LoadingClasses[$DocField->TargetDocClass]=1;
			}
		}
		$qdocfields->Dump();
		if (!$LoadingClasses) {
			return array(Error=>"Указанный класс документов портфеля не имеет ни одой коллекции");
		}
		$Containers=implode(",",array_keys($LoadingClasses));
		$AllClasses=$Containers.",$DocClassID";
		
		print "SELECT * FROM doc_Classes WHERE DocClassID IN ($AllClasses)<hr>";
		$qdocclasses=DBQuery("SELECT * FROM doc_Classes WHERE DocClassID IN ($AllClasses)","DocClassID");
		$qdocclasses->Dump();
		
		$qchilds=DBQuery("SELECT DocFieldID,DocClassID,TargetDocClass,FieldName 
		FROM doc_Fields WHERE (IsProperty=0) AND (FieldType='document') AND (DocClassID IN ($Containers))","TargetDocClass");
		$qchilds->Dump();

		$myclass=&$qdocclasses->Rows[$DocClassID];
		foreach ($selected as $item) {
			list ($aClassID,$aDocumentID)=explode (":",$item);
			$DestClassID=$qchilds->Rows[$aClassID]->DocClassID;
			$doc=$qdocclasses->Rows[$DestClassID];
			if (!$doc) {return array (Error=>"Container class not found for selected document ($item)");}
		}
		
		if ($BriefcaseID) $wh="BriefcaseID=$BriefcaseID";
		if ($BriefcaseTypeID) {$wh=(($wh)?" AND ":"")."BriefcaseTypeID=$BriefcaseTypeID";}
		if ($_USER->UserID) {$wh=(($wh)?" AND ":"")."(SessionKey='$_SESSION->SessionKey' OR UserID=$_USER->UserID)";}
		else {$wh=(($wh)?" AND ":"")."(SessionKey='$_SESSION->SessionKey')";}
		$qbc=DBQuery ("SELECT * FROM doc_Briefcases WHERE (SessionKey='$_SESSION->SessionKey' OR UserID=$_USER->UserID)","BriefcaseID");
		if ($qbc) $ParamValues=unserialize($qbc->Top->ParamValues);
		
		if ($Parameter) {
			foreach ($Parameter as $k=>$v) $ParamValues[$k]=$v;
		}
		
		if ($selected) foreach ($selected as $item) {
			list ($aClassID,$aDocumentID)=explode (":",$item);
			$qdocfields->
			$ContainerFieldName=$qchilds->Rows[$aClassID]->FieldName;
			$ParamValues[$ContainerFieldName][$aDocumentID]=array(TARGETKEY=>$aDocumentID);
		}
		
		print"<pre>";
		print_r($ParamValues);
		print "<hr>";

		$Params=$_ENV->Serialize($ParamValues);
		if (!$qbc) {
			$AutoCaption="Временная подборка документов от ".format_date("shortdate",time());  #preg_replace("|\{([^}]*?)}|",array("CurrentDate","Ini").
			$r=DBInsert (array(Debug=>1,Table=>"doc_Briefcases",Values=>array(
			  BriefcaseTypeID=>$BriefcaseTypeID,
			  UserID=>$_USER->UserID,
			  SessionKey=>$_SESSION->SessionKey,
			  Caption=>$AutoCaption,
			  DateOpen=>time(),
			  ParamValues=>$Params
			  )));
			 if ($r['Error']) return $r;
			 
		} else {
			
			DBUpdate(array(Debug=>1,Table=>"doc_Briefcases",Values=>array(
			  UserID=>$_USER->UserID,
			  SessionKey=>$_SESSION->SessionKey,
			  Caption=>$AutoCaption,
			  DateOpen=>time(),
			  ParamValues=>$Params
			  ),Keys=>array(BriefcaseTypeID=>$BriefcaseTypeID,BriefcaseID=>$BriefcaseID)));
		}
		#return array(Back=>1);
		
		
	}*/
	
	function ListAction($args) {
		extract(param_extract(array(
		action=>'*string',
		check=>'int_checkboxes',
		subaction=>'string',
		BriefcaseTypeID=>'int'
		),$args));
		
  	global $_SESSION,$_USER;
	  $wh="SessionKey='$_SESSION->SessionKey'";
	  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
		$IsManager=$_USER->HasRole("doc:BriefcaseManager");
		if ($IsManager) $wh=""; else $wh="($wh) AND DateClose=0 AND ";

	  if ($check && ($action=='delete')) {
	  	# Удаление заказов
	  	
			DBExec ("DELETE FROM doc_Briefcases WHERE $wh BriefcaseID IN (".implode (",",array_keys($check)).")");
			$back=$_SERVER['HTTP_REFERER'];
			return array(ForwardTo=>$back);
	  	
	  } elseif ($action=='add') {
	  	# Открываем новый заказ. Для этого просто создаем новый с дубликатом текущего (берем все, кроме коллекций)

	  	$BriefcaseID=intval($_SESSION->doc_Briefcase[$BriefcaseTypeID]);
	  	
	  	if (!$BriefcaseID) {
	  		if ($_USER->UserID) $wh2="UserID=$_USER->UserID"; else $wh2="SessionKey='$_SESSION->SessionKey'";
	  		$q=DBQuery ("SELECT ParamValues FROM doc_Briefcases WHERE $wh2 ORDER BY DateOpen LIMIT 0,1");
	  		if (!$q) return array(Message=>"Нет текущего заказа, на основе которого можно открыть новый заказ. Для того, чтобы сформировать заказ - просто добавьте произведения в портфель");
	  	} else {
				$q=DBQuery("SELECT ParamValues,DateClose FROM doc_Briefcases WHERE ".(($wh)?"$wh AND":"")."BriefcaseID=$BriefcaseID");
				if (!$q) {return array(Error=>"Заказ не существует");}
	  	}
			$ParamValues=$_ENV->Unserialize($q->Top->ParamValues);
			unset($ParamValues['_collections_']);
			$qbct=DBQuery("SELECT * FROM doc_BriefcaseTypes WHERE BriefcaseTypeID=$BriefcaseTypeID");
			if (!$qbct) return array(Error=>"Не указан тип портфеля");
			$ParamValues=$_ENV->Serialize($ParamValues);
			
			$AutoCaption=$qbct->Top->Caption." от ".format_date("shortdate",time());  #preg_replace("|\{([^}]*?)}|",array("CurrentDate","Ini").
			$BriefcaseID=DBInsert (array(Debug=>0,Table=>"doc_Briefcases",GetAutoInc=>1,Values=>array(
			  BriefcaseTypeID=>$BriefcaseTypeID,
			  UserID=>$_USER->UserID,
			  SessionKey=>$_SESSION->SessionKey,
			  Caption=>$AutoCaption,
			  DateOpen=>time(),
			  ParamValues=>$ParamValues
			  )));
			  
			$_SESSION->doc_Briefcase[$BriefcaseTypeID]=$BriefcaseID;
#			print_r($_SESSION->doc_Briefcase);

			$back=$_SERVER['HTTP_REFERER'];
			$p=strpos($back,"?");
			if ($p!==false) $back=substr($back,0,$p);
			$back.="?bcaseid=$BriefcaseID";
			return array(ForwardTo=>$back);
			
	  } 
	}
	
	
	function Update($args) {
		extract(param_extract(array(
		Caption=>'string',
		action=>'*string',
		BriefcaseID=>'*int',
		selected=>'array',
		subaction=>'string',
		CopyEmail=>'string', # for sendcopy
		),$args));
		
		global $_USER,$_SESSION,$cfg,$_LANGUAGE;
		$IsManager=$_USER->HasRole("doc:BriefcaseManager");
	 	if (!$IsManager) { 
	 		$wh="SessionKey='$_SESSION->SessionKey'";
		  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
		  $wh="($wh) AND";
	 	}
  
	 	$s="SELECT ParamValues,DateClose,UserID,SessionKey,BriefcaseTypeID FROM doc_Briefcases WHERE $wh BriefcaseID=$BriefcaseID";
	  $q=DBQuery($s);
		if (!$q) {return array(Error=>"Заявка не существует ли у Вас недостаточно прав");}
		$BriefcaseTypeID=$q->Top->BriefcaseTypeID;
		
		$UPDATEUSERID="";
		$TargetUserID=$q->Top->UserID;
		$TargetSessionKey=$q->Top->SessionKey;
		if ((!$TargetUserID)&&($_SESSION->SessionKey==$TargetSessionKey)) {
			$UPDATEUSERID=",UserID=$_USER->UserID";
		}
		
		if ((!$IsManager) && ($q->Top->DateClose)) {
			return array(Error=>"Портфель уже закрыт. Изменения невозможны");
		}
	
		if ($action=='delete') {
			if ($selected) {
				$ParamValues=$_ENV->Unserialize($q->Top->ParamValues);
				foreach ($selected as $kk) {
					list($path,$id)=explode (":",$kk);
					$pa=explode ("/",$path);
					$PropName=$pa[count($pa)-2];
					foreach (array_keys($ParamValues['_collections_']) as $collectionName) {
						list ($a,$classId)=explode (":",$collectionName);
						if ($a==$PropName) {
							$PropName=$collectionName;
							break;
						}
					}
					unset($ParamValues['_collections_'][$PropName][$id]);
				}
			$ParamValues=DBEscape($_ENV->Serialize($ParamValues));
			$s="UPDATE doc_Briefcases SET ParamValues='$ParamValues',DateModify=".time()."$UPDATEUSERID WHERE $wh BriefcaseID=$BriefcaseID";
			DBExec($s);
			}
		} elseif (($action=='ok')||($action=='send')) {
	  	if ((!$_USER->UserID)&&(!$args['briefcase']['ContactEmail'])&&(!$args['briefcase']['ContactPhone'])) {
	  		return array(ButtonBack=>1,Message=>"Вы не указали свой контактный E-mail и телефон. Пожалуйста вернитесь и заполните одно из этих полей",ButtonBack=>true);
	  	}
			$arr=(get_magic_quotes_gpc()) ? strip_slashes_deep($args['briefcase']): $args['briefcase'];
			$ParamValues=$_ENV->Serialize($arr);
			if ($action=='send') $cl=",DateClose=".time();
			if ($Caption) $Caption="Caption='$Caption',";
			$s="UPDATE doc_Briefcases SET $Caption ParamValues='$ParamValues'$cl,DateModify=".time()."$UPDATEUSERID WHERE $wh BriefcaseID=$BriefcaseID";
			DBExec($s);
		} 
		
		if ($action=='send') {

      $imail=&$_ENV->LoadInterface("mail.PMailSender");
      if (is_object($imail)) {
      	$mailfrom=$cfg['Settings']['um']['RegistratorEmail'];
	      if (!$imail->EnqueueMessage(array(
	      	Cartridge=>"doc",
	      	TemplateName=>"NewBriefcaseRequest",
	      	Language=>$_LANGUAGE,
	      	MailFrom=>$mailfrom,
      		MailTo=>$cfg['Settings']['doc']['BriefcaseManagerEmail'],
	      	QueueName=>"DOC:Новый запрос: ".$args['Caption'],
      		FieldValues=>array(
		      	REQUEST_NAME=>$args['Caption'],
		      	REQUEST_EDIT_URL=>"http://".$cfg['SiteURL'].$cfg['RootURL'].$cfg['OpeningDoor']."/site/1301.html?bcaseid=$BriefcaseID",
		      	REQUEST_PRINT_URL=>"http://".$cfg['SiteURL'].$cfg['RootURL'].$cfg['OpeningDoor']."/site/1303.html?bcaseid=$BriefcaseID",
		      	BODY=>"",
		      	CONTACT_NAME=>$args['briefcase']['ContactName'],
		      	CONTACT_PHONE=>$args['briefcase']['ContactPhone'],
		      	CONTACT_EMAIL=>$args['briefcase']['ContactEmail'],
	        )))) {
	        	return array(Error=>'Произошла ошибка при постановке письма в очередь');
	        }
      }
			$_SESSION->doc_Briefcase[$BriefcaseTypeID]=0;
			$back=$_SERVER['HTTP_REFERER'];
			$p=strpos($back,"?");
			if ($p!==false) $back=substr($back,0,$p);
			return array(ForwardTo=>$back);
		}
		
	return array(Previous=>1);
	}
	
	function SetDisplayFilters ($args) {
		global $_SESSION;
		$filters=array();
		if ($args['dffall']) {
			foreach ($args['dffall'] as $fname) {
				$filters[$fname]=($args['dff'][$fname])?0:1;
			}
		}		
		$_SESSION->doc_BriefcaseFieldFilters=$filters;
		return array(Previous=>1);
	}
	
	function SendCopy($args) {
		extract(param_extract(array(
			CopyEmail=>'string', # for sendcopy
			BriefcaseID=>'int',
		),$args));

		global $_USER,$_SESSION,$cfg,$_LANGUAGE;
		$IsManager=$_USER->HasRole("doc:BriefcaseManager");
	 	if (!$IsManager) { 
	 		$wh="SessionKey='$_SESSION->SessionKey'";
		  if ($_USER->UserID) {	$wh.=" OR UserID=$_USER->UserID"; }
		  $wh="($wh) AND";
	 	}
  
	 	$s="SELECT Caption FROM doc_Briefcases WHERE $wh BriefcaseID=$BriefcaseID";
	  $q=DBQuery($s);
		if (!$q) {return array(Error=>"Заявка не существует или у вас недостаточно прав");}
		$Caption=$q->Top->Caption;

		$imail=&$_ENV->LoadInterface("mail.PMailSender");
    if (is_object($imail)) {
    	$mailfrom=$cfg['Settings']['um']['RegistratorEmail'];
      if (!$imail->EnqueueMessage(array(
      	Cartridge=>"doc",
      	TemplateName=>"RequestCopyToEmail",
      	Language=>$_LANGUAGE,
      	MailFrom=>$mailfrom,
    		MailTo=>$CopyEmail,
      	QueueName=>"DOC:Копия запроса $Caption на $CopyEmail",
    		FieldValues=>array(
	      	REQUEST_NAME=>$Caption,
	      	REQUEST_EDIT_URL=>"http://".$cfg['SiteURL'].$cfg['RootURL'].$cfg['OpeningDoor']."/site/1301.html?bcaseid=$BriefcaseID",
	      	REQUEST_PRINT_URL=>"http://".$cfg['SiteURL'].$cfg['RootURL'].$cfg['OpeningDoor']."/site/1303.html?bcaseid=$BriefcaseID",
	      	BODY=>"",
        )))) {
        	return array(Error=>'Произошла ошибка при постановке письма в очередь');
     			}
    }
	return array(Previous=>1);
	}
}
?>