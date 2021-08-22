<?
class support_PAgent {
	var $CopyrightText="(c)2007 PHPSB Agents support";
	var $CopyrightURL="http://www.phpsb.com/support";
	var $ComponentVersion="1.0";

	function Login($args) {
		?>
		<table width='100%' height='100%'><tr><td align='center'>
		<h1>Связь установлена</h1>
		<p>Производится регистрация АРМ</p>
		<form id='Form1' method='post' action='<? print ActionURL("support.PAgent.DoLogin.f"); ?>'>
		<input id='InfoField' type='hidden' name='infoField'>
		</form>
		</td></tr></table>
		<script>function postFormData() {document.getElementById('Form1').submit();}</script>
		<?
	}
	
	function DoLogin($args) {
#		print "<pre>";
#		print_r($args);
		#print "<hr>Server:<br>";
		#print_r($_SERVER);
		
	  extract(param_extract(array(
	  	infoField=>'*string',
    ),$args));
    
    $arr=explode("\n",$infoField);
    foreach ($arr as $s) {$s=trim($s); if ($s) {list ($k,$v)=explode ("=",$s,2); $info[$k]=$v;}}
   
#	  if (!$info) return;
	  
	  extract(param_extract(array(
    	DomainName=>'string',
    	LogonServer=>'string',
	  	ComputerName=>'string',
	  	LogonUser=>'string',
	  	LogonDomain=>'string',
	  	LogonServer=>'string',
	  	MacAddress=>'string',
    ),$info));

		global $cfg;
		$now=time();
		$criteria="DomainName='$DomainName' AND ComputerName='$ComputerName' AND LogonUser='$LogonUser' AND 
		  	LogonDomain='$LogonDomain' AND LogonServer='$LogonServer' AND MacAddress='$MacAddress'";
		$q=DBQuery("SELECT AgentID,LoginCount FROM support_LoggedAgent WHERE $criteria");
		if ($q) {
			$AgentID=$q->Top->AgentID;
			DBUpdate (array(Table=>'support_LoggedAgent',
				Keys=>array(DomainName=>$DomainName,
					ComputerName=>$ComputerName,
					LogonUser=>$LogonUser,
					LogonDomain=>$LogonDomain,
					LogonServer=>$LogonServer,
					MacAddress=>$MacAddress),
				Values=>array(LoginCount=>$q->Top->LoginCount+1,IPAddress=>$_SERVER['REMOTE_ADDR'],LastLoginTime=>$now)
			));
		} else {
			# Пытаемся узнать что это за агент (AgentID) из таблицы support_Agent
			# Сначала по ключю  LogonDomain+LogonUser
			$q=DBQuery("SELECT AgentID FROM support_Agents WHERE LogonDomain='$LogonDomain' AND LogonUser='$LogonUser'");
			if ($q) {
				$AgentID=$q->Top->AgentID;
			} else {
		    $qd=DBQuery ("SELECT JSBPageID AS AgentGroupID,Title FROM jsb_Pages WHERE State=1 AND SysContext='".$cfg['Settings']['support']['AgentsGroupsContext']."' ORDER BY OrderNo","AgentGroupID");
		    $qr=DBQuery ("SELECT AgentRoleID,Caption FROM support_AgentRoles","AgentRoleID");
		    print "<table width='100%' height='80%'><tr><td align='center'>";
		    $_ENV->PutValueSet(array(ValueSetName=>'AgentGroups', Recordset=>$qd, CaptionField=>"Title" ));
		    $_ENV->PutValueSet(array(ValueSetName=>'AgentRoles', Recordset=>$qr, CaptionField=>"Caption" ));
		    
		    $_ENV->OpenForm(array(Name=>"Form1",Title=>"Регистрация сотрудника",ShowCancel=>0,Action=>ActionURL("support.PAgent.DoSelfRegister.f")));
		    foreach ($info as $k=>$v) $_ENV->PutFormField(array(Name=>$k,Value=>$v,Type=>'hidden'));
				$_ENV->PutFormField(array(Type=>'string',Caption=>'Ваша фамилия',Required=>1,Name=>"LastName",Value=>'',MaxLength=>45,Size=>45));
				$_ENV->PutFormField(array(Type=>'string',Caption=>'Имя',Required=>1,Name=>"FirstName",Value=>'',MaxLength=>45,Size=>45));
				$_ENV->PutFormField(array(Type=>'string',Caption=>'Отчество',Required=>1,Name=>"MiddleName",Value=>'',MaxLength=>45,Size=>45));
		    $_ENV->PutFormField(array(Type=>'droplist',Size=>60,Required=>1,Caption=>'Подразделение',ValueSetName=>'AgentGroups', Name=>"AgentGroupID",Value=>0));
		    $_ENV->PutFormField(array(Type=>'droplist',Size=>60,Required=>1,Caption=>'Должность',ValueSetName=>'AgentRoles', Name=>"RoleID",Value=>0));
		    $_ENV->PutFormField(array(Type=>'droplist',Size=>60,Caption=>'Вторая должность',ValueSetName=>'AgentRoles', Name=>"Role2ID",Value=>0));
				$_ENV->CloseForm();
				print "</td></tr></table>";
				return;
			}
			
			# И запихиваем узнанный AgentID в таблицу support_LoggedAgent
			DBInsert(array(Table=>'support_LoggedAgent',
			Values=>array(DomainName=>$DomainName,
				ComputerName=>$ComputerName,
				LogonUser=>$LogonUser,
				LogonDomain=>$LogonDomain,
				LogonServer=>$LogonServer,
				MacAddress=>$MacAddress,
				FirstLoginTime=>$now,
				LastLoginTime=>$now,
				LoginCount=>1,
				AgentID=>$AgentID,
				IPAddress=>$_SERVER['REMOTE_ADDR'])));
		}
		global $_SESSION;
		$_SESSION->AgentID=$AgentID;
		return array(ForwardTo=>ActionURL('support.PAgent.HomePage.f',array(FirstTime=>1)));
	}
	
