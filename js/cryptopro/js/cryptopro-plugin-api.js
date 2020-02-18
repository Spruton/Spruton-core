var async_script="js/cryptopro/js/cryptopro-async_code.js";

function IsObj( o ){  return o != null && o != undefined ;}

function ElById( n ) 
{ 
  return ( ( n + '' ) === n ) ? document.getElementById( n ) : n ; 
}

function setHtmlById(id,html){
 var o=ElById( id);
 if(IsObj(o)) o.innerHTML=html;
}

function setValuelById(id,val){
 var o=ElById( id);
 if(IsObj(o)) o.value=val;
}

CERTIFCATE_STATUS_NOT_STARTED=1// Срок действия не наступил;
CERTIFCATE_STATUS_EXPIRED=2;//Срок действия истек
CERTIFCATE_STATUS_NO_PRIVATE_KEY=3;// Нет привязки к закрытому ключу
CERTIFCATE_STATUS_CHAIN_ERROR=4;//Ошибка при проверке цепочки сертификатов
CERTIFCATE_STATUS_ACTIVE=5;//Действителен


function GetCertificateStatus(code,lang){
 if(code == CERTIFCATE_STATUS_NOT_STARTED)
  if(lang == "ru") return "Срок действия не наступил";
  else             return "Not started";
 
 if(code == CERTIFCATE_STATUS_EXPIRED)
  if(lang == "ru") return "Срок действия истек";
  else             return "Expired";
 
 if(code == CERTIFCATE_STATUS_NO_PRIVATE_KEY)
  if(lang == "ru") return "Нет привязки к закрытому ключу";
  else             return  "No connection with private key";

 if(code == CERTIFCATE_STATUS_CHAIN_ERROR)
  if(lang == "ru") return "Ошибка при проверке цепочки сертификатов";
  else             return  "Certificate chaines error";

 if(code == CERTIFCATE_STATUS_ACTIVE)
   if(lang == "ru") return "Действителен";
   else             return "Active";
 
if(lang == "ru") return "Неопределенный";
 return "Undefined";
}

function UICertificateObj(certificate)
{
    this.certObj = new CertificateObj(certificate);
    this.certFromDate = new Date(certificate.ValidFromDate);
    this.certToDate = new Date(certificate.ValidToDate);
    this.IsValid=false;
    try {
        this.IsValid = certificate.IsValid().Result;    
    } catch (e) {
        
    }
   this.hasPrivateKey = certificate.HasPrivateKey();
   this.owner= this.certObj.GetCertName();
   this.issuer =  this.certObj.GetIssuer();
   this.from =  this.certObj.GetCertFromDate();
   this.till = this.certObj.GetCertTillDate();
   this.provider="";
   if (this.hasPrivateKey)  this.provider=this.certObj.GetPrivateKeyProviderName();
   this.pubkey_algo=this.certObj.GetPubKeyAlgorithm();
   this.status_code=GetCertStatusCode(this.certFromDate,this.certToDate,this.hasPrivateKey,this.IsValid);
   this.Thumbprint=certificate.Thumbprint;
}

function GetCertStatusCode(certFromDate,certToDate,hasPrivateKey,IsValid){
   var Now = new Date();
   var stattus_code=0;
    if(Now < certFromDate) {
      status_code=CERTIFCATE_STATUS_NOT_STARTED;
    } else if( Now > certToDate){
        status_code=CERTIFCATE_STATUS_EXPIRED;
    } else if( !hasPrivateKey ){
        status_code=CERTIFCATE_STATUS_NO_PRIVATE_KEY;
    } else if( !IsValid ){
      status_code=CERTIFCATE_STATUS_CHAIN_ERROR;
    } else {
      status_code=CERTIFCATE_STATUS_ACTIVE;
    }

   return status_code;
}

var isPluginEnabled = false;
var fileContent; // Переменная для хранения информации из файла
var global_selectbox_container = new Array();
var global_isFromCont = new Array();
var global_selectbox_counter = 0;

var async_code_included = 0;
var async_Promise;
var async_resolve;
function include_async_code()
{
    if(async_code_included)
    {
        return async_Promise;
    }
    var fileref = document.createElement('script');
    fileref.setAttribute("type", "text/javascript");
    fileref.setAttribute("src", async_script);
    document.getElementsByTagName("head")[0].appendChild(fileref);
    async_Promise = new Promise(function(resolve, reject){
        async_resolve = resolve;
    });
    async_code_included = 1;
    return async_Promise;
}

function Common_RetrieveCertificate()
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return RetrieveCertificate_Async();
        });
    }else
    {
        return RetrieveCertificate_NPAPI();
    }
}

function Common_CreateSimpleSign(id)
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return CreateSimpleSign_Async(id);
        });
    }else
    {
        return CreateSimpleSign_NPAPI(id);
    }
}

function Common_SignCadesBES(id, text, setDisplayData)
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return SignCadesBES_Async(id, text, setDisplayData);
        });
    }else
    {
        return SignCadesBES_NPAPI(id, text, setDisplayData);
    }
}

function Common_SignCadesBES_File(id) {
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if (canAsync) {
        include_async_code().then(function () {
            return SignCadesBES_Async_File(id);
        });
    } else {
        return SignCadesBES_NPAPI_File(id);
    }
}

