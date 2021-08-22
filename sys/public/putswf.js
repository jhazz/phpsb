function PutSwf(args) {
	return;
	// width,height,src,   wmode,id,quality
	var s1="",s2="",wh="",i;
if (args.wmode==undefined) args.wmode='transparent';
if (args.quality==undefined) args.quality='best';

if (args.width!=undefined) wh="width='"+args.width+"' ";
if (args.height!=undefined) wh+="height='"+args.height+"' ";
for (i in args) {
	if ((i=='width')||(i=='height')||(i=='src')) continue;
	s1+=" "+i+"='"+args[i]+"'";
	s2=s2+"<param name='"+i+"' value='"+args[i]+"'/>";
}
var s="<object "+wh+" classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000'><param name='movie' value='"+args.src+"'> "
+s2+"<embed src='"+args.src+"' type='application/x-shockwave-flash' "+s1+" "+wh+"/></object>";
document.write(s);
}