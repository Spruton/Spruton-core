/*
Copyright 2004-2011 Herbert Street Technologies Ltd
*/

function ThrowError( err, msg )
{
  if( err ) throw Error( msg ) ;
}

function UnexpectedError( err ) { ThrowError( err, 'Unexpected error.' ) ; }  

function ElByName( n ) { return document.getElementsByName( n ) ; }

function ElById( n ) 
{ 
  return ( ( n + '' ) === n ) ? document.getElementById( n ) : n ; 
}

function Print()
{
  var a = arguments, n = a.length, f = a[ 0 ], i = 0 ;
  if( ( f + '' ) !== f ) return f.toString() ;
  return f.replace(/\%s|\%\%/g, 
    function( ss ) { return  ( ss == '%%' ) ? '%' : ( a[ ++i ] ? a[ i ] : '' ) ; } ) ;  
}

function ResetFields( val )
{
  var a = arguments, l = a.length ;
  for( var i = 1 ; i < l ; ) a[ i++ ].value = val ;
}

function IsObj( o )
{
  return o != null && o != undefined ;
}

function VIsError( args )
{
  for( var i = 0  ; i < args.length ; i++ ) {
    if( args[ i ] instanceof Error ) return true ;
  }
  return false ;
}

function IsError() { return VIsError( arguments ) ; }

function VHide( args )
{
  for( var i = 0  ; i < args.length ; i++ ) {
    var a = ElById( args[ i ] ) ;
    if( IsObj( a ) && IsObj( a.style ) ) a.style.display = "none" ;
  }
}

function Hide() { VHide( arguments ) ; }

function VShow( args )
{ 
  for( var i = 0  ; i < args.length ; i++ ) {
    var a = ElById( args[ i ] ) ;
    if( IsObj( a ) && IsObj( a.style ) ) a.style.display = 'block' ;
  }
}

function Show() { VShow( arguments ) ; }

function XShow()
{
  VHide( arguments ) ;
  Show( arguments[ 0 ] ) ;
}

function VDisplay( args )
{
  for( var i = 1  ; i < args.length ; i++ ) {
    var a = ElById( args[ i ] ) ;
    if( IsObj( a ) && IsObj( a.style ) ) a.style.display = args[ 0 ] ;
  }
}

function Display() { VDisplay( arguments ) ; } 

function Veil()
{
  var args = arguments, v = args[ 0 ] ? 'hidden' : 'visible' ;
  for( var i = 1  ; i < args.length ; i++ ) {
    var a = ElById( args[ i ] ) ;
    if( IsObj( a ) && IsObj( a.style ) ) a.style.visibility = v ;
  }
}

function SetVisible()
{
  for( var i = 0  ; i < arguments.length ; i++ ) {
    arguments[ i ].style.visibility = "visible" ;
  }
}

function SetHidden()
{
  for( var i = 0  ; i < arguments.length ; i++ ) {
    arguments[ i ].style.visibility = "hidden" ;
  }
}

function RadioValue( n )
{
  var r = ElByName( n ) ;
  for( var i = 0 ; i < r.length ; i++ ) if( r[ i ].checked ) return r[ i ].value ;
}

function FieldArray()
{
  v = new Array() ;
  for( var i = 0, a ; i < arguments.length ; i++ ) {
    if( IsObj( a = arguments[ i ] ) ) v[ i ] = a.value ;
  }
  return v ;
}

function ViewPswd( x ) {  alert( x.value ) ; }

function ShowUnexpected( x )
{
  alert( "Unexpected errror.\n" + x.message ) ;
}

function Home( noReload ) { try { parent.MHome( noReload ) ; } catch( x ) {} }

function ShowLogin()  { try { parent.MShowLogin() ; } catch( x ) {} }

function LFWin()
{
  try { return parent.document.getElementById('IFLogin').contentWindow ; }
  catch( x ) { return null ; }
}

function LFDoc()
{
  try { return LFWin().document ; }
  catch( x ) { return null ; }
}

function LFAll()
{
  try { return LFDoc().all ; }
  catch( x ) { return null ; }
}


