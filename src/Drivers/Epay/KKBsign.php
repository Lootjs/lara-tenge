<?php
namespace Loot\Tenge\Drivers\Epay;

/*
-----===++[Additional procedures by Pavel Nedelin (soius@soius.kz;tecc@mail.kz)		]++===-----
-----===++[05.10.2006									]++===-----
-----===++[SOIUS Ltd. 2006 (soius@soius.kz) http://www.soius.kz				]++===-----

-----===++[Дополнительные процедуры Pavel Nedelin (soius@soius.kz;tecc@mail.kz)		]++===-----
-----===++[05.10.2006									]++===-----
-----===++[SOIUS Ltd. 2006 (soius@soius.kz) http://www.soius.kz				]++===-----
*/

/* --------------------------------------------
    Модуль для создания/проверки подписи
    приватным/публичным ключом.
        KKBSign class
    -------------
    by Kirsanov Anton (webcompass@list.ru)
    01.06.2006
    ^^^^^^^^^^^^^^^
    Список методов:
    // ---------------------------------------
    // Загрузка приватного ключа в PEM формате
        load_private_key($file, $password);
    // ---------------------------------------
    // Инверсия строки
        invert();
    // ---------------------------------------
    // Подпись загруженным ключом строки $str
        sign($str);
    // ---------------------------------------
    // Подпись загруженным ключом строки $str
    // и кодирование в Base64
        sign64($str);
    // ---------------------------------------
    // Проверка публичным ключом $file,
    // является ли строка $str подписанной
    // приватным ключом строкой $data.
        check_sign($data, $str, $file);
    // ---------------------------------------
    // Проверка публичным ключом $file,
    // является ли строка $str в Base64
    // подписанной приватным ключом строкой $data.
        check_sign64($data, $str, $file);
    // ---------------------------------------
    // Обработка ошибок Open SSL
    // $error - строка ошибки получаемая openssl_error_string()
    // сохраняет внутри класса:
    // ecode - порядковый номер ошибки
    // estatus - текстовое описание ошибки
        parse_errors($error);

   ------------------------------------------*/

class KKBsign {
    /**
     * @var bool флаг инверсии
     */
    protected $invert = false;

    /**
     * @var string
     */
    protected $private_key;

    /**
     * @var array
     */
    protected $config;

    // -----------------------------------------------------------------------------------------------
    public function load_private_key($filename, $password = NULL){
        $this->ecode=0;

        if (!is_file($filename)) {
            $this->ecode=4;
            $this->estatus = "[KEY_FILE_NOT_FOUND]";

            return false;
        };

        $c = file_get_contents($filename);

        if (strlen(trim($password)) > 0) {
            $prvkey = openssl_get_privatekey($c, $password);
            $this->parse_errors(openssl_error_string());
        } else {
            $prvkey = openssl_get_privatekey($c);
            $this->parse_errors(openssl_error_string());
        }

        if (is_resource($prvkey)) {
            $this->private_key = $prvkey; return $c;
        }

        return false;
    }
    // -----------------------------------------------------------------------------------------------
    // Установка флага инверсии
    public function invert(){ $this->invert = 1;}
    // -----------------------------------------------------------------------------------------------
    // Процесс инверсии строки
    public function reverse($str){	return strrev($str);}
    // -----------------------------------------------------------------------------------------------
    public function sign($str){
        if($this->private_key){
            openssl_sign($str, $out, $this->private_key);
            if($this->invert == 1) $out = $this->reverse($out);
            //openssl_free_key($this->private_key);
            return $out;
        };
    }
    // -----------------------------------------------------------------------------------------------
    public function sign64($str){	return base64_encode($this->sign($str));}
    // -----------------------------------------------------------------------------------------------
    public function check_sign($data, $str, $filename){
        if($this->invert == 1)  $str = $this->reverse($str);
        if(!is_file($filename)){ $this->ecode=4; $this->estatus = "[KEY_FILE_NOT_FOUND]"; return 2;};
        $this->pubkey = file_get_contents($filename);
        $pubkeyid = openssl_get_publickey($this->pubkey);
        $this->parse_errors(openssl_error_string());
        if (is_resource($pubkeyid)){
            $result = openssl_verify($data, $str, $pubkeyid);
            $this->parse_errors(openssl_error_string());
            openssl_free_key($pubkeyid);
            return $result;
        };
        return 3;
    }
    // -----------------------------------------------------------------------------------------------
    public function check_sign64($data, $str, $filename){
        return $this->check_sign($data, base64_decode($str), $filename);
    }
    // -----------------------------------------------------------------------------------------------
    public function parse_errors($error){
        // -----===++[Parses error to errorcode and message]++===-----
        /*error:0906D06C - Error reading Certificate. Verify Cert type.
        error:06065064 - Bad decrypt. Verify your Cert password or Cert type.
        error:0906A068 - Bad password read. Maybe empty password.*/
        if (strlen($error)>0){
            if (strpos($error,"error:0906D06C")>0){$this->ecode = 1; $this->estatus = "Error reading Certificate. Verify Cert type.";};
            if (strpos($error,"error:06065064")>0){$this->ecode = 2; $this->estatus = "Bad decrypt. Verify your Cert password or Cert type.";};
            if (strpos($error,"error:0906A068")>0){$this->ecode = 3; $this->estatus = "Bad password read. Maybe empty password.";};
            if ($this->ecode = 0){$this->ecode = 255; $this->estatus = $error;};
        };
    }