	# Саморегистрация агента
	function DoSelfRegister($args) {
	  extract(param_extract(array(
	  	FirstName=>'*string',
	  	MiddleName=>'*string',
	  	LastName=>'*string',
	  	AgentGroupID=>'*string',
	  	RoleID=>'*int',
	  	Role2ID=>'int',

	  	DomainName=>'*string',
    	LogonServer=>'*string',
	  	ComputerName=>'*string',
	  	LogonUser=>'*string',
	  	LogonDomain=>'*string',
	  	LogonServer=>'*string',
	  	MacAddress=>'*string',
    ),$args));
		$now=time();
    
		$AgentID=DBInsert(array(Table=>'support_Agents',GetAutoInc=>true,
			Values=>array(
				FirstName=>$FirstName,
				MiddleName=>$MiddleName,
				LastName=>$LastName,
				AgentGroupID=>$AgentGroupID,
				RoleID=>$RoleID,
				Role2ID=>$Role2ID,
				LogonDomain=>$LogonDomain,
				LogonUser=>$LogonUser,
				MacAddress=>$MacAddress,
			)));
		DBInsert(array(Table=>'support_LoggedAgent',
			Values=>array(DomainName=>$DomainName,
				ComputerName=>$ComputerName,
				LogonUser=>$LogonUser,
				LogonDomain=>$LogonDomain,
				LogonServer=>$LogonServer,
				MacAddress=>$MacAddress,
				FirstLoginTime=>$now,
				LastLoginTime=>$now,
				LoginCount=>1,
				AgentID=>$AgentID,
				IPAddress=>$_SERVER['REMOTE_ADDR'])));
		global $_SESSION;
		$_SESSION->AgentID=$AgentID;
		return array(ForwardTo=>ActionURL('support.PAgent.HomePage.f',array(FirstTime=>1)));
	}
	
