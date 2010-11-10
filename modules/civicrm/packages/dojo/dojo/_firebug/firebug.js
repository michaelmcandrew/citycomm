/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojo._firebug.firebug"]){
dojo._hasResource["dojo._firebug.firebug"]=true;
dojo.provide("dojo._firebug.firebug");
dojo.deprecated=function(_1,_2,_3){
var _4="DEPRECATED: "+_1;
if(_2){
_4+=" "+_2;
}
if(_3){
_4+=" -- will be removed in version: "+_3;
}
console.warn(_4);
};
dojo.experimental=function(_5,_6){
var _7="EXPERIMENTAL: "+_5+" -- APIs subject to change without notice.";
if(_6){
_7+=" "+_6;
}
console.warn(_7);
};
if((!("console" in window)||!("firebug" in console))&&dojo.config.noFirebugLite!==true){
(function(){
try{
if(window!=window.parent){
if(window.parent["console"]){
window.console=window.parent.console;
}
return;
}
}
catch(e){
}
window.console={_connects:[],log:function(){
logFormatted(arguments,"");
},debug:function(){
logFormatted(arguments,"debug");
},info:function(){
logFormatted(arguments,"info");
},warn:function(){
logFormatted(arguments,"warning");
},error:function(){
logFormatted(arguments,"error");
},assert:function(_8,_9){
if(!_8){
var _a=[];
for(var i=1;i<arguments.length;++i){
_a.push(arguments[i]);
}
logFormatted(_a.length?_a:["Assertion Failure"],"error");
throw _9?_9:"Assertion Failure";
}
},dir:function(_c){
var _d=[];
for(var _e in _c){
try{
_d.push([_e,_c[_e]]);
}
catch(e){
}
}
_d.sort(function(a,b){
return a[0]<b[0]?-1:1;
});
var _11=["<table>"];
for(var i=0;i<_d.length;++i){
var _13=_d[i][0],_14=_d[i][1];
_11.push("<tr>","<td class=\"propertyNameCell\"><span class=\"propertyName\">",escapeHTML(_13),"</span></td>","<td><span class=\"propertyValue\">");
appendObject(_14,_11);
_11.push("</span></td></tr>");
}
_11.push("</table>");
logRow(_11,"dir");
},dirxml:function(_15){
var _16=[];
appendNode(_15,_16);
logRow(_16,"dirxml");
},group:function(){
logRow(arguments,"group",pushGroup);
},groupEnd:function(){
logRow(arguments,"",popGroup);
},time:function(_17){
_18[_17]=(new Date()).getTime();
},timeEnd:function(_19){
if(_19 in _18){
var _1a=(new Date()).getTime()-_18[_19];
logFormatted([_19+":",_1a+"ms"]);
delete _18[_19];
}
},count:function(){
this.warn(["count() not supported."]);
},trace:function(){
this.warn(["trace() not supported."]);
},profile:function(){
this.warn(["profile() not supported."]);
},profileEnd:function(){
},clear:function(){
while(_1b.childNodes.length){
dojo._destroyElement(_1b.firstChild);
}
dojo.forEach(this._connects,dojo.disconnect);
},open:function(){
toggleConsole(true);
},close:function(){
if(_1c){
toggleConsole();
}
},closeObjectInspector:function(){
consoleObjectInspector.innerHTML="";
consoleObjectInspector.style.display="none";
_1b.style.display="block";
}};
var _1d=document;
var _1e=window;
var _1f=0;
var _20=null;
var _1b=null;
var _21=null;
var _22=null;
var _1c=false;
var _23=[];
var _24=[];
var _18={};
var _25=">>> ";
function toggleConsole(_26){
_1c=_26||!_1c;
if(_20){
_20.style.display=_1c?"block":"none";
}
};
function focusCommandLine(){
toggleConsole(true);
if(_21){
_21.focus();
}
};
function openWin(x,y,w,h){
var win=window.open("","_firebug","status=0,menubar=0,resizable=1,top="+y+",left="+x+",width="+w+",height="+h+",scrollbars=1,addressbar=0");
if(!win){
var msg="Firebug Lite could not open a pop-up window, most likely because of a blocker.\n"+"Either enable pop-ups for this domain, or change the djConfig to popup=false.";
alert(msg);
}
createResizeHandler(win);
var _2d=win.document;
HTMLstring="<html style=\"height:100%;\"><head><title>Firebug Lite</title></head>\n"+"<body bgColor=\"#ccc\" style=\"height:98%;\" onresize=\"opener.onFirebugResize()\">\n"+"<div id=\"fb\"></div>"+"</body></html>";
_2d.write(HTMLstring);
_2d.close();
return win;
};
function createResizeHandler(wn){
var d=new Date();
d.setTime(d.getTime()+(60*24*60*60*1000));
d=d.toUTCString();
var dc=wn.document,_31;
if(wn.innerWidth){
_31=function(){
return {w:wn.innerWidth,h:wn.innerHeight};
};
}else{
if(dc.documentElement&&dc.documentElement.clientWidth){
_31=function(){
return {w:dc.documentElement.clientWidth,h:dc.documentElement.clientHeight};
};
}else{
if(dc.body){
_31=function(){
return {w:dc.body.clientWidth,h:dc.body.clientHeight};
};
}
}
}
window.onFirebugResize=function(){
layout(_31().h);
clearInterval(wn._firebugWin_resize);
wn._firebugWin_resize=setTimeout(function(){
var x=wn.screenLeft,y=wn.screenTop,w=wn.outerWidth||wn.document.body.offsetWidth,h=wn.outerHeight||wn.document.body.offsetHeight;
document.cookie="_firebugPosition="+[x,y,w,h].join(",")+"; expires="+d+"; path=/";
},5000);
};
};
function createFrame(){
if(_20){
return;
}
if(dojo.config.popup){
var _36="100%";
var _37=document.cookie.match(/(?:^|; )_firebugPosition=([^;]*)/);
var p=_37?_37[1].split(","):[2,2,320,480];
_1e=openWin(p[0],p[1],p[2],p[3]);
_1d=_1e.document;
djConfig.debugContainerId="fb";
_1e.console=window.console;
_1e.dojo=window.dojo;
}else{
_1d=document;
_36=(dojo.config.debugHeight||300)+"px";
}
var _39=_1d.createElement("link");
_39.href=dojo.moduleUrl("dojo._firebug","firebug.css");
_39.rel="stylesheet";
_39.type="text/css";
var _3a=_1d.getElementsByTagName("head");
if(_3a){
_3a=_3a[0];
}
if(!_3a){
_3a=_1d.getElementsByTagName("html")[0];
}
if(dojo.isIE){
window.setTimeout(function(){
_3a.appendChild(_39);
},0);
}else{
_3a.appendChild(_39);
}
if(dojo.config.debugContainerId){
_20=_1d.getElementById(dojo.config.debugContainerId);
}
if(!_20){
_20=_1d.createElement("div");
_1d.body.appendChild(_20);
}
_20.className+=" firebug";
_20.style.height=_36;
_20.style.display=(_1c?"block":"none");
var _3b=dojo.config.popup?"":"    <a href=\"#\" onclick=\"console.close(); return false;\">Close</a>";
_20.innerHTML="<div id=\"firebugToolbar\">"+"  <a href=\"#\" onclick=\"console.clear(); return false;\">Clear</a>"+"  <span class=\"firebugToolbarRight\">"+_3b+"  </span>"+"</div>"+"<input type=\"text\" id=\"firebugCommandLine\" />"+"<div id=\"firebugLog\"></div>"+"<div id=\"objectLog\" style=\"display:none;\"></div>";
_22=_1d.getElementById("firebugToolbar");
_22.onmousedown=onSplitterMouseDown;
_21=_1d.getElementById("firebugCommandLine");
addEvent(_21,"keydown",onCommandLineKeyDown);
addEvent(_1d,dojo.isIE||dojo.isSafari?"keydown":"keypress",onKeyDown);
_1b=_1d.getElementById("firebugLog");
consoleObjectInspector=_1d.getElementById("objectLog");
layout();
flush();
};
dojo.addOnLoad(createFrame);
function clearFrame(){
_1d=null;
if(_1e.console){
_1e.console.clear();
}
_1e=null;
_20=null;
_1b=null;
consoleObjectInspector=null;
_21=null;
_23=[];
_24=[];
_18={};
};
dojo.addOnUnload(clearFrame);
function evalCommandLine(){
var _3c=_21.value;
_21.value="";
logRow([_25,_3c],"command");
var _3d;
try{
_3d=eval(_3c);
}
catch(e){
console.debug(e);
}
console.log(_3d);
};
function layout(h){
var _3f=h?h-(_22.offsetHeight+_21.offsetHeight+25+(h*0.01))+"px":_20.offsetHeight-(_22.offsetHeight+_21.offsetHeight)+"px";
_1b.style.top=_22.offsetHeight+"px";
_1b.style.height=_3f;
consoleObjectInspector.style.height=_3f;
consoleObjectInspector.style.top=_22.offsetHeight+"px";
_21.style.bottom=0;
};
function logRow(_40,_41,_42){
if(_1b){
writeMessage(_40,_41,_42);
}else{
_23.push([_40,_41,_42]);
}
};
function flush(){
var _43=_23;
_23=[];
for(var i=0;i<_43.length;++i){
writeMessage(_43[i][0],_43[i][1],_43[i][2]);
}
};
function writeMessage(_45,_46,_47){
var _48=_1b.scrollTop+_1b.offsetHeight>=_1b.scrollHeight;
_47=_47||writeRow;
_47(_45,_46);
if(_48){
_1b.scrollTop=_1b.scrollHeight-_1b.offsetHeight;
}
};
function appendRow(row){
var _4a=_24.length?_24[_24.length-1]:_1b;
_4a.appendChild(row);
};
function writeRow(_4b,_4c){
var row=_1b.ownerDocument.createElement("div");
row.className="logRow"+(_4c?" logRow-"+_4c:"");
row.innerHTML=_4b.join("");
appendRow(row);
};
function pushGroup(_4e,_4f){
logFormatted(_4e,_4f);
var _50=_1b.ownerDocument.createElement("div");
_50.className="logGroupBox";
appendRow(_50);
_24.push(_50);
};
function popGroup(){
_24.pop();
};
function logFormatted(_51,_52){
var _53=[];
var _54=_51[0];
var _55=0;
if(typeof (_54)!="string"){
_54="";
_55=-1;
}
var _56=parseFormat(_54);
for(var i=0;i<_56.length;++i){
var _58=_56[i];
if(_58&&typeof _58=="object"){
_58.appender(_51[++_55],_53);
}else{
appendText(_58,_53);
}
}
var ids=[];
var obs=[];
for(i=_55+1;i<_51.length;++i){
appendText(" ",_53);
var _5b=_51[i];
if(_5b===undefined||_5b===null){
appendNull(_5b,_53);
}else{
if(typeof (_5b)=="string"){
appendText(_5b,_53);
}else{
if(_5b.nodeType==9){
appendText("[ XmlDoc ]",_53);
}else{
if(_5b.nodeType==1){
appendText("< "+_5b.tagName+" id=\""+_5b.id+"\" />",_53);
}else{
var id="_a"+_1f++;
ids.push(id);
obs.push(_5b);
var str="<a id=\""+id+"\" href=\"javascript:void(0);\">"+getObjectAbbr(_5b)+"</a>";
appendLink(str,_53);
}
}
}
}
}
logRow(_53,_52);
for(i=0;i<ids.length;i++){
var btn=_1d.getElementById(ids[i]);
if(!btn){
continue;
}
btn.obj=obs[i];
_1e.console._connects.push(dojo.connect(btn,"onclick",function(){
_1b.style.display="none";
consoleObjectInspector.style.display="block";
var _5f="<a href=\"javascript:console.closeObjectInspector();\">&nbsp;<<&nbsp;Back</a>";
try{
printObject(this.obj);
}
catch(e){
this.obj=e;
}
consoleObjectInspector.innerHTML=_5f+"<pre>"+printObject(this.obj)+"</pre>";
}));
}
};
function parseFormat(_60){
var _61=[];
var reg=/((^%|[^\\]%)(\d+)?(\.)([a-zA-Z]))|((^%|[^\\]%)([a-zA-Z]))/;
var _63={s:appendText,d:appendInteger,i:appendInteger,f:appendFloat};
for(var m=reg.exec(_60);m;m=reg.exec(_60)){
var _65=m[8]?m[8]:m[5];
var _66=_65 in _63?_63[_65]:appendObject;
var _67=m[3]?parseInt(m[3]):(m[4]=="."?-1:0);
_61.push(_60.substr(0,m[0][0]=="%"?m.index:m.index+1));
_61.push({appender:_66,precision:_67});
_60=_60.substr(m.index+m[0].length);
}
_61.push(_60);
return _61;
};
function escapeHTML(_68){
function replaceChars(ch){
switch(ch){
case "<":
return "&lt;";
case ">":
return "&gt;";
case "&":
return "&amp;";
case "'":
return "&#39;";
case "\"":
return "&quot;";
}
return "?";
};
return String(_68).replace(/[<>&"']/g,replaceChars);
};
function objectToString(_6a){
try{
return _6a+"";
}
catch(e){
return null;
}
};
function appendLink(_6b,_6c){
_6c.push(objectToString(_6b));
};
function appendText(_6d,_6e){
_6e.push(escapeHTML(objectToString(_6d)));
};
function appendNull(_6f,_70){
_70.push("<span class=\"objectBox-null\">",escapeHTML(objectToString(_6f)),"</span>");
};
function appendString(_71,_72){
_72.push("<span class=\"objectBox-string\">&quot;",escapeHTML(objectToString(_71)),"&quot;</span>");
};
function appendInteger(_73,_74){
_74.push("<span class=\"objectBox-number\">",escapeHTML(objectToString(_73)),"</span>");
};
function appendFloat(_75,_76){
_76.push("<span class=\"objectBox-number\">",escapeHTML(objectToString(_75)),"</span>");
};
function appendFunction(_77,_78){
_78.push("<span class=\"objectBox-function\">",getObjectAbbr(_77),"</span>");
};
function appendObject(_79,_7a){
try{
if(_79===undefined){
appendNull("undefined",_7a);
}else{
if(_79===null){
appendNull("null",_7a);
}else{
if(typeof _79=="string"){
appendString(_79,_7a);
}else{
if(typeof _79=="number"){
appendInteger(_79,_7a);
}else{
if(typeof _79=="function"){
appendFunction(_79,_7a);
}else{
if(_79.nodeType==1){
appendSelector(_79,_7a);
}else{
if(typeof _79=="object"){
appendObjectFormatted(_79,_7a);
}else{
appendText(_79,_7a);
}
}
}
}
}
}
}
}
catch(e){
}
};
function appendObjectFormatted(_7b,_7c){
var _7d=objectToString(_7b);
var _7e=/\[object (.*?)\]/;
var m=_7e.exec(_7d);
_7c.push("<span class=\"objectBox-object\">",m?m[1]:_7d,"</span>");
};
function appendSelector(_80,_81){
_81.push("<span class=\"objectBox-selector\">");
_81.push("<span class=\"selectorTag\">",escapeHTML(_80.nodeName.toLowerCase()),"</span>");
if(_80.id){
_81.push("<span class=\"selectorId\">#",escapeHTML(_80.id),"</span>");
}
if(_80.className){
_81.push("<span class=\"selectorClass\">.",escapeHTML(_80.className),"</span>");
}
_81.push("</span>");
};
function appendNode(_82,_83){
if(_82.nodeType==1){
_83.push("<div class=\"objectBox-element\">","&lt;<span class=\"nodeTag\">",_82.nodeName.toLowerCase(),"</span>");
for(var i=0;i<_82.attributes.length;++i){
var _85=_82.attributes[i];
if(!_85.specified){
continue;
}
_83.push("&nbsp;<span class=\"nodeName\">",_85.nodeName.toLowerCase(),"</span>=&quot;<span class=\"nodeValue\">",escapeHTML(_85.nodeValue),"</span>&quot;");
}
if(_82.firstChild){
_83.push("&gt;</div><div class=\"nodeChildren\">");
for(var _86=_82.firstChild;_86;_86=_86.nextSibling){
appendNode(_86,_83);
}
_83.push("</div><div class=\"objectBox-element\">&lt;/<span class=\"nodeTag\">",_82.nodeName.toLowerCase(),"&gt;</span></div>");
}else{
_83.push("/&gt;</div>");
}
}else{
if(_82.nodeType==3){
_83.push("<div class=\"nodeText\">",escapeHTML(_82.nodeValue),"</div>");
}
}
};
function addEvent(_87,_88,_89){
if(document.all){
_87.attachEvent("on"+_88,_89);
}else{
_87.addEventListener(_88,_89,false);
}
};
function removeEvent(_8a,_8b,_8c){
if(document.all){
_8a.detachEvent("on"+_8b,_8c);
}else{
_8a.removeEventListener(_8b,_8c,false);
}
};
function cancelEvent(_8d){
if(document.all){
_8d.cancelBubble=true;
}else{
_8d.stopPropagation();
}
};
function onError(msg,_8f,_90){
var _91=_8f.lastIndexOf("/");
var _92=_91==-1?_8f:_8f.substr(_91+1);
var _93=["<span class=\"errorMessage\">",msg,"</span>","<div class=\"objectBox-sourceLink\">",_92," (line ",_90,")</div>"];
logRow(_93,"error");
};
var _94=(new Date()).getTime();
function onKeyDown(_95){
var _96=(new Date()).getTime();
if(_96>_94+200){
_95=dojo.fixEvent(_95);
var _97=dojo.keys;
var ekc=_95.keyCode;
_94=_96;
if(ekc==_97.F12){
toggleConsole();
}else{
if((ekc==_97.NUMPAD_ENTER||ekc==76)&&_95.shiftKey&&(_95.metaKey||_95.ctrlKey)){
focusCommandLine();
}else{
return;
}
}
cancelEvent(_95);
}
};
function onSplitterMouseDown(_99){
if(dojo.isSafari||dojo.isOpera){
return;
}
addEvent(document,"mousemove",onSplitterMouseMove);
addEvent(document,"mouseup",onSplitterMouseUp);
for(var i=0;i<frames.length;++i){
addEvent(frames[i].document,"mousemove",onSplitterMouseMove);
addEvent(frames[i].document,"mouseup",onSplitterMouseUp);
}
};
function onSplitterMouseMove(_9b){
var win=document.all?_9b.srcElement.ownerDocument.parentWindow:_9b.target.ownerDocument.defaultView;
var _9d=_9b.clientY;
if(win!=win.parent){
_9d+=win.frameElement?win.frameElement.offsetTop:0;
}
var _9e=_20.offsetTop+_20.clientHeight;
var y=_9e-_9d;
_20.style.height=y+"px";
layout();
};
function onSplitterMouseUp(_a0){
removeEvent(document,"mousemove",onSplitterMouseMove);
removeEvent(document,"mouseup",onSplitterMouseUp);
for(var i=0;i<frames.length;++i){
removeEvent(frames[i].document,"mousemove",onSplitterMouseMove);
removeEvent(frames[i].document,"mouseup",onSplitterMouseUp);
}
};
function onCommandLineKeyDown(_a2){
if(_a2.keyCode==13&&_21.value){
addToHistory(_21.value);
evalCommandLine();
}else{
if(_a2.keyCode==27){
_21.value="";
}else{
if(_a2.keyCode==dojo.keys.UP_ARROW||_a2.charCode==dojo.keys.UP_ARROW){
navigateHistory("older");
}else{
if(_a2.keyCode==dojo.keys.DOWN_ARROW||_a2.charCode==dojo.keys.DOWN_ARROW){
navigateHistory("newer");
}else{
if(_a2.keyCode==dojo.keys.HOME||_a2.charCode==dojo.keys.HOME){
_a3=1;
navigateHistory("older");
}else{
if(_a2.keyCode==dojo.keys.END||_a2.charCode==dojo.keys.END){
_a3=999999;
navigateHistory("newer");
}
}
}
}
}
}
};
var _a3=-1;
var _a4=null;
function addToHistory(_a5){
var _a6=cookie("firebug_history");
_a6=(_a6)?dojo.fromJson(_a6):[];
var pos=dojo.indexOf(_a6,_a5);
if(pos!=-1){
_a6.splice(pos,1);
}
_a6.push(_a5);
cookie("firebug_history",dojo.toJson(_a6),30);
while(_a6.length&&!cookie("firebug_history")){
_a6.shift();
cookie("firebug_history",dojo.toJson(_a6),30);
}
_a4=null;
_a3=-1;
};
function navigateHistory(_a8){
var _a9=cookie("firebug_history");
_a9=(_a9)?dojo.fromJson(_a9):[];
if(!_a9.length){
return;
}
if(_a4===null){
_a4=_21.value;
}
if(_a3==-1){
_a3=_a9.length;
}
if(_a8=="older"){
--_a3;
if(_a3<0){
_a3=0;
}
}else{
if(_a8=="newer"){
++_a3;
if(_a3>_a9.length){
_a3=_a9.length;
}
}
}
if(_a3==_a9.length){
_21.value=_a4;
_a4=null;
}else{
_21.value=_a9[_a3];
}
};
function cookie(_aa,_ab){
var c=document.cookie;
if(arguments.length==1){
var _ad=c.match(new RegExp("(?:^|; )"+_aa+"=([^;]*)"));
return _ad?decodeURIComponent(_ad[1]):undefined;
}else{
var d=new Date();
d.setMonth(d.getMonth()+1);
document.cookie=_aa+"="+encodeURIComponent(_ab)+((d.toUtcString)?"; expires="+d.toUTCString():"");
}
};
function isArray(it){
return it&&it instanceof Array||typeof it=="array";
};
function getAtts(o){
if(isArray(o)){
return "[array with "+o.length+" slots]";
}else{
var i=0;
for(var nm in o){
i++;
}
return "{object with "+i+" items}";
}
};
function printObject(o,i,txt,_b6){
var br="\n";
var ind="  ";
txt=txt||"";
i=i||ind;
_b6=_b6||[];
looking:
for(var nm in o){
if(o[nm]===window||o[nm]===document){
continue;
}else{
if(o[nm]&&o[nm].nodeType){
if(o[nm].nodeType==1){
txt+=i+nm+" : < "+o[nm].tagName+" id=\""+o[nm].id+"\" />"+br;
}else{
if(o[nm].nodeType==3){
txt+=i+nm+" : [ TextNode "+o[nm].data+" ]"+br;
}
}
}else{
if(typeof o[nm]=="object"&&(o[nm] instanceof String||o[nm] instanceof Number||o[nm] instanceof Boolean)){
txt+=i+nm+" : "+o[nm]+br;
}else{
if(typeof (o[nm])=="object"&&o[nm]){
for(var j=0,_bb;_bb=_b6[j];j++){
if(o[nm]===_bb){
txt+=i+nm+" : RECURSION"+br;
continue looking;
}
}
_b6.push(o[nm]);
txt+=i+nm+" -> "+getAtts(o[nm])+br;
txt+=printObject(o[nm],i+ind,"",_b6);
}else{
if(typeof o[nm]=="undefined"){
txt+=i+nm+" : undefined"+br;
}else{
if(nm=="toString"&&typeof o[nm]=="function"){
var _bc=o[nm]();
if(typeof _bc=="string"&&_bc.match(/function ?(.*?)\(/)){
_bc=escapeHTML(getObjectAbbr(o[nm]));
}
txt+=i+nm+" : "+_bc+br;
}else{
txt+=i+nm+" : "+escapeHTML(getObjectAbbr(o[nm]))+br;
}
}
}
}
}
}
}
txt+=br;
return txt;
};
function getObjectAbbr(obj){
var _be=(obj instanceof Error);
var nm=(obj&&(obj.id||obj.name||obj.ObjectID||obj.widgetId));
if(!_be&&nm){
return "{"+nm+"}";
}
var _c0=2;
var _c1=4;
var cnt=0;
if(_be){
nm="[ Error: "+(obj.message||obj.description||obj)+" ]";
}else{
if(isArray(obj)){
nm="["+obj.slice(0,_c1).join(",");
if(obj.length>_c1){
nm+=" ... ("+obj.length+" items)";
}
nm+="]";
}else{
if(typeof obj=="function"){
nm=obj+"";
var reg=/function\s*([^\(]*)(\([^\)]*\))[^\{]*\{/;
var m=reg.exec(nm);
if(m){
if(!m[1]){
m[1]="function";
}
nm=m[1]+m[2];
}else{
nm="function()";
}
}else{
if(typeof obj!="object"||typeof obj=="string"){
nm=obj+"";
}else{
nm="{";
for(var i in obj){
cnt++;
if(cnt>_c0){
break;
}
nm+=i+"="+obj[i]+"  ";
}
nm+="}";
}
}
}
}
return nm;
};
window.onerror=onError;
addEvent(document,dojo.isIE||dojo.isSafari?"keydown":"keypress",onKeyDown);
if((document.documentElement.getAttribute("debug")=="true")||(dojo.config.isDebug)){
toggleConsole(true);
}
})();
}
}
