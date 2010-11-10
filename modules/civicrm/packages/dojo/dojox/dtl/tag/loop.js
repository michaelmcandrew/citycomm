/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.dtl.tag.loop"]){
dojo._hasResource["dojox.dtl.tag.loop"]=true;
dojo.provide("dojox.dtl.tag.loop");
dojo.require("dojox.dtl._base");
dojo.require("dojox.string.tokenize");
(function(){
var dd=dojox.dtl;
var _2=dd.tag.loop;
_2.CycleNode=dojo.extend(function(_3,_4,_5,_6){
this.cyclevars=_3;
this.name=_4;
this.TextNode=_5;
this.shared=_6||{counter:-1,map:{}};
},{render:function(_7,_8){
if(_7.forloop&&!_7.forloop.counter0){
this.shared.counter=-1;
}
++this.shared.counter;
var _9=this.cyclevars[this.shared.counter%this.cyclevars.length];
var _a=this.shared.map;
if(!_a[_9]){
_a[_9]=new dd._Filter(_9);
}
_9=_a[_9].resolve(_7,_8);
if(this.name){
_7[this.name]=_9;
}
if(!this.contents){
this.contents=new this.TextNode("");
}
this.contents.set(_9);
return this.contents.render(_7,_8);
},unrender:function(_b,_c){
return this.contents.unrender(_b,_c);
},clone:function(){
return new this.constructor(this.cyclevars,this.name,this.TextNode,this.shared);
}});
_2.IfChangedNode=dojo.extend(function(_d,_e,_f){
this.nodes=_d;
this._vars=_e;
this.shared=_f||{last:null};
this.vars=dojo.map(_e,function(_10){
return new dojox.dtl._Filter(_10);
});
},{render:function(_11,_12){
if(_11.forloop&&_11.forloop.first){
this.shared.last=null;
}
var _13;
if(this.vars.length){
_13=dojo.toJson(dojo.map(this.vars,function(_14){
return _14.resolve(_11);
}));
}else{
_13=this.nodes.dummyRender(_11,_12);
}
if(_13!=this.shared.last){
var _15=(this.shared.last===null);
this.shared.last=_13;
_11.push();
_11.ifchanged={firstloop:_15};
_12=this.nodes.render(_11,_12);
_11.pop();
}
return _12;
},unrender:function(_16,_17){
this.nodes.unrender(_16,_17);
},clone:function(_18){
return new this.constructor(this.nodes.clone(_18),this._vars,this.shared);
}});
_2.RegroupNode=dojo.extend(function(_19,key,_1b){
this._expression=_19;
this.expression=new dd._Filter(_19);
this.key=key;
this.alias=_1b;
},{_push:function(_1c,_1d,_1e){
if(_1e.length){
_1c.push({grouper:_1d,list:_1e});
}
},render:function(_1f,_20){
_1f[this.alias]=[];
var _21=this.expression.resolve(_1f);
if(_21){
var _22=null;
var _23=[];
for(var i=0;i<_21.length;i++){
var id=_21[i][this.key];
if(_22!==id){
this._push(_1f[this.alias],_22,_23);
_22=id;
_23=[_21[i]];
}else{
_23.push(_21[i]);
}
}
this._push(_1f[this.alias],_22,_23);
}
return _20;
},unrender:function(_26,_27){
return _27;
},clone:function(_28,_29){
return this;
}});
dojo.mixin(_2,{cycle:function(_2a,_2b){
var _2c=_2b.split(" ");
if(_2c.length<2){
throw new Error("'cycle' tag requires at least two arguments");
}
if(_2c[1].indexOf(",")!=-1){
var _2d=_2c[1].split(",");
_2c=[_2c[0]];
for(var i=0;i<_2d.length;i++){
_2c.push("\""+_2d[i]+"\"");
}
}
if(_2c.length==2){
var _2f=_2c[_2c.length-1];
if(!_2a._namedCycleNodes){
throw new Error("No named cycles in template: '"+_2f+"' is not defined");
}
if(!_2a._namedCycleNodes[_2f]){
throw new Error("Named cycle '"+_2f+"' does not exist");
}
return _2a._namedCycleNodes[_2f];
}
if(_2c.length>4&&_2c[_2c.length-2]=="as"){
var _2f=_2c[_2c.length-1];
var _30=new _2.CycleNode(_2c.slice(1,_2c.length-2),_2f,_2a.getTextNodeConstructor());
if(!_2a._namedCycleNodes){
_2a._namedCycleNodes={};
}
_2a._namedCycleNodes[_2f]=_30;
}else{
_30=new _2.CycleNode(_2c.slice(1),null,_2a.getTextNodeConstructor());
}
return _30;
},ifchanged:function(_31,_32){
var _33=dojox.dtl.text.pySplit(_32);
var _34=_31.parse(["endifchanged"]);
_31.next();
return new _2.IfChangedNode(_34,_33.slice(1));
},regroup:function(_35,_36){
var _37=dojox.string.tokenize(dojo.trim(_36),/(\s+)/g,function(_38){
return _38;
});
if(_37.length<11||_37[_37.length-3]!="as"||_37[_37.length-7]!="by"){
throw new Error("Expected the format: regroup list by key as newList");
}
var _39=_37.slice(2,-8).join("");
var key=_37[_37.length-5];
var _3b=_37[_37.length-1];
return new _2.RegroupNode(_39,key,_3b);
}});
})();
}