function LFValue( n ) 
{
  try { return LFDoc().getElementById( n ).value ; }
  catch( x ) { return '' ; }
}

function Login( p )
{
  try { LFWin().LPLogin( p ) ; } 
  catch( x ) { ShowUnexpected( x ) ; } 
}

function XXW()
{
  try { return LFValue('IXXW') ; }
  catch( x ) { 
    throw new Error( "Cannot complete action.\nMaster password is unknown." ) ;  
  }
}

function XXHacs() { 
  try { return LFValue('IHacs'); ; }
  catch( x ) {
    throw new Error( "Cannot complete action.\nHash is unknown." ) ;  
  }
}

function XXHash() { 
  try { return LFValue('IHash') ; }
  catch( x ) {
    throw new Error( "Cannot complete action.\nHash is unknown." ) ;  
  }
}

function XXSync()
{
  try { return LFValue('ISync') ; }
  catch( x ) { 
    throw new Error( "Cannot complete action.\nSync is unknown." ) ;  
  }
}

function SetCryptoData( sync, hash, xxw )
{
  try {
    var doc = LFDoc() ;
    doc.getElementsByName('IXXW').value = xxw ;
    doc.getElementsByName('ISync').value = sync ;
    doc.getElementsByName('IHash').value = hash ;
  }
  catch( x ) { 
    throw new Error( "Cannot update internal data.\nPlease relogin." ) ;  
  }
}

function EncodeArgs()
{
  return XHREncodeArgs( "vnid", GVID, "vnak", GVAK ) + "&" +
      XHRVEncodeArgs( arguments )
}

function CalcAbsLeft( obj )
{
  var l = 0 ;
  for( ; obj != null ; obj = obj.offsetParent ) l += obj.offsetLeft ;
  return l ;
}

function CalcAbsTop( obj )
{
  var l = 0 ;
  for( ; obj != null ; obj = obj.offsetParent ) l += obj.offsetTop ;
  return l ;
}

function ExtractFileName( p )
{
  p = p.replace( /\\/g, '/' ) ;
  p = p.substr( p.lastIndexOf( '/' ) + 1 ) ;
  return p.substr( 0, p.lastIndexOf( '.' ) ) ;
}

function ExtractFileExt( p )
{
  var i = p.lastIndexOf( '.' ) ;
  return i < 0 ? "" : p.substr( i ) ;
}

function Trim(f)
{
  return f.replace(/^\s+|\s+$/g, "");
}

function IsImageFile( f )
{
  var type = ExtractFileExt( f ).toLowerCase() ;
  var types = Array( ".jpg", ".jpeg", ".jpe", ".gif", ".png", ".bmp", 
      ".tif", ".tiff" ) ;
  for( var i = 0, l = types.length ; i < l ; i++ ) {
    if( types[ i ] == type ) return true ;
  }
  return false ;
}

function SetFrameBody( fr, html )
{
  fr.contentWindow.document.body.innerHTML = html ;
}

function DocA() { return document.all ; }

function DA() { return document.all ; }

function ViewImage( url )
{
  var x = 300, width = 600, height = 400 ;
  width += 8, height += 8 ;
  var sWidth = screen.width, sHeight = screen.height ;
  if( width > sWidth ) width = sWidth ;
  if( height > sHeight ) height = sHeight ;
  var y = ( sHeight - height ) / 2 ;
  if( ( x + width ) > sWidth ) x = sWidth - width ;
  width -= 8 ;
  height -= 8 ;
  var optns = new Array( "width=" + width, "height=" + height, 
      "left=" + x, "top=" + y, "scrollbars=1, resizable=1, menubar=1" ) ; 
  var win = window.open( url, "", optns.toString() ) ;
  win.focus() ;  
}

function IsInfo( html )
{
  return IsObj( html ) ? ( html.search( 'sso-info-signature' ) >= 0 ) : false ;
}

function StrToHtml( s )
{
  return s.replace( /<|&|>/g , 
     function( ch )
     {
       if( ch == '<' ) return '&lt;' ;
       if( ch == '>' ) return '&gt;' ;
       if( ch == '&' ) return '&amp;' ;
       return ch ;
     }
  ) ;
}