function Common_SignCadesEnhanced(id, sign_type)
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return SignCadesEnhanced_Async(id, sign_type);
        });
    }else
    {
        return SignCadesEnhanced_NPAPI(id, sign_type);
    }
}

function Common_SignCadesXML(id)
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return SignCadesXML_Async(id);
        });
    }else
    {
        return SignCadesXML_NPAPI(id);
    }
}

function Common_CheckForPlugIn() {
    //cadesplugin.set_log_level(cadesplugin.LOG_LEVEL_DEBUG);
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return CheckForPlugIn_Async();
        });
    }else
    {
        return CheckForPlugIn_NPAPI();
    }
}

function Common_Encrypt() {
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return Encrypt_Async();
        });
    }else
    {
        return Encrypt_NPAPI();
    }
}

function Common_Decrypt(id) {
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return Decrypt_Async(id);
        });
    }else
    {
        return Decrypt_NPAPI(id);
    }
}

function GetCertificate_NPAPI(certListBoxId) {
    var e = document.getElementById(certListBoxId);
    var selectedCertID = e.selectedIndex;
    if (selectedCertID == -1) {
        alert("Select certificate");
        return;
    }
    return global_selectbox_container[selectedCertID];
}


function MakeCadesBesSign_NPAPI(dataToSign, certObject, setDisplayData, isBase64) {
    var errormes = "";

    try {
        var oSigner = cadesplugin.CreateObject("CAdESCOM.CPSigner");
    } catch (err) {
        errormes = "Failed to create CAdESCOM.CPSigner: " + err.number;
        throw errormes;
    }

    if (oSigner) {
        oSigner.Certificate = certObject;
    }
    else {
        errormes = "Failed to create CAdESCOM.CPSigner";
        throw errormes;
    }

    try {
        var oSignedData = cadesplugin.CreateObject("CAdESCOM.CadesSignedData");
    } catch (err) {
        alert('Failed to create CAdESCOM.CadesSignedData: ' + err.number);
        return;
    }

    var CADES_BES = 1;
    var Signature;

    if (dataToSign) {
        // Данные на подпись ввели
        oSignedData.ContentEncoding = 1; //CADESCOM_BASE64_TO_BINARY
        if(typeof(setDisplayData) != 'undefined')
        {
            //Set display data flag flag for devices like Rutoken PinPad
            oSignedData.DisplayData = 1;
        }
        if (typeof(isBase64) == 'undefined'){
            oSignedData.Content = Base64.encode(dataToSign);
        } else {
            oSignedData.Content = dataToSign;
        }
        oSigner.Options = 1; //CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN
        try {
            Signature = oSignedData.SignCades(oSigner, CADES_BES);
        }
        catch (err) {
            errormes = "Не удалось создать подпись из-за ошибки: " + cadesplugin.getLastError(err);
            throw errormes;
        }
    }
    return Signature;
}

function MakeCadesEnhanced_NPAPI(dataToSign, tspService, certObject, sign_type) {
    var errormes = "";

    try {
        var oSigner = cadesplugin.CreateObject("CAdESCOM.CPSigner");
    } catch (err) {
        errormes = "Failed to create CAdESCOM.CPSigner: " + err.number;
        throw errormes;
    }

    if (oSigner) {
        oSigner.Certificate = certObject;
    }
    else {
        errormes = "Failed to create CAdESCOM.CPSigner";
        throw errormes;
    }

    try {
        var oSignedData = cadesplugin.CreateObject("CAdESCOM.CadesSignedData");
    } catch (err) {
        alert('Failed to create CAdESCOM.CadesSignedData: ' + cadesplugin.getLastError(err));
        return;
    }

    var Signature;

    if (dataToSign) {
        // Данные на подпись ввели
        oSignedData.Content = dataToSign;
        oSigner.Options = 1; //CAPICOM_CERTIFICATE_INCLUDE_WHOLE_CHAIN
        oSigner.TSAAddress = tspService;
        try {
            Signature = oSignedData.SignCades(oSigner, sign_type);
        }
        catch (err) {
            errormes = "Не удалось создать подпись из-за ошибки: " + cadesplugin.getLastError(err);
            throw errormes;
        }
    }
    return Signature;
}

function MakeXMLSign_NPAPI(dataToSign, certObject) {
    try {
        var oSigner = cadesplugin.CreateObject("CAdESCOM.CPSigner");
    } catch (err) {
        errormes = "Failed to create CAdESCOM.CPSigner: " + err.number;
        throw errormes;
    }

    if (oSigner) {
        oSigner.Certificate = certObject;
    }
    else {
        errormes = "Failed to create CAdESCOM.CPSigner";
        throw errormes;
    }

    var signMethod = "";
    var digestMethod = "";

    var pubKey = certObject.PublicKey();
    var algo = pubKey.Algorithm;
    var algoOid = algo.Value;
    if (algoOid == "1.2.643.7.1.1.1.1") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 256 бит
        signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256";
        digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256";
    }
    else if (algoOid == "1.2.643.7.1.1.1.2") {   // алгоритм подписи ГОСТ Р 34.10-2012 с ключом 512 бит
        signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-512";
        digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-512";
    }
    else if (algoOid == "1.2.643.2.2.19") {  // алгоритм ГОСТ Р 34.10-2001
        signMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102001-gostr3411";
        digestMethod = "urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr3411";
    }
    else {
        errormes = "Данная демо страница поддерживает XML подпись сертификатами с алгоритмом ГОСТ Р 34.10-2012, ГОСТ Р 34.10-2001";
        throw errormes;
    }
    
    var CADESCOM_XML_SIGNATURE_TYPE_ENVELOPED = 0;

    try {
        var oSignedXML = cadesplugin.CreateObject("CAdESCOM.SignedXML");
    } catch (err) {
        alert('Failed to create CAdESCOM.SignedXML: ' + cadesplugin.getLastError(err));
        return;
    }

    oSignedXML.Content = dataToSign;
    oSignedXML.SignatureType = CADESCOM_XML_SIGNATURE_TYPE_ENVELOPED;
    oSignedXML.SignatureMethod = signMethod;
    oSignedXML.DigestMethod = digestMethod;

    var sSignedMessage = "";
    try {
        sSignedMessage = oSignedXML.Sign(oSigner);
    }
    catch (err) {
        errormes = "Не удалось создать подпись из-за ошибки: " + cadesplugin.getLastError(err);
        throw errormes;
    }

    return sSignedMessage;
}

