function PutRelatedLinks(category,caption,links)
{
var c,acls,img,l,i,catcontainer=document.getElementById("lmenu"+category);
if (catcontainer==undefined)
  {
  container=document.getElementById("lmenu_related");
  if (container==undefined)
    {
    document.write("<table width='100%' height='100%' cellspacing='0' cellpadding='10'><tr valign='top'><td class='lmenu_panel' id='lmenu_related'></td><td>");
    container=document.getElementById("lmenu_related");
    }
  container.innerHTML+="<div id='lmenu"+category+"'></div>";
  }
lstr="";
for (i in links)
  {
  l=links[i];
  img=l.i; if (img!=undefined) {img="<img src='"+img+"'/>";} else img=(l.b!=undefined)?l.b:"";
  acls=(l.a)?'lmenu_itemhrefa':'lmenu_itemhref';
  c=(l.u)?"<a class='"+acls+"' href='"+l.u+"'>"+l.c+"</a>":l.c;
  lstr+="<tr><td>"+img+"</td><td lmenu_item>"+c+"</td>";
  if (l.c2) lstr+="<td>"+l.c2+"</td>";
  if (l.c3) lstr+="<td>"+l.c3+"</td>";
  lstr+="</tr>";
  }
catcontainer=document.getElementById("lmenu"+category);
if (catcontainer==undefined) {alert ('No table at left');}
catcontainer.innerHTML="<br><table width='100%' cellspacing='0' cellpadding='5'><tr><td class='lmenu_cathead'>"+caption+"</td></tr>"
+"<tr><td class='lmenu_catbody'><table>"+lstr+"</table></td></tr></table></div>";
}