function VDisable( objs )
{
  for( var i = 0, l = objs.length ; i < l ; i++ ) {
    var obj = objs[ i ] ;
    if( IsObj( obj ) ) obj.disabled = true ;
  }
}

function Disable() { VDisable( arguments ) ; }

function VEnable( objs )
{
  for( var i = 0, l = objs.length ; i < l ; i++ ) {
    var obj = objs[ i ] ;
    if( IsObj( obj ) ) obj.disabled = false ; 
  }
}

function Enable() { VEnable( arguments ) ; } 

function VTurn( objs )
{
  for( var i = 1, l = objs.length ; i < l ; i++ ) {
    var obj = objs[ i ] ;
    if( IsObj( obj ) ) obj.disabled = !objs[ 0 ] ; 
  }
}

function Turn() { VTurn( arguments ) ; }

function FixClass( obj, cls, sel )
{
  if( IsObj( obj ) && obj.className != sel ) obj.className = cls ;
}

function SetClass( cls ) 
{ 
  for( var i = 1, a, l = arguments.length ; i < l ; i++ ) {
    if( IsObj( a = arguments[ i ] ) ) a.className = cls ;
  }
}

function XSetClass( obj, sel, cls )
{
  for( var i = 3, a, l = arguments.length ; i < l ; i++ ) {
    if( IsObj( a = arguments[ i ] ) ) a.className = cls ;
  }
  if( IsObj( obj ) ) obj.className = sel ;
}


function SetLeftTop( obj, x, y )
{
 if( x != undefined ) obj.style.left = x ;
 if( y != undefined ) obj.style.top = y ;
}


function Focus( obj )
{
  try { obj.focus() ; } catch( x ) {}
}

function Warn( m, obj ) 
{ 
  alert( m ) ; 
  Focus( obj ) ; 
  return false ; 
}

function Call( fun, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10 )
{
  return IsObj( fun ) ? fun( a1, a2, a3, a4, a5, a6, a7, a8, a9, a10 ) :
      undefined ;
}

function SetValue( obj, v, def ) { obj.value = NormVal( v, def ) ; }

function NormVal( v, def ) { return IsObj( v ) ? v : def ; }

function ShowMenu( x, y, menu )
{
  var w, h, bw, bh ;
  SetHidden( menu ) ;
  Show( menu ) ;
  with( menu ) { w = offsetWidth, h = offsetHeight ; } 
  with( document.body ) { 
    bw = scrollLeft + offsetWidth ;
    bh = scrollTop + offsetHeight ; 
  }
  if( x - w >= 0 && x + w > bw ) x -= w ;
  if( y - h >= 0 && y + h > bh ) y -= h ;
  menu.style.left = x + "px" ;
  menu.style.top = y + "px" ;
  SetVisible( menu ) ;
}


function DropDownMenu( m, s )
{
  SetHidden( m ) ;
  Show( m ) ;
  var x = CalcAbsLeft( s ), y = CalcAbsTop( s ) + s.offsetHeight,
      w = m.offsetWidth, bw ;
  with( document.body ) { bw = scrollLeft + offsetWidth ; }
  if( x - w >= 0 && x + w > bw ) x = bw - w ;
  m.style.left = x + "px"; m.style.top = y + "px" ; 
  SetVisible( m ) ;
}

function NormFormPos( f, x, y )
{
  if( IsObj( f ) == false ) return ;
  var w, h, bw, bh, x, y ;
  with( f ) { w = offsetWidth, h = offsetHeight ; } 
  var body=document.body;
  bw = body.offsetWidth, bh = body.offsetHeight ;
  if( x == undefined ) x = Math.floor( ( bw - w ) / 2 ) ;
  if( y == undefined ) y = Math.floor( ( bh - h ) / 2 ) ;
  if( ( x + w ) > bw ) x = bw - w ;  if(x < 0) x=10;
  if( ( y + h ) > bh ) y = bh - h ;  if(y < 0) y=10;
  x+=body.scrollLeft; y+=body.scrollTop;

  f.style.left = x + "px" ;
  f.style.top = y + "px" ;
}

function GetEvent( ev ) { return window.event ? window.event : ev ; }