function GetSignatureTitleElement()
{
    var elementSignatureTitle = null;
    var x = document.getElementsByName("SignatureTitle");

    if(x.length == 0)
    {
        elementSignatureTitle = document.getElementById("SignatureTxtBox").parentNode.previousSibling;

        if(elementSignatureTitle.nodeName == "P")
        {
            return elementSignatureTitle;
        }
    }
    else
    {
        elementSignatureTitle = x[0];
    }

    return elementSignatureTitle;
}

function SignCadesBES_NPAPI(certListBoxId, data, setDisplayData) {
    var certificate = GetCertificate_NPAPI(certListBoxId);
    var dataToSign = document.getElementById("DataToSignTxtBox").value;
    if(typeof(data) != 'undefined')
    {
        dataToSign = data;
    }
    var x = GetSignatureTitleElement();
    try
    {
        var signature = MakeCadesBesSign_NPAPI(dataToSign, certificate, setDisplayData);
        document.getElementById("SignatureTxtBox").innerHTML = signature;
        if(x!=null)
        {
            x.innerHTML = "Подпись сформирована успешно:";
        }
    }
    catch(err)
    {
        if(x!=null)
        {
            x.innerHTML = "Возникла ошибка:";
        }
        //document.getElementById("SignatureTxtBox").innerHTML = err;
        throw err;
    }
}

function SignCadesBES_NPAPI_File(certListBoxId) {
    var certificate = GetCertificate_NPAPI(certListBoxId);
    var dataToSign = fileContent;
    var x = GetSignatureTitleElement();
    try {
        var StartTime = Date.now();
        var setDisplayData;
        var signature = MakeCadesBesSign_NPAPI(dataToSign, certificate, setDisplayData, 1);
        var EndTime = Date.now();
        document.getElementsByName('TimeTitle')[0].innerHTML = "Время выполнения: " + (EndTime - StartTime) + " мс";
        document.getElementById("SignatureTxtBox").innerHTML = signature;
        if (x != null) {
            x.innerHTML = "Подпись сформирована успешно:";
        }
    }
    catch (err) {
        if (x != null) {
            x.innerHTML = "Возникла ошибка:";
        }
        //document.getElementById("SignatureTxtBox").innerHTML = err;
        throw err;

    }
}

function SignCadesEnhanced_NPAPI(certListBoxId, sign_type) {
    var certificate = GetCertificate_NPAPI(certListBoxId);
    var dataToSign = document.getElementById("DataToSignTxtBox").value;
    var tspService = document.getElementById("TSPServiceTxtBox").value ;
    var x = GetSignatureTitleElement();
    try
    {
        var signature = MakeCadesEnhanced_NPAPI(dataToSign, tspService, certificate, sign_type);
        document.getElementById("SignatureTxtBox").innerHTML = signature;
        if(x!=null)
        {
            x.innerHTML = "Подпись сформирована успешно:";
        }
    }
    catch(err)
    {
        if(x!=null)
        {
            x.innerHTML = "Возникла ошибка:";
        }
        //document.getElementById("SignatureTxtBox").innerHTML = err;
        throw err;

    }
}

function SignCadesXML_NPAPI(certListBoxId) {
    var certificate = GetCertificate_NPAPI(certListBoxId);
    var dataToSign = document.getElementById("DataToSignTxtBox").value;
    var x = GetSignatureTitleElement();
    try
    {
        var signature = MakeXMLSign_NPAPI(dataToSign, certificate);
        document.getElementById("SignatureTxtBox").innerHTML = signature;

        if(x!=null)
        {
            x.innerHTML = "Подпись сформирована успешно:";
        }
    }
    catch(err)
    {
        if(x!=null)
        {
            x.innerHTML = "Возникла ошибка:";
        }
        //document.getElementById("SignatureTxtBox").innerHTML = err;
        throw err;

    }
}

function MakeVersionString(oVer)
{
    var strVer;
    if(typeof(oVer)=="string")
        return oVer;
    else
        return oVer.MajorVersion + "." + oVer.MinorVersion + "." + oVer.BuildVersion;
}

