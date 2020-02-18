/*
Copyright 2004-2015 Herbert Street Technologies Ltd
*/



function  IsActiveX()
{
 if(window.ActiveXObject || "ActiveXObject" in window) return true;
 return false;
}



var XHRequest = null ;

function IsXML( xml )
{
  return xml != null && xml.documentElement != null ;
}

function AjaxIsObj( b ) { return b != null && b != undefined ; }

var FAjaxReqs, UrlAjaxReqs ;

function AjaxReqs()
{
  if( AjaxIsObj( FAjaxReqs ) ) FAjaxReqs() ;
  else if( AjaxIsObj( UrlAjaxReqs ) ) window.top.location = UrlAjaxReqs ;
  else alert( "Browser does not support AJAX applications." ) ;
}


function XHRCreate()
{ 
  try {
    if( IsActiveX()) {
    return new ActiveXObject( "Microsoft.XMLHTTP" ) ;
   }

    if( window.XMLHttpRequest)  return new XMLHttpRequest() ; 
  }
  catch( x ) {}
}


function XHRInit()
{
  if( XHRequest == null ) XHRequest = XHRCreate() ;
  return XHRequest ;
}

function XHRAbort( xhr ) 
{ 
  if( xhr == undefined ) xhr = XHRequest ;
  if( AjaxIsObj( xhr ) ) xhr.abort() ;
}


function XHRReadyState( xhr )
{
  if( xhr == undefined ) xhr = XHRequest ;
  return xhr == null ? 0 : xhr.readyState ;
}

function XHRIsCompleted( xhr ) { return XHRReadyState( xhr ) == 4 ; }