function EventSource( ev )
{
  ev = GetEvent( ev ) ;
  return IsObj( ev.srcElement ) ? ev.srcElement : ev.target ;  
}

function IsLeftButton( ev )
{
  ev = GetEvent( ev ) ;
  return ev.button == ( IsObj( window.event ) ? 1 : 0 ) ;
}

function PreventDefault( ev )
{
  ev = GetEvent( ev ) ;
  if( ev.preventDefault ) ev.preventDefault() ;
  ev.returnValue = false ;
}

var msgCUs =  "Please try a different browser, or ContactUs\n" +
    "with a description of the problem." ;

function CancelBubble( ev )
{
  ev = GetEvent( ev ) ;
  var msg = "Your browser does not support cancelling bubble.\n" + msgCUs ;
  if( ev.stopPropagation ) ev.stopPropagation() ;
  else if( ev.cancelBubble != undefined ) ev.cancelBubble = true ;
//  else throw Error( msg ) ;
}

function AttachEvent( obj, type, fun )
{
  var msg = "Your browser does not support attaching event.\n" + msgCUs ;
  if( obj.addEventListener ) obj.addEventListener( type, fun, false ) ;
  else if( obj.attachEvent ) obj.attachEvent( "on" + type, fun ) ;
  else throw Error( msg ) ;
}

function DetachEvent( obj, type, fun )
{
  var msg = "Your browser does not support detaching event.\n" + msgCUs ;
  if( obj.removeEventListener ) obj.removeEventListener( type, fun, false ) ;
  else if( obj.detachEvent ) obj.detachEvent( "on" + type, fun ) ;
  else throw Error( msg ) ;
}

function TrimVal( el ) { return Trim( el.value ) ; }

function NormText( el, len ) { return TrimVal( el ).substr( 0, len ) ; }

function NormDesc( el ) { return NormText( el, 300 ) ; }

function NormTitle( el ) { return NormText( el, 128 ) ; }

function NormAddress( el ) { return NormText( el, 500 ) ; }

function SelText( sel ) { return sel.options[ sel.selectedIndex ].text ; }

function VerifyQA( qi, ai )
{
  var msgQuest = "Please specify your identity question.",
      msgLongQuest = "Length of question should not exceed 128 characters." ;
      msgShortAnswer = "Length of answer should not be less 5 characters.",
      msgLongAnswer = "Length of answer should not exceed 128 characters." ;
  var obj, m, q = TrimVal( qi ), a = TrimVal( ai ), ql, al ;
  ql = q.length, al = a.length ;
  if( ql <= 0 ) obj = qi, m = msgQuest ;
  else if( ql > 128 ) obj = qi, m = msgLongQuest ;
  else if( al < 5 ) obj = ai, m = msgShortAnswer ;
  else if( al > 128 ) obj = ai, m = msgLongAnswer ;
  else return true ;
  return Warn( m, obj ) ;
}

function VerifyMP( pi )
{
  var svc = new TSVC(), 
      msg = "Password entered is incorrect.\n" +
            "Please note that passwords are case-sensitive." ;
  svc.SetStrPswd( pi.value ) ;
  svc.InitW( XXToSA( XXSync() ) ) ;
  return ( XXW() == SAToXX( svc.W ) ) ? true : Warn( msg, pi ) ;
}

function VerifyEmail(emi) {
 if(CheckEmail(emi.value) == false) { try{ emi.focus();}catch(e){;} return false;}
 return true;
}

function CheckEmail(emailStr) {
var checkTLD=1;
var knownDomsPat=/^(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum)$/;
var emailPat=/^(.+)@(.+)$/;
var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]";
var validChars="\[^\\s" + specialChars + "\]";
var quotedUser="(\"[^\"]*\")";
var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
var atom=validChars + '+';

var word="(" + atom + "|" + quotedUser + ")";

var userPat=new RegExp("^" + word + "(\\." + word + ")*$");
var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");
var matchArray=emailStr.match(emailPat);

if (matchArray==null) {

alert("Please specify valid email address.");
return false;
}
var user=matchArray[1];
var domain=matchArray[2];