function CheckForPlugIn_NPAPI() {
    function VersionCompare_NPAPI(StringVersion, ObjectVersion)
    {
        if(typeof(ObjectVersion) == "string")
            return -1;
        var arr = StringVersion.split('.');

        if(ObjectVersion.MajorVersion == parseInt(arr[0]))
        {
            if(ObjectVersion.MinorVersion == parseInt(arr[1]))
            {
                if(ObjectVersion.BuildVersion == parseInt(arr[2]))
                {
                    return 0;
                }
                else if(ObjectVersion.BuildVersion < parseInt(arr[2]))
                {
                    return -1;
                }
            }else if(ObjectVersion.MinorVersion < parseInt(arr[1]))
            {
                return -1;
            }
        }else if(ObjectVersion.MajorVersion < parseInt(arr[0]))
        {
            return -1;
        }

        return 1;
    }

    function GetCSPVersion_NPAPI() {
        try {
           var oAbout = cadesplugin.CreateObject("CAdESCOM.About");
        } catch (err) {
            alert('Failed to create CAdESCOM.About: ' + cadesplugin.getLastError(err));
            return;
        }
        var ver = oAbout.CSPVersion("", 75);
        return ver.MajorVersion + "." + ver.MinorVersion + "." + ver.BuildVersion;
    }

    function GetCSPName_NPAPI() {
        var sCSPName = "";
        try {
            var oAbout = cadesplugin.CreateObject("CAdESCOM.About");
            sCSPName = oAbout.CSPName(75);

        } catch (err) {
        }
        return sCSPName;
    }

    function ShowCSPVersion_NPAPI(CurrentPluginVersion)
    {
        if(typeof(CurrentPluginVersion) != "string")
        {
            document.getElementById('CSPVersionTxt').innerHTML = "Версия криптопровайдера: " + GetCSPVersion_NPAPI();
        }
        var sCSPName = GetCSPName_NPAPI();
        if(sCSPName!="")
        {
            var o=document.getElementById('CSPNameTxt');
            if(IsObj(o)) o.innerHTML = "Криптопровайдер: " + sCSPName;
        }
    }

    var isPluginLoaded = false;
    var isPluginWorked = false;
    var isActualVersion = false;
    try {
        var oAbout = cadesplugin.CreateObject("CAdESCOM.About");
        isPluginLoaded = true;
        isPluginEnabled = true;
        isPluginWorked = true;

        // Это значение будет проверяться сервером при загрузке демо-страницы
        var CurrentPluginVersion = oAbout.PluginVersion;
        if( typeof(CurrentPluginVersion) == "undefined")
            CurrentPluginVersion = oAbout.Version;

        document.getElementById('PlugInEnabledTxt').innerHTML = "Плагин загружен.";
        document.getElementById('PlugInVersionTxt').innerHTML = "Версия плагина: " + MakeVersionString(CurrentPluginVersion);
        ShowCSPVersion_NPAPI(CurrentPluginVersion);
    }
    catch (err) {
        // Объект создать не удалось, проверим, установлен ли
        // вообще плагин. Такая возможность есть не во всех браузерах
        var mimetype = navigator.mimeTypes["application/x-cades"];
        if (mimetype) {
            isPluginLoaded = true;
            var plugin = mimetype.enabledPlugin;
            if (plugin) {
                isPluginEnabled = true;
            }
            else
            {
            	document.getElementById('PlugInEnabledTxt').innerHTML = "КриптоПро плагин не загружен.";
            }
        }
    }
    FillCertList_NPAPI('CertListBox');

}

function CertificateObj(certObj)
{
    this.cert = certObj;
    this.certFromDate = new Date(this.cert.ValidFromDate);
    this.certTillDate = new Date(this.cert.ValidToDate);
}

CertificateObj.prototype.check = function(digit)
{
    return (digit<10) ? "0"+digit : digit;
}

CertificateObj.prototype.extract = function(from, what)
{
    certName = "";

    var begin = from.indexOf(what);

    if(begin>=0)
    {
        var end = from.indexOf(', ', begin);
        certName = (end<0) ? from.substr(begin) : from.substr(begin, end - begin);
    }

    return certName;
}

CertificateObj.prototype.DateTimePutTogether = function(certDate)
{
    return this.check(certDate.getUTCDate())+"."+this.check(certDate.getUTCMonth()+1)+"."+certDate.getFullYear();
    // + " " +this.check(certDate.getUTCHours()) + ":" + this.check(certDate.getUTCMinutes()) + ":" + this.check(certDate.getUTCSeconds());
}

CertificateObj.prototype.GetCertString = function()
{
    return this.extract(this.cert.SubjectName,'CN=') + "; Выдан: " + this.GetCertFromDate();
}

CertificateObj.prototype.GetCertFromDate = function()
{
    return this.DateTimePutTogether(this.certFromDate);
}

CertificateObj.prototype.GetCertTillDate = function()
{
    return this.DateTimePutTogether(this.certTillDate);
}

CertificateObj.prototype.GetPubKeyAlgorithm = function()
{
    return this.cert.PublicKey().Algorithm.FriendlyName;
}

CertificateObj.prototype.GetCertName = function()
{
    return this.extract(this.cert.SubjectName, 'CN=');
}

CertificateObj.prototype.GetIssuer = function()
{
    return this.extract(this.cert.IssuerName, 'CN=');
}

CertificateObj.prototype.GetPrivateKeyProviderName = function()
{
    return this.cert.PrivateKey.ProviderName;
}

