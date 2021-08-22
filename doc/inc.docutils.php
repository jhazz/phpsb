<?
	function _putMenu($Current,&$qcl,$CurrentFormID=0) {
		global $_THEME;

		$Caption=langstr_get($qcl->Top->Caption);
		$DocClassID=$qcl->Top->DocClassID;
		$q=DBQuery("SELECT FormID,FormType,Caption FROM doc_Forms WHERE DocClassID=$DocClassID","FormID");

		$links1=array(Caption=>$Caption);
		$_ENV->PutRelatedLinks("links1",array(Caption=>$Caption,Items=>array(
		array(Caption=>"Структура документа",Icon=>"$_THEME[SkinURL]/tree_page.gif",URL=>ActionURL("doc.IDocFields.BrowseFields.bm",array(DocClassID=>$DocClassID)),Active=>($Current=="BrowseFields")),
		array(Caption=>"Содержимое",Icon=>"$_THEME[SkinURL]/tree_f.gif",URL=>ActionURL("doc.IDocFields.BrowseContent.bm",array(DocClassID=>$DocClassID)),Active=>($Current=="BrowseContent")),
		)));
#		$q->Dump();
		$items=array(array(Caption=>"Создать новую форму",
		  Icon=>"$_THEME[SkinURL]/jsb_add.gif",
		  URL=>ActionURL("doc.IForms.Edit.bm",array(DocClassID=>$DocClassID)),Active=>($Current=="Browse")));
		if ($q) {
			foreach ($q->Rows as $FormID=>$form) {
				$items[]=array(
				  Icon=>"$_THEME[SkinURL]/tree_page.gif",
				  Caption=>langstr_get($form->Caption),
				  URL=>ActionURL("doc.IFormFields.BrowseFields.bm",array(FormID=>$FormID,DocClassID=>$DocClassID)),Active=>($CurrentFormID==$FormID));
			}
		}
		$_ENV->PutRelatedLinks("links2",array(Caption=>"Формы ($Caption)",Items=>$items));
	}

?>