	function HomePage($args) {
		extract(param_extract(array(
			FirstTime=>'int',
    ),$args));
	  		
		$this->_header();
		if ($FirstTime) {
			print "Добро пожаловать. Это ваш первый доступ в систему. Если при заполнении формы были неточности - вы сможете поправить это позже.";
		}
		$this->_footer();
		
	}
	
	function _header() {
		global $_SESSION;
		$AgentID=$_SESSION->AgentID;
		if (!$AgentID) {print "Система не определила вас<br/>";} else {
			print "[# $AgentID ]<br/>";
			$qagent=DBQuery("SELECT * FROM support_Agents WHERE AgentID=$AgentID");
			if (!$qagent) {
				print "В системе нет учетной записи, соответствующей вам";
			} else {print_r($qagent->Top);}
			print "<hr>";
		}

		print "<table width='100%'><tr valign='top'><td width='200'>";
		print "<li><a href='".ActionURL('support.PAgent.ListAllRequests.f')."'>Список всех заявок</a></li>";
		print "<li><a href='".ActionURL('support.PAgent.ListAllAgents.f')."'>Справочник отделов и сотрудников</a></li></td><td>";
	}
	
	function _footer() {
		print "</td></tr></table>";		
	}
	
	function _tab_name(&$id,&$row,$fname,$args) {
		print $row->LastName;
		if ($row->FirstName) print " ".mb_substr($row->FirstName,0,1).'.';
		if ($row->MiddleName) print mb_substr($row->MiddleName,0,1).'.';
	}
	
	function ListAllAgents ($args) {
	  extract(param_extract(array(
	    PageNo=>'int=1',RowsPerPage=>'int=20',
	  ),$args));

	  global $cfg;
		$this->_header();
		$qc=DBQuery ("SELECT COUNT(*) AS RowCount FROM support_Agents");
    $qd=DBQuery ("SELECT JSBPageID AS AgentGroupID,Title FROM jsb_Pages WHERE State=1 AND SysContext='".$cfg['Settings']['support']['AgentsGroupsContext']."' ORDER BY OrderNo","AgentGroupID");
    $qr=DBQuery ("SELECT AgentRoleID,Caption FROM support_AgentRoles","AgentRoleID");
		$qagents=DBQuery("SELECT * FROM support_Agents ORDER BY LastName LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","AgentID");

#		$qr->Dump();
		$_ENV->PrintTable($qagents,array(
    ModalWindowURL=>ActionURL("store.IPriceColumns.Update.b"),
    Fields=>array(
      Name=>"Фамилия И.О.",
      RoleID=>"Должность",
      AgentGroupID=>"Отдел",
      ),
#    HiddenFields=>array(LangID=>$FilterLangID),
    FieldTypes=>array(
      RoleID=>array(Type=>'lookup',Recordset=>&$qr,LookupCaption=>"Caption"),
      AgentGroupID=>array(Type=>'lookup',Recordset=>&$qd,LookupCaption=>"Title"),
#      AgentGroupID=>array(Type=>'droplist',Recordset=>&$qd,CaptionField=>"Title"),
      ),
    FieldHooks=>array(Name=>'_tab_name'),
    TableStyle=>1,
#    PutKeyFieldsList=>true,
    Width=>'100%',
    ShowCheckers=>true,
#    ShowDelete=>true,
#    ButtonAdd=>array(ModalWindowURL=>ActionURL("store.IPriceColumns.Add.b")),
#    ShowOk=>true,
		Pages=>array(RowCount=>$qc->Top->RowCount,RowsPerPage=>$RowsPerPage),
    ThisObject=>&$this));
		$this->_footer();
		
	}
	
	function ListAllRequests ($args) {
		$this->_header();
		print "Заявок нет";
		$this->_footer();
	}
	
}
?>