function GetFirstCert_NPAPI() {
    try {
        var oStore = cadesplugin.CreateObject("CAdESCOM.Store");
        oStore.Open();
    }
    catch (e) {
        throw ("Certificate not found");
        return;
    }

    var dateObj = new Date();
    var certCnt;

    try {
        certCnt = oStore.Certificates.Count;
        if(certCnt==0)
            throw "Certificate not found";
    }
    catch (ex) {
        oStore.Close();
        document.getElementById("boxdiv").style.display = '';
        return;
    }

    if(certCnt) {
        try {
            for (var i = 1; i <= certCnt; i++) {
                var cert = oStore.Certificates.Item(i);
                if(dateObj<cert.ValidToDate && cert.HasPrivateKey() && cert.IsValid().Result){
                    return cert;
                }
            }
        }
        catch (ex) {
            //alert("Ошибка при перечислении сертификатов: " + cadesplugin.getLastError(ex));
             throw "Ошибка при перечислении сертификатов: " + cadesplugin.getLastError(ex);

            return;
        }
    }
}

function CreateSimpleSign_NPAPI()
{
    oCert = GetFirstCert_NPAPI();
    var x = GetSignatureTitleElement();
    try
    {
        if (typeof oCert != "undefined") {
            FillCertInfo_NPAPI(oCert);
            var sSignedData = MakeCadesBesSign_NPAPI(txtDataToSign, oCert);
            document.getElementById("SignatureTxtBox").innerHTML = sSignedData;
            if(x!=null)
            {
                x.innerHTML = "Подпись сформирована успешно:";
            }
        }
    }
    catch(err)
    {
        if(x!=null)
        {
            x.innerHTML = "Возникла ошибка:";
        }
        //document.getElementById("SignatureTxtBox").innerHTML = err;
        throw err;
    }
}

function onCertificateSelected(event) {
    var selectedCertID = event.target.selectedIndex;
    var certificate = global_selectbox_container[selectedCertID];
    FillCertInfo_NPAPI(certificate, event.target.boxId, global_isFromCont[selectedCertID]);
}


function FillCertList_NPAPI(lstId) {
    try {
        var lst = document.getElementById(lstId);
        if(!lst)
            return;
    }
    catch (ex) {
        return;
    }

    lst.onchange = onCertificateSelected;
    lst.boxId = lstId;
    var MyStoreExists = true;

    try {
        var oStore = cadesplugin.CreateObject("CAdESCOM.Store");
        oStore.Open();
    }
    catch (ex) {
        MyStoreExists = false;
    }


    var certCnt;
    if(MyStoreExists) {
        certCnt = oStore.Certificates.Count;
        for (var i = 1; i <= certCnt; i++) {
            var cert;
            try {
                cert = oStore.Certificates.Item(i);
              if(!cert.HasPrivateKey()) continue; // RAI , show only with private key

            }
            catch (ex) {
                alert("Ошибка при перечислении сертификатов: " + cadesplugin.getLastError(ex));
                return;
            }

            var oOpt = document.createElement("OPTION");
            
            try {
                    var certObj = new CertificateObj(cert, true);
                    
                    oOpt.text = certObj.GetCertString();
            }
            catch (ex) {
                alert("Ошибка при получении свойства SubjectName: " + cadesplugin.getLastError(ex));
            }

          

            try {
                oOpt.value = global_selectbox_counter
                global_selectbox_container.push(cert);
                global_isFromCont.push(false);
                global_selectbox_counter++;
            }
            catch (ex) {
                alert("Ошибка при получении свойства Thumbprint: " + cadesplugin.getLastError(ex));
            }

            lst.options.add(oOpt);
        }

        oStore.Close();
    }

    //В версии плагина 2.0.13292+ есть возможность получить сертификаты из 
    //закрытых ключей и не установленных в хранилище

    try {
        oStore.Open(cadesplugin.CADESCOM_CONTAINER_STORE);
        certCnt = oStore.Certificates.Count;
        for (var i = 1; i <= certCnt; i++) {
            var cert = oStore.Certificates.Item(i);
            //Проверяем не добавляли ли мы такой сертификат уже?
            var found = false;
            for (var j = 0; j < global_selectbox_container.length; j++)
            {
                if (global_selectbox_container[j].Thumbprint === cert.Thumbprint)
                {
                    found = true;
                    break;
                }
            }
            if(found)                 continue;
            var certObj = new CertificateObj(cert);
            if(!cert.HasPrivateKey()) continue; // RAI , show only with private key
            var oOpt = document.createElement("OPTION");
            oOpt.text = certObj.GetCertString();
            oOpt.value = global_selectbox_counter
            global_selectbox_container.push(cert);
            global_isFromCont.push(true);
            global_selectbox_counter++;
            lst.options.add(oOpt);
        }
        oStore.Close();
    }
    catch (ex) {
    }



    if(global_selectbox_container.length == 0) {
        document.getElementById("boxdiv").style.display = '';
    }
}



