<?php
/**
 * Webpage component TNewsBrowser
 *
 * @package news
 * @subpackage TNewsBrowser
 * @copyright PHP Site Builder (www.phpsb.com)
 *
 *
 **/
class news_TNewsBrowser
{
	var $CopyrightText="(c)2003 JhAZZ Site Builder. News system cartridge";
	var $CopyrightURL="www.jhazz.com/jsb";
	var $ComponentVersion="1.0";

	var $Subscribers="BindToNewsGroup";

	function InitComponent()
	{
		$_=&$GLOBALS[_STRINGS][news];
		global $cfg;

		$q=DBQuery ("SELECT JSBPageID,Caption FROM jsb_Pages WHERE State=1 AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."'","JSBPageID");
		if ($q)
		{
			foreach ($q->Rows as $JSBPageID=>$row)
			{
				$BindToGroupList["$JSBPageID"]=langstr_get($row->Caption);
			}
		}
		$this->About=$_['TNEWSEVENT_ABOUT'];
		$this->Propdefs=array(
		NewsGroup         =>array(Type=>"List",Values=>$BindToGroupList,Caption=>$_['NEWSBROWSER_P_NEWSGROUP']),
		BindToNewsGroup   =>array(Type=>"Binding",DataType=>"news.Group",Caption=>$_['NEWSBROWSER_P_GROUPFROMEVENT']),
		Caption_ContinueList=>array(Type=>"LangCaption",Caption=>$_['NEWSBROWSER_C_MORE'],DefaultValue=>$_['NEWSBROWSER_C_MORE']),
		Caption_Details   =>array(Type=>"LangCaption",Caption=>$_['NEWSBROWSER_C_DETAILS'],DefaultValue=>$_['NEWSBROWSER_C_DETAILS']),
		TnFormatNo        =>array(Type=>"List",GetListValuesFrom=>"news.IEvent.GetAlbumFormats",DefaultValue=>1,Caption=>$_[NEWS_TN_FORMAT]),
		ImageStyle				=>array(Type=>"ThemeElement",Section=>"ImageStyles"),
		Show_BodyAsHref   =>array(Type=>"Boolean",Caption=>$_['NEWSBROWSER_P_SHOWBODYASHREF']),
		ViewEventContext  =>array(Type=>"SysContext",ObjectClass=>"news.Event",Caption=>$_['NEWSGROUP_TARGET_NEWSEVENTS_CONTEXT'],DefaultValue=>$cfg['Settings']['news']['PagesDefaultContext']),
		DateFormat        =>array(Type=>"Dateformat",Required=>true,DefaultValue=>"day mon year",Caption=>$_['NEWSDATEFORMAT']),
		Hide_Date         =>array(Type=>"Boolean"),
		Hide_Intro        =>array(Type=>"Boolean"),
		Hide_Title        =>array(Type=>"Boolean"),
		Hide_Details      =>array(Type=>"Boolean"),
		Hide_Icon         =>array(Type=>"Boolean"),
		CSS_NewsDate      =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"p.list-date"),
		CSS_NewsTitle     =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"p.list-tit"),
		CSS_NewsIntro			=>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"p.list-text"),
		CSS_NewsHref      =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"a.list",BaseCSSClass=>"a"),
		SeparatorStyle    =>array(Type=>"ThemeElement",Caption=>"Horizontal separator ",Section=>"HorizontalSeparators"),
		IntroLength       =>array(Type=>"Integer",Caption=>$_['NEWSBROWSER_P_INTROLENGTH'],DefaultValue=>150),
		RowsPerPage       =>array(Type=>"Integer",Caption=>$_['NEWSBROWSER_P_EVENTSPERPAGE'],DefaultValue=>10),
		Padding           =>array(Type=>"Integer",DefaultValue=>0),
		Spacing           =>array(Type=>"Integer",DefaultValue=>5),
		ShowOneRandom     =>array(Type=>"Boolean",Caption=>$_['NEWSBROWSER_SHOWONERANDOM']),
		CheckForDetails   =>array(Type=>"Boolean",Caption=>$_['NEWSBROWSER_CHECKFORDETAILS']),
		HideOutdateEvents =>array(Type=>"Boolean"),
		AscendingOrder    =>array(Type=>"Boolean"),
		);
		$this->Datadefs=array(
		NewsGroupCaption=>array(DataType=>"String",Caption=>$_['NEWSGROUP_CAPTION']),
		Pages =>array(DataType=>"Pages",Caption=>$_['NEWSBROWSER_PAGES']),
		);
	}

	function AfterInit (&$Control)
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		global $cfg,$SysContext;
		extract ($Control->Properties);

		if (!$NewsGroup){
			if ($SysContext=='layouts')	{
				$Control->Data['NewsGroupName']=$_['NEWSBROWSER_GROUP_NAME_SAMPLE'];
				return true;
			}

			if ($Control->Properties['BindToNewsGroup']){
				$bc=$Control->BindToNewsGroup;
				if ($bc);{
					$BindToInfo=BindPathInfo($bc);
					$Control->NewsGroupID=$BindToInfo->ID;
				}
			}
		}else{
			$Control->NewsGroupID=intval($NewsGroup);
		}

		if (!$Control->NewsGroupID){
			return array(Warning=>"GroupID not defined!");
		}

		#  $Control->Data['NewsGroupID']=$Control->NewsGroupID;
		$qg=DBQuery ("SELECT Caption,ParentID FROM jsb_Pages WHERE JSBPageID=$Control->NewsGroupID AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."'");
		if (!$qg)
		{
			return array(Error=>"News groupID $Control->NewsGroupID does not exists in context '".$cfg['Settings']['news']['NewsGroupsContext']."'");
		}

		$now=time();
		$today=$now-($now % (60*60*24));
		$yesterday=$today-(60*60*24);
		$Control->NewsCount=0;
		$AndOutdateHiding="";
		if ($HideOutdateEvents)
		{
			$AndOutdateHiding=" AND DateOfNews>$yesterday";
		}
		$qc=DBQuery ("SELECT COUNT(*) as NewsCount FROM news_Events WHERE NewsGroupID=$Control->NewsGroupID AND PubStatus=10 AND DateToShow<$now AND DateToHide>$now $AndOutdateHiding");
		if (($qc)&&($qc->Top->NewsCount!=0))
		{
			$Control->NewsCount=$qc->Top->NewsCount;
			$PageCount=ceil($Control->NewsCount/$RowsPerPage);
		}

		if (($Control->NewsCount!=0)||($Control->DesignMode))
		{
			$Control->Data['NewsGroupCaption']=langstr_get($qg->Top->Caption);
		}
		$Control->PageNo=$Control->Arguments['p'];
		if (!$Control->PageNo) {$Control->PageNo=intval($_GET['news_PageNo']);}
		if (!$Control->PageNo) {$Control->PageNo=1;}
		$Control->Data['Pages']=array(PageCount=>$PageCount,PageNo=>$Control->PageNo,JSBPageControlID=>$Control->JSBPageControlID);
		$qstr="SELECT * FROM news_Events
     WHERE NewsGroupID=$Control->NewsGroupID AND PubStatus=10 AND DateToShow<$now AND DateToHide>$now $AndOutdateHiding
     ORDER BY DateOfNews DESC ";

		if ($ShowOneRandom)
		{
			$random=rand(0,$Control->NewsCount-1);
			$Control->qnews=DBQuery ($qstr."LIMIT $random,1","NewsID");
		}
		else
		{
			$Control->qnews=DBQuery ($qstr."LIMIT ".(($Control->PageNo-1)*$RowsPerPage).",$RowsPerPage","NewsID");
		}
	}


	function Render(&$Control)
	{
		$_=&$GLOBALS['_STRINGS']['news'];
		global $_HOMEURL,$cfg,$_THEME_NAME,$_THEME,$SysContext;
		extract ($Control->Properties);

		if ($SeparatorStyle)
		{
			$SeparatorStyleParams=&$_THEME['HorizontalSeparators'][$SeparatorStyle];
		}

		$NewsGroupsURL=$_HOMEURL.'/'.$cfg['Settings']['news']['NewsGroupsContext'];
		$NewsEventURL =$_HOMEURL.'/'.$ViewEventContext;
		$NewsUploadURL=   $cfg['FilesURL'].'/news.Uploads';
		$NewsUploadPath= $cfg['FilesPath'].'/news.Uploads';
		$NewsHeadImgURL=  $cfg['FilesURL'].'/img/news.Event/imghead';
		$NewsHeadImgPath=$cfg['FilesPath'].'/img/news.Event/imghead';

		if (!$Control->NewsGroupID)
		{
			if ($SysContext=='layouts')
			{
				print $_['NEWSBROWSER_SAMPLELIST'];
			}
			return true;
		}

		$now=time();
		$NewsCount=$Control->NewsCount;
		if (!$Control->qnews) {return;}

		$next="";
		if ($Control->PageNo!=$PagesTotal) {$next="$NewsGroupsURL/$Control->NewsGroupID.".$cfg[VirtualExtension];}

		$NewsImgBinds=$NewsTextBinds="";
		foreach ($Control->qnews->Rows as $news_EventID=>$row)
		{
			if ($NewsImgBinds) { $NewsTextBinds.=","; $NewsImgBinds.=",";}
			$NewsTextBinds.="'news.Event/$news_EventID'";
			$NewsImgBinds .="'news.Event/imghead/$news_EventID'";
		}

		if (!$Hide_Icon)
		{
			$qico=DBQuery ("SELECT BindTo,Filenames FROM img_Documents WHERE BindTo IN ($NewsImgBinds) ","BindTo");
		}

		if ($CheckForDetails)
		{
			$qhasdetails=DBQuery ("SELECT BindTo FROM stdctrls_Richtexts WHERE BindTo IN ($NewsTextBinds) GROUP BY BindTo","BindTo");
		}

		$PrevDate="";

		$Padding=intval($Padding);
		$Spacing=intval($Spacing);

		/*  $ImgHTML="#IMAGE#";
		if ($ImageStyle)
		{
		$Style=$_THEME['ImageStyles'][$ImageStyle];
		$ImgHTML=$Style['HTML'];
		if (!$ImgHTML)
		{
		if ($Style['CSS_Cell'])
		{
		list ($Border_tag,$Border_class)=get_css_pair ($Style['CSS_Cell'],"td");
		$ImgHTML="<table cellpadding='0' cellspacing='0' border='0'><tr><td $Border_class>#IMAGE#</td></tr></table>";
		}
		}
		}
		*/
		$IImage=&$_ENV->LoadInterface("img.IImage");
		print "<table cellpadding='$Padding' cellspacing='$Spacing' border=0 width='100%'>";
		$arowcount=0;
		
		if ($AscendingOrder) $Control->qnews->Rows=array_reverse($Control->qnews->Rows,true);
		foreach ($Control->qnews->Rows as $news_EventID=>$row)
		{
			extract(param_extract(array("Intro"=>"nonesc_langstring",'DateOfNews'=>'int'),$row));

			$dj="";
			if ($DateOfNews)
			{
				$DateOfNews=format_date($DateFormat,$DateOfNews);
				if ($PrevDate != $DateOfNews)
				{
					if (!$Hide_Date)
					{
						list($t,$c)=get_css_pair($CSS_NewsDate,'span');
						$dj="<$t $c>$DateOfNews</$t>";
					}
				}
			}
			$PrevDate=$DateOfNews;

			if (mb_strlen($Intro)>$IntroLength) {
				$Intro=mb_substr ($Intro,0,$IntroLength-3);
				$pos=strrpos ($Intro," ");
				$Intro=substr ($Intro,0,$pos);
				$Intro.="...";
			}
			$Intro=nl2br($Intro);

			$hrefopen="";
			if ((!$CheckForDetails) || (($qhasdetails)&&($qhasdetails->Rows["news.Event/$news_EventID"])))
			{
				$hrefopen=$NewsEventURL.'/'.$news_EventID.'.'.$cfg['VirtualExtension'];
			}
			if ($row->URL) {$hrefopen=$row->URL;}


			if ($dj) {print "<tr><td colspan='2'>$dj</td></tr>";}
			print "<tr valign='top'>";

			if (!$Hide_Icon)
			{
				$ico="";
				if ($qico)
				{
					$imgdoc=$qico->Rows["news.Event/imghead/$news_EventID"];
					if ($imgdoc)
					{
						$TnNames=$_ENV->Unserialize($imgdoc->Filenames);
						if (!$TnFormatNo) $TnFormatNo=1;
						$TnName=$TnNames[$TnFormatNo];
						$TnPath="$NewsHeadImgPath/$TnName";
						$ico="";
						if ($TnName && is_file($TnPath))
						{
							$size=@getimagesize($TnPath);
							$ico="<img border='0' $size[3] src='$NewsHeadImgURL/$TnName'>";
							$alt=$imgdoc->Caption; if ($alt) $alt=" alt='$alt'";
							if ($hrefopen) {$ico="<a href='$hrefopen'>$ico</a>";}
							$ico=$IImage->MakeThumbnailHtml(array(
								ImageHtml=>$ico,
								ImageStyle=>$ImageStyle
							));
						}
					}
				}

				print "<td align='center' width='1%'>$ico</td>";
			}

			print "<td>";
			list($lt,$lc)=get_css_pair($CSS_NewsHref);
			if (!$Hide_Title)
			{
				$txt=langstr_get($row->Title);

				if ($Show_TitleAsHref && $hrefopen)
				{
					list($t,$c)=get_css_pair($CSS_NewsHref,'a');
					if (($Show_BodyAsHref)&&($hrefopen)) $txt="<a $lc href='$hrefopen'>$txt</a>";
					$txt="<$t $c href='$hrefopen'>$txt</$t>";
				}
				else
				{
					list($t,$c)=get_css_pair($CSS_NewsTitle,'span');
					$txt="<$t $c>$txt</$t>";
				}
				print $txt;
			}

			if (!$Hide_Intro)
			{
				list($t,$c)=get_css_pair($CSS_NewsIntro,'span');
				if (($Show_BodyAsHref)&&($hrefopen)) $Intro="<a $lc href='$hrefopen'>$Intro</a>";
				print "<$t $c>$Intro</$t>";
			}

			if ((!$Hide_Details)&&($hrefopen)&&(!$Show_BodyAsHref))
			{
				list($t,$c)=get_css_pair($CSS_NewsHref,'a');
				$Caption_Details=langstr_get($Caption_Details);
				print " <a $c href='$hrefopen'>$Caption_Details</a>";
			}

			print "</td></tr>";
			if ($SeparatorStyleParams)
			{
				$SepHeight=$SeparatorStyleParams['Height'];
				if (!$SepHeight) $SepHeight=1;
				if ($SeparatorStyleParams['CSS'])
				{
					list($t,$c)=get_css_pair($SeparatorStyleParams['CSS'],'td');
					print "<tr><$t$c colspan='2' ><img src='$cfg[SkinsURL]/$_THEME_NAME/$_THEME[Spacer]' width='1' height='$SepHeight'></$t></tr>";
				}
				elseif ($SeparatorStyleParams['BgImage'])
				{
					print "<tr><$t$c colspan='2' background='$cfg[SkinsURL]/$_THEME_NAME/$SeparatorStyleParams[BgImage]'><img src='$cfg[SkinsURL]/$_THEME_NAME/$_THEME[Spacer]' width='1' height='$SepHeight'/></$t></tr>";

				}
			}

			$arowcount++;
			if ($arowcount>=$RowsPerPage)
			{
				if ($next)
				{
					$Caption_ContinueList=langstr_get($Caption_ContinueList);
					print ("<tr><td colspan='2'><a href='$next'>$Caption_ContinueList</a></td></tr>");
				}
				break;
			}
		}
		print ("</table>");
	}
}
?>