function XHRResText()
{
  return XHRequest == null ? null : 
      XHRequest.responseText.replace( /rel=("|'|)stylesheet("|'|)/ig, '' ) ;
}

function XHRResXML()
{
  return ( XHRequest == null ) ? null : XHRequest.responseXML ;
}

function XHRVerify( r )
{
  var msg = "Previous request has not been completed yet.\n" +
      "In order to interrupt request press 'OK', otherwise 'Cancel'." ;
  var state = ( r == null || r == undefined ) ? 0 : r.readyState ;
  return ( state == 0 || state == 4 ) ? true : confirm( msg ) ;
}

function XHRGet( url, handler, r )
{
  var asynch = handler != undefined ;
  if( r == undefined ) r = XHRInit() ;
  r.abort() ;
  if( asynch ) r.onreadystatechange = handler ;
  r.open( "GET", url, asynch ) ;
  r.send( null ) ;
  return asynch ? null : r.responseXML;
}

function XHREncodeStr( s )
{
  var utf8 = new Array() ;
  s = ( s == undefined || s == null ) ? '' : ( s + '' ) ;
  for( var i = 0, j = 0, l = s.length ; i < l ; i++ ) {
    var c = s.charCodeAt( i ) ;
    if( c < 0x80 ) utf8[ j++ ] = c ;
    else if( c < 0x800 ) {
      utf8[ j++ ] = ( c >> 6 ) | 0xC0 ;
      utf8[ j++ ] = ( c & 0x3F ) | 0x80 ;
    }
    else {
      utf8[ j++ ] = ( c >> 12 ) | 0xE0 ;
      utf8[ j++ ] = ( ( c >> 6 ) & 0x3F ) | 0x80 ;
      utf8[ j++ ] = ( c & 0x3F ) | 0x80 ;
    }
  }
  for( var i = 0, l = utf8.length ; i < l ; i++ ) {
    utf8[ i ] = String.fromCharCode( utf8[ i ] ) ;
  }
  return escape( utf8.join( '' ) ).replace( /\+/g, '%2B' ) ;
}

function XHREncodeForm( f )
{
  var els = f.elements, el, a = new Array() ;
  for( var i = 0, j = 0, l = els.length ; i < l ; i++ ) {
    if( ( el = els[ i ] ).name == "" ) continue ;
    a[ j++ ] = el.name + "=" + XHREncodeStr( el.value ) ;
  }
  return a.join( '&' ) ;
}

function XHRVEncodeArgs( a )
{
  var l = a.length, t = new Array() ;
  if( l == undefined ) l = 0 ;
  for( var i = 0, j = 0 ; i < l ; i += 2 ) {
    if( !AjaxIsObj( a[ i ] ) || !AjaxIsObj( a[ i + 1 ] ) ) continue ;
    t[ j++ ] = a[ i ] + "=" + XHREncodeStr( a[ i + 1 ] ) ;
  }
  return t.join( '&' ) ;
}

function XHREncodeArgs() { return XHRVEncodeArgs( arguments ) ; }

function XHRPost( url, data, handler, xhr )
{
  if( xhr == undefined ) xhr = XHRInit() ; 
  xhr.abort() ;
  var asynch = handler != undefined ;
  if( asynch ) xhr.onreadystatechange = handler ;
  xhr.open( "POST", url, asynch ) ;
  xhr.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded" ) ; 
  xhr.send( data ) ;
  return asynch ? null : xhr.responseXML;
}

function XHRPostForm( url, form, handler )
{
  return XHRPost( url, XHREncodeForm( form ), handler ) ;
}

function LoadXSL( url )
{
  try {
    if( IsActiveX() ) {
      var xml = new ActiveXObject( "Msxml2.FreeThreadedDOMDocument.3.0" ) ;
      xml.async = false ;
      xml.validateOnParse = false ;
      xml.load( url ) ;
      return xml ;
    }
    if( typeof XMLHttpRequest != "undefined" ) {
      var xhr = XHRCreate() ;
      xhr.open( "GET", url, false ) ;
      xhr.send( null ) ;
      return xhr.responseXML ;
    }
  }
  catch( x ) {}
}


function CreateDom()
{
  try {
    if( IsActiveX())  return new ActiveXObject( "Microsoft.XMLDOM" ) ;
    var ok = AjaxIsObj( document.implementation ) && 
       AjaxIsObj( document.implementation.createDocument ) ;
    if( ok ) return document.implementation.createDocument( '', '', null ) ;
  }
  catch( x ) {}
}

function ParseXml( text )
{
  try {
    if( IsActiveX() ) {
      var xml = new ActiveXObject( "Msxml2.FreeThreadedDOMDocument.3.0" ) ;
      xml.async = false ;
      xml.validateOnParse = false ;
      xml.loadXML( text ) ;
      return xml ;
    }
    if( typeof DOMParser != "undefined" ) {
      var p = new DOMParser() ;
      return p.parseFromString( text, "text/xml" ) ;
    }
  }
  catch( x ) {}
}


function ExtractXml( div )
{
  var t = div.innerHTML ;
  var l0 = t.indexOf( '<!--' ) + 4, l1 = t.lastIndexOf( '-->' ) ;
  l0 = t.indexOf( '<', l0 ) ;
  l1 = t.lastIndexOf( '>', l1 ) + 1 ;
  return t.substring( l0, l1 ) ;
}

function XmlFromDiv( div ) { return ParseXml( ExtractXml( div ) ) ; }

function XSLTransform( xsl, xml )
{
  try {
    if( IsActiveX() ) {
      var templ = new ActiveXObject( "Msxml2.XSLTemplate" ) ;
      templ.stylesheet = xsl ;	     
      var p = templ.createProcessor() ;
      p.input = xml ;
      p.transform() ;
      return p.output ;
    }
    if( typeof XSLTProcessor != "undefined" ) {
      var p = new XSLTProcessor() ;
      p.importStylesheet( xsl ) ;
      var f = p.transformToFragment( xml, document ) ;
      var s = new XMLSerializer() ;	
      return s.serializeToString( f ) ;
    }
  }
  catch( x ) {}

}


function XMLIsFF( node )
{
  return node.evaluate || ( node.ownerDocument && node.ownerDocument.evaluate ) ;
}


function XMLIsIE( node )
{
  return IsActiveX();
}


function XMLSelectNode( node, xpath )
{
  if( !AjaxIsObj( node ) ) return null ;
  if(  IsActiveX() ) return node.selectSingleNode( xpath ) ;
  if( XMLIsFF( node ) ) {
    var owner = node.ownerDocument, el = node ;
    if( owner == null ) owner = node, el = node.documentElement ;
    return owner.evaluate( xpath, el, null, 9, null ).singleNodeValue ;

  }
  throw Error( "Browser does not support xpath selection." ) ;
}


function GetAttr( d, a, def )
{
  var v = AjaxIsObj( d ) && d.nodeType == 1 ? d.getAttribute( a ) : null ;
  return v != null ? v : ( ( def == undefined ) ? null : def ) ;
}

function SetAttr( d, a, v )
{
  if( AjaxIsObj( d ) ) d.setAttribute( a, v ) ;
}

function SetDocAttr( xml, a, v )
{
  if( IsXML( xml ) ) xml.documentElement.setAttribute( a, v ) ;
}

function GetDocAttr( xml, a, def )
{
  var v = IsXML( xml ) ? xml.documentElement.getAttribute( a ) : null ;
  return v != null ? v : ( ( def == undefined ) ? null : def ) ;
}

function CreateElement( xml, tag )
{
  return IsXML( xml ) ? xml.createElement( tag ) : null ;
}


function XMLSelectNodes( node, xpath )
{
  if( !AjaxIsObj( node ) ) return null ;
  if(  IsActiveX()) return node.selectNodes( xpath ) ;
  if( XMLIsFF( node ) ) {
    var owner = node.ownerDocument, el = node ;
    if( owner == null ) owner = node, el = node.documentElement ;
    return owner.evaluate( xpath, el, null, XPathResult.ANY_TYPE, null );

  }
  throw Error( "Browser does not support xpath selection." ) ;
}

function XSLTransformX( xsl, xml , obj)
{
  try {
    if( IsActiveX() ) {
      var templ = new ActiveXObject( "Msxml2.XSLTemplate" ) ;
      templ.stylesheet = xsl ;	     
      var p = templ.createProcessor() ;
      p.input = xml ;
      p.transform() ;
      obj.innerHTML=p.output ;
    }
    if( typeof XSLTProcessor != "undefined" ) {
      var p = new XSLTProcessor() ;
      p.importStylesheet( xsl ) ;
      var f = p.transformToFragment( xml, document ) ;
      obj.innerHTML=""; obj.appendChild(f);
    }
  }
  catch( x ) {}

}