// Start by checking that only basic ASCII characters are in the strings (0-127).

for (i=0; i<user.length; i++) {
if (user.charCodeAt(i)>127) {
alert("The username contains invalid characters.");
return false;
   }
}
for (i=0; i<domain.length; i++) {
if (domain.charCodeAt(i)>127) {
alert("Ths domain name contains invalid characters.");
return false;
   }
}

// See if "user" is valid 

if (user.match(userPat)==null) {

// user is not valid

alert("The username doesn't seem to be valid.");
return false;
}

/* if the e-mail address is at an IP address (as opposed to a symbolic
host name) make sure the IP address is valid. */

var IPArray=domain.match(ipDomainPat);
if (IPArray!=null) {

// this is an IP address

for (var i=1;i<=4;i++) {
if (IPArray[i]>255) {
alert("Destination IP address is invalid!");
return false;
   }
}
return true;
}

// Domain is symbolic name.  Check if it's valid.
var EmailIsnotValid="The email address does not seem valid .Maybe missing a dot? Please check and try again."; 
var atomPat=new RegExp("^" + atom + "$");
var domArr=domain.split(".");
var len=domArr.length;
for (i=0;i<len;i++) {
if (domArr[i].search(atomPat)==-1) {
//alert("The domain name does not seem to be valid.");
alert(EmailIsnotValid);
return false;
   }
}


if (checkTLD && domArr[domArr.length-1].length!=2 && 
domArr[domArr.length-1].search(knownDomsPat)==-1) {
//alert("The address must end in a well-known domain or two letter " + "country.");

alert(EmailIsnotValid);
return false;
}

// Make sure there's a host name preceding the domain.

if (len<2) {
//alert("This address is missing a hostname!");
alert(EmailIsnotValid);
return false;
}

// If we've gotten this far, everything's valid!
return true;
}




function VerifyDetails( gender, first, last, birth, lang, country, city, addr )
{
  var msgCountry = "Please specify country.", 
      msgGender = "Please specify gender.",
      msgFirst = "Please specify first name.",
      msgLast = "Please specify last name.",
      msgBirth = "Please specify when you was born.",
      msgLang = "Please specify language preference.",
      msgCity = "Please specify city/region.",
      msgAddr = "Please specify address." ;

  var obj, m;
  if(IsObj(gender))
   if( gender.value == '' ) return Warn( msgGender, gender );

  if(IsObj(first))
    if( TrimVal( first ) == '' ) return Warn(msgFirst,first);
 
  if(IsObj(last))
    if( TrimVal(last ) == '' ) return Warn(msgLast,last);

 if(IsObj(birth)) 
  if( birth.value == '' ) return Warn(msgBirth,birth);

 if(IsObj(lang)) 
  if( lang.value == '' ) return Warn(msgLang,lang);


 if(IsObj(country)) 
  if( country.value == '' ) return Warn(msgCountry,country);

 if(IsObj(city)) 
  if( TrimVal(city) == '' ) return Warn(msgCity,city);


 if(IsObj(addr)) 
   if( TrimVal(addr) == '' ) return Warn(msgAddr,addr);
  return true ;

}


function VerifyName( ni )
{
  var name = TrimVal( ni ), msg = "Please specify name." ;
  return ( name != '' ) ? true : Warn( msg, ni ) ;
}

function VerifyTitle( ti )
{
  var t = TrimVal( ti ), msg = "Please specify title." ;
  return ( t != '' ) ? true : Warn( msg, ti ) ;
}

function VerifyHost( obj )
{
  var msgScheme= "Please specify scheme, e.g. http:// or https://", 
      msgHost = "Please specify host address.",
      msgLongHost = "Length of host should not exceed 240 characters.",
      h = TrimVal( obj ), m ;

  var re = /^http(s)?\:\/\// ;
  if( h == '' ) m = msgHost ;
  else if( !re.test( h ) ) m = msgScheme ;
  else if( h.length > 240 ) m = msgLongHost ;
  else return true ;
  return Warn( m, obj ) ;
}