function CreateCertRequest_NPAPI()
{
    try {
        var PrivateKey = cadesplugin.CreateObject("X509Enrollment.CX509PrivateKey");
    }
    catch (e) {
        alert('Failed to create X509Enrollment.CX509PrivateKey: ' + cadesplugin.getLastError(e));
        return;
    }

    PrivateKey.ProviderName = "Crypto-Pro GOST R 34.10-2001 Cryptographic Service Provider";
    PrivateKey.ProviderType = 75;
    PrivateKey.KeySpec = 1; //XCN_AT_KEYEXCHANGE

    try {
        var CertificateRequestPkcs10 = cadesplugin.CreateObject("X509Enrollment.CX509CertificateRequestPkcs10");
    }
    catch (e) {
        alert('Failed to create X509Enrollment.CX509CertificateRequestPkcs10: ' + cadesplugin.getLastError(e));
        return;
    }

    CertificateRequestPkcs10.InitializeFromPrivateKey(0x1, PrivateKey, "");

    try {
        var DistinguishedName = cadesplugin.CreateObject("X509Enrollment.CX500DistinguishedName");
    }
    catch (e) {
        alert('Failed to create X509Enrollment.CX500DistinguishedName: ' + cadesplugin.getLastError(e));
        return;
    }

    var CommonName = "Test Certificate";
    DistinguishedName.Encode("CN=\""+CommonName.replace(/"/g, "\"\"")+"\";");

    CertificateRequestPkcs10.Subject = DistinguishedName;

    var KeyUsageExtension = cadesplugin.CreateObject("X509Enrollment.CX509ExtensionKeyUsage");
    var CERT_DATA_ENCIPHERMENT_KEY_USAGE = 0x10;
    var CERT_KEY_ENCIPHERMENT_KEY_USAGE = 0x20;
    var CERT_DIGITAL_SIGNATURE_KEY_USAGE = 0x80;
    var CERT_NON_REPUDIATION_KEY_USAGE = 0x40;

    KeyUsageExtension.InitializeEncode(
                CERT_KEY_ENCIPHERMENT_KEY_USAGE |
                CERT_DATA_ENCIPHERMENT_KEY_USAGE |
                CERT_DIGITAL_SIGNATURE_KEY_USAGE |
                CERT_NON_REPUDIATION_KEY_USAGE);

    CertificateRequestPkcs10.X509Extensions.Add(KeyUsageExtension);

    try {
        var Enroll = cadesplugin.CreateObject("X509Enrollment.CX509Enrollment");
    }
    catch (e) {
        alert('Failed to create X509Enrollment.CX509Enrollment: ' + cadesplugin.getLastError(e));
        return;
    }
    var cert_req;
    try {
        Enroll.InitializeFromRequest(CertificateRequestPkcs10);
        cert_req = Enroll.CreateRequest(0x1);
    } catch (e) {
        alert('Failed to generate KeyPair or reguest: ' + cadesplugin.getLastError(e));
        return;    
    }
    
    return cert_req;
}

function RetrieveCertificate_NPAPI()
{
    var cert_req = CreateCertRequest_NPAPI();
    var params = 'CertRequest=' + encodeURIComponent(cert_req) +
                 '&Mode=' + encodeURIComponent('newreq') +
                 '&TargetStoreFlags=' + encodeURIComponent('0') +
                 '&SaveCert=' + encodeURIComponent('no');

    var xmlhttp = getXmlHttp();
    xmlhttp.open("POST", "https://www.cryptopro.ru/certsrv/certfnsh.asp", true);
    xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var response;
    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == 4) {
            if(xmlhttp.status == 200) {
                response = xmlhttp.responseText;
                var cert_data = "";

                if(!isIE())
                {
                    var start = response.indexOf("var sPKCS7");
                    var end = response.indexOf("sPKCS7 += \"\"") + 13;
                    cert_data = response.substring(start, end);
                }
                else
                {
                    var start = response.indexOf("sPKCS7 & \"") + 9;
                    var end = response.indexOf("& vbNewLine\r\n\r\n</Script>");
                    cert_data = response.substring(start, end);
                    cert_data = cert_data.replace(new RegExp(" & vbNewLine",'g'),";");
                    cert_data = cert_data.replace(new RegExp("&",'g'),"+");
                    cert_data = "var sPKCS7=" + cert_data + ";";
                }

                eval(cert_data);

                try {
                    var Enroll = cadesplugin.CreateObject("X509Enrollment.CX509Enrollment");
                }
                catch (e) {
                    alert('Failed to create X509Enrollment.CX509Enrollment: ' + cadesplugin.getLastError(e));
                    return;
                }

                Enroll.Initialize(0x1);
                Enroll.InstallResponse(0, sPKCS7, 0x7, "");
                document.getElementById("boxdiv").style.display = 'none';
                if(location.pathname.indexOf("simple")>=0) {
                    location.reload();
                }
                else if(location.pathname.indexOf("symalgo_sample.html")>=0){
                    FillCertList_NPAPI('CertListBox1');
                    FillCertList_NPAPI('CertListBox2');
                }
                else{
                    FillCertList_NPAPI('CertListBox');
                }
            }
        }
    }
    xmlhttp.send(params);
}