    public function process_XML($filename,$reparray) {
        // -----===++[Process XML template - replaces tags in file to array values]++===-----
        // variables:
        // $filename - string: name of XML template
        // $reparray - array: data to replace
        //
        // XML template tag format:[tag name] example: [MERCHANT_CERTIFICATE_ID]
        //
        // Functionality:Searches file for array index and replaces to value
        // example: in array > $reparray['MERCHANT_CERTIFICATE_ID'] = "12345"
        // before replace: cert_id="[MERCHANT_CERTIFICATE_ID]"
        // after replace: cert_id="12345"
        // if operation successful returns file contents with replaced values
        // if template not found returns "[ERROR]"
        //
        // -----===++[Обработка XML шаблона - замена тэгов в файле на значения из массива]++===-----
        // переменные:
        // $filename - строка: имя XML шаблона
        // $reparray - массив: данные для замены
        //
        // Формат тэгов в XML шаблоне:[tag name] пример: [MERCHANT_CERTIFICATE_ID]
        //
        // Функциональность: Ищет в шаблоне индексы массива и заменяет их на значения
        // пример: в массиве > $reparray['MERCHANT_CERTIFICATE_ID'] = "12345"
        // перед заменой: cert_id="[MERCHANT_CERTIFICATE_ID]"
        // после замены: cert_id="12345"
        // Если операции прошли успешно возврашает текст файла с замененными значениями
        // Если файл шаблона не нйден возвращает "[ERROR]"

        if(is_file($filename)){
            $content = file_get_contents($filename);
            foreach ($reparray as $key => $value) {$content = str_replace("[".$key."]",$value,$content);};
            return $content;
        } else {
            return "[ERROR]";
        }
    }
    public static function split_sign($xml,$tag){
        // -----===++[Process XML string to array of values]++===-----
        // variables:
        // $xml - string: xml string
        // $tag - string: split tag name
        // $array["LETTER"] = an XML section enclosed in <$tag></$tag>
        // $array["SIGN"] = an XML sign section enclosed in <$tag+"_sign"></$tag+"_sign">
        // $array["RAWSIGN"] = an XML sign section with stripped <$tag+"_sign"></$tag+"_sign"> tags
        // example:
        // income data:
        // $xml = "<order order_id="12345"><department amount="10"/></order><order_sign type="SHA/RSA">ljkhsdfmnuuewrhkj</order_sign>"
        // $tag = "ORDER"
        // result:
        // $array["LETTER"] = "<order order_id="12345"><department amount="10"/></order>"
        // $array["SIGN"] = "<order_sign type="SHA/RSA">ljkhsdfmnuuewrhkj</order_sign>"
        // $array["RAWSIGN"] = "ljkhsdfmnuuewrhkj"
        //
        // -----===++[Обработка XML строки в массивзначений]++===-----
        // переменные:
        // $xml - строка: xml строка
        // $tag - строка: имя тэга разделителя
        // $array["LETTER"] = XML секция заключенная в <$tag></$tag>
        // $array["SIGN"] = XML секция подписи заключенная в <$tag+"_sign"></$tag+"_sign">
        // $array["RAWSIGN"] = XML секция подписи с отрезанными <$tag+"_sign"></$tag+"_sign"> тэгами
        // Пример:
        // Входные данные:
        // $xml = "<order order_id="12345"><department amount="10"/></order><order_sign type="SHA/RSA">ljkhsdfmnuuewrhkj</order_sign>"
        // $tag = "ORDER"
        // Результат:
        // $array["LETTER"] = "<order order_id="12345"><department amount="10"/></order>"
        // $array["SIGN"] = "<order_sign type="SHA/RSA">ljkhsdfmnuuewrhkj</order_sign>"
        // $array["RAWSIGN"] = "ljkhsdfmnuuewrhkj"


        $array = array();
        $letterst = stristr($xml,"<".$tag);
        $signst = stristr($xml,"<".$tag."_SIGN");
        $signed = stristr($xml,"</".$tag."_SIGN");
        $doced = stristr($signed,">");
        $array['LETTER'] = substr($letterst,0,-strlen($signst));
        $array['SIGN'] = substr($signst,0,-strlen($doced)+1);
        $rawsignst = stristr($array['SIGN'],">");
        $rawsigned = stristr($rawsignst,"</");
        $array['RAWSIGN'] = substr($rawsignst,1,-strlen($rawsigned));
        return $array;
    }
// -----------------------------------------------------------------------------------------------
    public static function process_request($order_id,$currency_code,$amount,$config,$b64=true) {
        // -----===++[Process incoming data to full bank request]++===-----
        // variables:
        // $order_id - integer: order index - recoded to 6 digit format with leaded zero
        // $currency_code - string: preferred currency codes 840-USD, 398-Tenge
        // $amount - integer: total payment amount
        // $config_file - string: full path to config file
        // $b64 - boolean: flag to encode result in base64 default = true
        // example:
        // income data: process_request(1,"398",10,"config.txt")
        // result:
        // string = "<document><merchant cert_id="123" name="test"><order order_id="000001" amount="10" currency="398">
        // <department merchant_id="12345" amount="10"/></order></merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"
        //
        // -----===++[Обработка входных данных в полный банковский запрос]++===-----
        // Переменные:
        // $order_id - целое: номер заказа - перекодируется в 6 значный формат с ведущими нулями
        // $currency_code - строка: заданные шифры валют 840-USD, 398-Tenge
        // $amount - целое: общая сумма платежа
        // $config_file - строка: полный путь к файлу конфигурации
        // $b64 - булевое: флаг для кодирования результата в base64 по умолчанию = true
        // пример:
        // входные данные: process_request(1,"398",10,"config.txt")
        // результат:
        // строка = "<document><merchant cert_id="123" name="test"><order order_id="000001" amount="10" currency="398">
        // <department merchant_id="12345" amount="10"/></order></merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"

        if (strlen($order_id)>0){
            if (is_numeric($order_id)){
                if ($order_id>0){
                    $order_id = sprintf ("%06d",$order_id);
                } else { return "Null Order ID";};
            } else { return "Order ID must be number";};
        } else { return "Empty Order ID";};

        if (strlen($currency_code)==0){return "Empty Currency code";};
        if ($amount==0){return "Nothing to charge";};
        if (strlen($config['PRIVATE_KEY_FN'])==0){return "Path for Private key not found";};
        if (strlen($config['XML_TEMPLATE_FN'])==0){return "Path for Private key not found";};

        $request = array();
        $request['MERCHANT_CERTIFICATE_ID'] = $config['MERCHANT_CERTIFICATE_ID'];
        $request['MERCHANT_NAME'] = $config['MERCHANT_NAME'];
        $request['ORDER_ID'] = $order_id;
        $request['CURRENCY'] = $currency_code;
        $request['MERCHANT_ID'] = $config['MERCHANT_ID'];
        $request['AMOUNT'] = $amount;

        $kkb = new KKBSign();
        $kkb->invert();
        if (!$kkb->load_private_key($config['PRIVATE_KEY_FN'],$config['PRIVATE_KEY_PASS'])){
            if ($kkb->ecode>0){return $kkb->estatus;};
        };

        $result = $kkb->process_XML($config['XML_TEMPLATE_FN'],$request);
        if (strpos($result,"[RERROR]")>0){ return "Error reading XML template.";};
        $result_sign = '<merchant_sign type="RSA">'.$kkb->sign64($result).'</merchant_sign>';
        $xml = "<document>".$result.$result_sign."</document>";
        if ($b64){return base64_encode($xml);} else {return $xml;};
    }
// -----------------------------------------------------------------------------------------------
    public static function process_response($response,$config_file) {
        // -----===++[Process incoming XML to array of values with verifying electronic sign]++===-----
        // variables:
        // $response - string: XML response from bank
        // $config_file - string: full path to config file
        // returns:
        // array with parced XML and sign verifying result
        // if array has in values "DOCUMENT" following values available
        // $data['CHECKRESULT'] = "[SIGN_GOOD]" - sign verify successful
        // $data['CHECKRESULT'] = "[SIGN_BAD]" - sign verify unsuccessful
        // $data['CHECKRESULT'] = "[SIGN_CHECK_ERROR]" - an error has occured while sign processing full error in that string after ":"
        // if array has in values "ERROR" following values available
        // $data["ERROR_TYPE"] = "ERROR" - internal error occured
        // $data["ERROR"] = "Config not exist" - the configuration file not found
        // $data["ERROR_TYPE"] = "system" - external error in bank process
        // $data["ERROR_TYPE"] = "auth" - external autentication error in bank process
        // example:
        // income data:
        // $response = "<document><bank><customer name="123"><merchant name="test merch">
        // <order order_id="000001" amount="10" currency="398"><department amount="10"/></order></merchant>
        // <merchant_sign type="RSA"/></customer><customer_sign type="RSA"/><results timestamp="2001-01-01 00:00:00">
        // <payment amount="10" response_code="00"/></results></bank>
        // <bank_sign type="SHA/RSA">;skljfasldimn,samdbfyJHGkmbsa;fliHJ:OIUHkjbn</bank_sign ></document>"
        // $config_file = "config.txt"
        // result:
        // $data['BANK_SIGN_CHARDATA'] = ";skljfasldimn,samdbfyJHGkmbsa;fliHJ:OIUHkjbn"
        // $data['BANK_SIGN_TYPE'] = "SHA/RSA"
        // $data['CUSTOMER_NAME'] = "123"
        // $data['CUSTOMER_SIGN_TYPE'] = "RSA"
        // $data['DEPARTMENT_AMOUNT'] = "10"
        // $data['MERCHANT_NAME'] = "test merch"
        // $data['MERCHANT_SIGN_TYPE'] = "RSA"
        // $data['ORDER_AMOUNT'] = "10"
        // $data['ORDER_CURRENCY'] = "398"
        // $data['ORDER_ORDER_ID'] = "000001"
        // $data['PAYMENT_AMOUNT'] = "10"
        // $data['PAYMENT_RESPONSE_CODE'] = "00"
        // $data['RESULTS_TIMESTAMP'] = "2001-01-01 00:00:00"
        // $data['TAG_BANK'] = "BANK"
        // $data['TAG_BANK_SIGN'] = "BANK_SIGN"
        // $data['TAG_CUSTOMER'] = "CUSTOMER"
        // $data['TAG_CUSTOMER_SIGN'] = "CUSTOMER_SIGN"
        // $data['TAG_DEPARTMENT'] = "DEPARTMENT"
        // $data['TAG_DOCUMENT'] = "DOCUMENT"
        // $data['TAG_MERCHANT'] = "MERCHANT"
        // $data['TAG_MERCHANT_SIGN'] = "MERCHANT_SIGN"
        // $data['TAG_ORDER'] = "ORDER"
        // $data['TAG_PAYMENT'] = "PAYMENT"
        // $data['TAG_RESULTS'] = "RESULTS"
        // $data['CHECKRESULT'] = "[SIGN_GOOD]"
        //
        // -----===++[Обработкавходящего XML в массив значений с проверкой электронной подписи]++===-----
        // Переменные:
        // $response - строка: XML ответ от банка
        // $config_file - строка: полный путь к файлу конфигурации
        // возвращает:
        // массив с нарезанным XML и результатом проверки подписи
        // если в массиве есть значение "DOCUMENT" доступны следующие значения
        // $data['CHECKRESULT'] = "[SIGN_GOOD]" - проверка подписи успешна
        // $data['CHECKRESULT'] = "[SIGN_BAD]" - проверка подписи провалена
        // $data['CHECKRESULT'] = "[SIGN_CHECK_ERROR]" - произошла ошибка во время обработки подписи, подное поисание ошибки в этой же строке после ":"
        // если в массиве есть значение "ERROR" доступны следующие значения
        // $data["ERROR_TYPE"] = "ERROR" - произошла внутренняя ошибка
        // $data["ERROR"] = "Config not exist" - не найден файл конфигурации
        // $data["ERROR_TYPE"] = "system" - внешняя ошибка при обработке данных в банке
        // $data["ERROR_TYPE"] = "auth" - внешняя ошибка авторизации при обработке данных в банке
        // пример:
        // входные данные:
        // $response = "<document><bank><customer name="123"><merchant name="test merch">
        // <order order_id="000001" amount="10" currency="398"><department amount="10"/></order></merchant>
        // <merchant_sign type="RSA"/></customer><customer_sign type="RSA"/><results timestamp="2001-01-01 00:00:00">
        // <payment amount="10" response_code="00"/></results></bank>
        // <bank_sign type="SHA/RSA">;skljfasldimn,samdbfyJHGkmbsa;fliHJ:OIUHkjbn</bank_sign ></document>"
        // $config_file = "config.txt"
        // результат:
        // $data['BANK_SIGN_CHARDATA'] = ";skljfasldimn,samdbfyJHGkmbsa;fliHJ:OIUHkjbn"
        // $data['BANK_SIGN_TYPE'] = "SHA/RSA"
        // $data['CUSTOMER_NAME'] = "123"
        // $data['CUSTOMER_SIGN_TYPE'] = "RSA"
        // $data['DEPARTMENT_AMOUNT'] = "10"
        // $data['MERCHANT_NAME'] = "test merch"
        // $data['MERCHANT_SIGN_TYPE'] = "RSA"
        // $data['ORDER_AMOUNT'] = "10"
        // $data['ORDER_CURRENCY'] = "398"
        // $data['ORDER_ORDER_ID'] = "000001"
        // $data['PAYMENT_AMOUNT'] = "10"
        // $data['PAYMENT_RESPONSE_CODE'] = "00"
        // $data['RESULTS_TIMESTAMP'] = "2001-01-01 00:00:00"
        // $data['TAG_BANK'] = "BANK"
        // $data['TAG_BANK_SIGN'] = "BANK_SIGN"
        // $data['TAG_CUSTOMER'] = "CUSTOMER"
        // $data['TAG_CUSTOMER_SIGN'] = "CUSTOMER_SIGN"
        // $data['TAG_DEPARTMENT'] = "DEPARTMENT"
        // $data['TAG_DOCUMENT'] = "DOCUMENT"
        // $data['TAG_MERCHANT'] = "MERCHANT"
        // $data['TAG_MERCHANT_SIGN'] = "MERCHANT_SIGN"
        // $data['TAG_ORDER'] = "ORDER"
        // $data['TAG_PAYMENT'] = "PAYMENT"
        // $data['TAG_RESULTS'] = "RESULTS"
        // $data['CHECKRESULT'] = "[SIGN_GOOD]"


        if(is_file($config_file)){
            $config=parse_ini_file($config_file,0);
        } else {$data["ERROR"] = "Config not exist";$data["ERROR_TYPE"] = "ERROR"; return $data;};

        $xml_parser = new xml();
        $result = $xml_parser->parse($response);
        if (in_array("ERROR",$result)){
            return $result;
        };
        if (in_array("DOCUMENT",$result)){
            $kkb = new KKBSign();
            $kkb->invert();
            $data = self::split_sign($response,"BANK");
            $check = $kkb->check_sign64($data['LETTER'], $data['RAWSIGN'], $config['PUBLIC_KEY_FN']);
            if ($check == 1)
                $data['CHECKRESULT'] = "[SIGN_GOOD]";
            elseif ($check == 0)
                $data['CHECKRESULT'] = "[SIGN_BAD]";
            else
                $data['CHECKRESULT'] = "[SIGN_CHECK_ERROR]: ".$kkb->estatus;
            return array_merge($result,$data);
        };
        return "[XML_DOCUMENT_UNKNOWN_TYPE]";
    }
// -----------------------------------------------------------------------------------------------
    public static function process_refund($reference, $approval_code, $order_id, $currency_code, $amount, $reason, $config_file) {
        // -----===++[Process refund for processed transaction]++===-----
        // variables:
        // $reference - integer: transaction ID
        // $approval_code - string: transaction approval code
        // $order_id - integer: order index - recoded to 6 digit format with leaded zero
        // $currency_code - string: preferred currency codes 840-USD, 398-Tenge
        // $amount - integer: total payment amount
        // $reason - string: reason of the refund
        // $config_file - string: full path to config file
        // example:
        // income data: process_request(016604285111, 12345, 1, "398", 10, "Order cancelled", "config.txt")
        // result:
        // string = "<document><merchant cert_id="123" name="test"><command type="reverse"/>
        // <payment reference="016604285111" orderid="000001" amount="10" currency="398" />
        // <reason>Order cancelled</reason>
        // </merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"
        //
        // -----===++[Возврат средств по уже проведённой транзакции]++===-----
        // Переменные:
        // $reference - integer: ID транзакции
        // $approval_code - string: код подтверждения транзакции
        // $order_id - целое: номер заказа - перекодируется в 6 значный формат с ведущими нулями
        // $currency_code - строка: заданные шифры валют 840-USD, 398-Tenge
        // $amount - целое: общая сумма платежа
        // $reason - строка: причина возврата средств
        // $config_file - строка: полный путь к файлу конфигурации
        // пример:
        // входные данные: process_request(016604285111, 12345, 1, "398", 10, "Order cancelled", "config.txt")
        // результат:
        // строка = "<document><merchant cert_id="123" name="test"><command type="reverse"/>
        // <payment reference="016604285111" orderid="000001" amount="10" currency="398" />
        // <reason>Order cancelled</reason>
        // </merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"

        if(!$reference) return "Empty Transaction ID";

        if(is_file($config_file)){
            $config=parse_ini_file($config_file,0);
        } else { return "Config not exist";};

        if (strlen($order_id)>0){
            if (is_numeric($order_id)){
                if ($order_id>0){
                    $order_id = sprintf ("%06d",$order_id);
                } else { return "Null Order ID";};
            } else { return "Order ID must be number";};
        } else { return "Empty Order ID";};

        if(!$reason) $reason = "Transaction revert";

        if (strlen($currency_code)==0){return "Empty Currency code";};
        if ($amount==0){return "Nothing to charge";};
        if (strlen($config['PRIVATE_KEY_FN'])==0){return "Path for Private key not found";};
        if (strlen($config['XML_COMMAND_TEMPLATE_FN'])==0){return "Path to xml command template not found";};

        $request = array();
        $request['MERCHANT_ID'] = $config['MERCHANT_ID'];
        $request['MERCHANT_NAME'] = $config['MERCHANT_NAME'];
        $request['COMMAND'] = 'reverse';
        $request['REFERENCE_ID'] = $reference;
        $request['APPROVAL_CODE'] = $approval_code;
        $request['ORDER_ID'] = $order_id;
        $request['CURRENCY'] = $currency_code;
        $request['MERCHANT_ID'] = $config['MERCHANT_ID'];
        $request['AMOUNT'] = $amount;
        $request['REASON'] = $reason;

        return self::generateXML($request, $config);
    }
// -----------------------------------------------------------------------------------------------
    public static function process_complete($reference, $approval_code, $order_id, $currency_code, $amount, $config) {
        // -----===++[Process complete for processed transaction]++===-----
        // variables:
        // $reference - integer: transaction ID
        // $approval_code - string: transaction approval code
        // $order_id - integer: order index - recoded to 6 digit format with leaded zero
        // $currency_code - string: preferred currency codes 840-USD, 398-Tenge
        // $amount - integer: total payment amount
        // $config_file - string: full path to config file
        // example:
        // income data: process_request(016604285111, 12345, 1, "398", 10, "config.txt")
        // result:
        // string = "<document><merchant cert_id="123" name="test"><command type="complete"/>
        // <payment reference="016604285111" orderid="000001" amount="10" currency="398" />
        // <reason></reason>
        // </merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"
        //
        // -----===++[Возврат средств по уже проведённой транзакции]++===-----
        // Переменные:
        // $reference - integer: ID транзакции
        // $approval_code - string: код подтверждения транзакции
        // $order_id - целое: номер заказа - перекодируется в 6 значный формат с ведущими нулями
        // $currency_code - строка: заданные шифры валют 840-USD, 398-Tenge
        // $amount - целое: общая сумма платежа
        // $config_file - строка: полный путь к файлу конфигурации
        // пример:
        // входные данные: process_request(016604285111, 12345, 1, "398", 10, "config.txt")
        // результат:
        // строка = "<document><merchant cert_id="123" name="test"><command type="complete"/>
        // <payment reference="016604285111" orderid="000001" amount="10" currency="398" />
        // <reason>Order cancelled</reason>
        // </merchant><merchant_sign type="RSA">LJlkjkLHUgkjhgmnYI</merchant_sign>
        // </document>"

        if(!$reference) return "Empty Transaction ID";

        if (strlen($order_id)>0){
            if (is_numeric($order_id)){
                if ($order_id>0){
                    $order_id = sprintf ("%06d",$order_id);
                } else { return "Null Order ID";};
            } else { return "Order ID must be number";};
        } else { return "Empty Order ID";};

        if (strlen($currency_code)==0){return "Empty Currency code";};
        if ($amount==0){return "Nothing to charge";};
        if (strlen($config['PRIVATE_KEY_FN'])==0){return "Path for Private key not found";};
        if (strlen($config['XML_COMMAND_TEMPLATE_FN'])==0){return "Path for xml command template not found";};

        $request = array();
        $request['MERCHANT_ID'] = $config['MERCHANT_ID'];
        $request['MERCHANT_NAME'] = $config['MERCHANT_NAME'];
        $request['COMMAND'] = 'complete';
        $request['REFERENCE_ID'] = $reference;
        $request['APPROVAL_CODE'] = $approval_code;
        $request['ORDER_ID'] = $order_id;
        $request['CURRENCY'] = $currency_code;
        $request['MERCHANT_ID'] = $config['MERCHANT_ID'];
        $request['AMOUNT'] = $amount;
        $request['REASON'] = '';

        return self::generateXML($request, $config);
    }

    public static function generateXML($request, $config)
    {
        $kkb = new KKBSign();
        $kkb->invert();
        if (!$kkb->load_private_key($config['PRIVATE_KEY_FN'],$config['PRIVATE_KEY_PASS'])){
            if ($kkb->ecode>0){return $kkb->estatus;};
        };

        $result = $kkb->process_XML($config['XML_COMMAND_TEMPLATE_FN'],$request);
        if (strpos($result,"[RERROR]")>0){ return "Error reading XML template.";};
        $result_sign = '<merchant_sign type="RSA" cert_id="' . $config['MERCHANT_CERTIFICATE_ID'] . '">'.$kkb->sign64($result).'</merchant_sign>';
        return "<document>".$result.$result_sign."</document>";
    }
}