function VerifyLogin( li )
{
  var msgLogin = "Please specify login name.",
      msgLongLogin = "Length of login name should not exceed 240 characters.",
      l = TrimVal( li ), m ;
  if( l == '' ) m = msgLogin ;
  else if( l.length > 240 ) m = msgLongLogin ;
  else return true ;
  return Warn( m, li ) ;
}

function VerifyConfirm( mp, cp )
{
  var msg = "Confirm password does not match.\nPlease repeat." ;
  return ( mp.value == cp.value ) ? true : Warn( msg, cp ) ;
}

function PopupBlockerAlert()
{
  var msg = "It seems you have a popup blocker\n" + 
      "which is blocking the opening of a new window on this network\n" +
      "...perhaps you should modify the blockers instructions for this site...!" ;
  alert( msg ); 
}

function OpenDoc( url, n )
{
  var argc = OpenDoc.arguments.length;  
  var argv = OpenDoc.arguments;
  if(argc < 2) n="_blank";
  var sw = screen.width, sh = screen.height ;
  var w = sw * 0.8, h = sh * 0.8 ;
  if(argc >= 4){
   w=argv[2]; h=argv[3];
  }

  var y = 14, x = sw - w - 25 ;
  var optns = new Array( "width=" + w, "height=" + h, 
      "left=" + x, "top=" + y, 
      "location=0, toolbar=1, scrollbars=1, resizable=1, menubar=1" ) ; 
  var win = window.open( url, n, optns.toString() ) ;
  Focus( win ) ;  
}


function OpenUrl( url ) 
{ 
  return window.open( typeof( url ) == 'string' ? url : url.value, '', '' ) ; 
}

function StartGif( img ) { img.src = img.src ; }

function InitAxNtf( obj, i, u, na )
{
  var v = AxVerify( obj ), d ;
  if( v == 'fail' ) d = u ;
  else if( v == 'none' ) d = i ;
  else if( v != 'ok' ) d = na ;
  Display( 'inline', d ) ;
}

function FixScheme( url )
{
  return ( url.match( /^[a-z]\w+:.+/i ) == null ) ? ( "http://" + url ) : url ;
}

function TextToHtml( t )
{
  return t.replace( /</g, "&lt;" ).replace( />/g, "&gt;" ) ;
}

function SetCaption( c, t, m ) 
{ 
  if( !IsObj( m ) ) m = 30 ;
  t = Trim( IsObj( t ) ? ( t + '' ) : '' ) ;
  if( t.length > m ) t = t.substr( 0, m ) + '...' ;
  c.innerHTML = TextToHtml( t.length > 0 ? ( ' - ' + t ) : t ) ; 
}

function VCenter( el, cont )
{
  var t = CalcAbsTop( cont ) + ( cont.offsetHeight - el.offsetHeight ) / 2 ;
  el.style.top = t + "px" ;
}

function GetCookie( n )
{
  var r = document.cookie.match( '(^|;) ?' + n + '=([^;]*)(;|$)' ) ;
  return r ? r[ 2 ] : null ;
}

function Ceil( n ) { return Math.ceil( n ) ; }

function Floor( n ) { return Math.floor( n ) ; }

function ValueOf( id ) { return ( id = ElById( id ) ) ? id.value : '' ; }

function DefNormDesc(obj)
{

 if(CheckDefDescClass(obj) >= 0){
  ChangeDescClass(obj,false)
  SetValue( obj, '' ) ;
 }
 return NormDesc(obj);
}
function DescClassFocus(obj)
{ 
 if(CheckDefDescClass(obj) < 0) return;
 ChangeDescClass(obj,false)
 SetValue( obj, '' ) ;
}
function CheckDefDescClass(obj)
{
 var cls=obj.className;
 return cls.indexOf("defdesc");
}

function ChangeDescClass(obj,flag)
{
 var cls=obj.className;
 var n=cls.indexOf("defdesc");
 if(flag){//add
   if(n >= 0) return;
   obj.className="defdesc "+obj.className;
   return;
 }else{ //delete
  if(n < 0) return;
  obj.className=Trim(cls.substr(7));
 }

}

function return2br(dataStr) {
        return dataStr.replace(/(\r\n|\r|\n)/g, "<br/>");
    }