function Encrypt_NPAPI() {

    document.getElementById("DataEncryptedIV1").innerHTML = "";
    document.getElementById("DataEncryptedIV2").innerHTML = "";
    document.getElementById("DataEncryptedDiversData1").innerHTML = "";
    document.getElementById("DataEncryptedDiversData2").innerHTML = "";
    document.getElementById("DataEncryptedBox1").innerHTML = "";
    document.getElementById("DataEncryptedBox2").innerHTML = "";
    document.getElementById("DataEncryptedKey1").innerHTML = "";
    document.getElementById("DataEncryptedKey2").innerHTML = "";
    document.getElementById("DataDecryptedBox1").innerHTML = "";
    document.getElementById("DataDecryptedBox2").innerHTML = "";

    var certificate1 = GetCertificate_NPAPI('CertListBox1');
    if(typeof(certificate1) == 'undefined')
    {
        return;
    }
    var certificate2 = GetCertificate_NPAPI('CertListBox2');
    if(typeof(certificate2) == 'undefined')
    {
        return;
    }

    var dataToEncr1 = Base64.encode(document.getElementById("DataToEncrTxtBox1").value);
    var dataToEncr2 = Base64.encode(document.getElementById("DataToEncrTxtBox2").value);

    if(dataToEncr1 === "" || dataToEncr2 === "") {
        errormes = "Empty data to encrypt";
        throw errormes;
    }

    try
    {
        //FillCertInfo_NPAPI(certificate1, 'cert_info1');
        //FillCertInfo_NPAPI(certificate2, 'cert_info2');
        var errormes = "";

        try {
            var oSymAlgo = cadesplugin.CreateObject("cadescom.symmetricalgorithm");
        } catch (err) {
            errormes = "Failed to create cadescom.symmetricalgorithm: " + err;
            throw errormes;
        }

        oSymAlgo.GenerateKey();

        var oSesKey1 = oSymAlgo.DiversifyKey();
        var oSesKey1DiversData = oSesKey1.DiversData;
        document.getElementById("DataEncryptedDiversData1").value = oSesKey1DiversData;
        var oSesKey1IV = oSesKey1.IV;
        document.getElementById("DataEncryptedIV1").value = oSesKey1IV;
        var EncryptedData1 = oSesKey1.Encrypt(dataToEncr1, 1);
        document.getElementById("DataEncryptedBox1").value = EncryptedData1;

        var oSesKey2 = oSymAlgo.DiversifyKey();
        var oSesKey2DiversData = oSesKey2.DiversData;
        document.getElementById("DataEncryptedDiversData2").value = oSesKey2DiversData;
        var oSesKey2IV = oSesKey2.IV;
        document.getElementById("DataEncryptedIV2").value = oSesKey2IV;
        var EncryptedData2 = oSesKey2.Encrypt(dataToEncr2, 1);
        document.getElementById("DataEncryptedBox2").value = EncryptedData2;

        var ExportedKey1 = oSymAlgo.ExportKey(certificate1);
        document.getElementById("DataEncryptedKey1").value = ExportedKey1;

        var ExportedKey2 = oSymAlgo.ExportKey(certificate2);
        document.getElementById("DataEncryptedKey2").value = ExportedKey2;

        alert("Данные зашифрованы успешно:");
    }
    catch(err)
    {
        alert("Ошибка при шифровании данных:" + err);
    }
}

function Decrypt_NPAPI(certListBoxId) {

    document.getElementById("DataDecryptedBox1").value = "";
    document.getElementById("DataDecryptedBox2").value = "";

    var certificate = GetCertificate_NPAPI(certListBoxId);
    if(typeof(certificate) == 'undefined')
    {
        return;
    }
    var dataToDecr1 = document.getElementById("DataEncryptedBox1").value;
    var dataToDecr2 = document.getElementById("DataEncryptedBox2").value;
    var field;
    if(certListBoxId == 'CertListBox1')
        field ="DataEncryptedKey1";
    else
        field ="DataEncryptedKey2";

    var EncryptedKey = document.getElementById(field).value;
    try
    {
        FillCertInfo_NPAPI(certificate, 'cert_info_decr');
        var errormes = "";

        try {
            var oSymAlgo = cadesplugin.CreateObject("cadescom.symmetricalgorithm");
        } catch (err) {
            errormes = "Failed to create cadescom.symmetricalgorithm: " + err;
            throw errormes;
        }
        oSymAlgo.ImportKey(EncryptedKey, certificate);
        var oSesKey1DiversData = document.getElementById("DataEncryptedDiversData1").value;
        var oSesKey1IV = document.getElementById("DataEncryptedIV1").value;
        oSymAlgo.DiversData = oSesKey1DiversData;
        var oSesKey1 = oSymAlgo.DiversifyKey();
        oSesKey1.IV = oSesKey1IV;
        var EncryptedData1 = oSesKey1.Decrypt(dataToDecr1, 1);
        document.getElementById("DataDecryptedBox1").value = Base64.decode(EncryptedData1);
        var oSesKey2DiversData = document.getElementById("DataEncryptedDiversData2").value;
        var oSesKey2IV = document.getElementById("DataEncryptedIV2").value;
        oSymAlgo.DiversData = oSesKey2DiversData;
        var oSesKey2 = oSymAlgo.DiversifyKey();
        oSesKey2.IV = oSesKey2IV;
        var EncryptedData2 = oSesKey2.Decrypt(dataToDecr2, 1);
        document.getElementById("DataDecryptedBox2").value = Base64.decode(EncryptedData2);

        alert("Данные расшифрованы успешно:");
    }
    catch(err)
    {
        alert("Ошибка при шифровании данных:" + err);
    }
}

function isIE() {
    var retVal = (("Microsoft Internet Explorer" == navigator.appName) || // IE < 11
        navigator.userAgent.match(/Trident\/./i)); // IE 11
    return retVal;
}

function isEdge() {
    var retVal = navigator.userAgent.match(/Edge\/./i);
    return retVal;
}

function ShowEdgeNotSupported() {    
    document.getElementById('PlugInEnabledTxt').innerHTML = "Браузер Edge не поддерживается!";
}

///////////////////UI FUNCTION ///////////////////////////////////////

function FillCertInfo_NPAPI(certificate, certBoxId, isFromContainer)
{
    var BoxId;
    var field_prefix;
    if(typeof(certBoxId) == 'undefined' || certBoxId == "CertListBox")
    {
        BoxId = 'cert_info';
        field_prefix = '';
    }else if (certBoxId == "CertListBox1") {
        BoxId = 'cert_info1';
        field_prefix = 'cert_info1';
    } else if (certBoxId == "CertListBox2") {
        BoxId = 'cert_info2';
        field_prefix = 'cert_info2';
    } else {
        BoxId = certBoxId;
        field_prefix = certBoxId;
    }

    var uicert=new UICertificateObj(certificate);
    var ValidToDate = new Date(certificate.ValidToDate);
    var ValidFromDate = new Date(certificate.ValidFromDate);
    var IsValid = false;
    //если попадется сертификат с неизвестным алгоритмом
    //тут будет исключение. В таком сертификате просто пропускаем такое поле
    try {
        IsValid = certificate.IsValid().Result;    
    } catch (e) {
        
    }
    var hasPrivateKey = certificate.HasPrivateKey();
    var Now = new Date();

    var certObj = new CertificateObj(certificate);

    setHtmlById(field_prefix + "thumbprint",uicert.Thumbprint);
    setHtmlById(field_prefix + "subject",uicert.owner);
    setHtmlById(field_prefix + "issuer",uicert.issuer);
    setHtmlById(field_prefix + "from",uicert.from);
    setHtmlById(field_prefix + "till", uicert.till);

    if (uicert.provider != "") {
       setHtmlById(field_prefix + "provname", uicert.provider);
    }
    setHtmlById(field_prefix + "algorithm", uicert.pubkey_algo);
    setHtmlById(field_prefix + "status",GetCertificateStatus(uicert.status_code,"ru"));

    if(isFromContainer) sl="Нет"; else   sl="Да";

    setHtmlById(field_prefix + "location",sl);
    setValuelById(field_prefix + "status_code",uicert.status_code);
    setValuelById(field_prefix + "has_private_key", hasPrivateKey ? "1" : "0" );
    o=ElById(BoxId);
    if(IsObj(o)) o.style.display = '';

}



//-----------------------------------
var Base64 = {


    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


    encode: function(input) {
            var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                    enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },


    decode: function(input) {
            var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    _utf8_encode: function(string) {
            string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                    utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode: function(utftext) {
            var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                    string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                    c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}

//////extended functions. RAI./////////////////////////////////////////////////////////

function SignCadesBES_NPAPIX(certListBoxId, data, setDisplayData,onSignedData,onError) {
    var certificate = GetCertificate_NPAPI(certListBoxId);

    function error(err){
        if(IsObj(onError)) onError(err);
        else               throw err;
        return false;
    }
    try
    {
        var signature = MakeCadesBesSign_NPAPI(data, certificate, setDisplayData);
        if(IsObj(onSignedData)) onSignedData(signature);
    } catch(err)
    {
        error(err);
    }
    return true;
}

function Common_SignCadesBESX(id, text, setDisplayData)
{
    var canAsync = !!cadesplugin.CreateObjectAsync;
    if(canAsync)
    {
        include_async_code().then(function(){
            return SignCadesBES_AsyncX(id, text, setDisplayData,onSignedData,ShowError);
        });
    }else
    {
        return SignCadesBES_NPAPIX(id, text, setDisplayData,onSignedData,ShowError);
    }
}




function SignCadesBES_NPAPIX(certListBoxId, data, setDisplayData,onSignedData,onError) {
    var certificate = GetCertificate_NPAPI(certListBoxId);
    return _SignCadesBES_NPAPIX(certifcate, data, onSignedData,onError);
}


function _SignCadesBES_NPAPIX(certificate, data, onSignedData,onError) {
    function error(err){
        if(IsObj(onError)) onError(err);
        else               throw err;
        return false;
    }
    try
    {
      var signature = MakeCadesBesSign_NPAPI(data, certificate, null);
       if(IsObj(onSignedData)) onSignedData(signature);
    } catch(err){ error(err); }
    return true;
}

function SignCadesBES_NPAPIXByIndex(index, data, onSignedData,onError) {
 var certificate=global_selectbox_container[index];
 return _SignCadesBES_NPAPIX(certificate, data, onSignedData,onError);
}


function FindByThumbprint(thp){
 var t;
 thp=thp.toUpperCase();
 for( var i=0; i < global_selectbox_container.length; i++){
  t=global_selectbox_container[i].Thumbprint;
  t=t.toUpperCase();
  if(t == thp) return global_selectbox_container[i];
 }
 return null;
}

function SignCadesBES_NPAPIXByThumbprint(thumbprint, data, onSignedData,onError) {
 var certificate=FindByThumbprint(thumbprint);
 if(certificate == null) {
  onError("Certificate not found");
  return false; 
 }
 return _SignCadesBES_NPAPIX(certificate, data, onSignedData,onError);
}

