<?php

function msgJson($array,$code=200,$addHeader=[])
{
    $header = getAllowOrigins();
    $header['Content-Type'] = 'application/json';

    if( !empty( $addHeader ) ){
        $header = array_merge( $header, $addHeader );
    }
    if( !empty( $array ) ) {
        return response()->json($array, $code, $header);
    }

    return msgErroJson(\Lang::get('default.registers_not_found'), 404);
}

function msgErroJson($msg,$code=400) {

    if( empty( \Request::header('X-Content-Type-Return') ) && empty( \Request::header('X-DreamFactory-API-Key') ) ) {
        $header = getAllowOrigins();
        $header['Content-Type'] = 'application/json';
        $return                 = ['error' => 1, 'messages' => $msg];

        if( is_array( $msg ) ){
            array_pull( $msg, 'error' );
            $message = array_pull( $msg, 'message' );
            $detail  = array_pull( $msg, 'detail' );
            if( !empty( $message ) ){$return['messages'] = $message;}
            if( !empty( $detail ) ) {$return['detail']   = $detail;}
            if( count( $msg )  > 0 ){
                $return = array_merge( $return, $msg );
            }
        }

        if( $code < 100 or $code > 599 ){
            $code = 400;
        }

        return response()->json( $return , $code, $header);
    }

    if( is_array( $msg ) ) {
        $msgTxt = array_has($msg, 'message') ? $msg['message'] : $msg;
        $msgTxt = array_has($msg, 'detail')  ? $msg['detail']  : $msgTxt;
    } else{
        $msgTxt = $msg;
    }

    return msgErroTxt($msgTxt,$code);
}

function msgErroTxt($msg,$code=400) {
    $header = getAllowOrigins();
    $header['Content-Type'] = 'text/plain';
    return response($msg, $code)->withHeaders($header);
}

function msgSuccessJson( $msg, $dados = [], $code=200 ) {
    $dados['messages'] = $msg;
    return msgJson($dados, $code);
}

function TransformaMaiscula ( &$item ) {
    array_walk_recursive($item, 'Maiscula');
}

function Maiscula(&$item) {

    if ( !is_object($item) ) {
        $LATIN_UC_CHARS = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ°°ª";
        $LATIN_LC_CHARS = "àáâãäåæçèéêëìíîïðñòóôõöøùúûüý°ºª";
        $item           = strtoupper( strtr( $item, $LATIN_LC_CHARS, $LATIN_UC_CHARS ) );
    } else
        array_walk_recursive( $item, 'Maiscula' );

}

function objectToArray($d) {
    if (is_object($d)) {
        // Gets the properties of the given object
        // with get_object_vars function
        $d = get_object_vars($d);
    }

    if (is_array($d)) {
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else {
        // Return array
        return $d;
    }
}

function removeAcentos($string, $slug = false) {
    return preg_replace(
        array(
                "/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/","/(ç)/","/(Ç)/"
            ),
        explode(" ","a A e E i I o O u U n N c C"),$string);
}

function getRouteName() {
    return \Illuminate\Support\Facades\Route::currentRouteName();
}

function getRoutePrefixName( $clean = ['/'] ) {
    return str_replace( $clean, '', \Illuminate\Support\Facades\Route::getCurrentRoute()->getPrefix() );
}

function isProduction() {
    return ( config('app.env') == 'production' ? TRUE : FALSE );
}

function isLocal() {
    return ( config('app.env') == 'local' ? TRUE : FALSE );
}

function isHmg() {
    return ( config('app.env') == 'hmg' ? TRUE : FALSE );
}

function isVPN() {
    return ( config('app.vpn_on') ? TRUE : FALSE );
}

function isJson($string) {
    return ((is_string($string) &&
        (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

function justNumber(&$string){
    $string = preg_replace("/[^0-9]/", "", $string);
    return $string;
}

function daysBetWeenDates( $dateIn, $dateEnd ){
    $days = null;
    if( validateDate($dateIn, 'YYYMMDDHHMMSS') && validateDate($dateEnd, 'YYYMMDDHHMMSS') ){
        $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateIn);
        $end   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateEnd);
        $days = $end->diffInDays($start);
    }
    return $days;
}

/**
 * COMPARA SE A DATA 'End' É >= A DATA 'In'
 * @param $dateTimeEnd
 * @param null $dateTimeIn
 * @return bool
 */
function compareToDates( $dateTimeEnd, $dateTimeIn=null ){
    $return = false;
    if( !empty( $dateTimeEnd ) ) {
        $dateTimeIn  = empty($dateTimeIn) ? \Carbon\Carbon::now()->timestamp : \Carbon\Carbon::parse($dateTimeIn)->timestamp;
        $dateTimeEnd = \Carbon\Carbon::parse($dateTimeEnd)->timestamp;
        $return      = $dateTimeEnd >= $dateTimeIn;
    }
    return $return;
}

function minutesBetWeenDates( $dateIn, $dateEnd ){
    $days = null;
    if( validateDate($dateIn, 'YYYMMDDHHMMSS') && validateDate($dateEnd, 'YYYMMDDHHMMSS') ){
        $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateIn);
        $end   = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateEnd);
        $days = $end->diffInMinutes($start);
    }
    return $days;
}

function validateDate($date, $formatIn = 'DDMMYYY' ) {
    $flag = false;
    switch ( $formatIn ){
        case 'DDMMYYY':
                $date  = justNumber( $date );
                $day   = substr($date, 0, 2);
                $month = substr($date, 2, 2);
                $year  = substr($date, -4);
                $flag  = checkdate($month, $day, $year);
                break;
        case 'YYYMMDDHHMMSS':
                $newdate = \Carbon\Carbon::createFromFormat( 'Y-m-d H:i:s', $date )->format('Y-m-d H:i:s');
                $flag    = ($newdate == $date);
                break;
        default:
            $day = $month = $year = null;
            break;
    }
    return $flag;
}

/**
 * @param $data
 * @param string $formatIn
 * @param bool $clear NAO vem com / ou -, somente numero
 * @return string
 */
function convertData($data, $formatIn = 'DDMMYYY', $clear = false ) {
    $formatIn = strtoupper( $formatIn );
    $retorno  = $data;
    switch( $formatIn ) {

        case 'DDMMYYY':
            if( $clear ){
                justNumber($data);
                $data = substr( $data, 0, 2 ) . '/' . substr( $data, 2, 2 ) . '/' . substr( $data, -4 );
            }
            $retorno = \Carbon\Carbon::createFromFormat('d/m/Y', $data)->format('Y-m-d');
            break;
        case 'YYYMMDD':
            $retorno = \Carbon\Carbon::createFromFormat('Y-m-d', $data)->format('d/m/Y');
            break;

        case 'YYYMMDDHHMMSS':
            $retorno = \Carbon\Carbon::parse( $data )->format('d/m/Y H:i:s');
            break;
        case 'DDMMYYYYHHMMSS':
            $retorno = \Carbon\Carbon::createFromFormat( 'd/m/Y H:i:s', $data )->format('Y-m-d H:i:s');
            break;

    }

    return $retorno;
}

function utf8Ansi($valor='') {
    $utf8_ansi2 = array(
        "\u00c0" =>"À", "\u00c1" =>"Á", "\u00c2" =>"Â", "\u00c3" =>"Ã", "\u00c4" =>"Ä", "\u00c5" =>"Å", "\u00c6" =>"Æ", "\u00c7" =>"Ç", "\u00c8" =>"È", "\u00c9" =>"É",
        "\u00ca" =>"Ê", "\u00cb" =>"Ë", "\u00cc" =>"Ì", "\u00cd" =>"Í", "\u00ce" =>"Î", "\u00cf" =>"Ï", "\u00d1" =>"Ñ", "\u00d2" =>"Ò", "\u00d3" =>"Ó", "\u00d4" =>"Ô",
        "\u00d5" =>"Õ", "\u00d6" =>"Ö", "\u00d8" =>"Ø", "\u00d9" =>"Ù", "\u00da" =>"Ú", "\u00db" =>"Û", "\u00dc" =>"Ü", "\u00dd" =>"Ý", "\u00df" =>"ß", "\u00e0" =>"à",
        "\u00e1" =>"á", "\u00e2" =>"â", "\u00e3" =>"ã", "\u00e4" =>"ä", "\u00e5" =>"å", "\u00e6" =>"æ", "\u00e7" =>"ç", "\u00e8" =>"è", "\u00e9" =>"é", "\u00ea" =>"ê",
        "\u00eb" =>"ë", "\u00ec" =>"ì", "\u00ed" =>"í", "\u00ee" =>"î", "\u00ef" =>"ï", "\u00f0" =>"ð", "\u00f1" =>"ñ", "\u00f2" =>"ò", "\u00f3" =>"ó", "\u00f4" =>"ô",
        "\u00f5" =>"õ", "\u00f6" =>"ö", "\u00f8" =>"ø", "\u00f9" =>"ù", "\u00fa" =>"ú", "\u00fb" =>"û", "\u00fc" =>"ü", "\u00fd" =>"ý", "\u00ff" =>"ÿ", "\u00bf" =>"é"
    );

    $valor = str_replace('u00','\u00',$valor);

    return strtr(utf8_encode($valor), $utf8_ansi2);

}

function getEstados( $soState = FALSE ){
    $lista = [
        'AC' => 'ACRE', 'AL' => 'ALAGOAS', 'AP' => 'AMAPÁ', 'AM' => 'AMAZONAS', 'BA' => 'BAHIA', 'CE' => 'CEARÁ', 'DF' => 'DISTRITO FEDERAL', 'ES' => 'ESPÍRITO SANTO',
        'GO' => 'GOIÁS', 'MA' => 'MARANHÃO', 'MG' => 'MINAS GERAIS', 'MS' => 'MATO GROSSO DO SUL', 'MT' => 'MATO GROSSO', 'PA' => 'PARÁ', 'PB' => 'PARAÍBA',
        'PE' => 'PERNAMBUCO', 'PI' => 'PIAUÍ', 'PR' => 'PARANÁ', 'RJ' => 'RIO DE JANEIRO', 'RN' => 'RIO GRANDE DO NORTE', 'RO' => 'RONDÔNIA', 'RR' => 'RORAIMA',
        'RS' => 'RIO GRANDE DO SUL', 'SC' => 'SANTA CATARINA', 'SE' => 'SERGIPE', 'SP' => 'SÃO PAULO', 'TO' => 'TOCANTINS'
    ];

    if( $soState ){
        list($lista, $nome) = array_divide( $lista );
    }

    return $lista;
}

/**
 * Função que coleta o ip do visitante
 * @return string IP do visitante
 */
function getIp() {

    $ip = null;
    if ( isProduction() ) {

        $ip = array_get( $_SERVER, 'REMOTE_ADDR', NULL);

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        if (empty ($ip)) {
            if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }

        // Corrigindo situação onde IP retornar Proxy e IP
        if ( !empty($ip) && strpos($ip, ',') !== false ) {

            list($proxy, $addr) = explode(',', $ip);
            $ip = trim ( $proxy );
        }

        // Corrigindo situação onde IP retornar MAC Address
        if ( !empty ($ip) && preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $ip) === true ) {
            $ip = null;
        }
    }

    // Corrigindo situação onde IP seja localhost
    if ( $ip == "::1" || empty( $ip ) ) {
        $ip = '127.0.0.1';
    }

    return $ip;
}

function getDDDs(){
    return [
        11, 12, 13, 14, 15, 16, 17, 18, 19,
        21, 22, 24, 27, 28,
        31, 32, 33, 34, 35, 37, 38,
        41, 42, 43, 44, 45, 46, 47, 48, 49,
        51, 53, 54, 55,
        61, 62, 63, 64, 65, 66, 67, 68, 69,
        71, 73, 74, 75, 77, 79,
        81, 82, 83, 84, 85, 86, 87, 88, 89,
        91, 92, 93, 94, 95, 96, 97, 98, 99
    ];
}

function escapeString($inp)
{
    if(is_array($inp)) return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

function formatBytes($size, $precision = 2)
{
    if ($size > 0) {
        $size = (int) $size;
        $base = log($size) / log(1024);
        $suffixes = array('bytes', 'KB', 'MB', 'GB', 'TB');

        return $suffixes[floor($base)];
    } else {
        return $size;
    }
}

function convertSize( $size, $for='MB' ){

    $origem = formatBytes( $size );
    if( $origem == 'KB' && $for == 'MB' ){
        return round($size/1024/1024,2);
    }

}

function serializeObj( $obj ){

    if ($obj instanceof \Illuminate\Http\Request){
        $all = $obj->all();
    } else {
        $all = objectToArray($obj);
    }

    $obj = new \StdClass();

    foreach ( $all AS $label => $valor ) {
        $obj->$label = $valor;
    }

    return base64_encode( serialize( $obj ) );
}

function unSerializeObj( $serialize ){
    return ( !is_array( $serialize ) ? objectToArray( unserialize( base64_decode( $serialize ) ) ) : $serialize );
}

#extrair parte de uma matriz com base num determinado idx
function pluckMatriz( $array, $idx, $setup=[] ){
    $dados = [];
    foreach ( $array AS $row ){
        $valor = array_get( $row, $idx, null );
        if( !empty( $valor ) ) {
            $dados[] = $valor;
        }
    }

    return $dados;
}

#extrair parte de uma matriz com base num determinado idx
function pluckArrayInMatriz( $array, $idx, $patternReturn=false ){

    $idx = is_array( $idx ) ? $idx : [$idx];
    $new = [];
    foreach ( $array AS $key => $row ){

        foreach ( $idx AS $idxRows ) {
            $idxRows = trim( $idxRows );
            if (array_has($row, $idxRows)) {
                $tmp = $row[$idxRows];

                if( $patternReturn && in_array( $idxRows, [
                        'status', 'in_app', 'is_input', 'in_home_app', 'its_paid', 'is_travel', 'is_delivery', 'have_nfc'
                    ] ) )
                {
                    $flag = true;
                    if( $idxRows === 'status' && $tmp != '0' && $tmp != '1' ){
                        $flag = false;
                    }
                    if( $flag ) {
                        $tmp = (boolean)$tmp;
                    }
                }

                $new[$key][$idxRows] = $tmp;
                unset($row[$idxRows]);
            }
        }
    }

    return $new;
}

#extrair parte de uma matriz com base num determinado idx
function removeInMatriz( $array, $idx ){

    $idx = is_array( $idx ) ? $idx : [$idx];
    foreach ( $array AS $key => $row ){

        foreach ( $idx AS $idxRows ) {
            if (array_has($row, $idxRows)) {
                unset($row[$idxRows]);
                $array[$key] = $row;
            }
        }
    }

    return $array;
}

function loadFiles( $fileName ){

    $cacheFile = "loadFiles_" . $fileName;

    if( \Cache::has( $cacheFile ) ){
        $value = \Cache::get( $cacheFile );
    } else {
        $value = \File::getRequire(base_path().'/files/'.$fileName.'.php');
        \Cache::forever($cacheFile, $value);
    }

    return $value;
}

function getDiasSemanas(){
    return [
        'SEG' => 'Segunda',
        'TER' => 'Terça',
        'QUA' => 'Quarta',
        'QUI' => 'Quinta',
        'SEX' => 'Sexta',
        'SAB' => 'Sábado',
        'DOM' => 'Domingo',
    ];
}

function getWeekMap($day=null){
    $weekMap = [
        0 => 'SU',
        1 => 'MO',
        2 => 'TU',
        3 => 'WE',
        4 => 'TH',
        5 => 'FR',
        6 => 'SA',
    ];

    return empty( $day ) ? $weekMap : ( $day >= 0 && $day < 7 ? $weekMap[$day] : null ) ;
}

function getdayOfTheWeek($initial=false){
    $cabonNow  = \Carbon\Carbon::now();
    $dayOfWeek = $cabonNow->dayOfWeek;
    return !$initial ? $dayOfWeek : getWeekMap($dayOfWeek);
}

function getAllowOrigins(){

    //$header['Access-Control-Allow-Origin' ]     = '*';
    $header['Access-Control-Allow-Methods']     = 'GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS';
    $header['Access-Control-Allow-Headers']     = 'Origin, X-Requested-With, Content-Type, Accept, Authorization, auth-user';
    $header['access-control-max-age']           = 1728000;
    $header['access-control-expose-headers']    = 'WWW-Authenticate, Server-Authorization';
    $header['access-control-allow-credentials'] = true;
    $header['P3P']                              = 'CP="CAO PSA OUR"'; // Makes IE to support cookies

    #colocando o tempo restante do token (seconds)
    $timeTokenTimeLeft = Request::get('token_time_left');
    if( $timeTokenTimeLeft ){
        $header['access-token-time-left']        = $timeTokenTimeLeft;
        $header['Access-Control-Expose-Headers'] = 'access-token-time-left';
    }

    return $header;
}

function calcPercente($value, $total, $precision=1, $invert=false){

    if( (string)$value === (string)$total ) return 0;

    $return = ( $total > 0 ? round( ( !$invert ? 0 : 100 ) - ( ($value * 100) / $total ), $precision) : NULL );
    $return = ( !is_null( $return ) ? number_format( $return, $precision ) : 0 );
    return $return;
}

function generateRandomString($length = 0) {
    $length = ( !empty( $length ) ? $length : rand(4,10) );
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

function convert_data_in_row($row=[], $formatIn='YYYMMDDHHMMSS', $fields=['created_at','updated_at']){
    foreach ( $fields as $field ){
        if( is_array( $row ) ) {
            if (key_exists($field, $row)) {
                $updatedAt = $row[$field];
                $row[$field] = convertData($updatedAt, $formatIn);
            }
        }
    }
    return $row;
}

function format_number($valor, $decimal=0, $money=false){

    $dec_point     = ',';
    $thousands_sep = '.';
    if($money){
        $dec_point     = '.';
        $thousands_sep = ',';
    }

    return number_format($valor,$decimal, $dec_point, $thousands_sep);
}

/**
 * CONVERT 3.222,443 => 3222.443
 * @param $num
 * @return float
 */
function to_float($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    }

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    );
}

function convert_price_in_row($row=[], $fields=['final_price', 'product_price', 'grand_total','discount_price','special_price', 'price'], $precision=2){
    foreach ( $fields as $field ){
        if( is_array( $row ) ) {
            if (key_exists($field, $row) && !is_null( $row[$field] ) ) {
                $row[$field] = format_number($row[$field], $precision);
            }
        }
    }
    return $row;
}

function convert_data_in_array(&$arrayValues=[], $formatIn='YYYMMDDHHMMSS', $fields=['created_at','updated_at']){
    $arrayValues = array_map(function ($row) use ($fields,$formatIn){
        return convert_data_in_row($row,$formatIn, $fields);
    },$arrayValues);

    return $arrayValues;
}

function remove_key_in_array( &$array, $keys=[], $reverse = FALSE ){

    $keys  = is_array($keys) ? $keys : explode(',', $keys);
    $array = array_map( function($row) use( $keys, $reverse ) {
        if( $reverse === FALSE ){
            foreach ( $keys as $key ){
                if( key_exists($key, $row) ){
                    array_forget($row,$key);
                }
            }
        } else {
            foreach ( $row as $key => $value ){
                if( !in_array($key, $keys) ){
                    array_forget($row,$key);
                }
            }
        }
        return $row;
    },$array);

}

function convert_string_float($value)
{
    return !is_float( $value ) ? (float)str_replace(['.',','],['','.'],$value) : $value;
}

function only_number( $string ){
    $string = !empty( $string ) ? preg_replace("/[^0-9]/", "", $string ) : null;
    return $string;
}

function only_string( $string ){
    $string = !empty( $string ) ? preg_replace("/\d/", "", $string ) : null;
    return $string;
}

function search_in_array($array, $key, $search, $first=false)
{
    $search = !is_array( $search ) ? [$search] : $search;

    $return = array_where($array, function ($row) use ($key, $search) {
        return in_array( array_get( $row, $key ), $search );
    });

    return !empty($return) ? ( $first===false ? $return: current( $return ) ) : [] ;
}

function search_like_in_array($array, $search)
{
    $return = [];
    if( !empty( $search ) && !is_array( $search ) ) {
        foreach ($array as $key => $value) {
            if (strpos($value, $search) !== FALSE) { // Yoshi version
                $return[] = $value;
            }
        }
    }
    return $return;
}

function getDateNow($timeZone='UTC', $noTime=false)
{
    if( $timeZone === 'SP' ){ $timeZone = 'America/Fortaleza'; }#Sao_Paulo

    $result = \Carbon\Carbon::now()->setTimezone($timeZone);

   return !$noTime ? $result->toDateTimeString() : $result->toDateString();
}

/**
 * 0 - FALSE
 * 1 - TRUE
 * @param $char
 * @return int|null
 */
function convert_number_bool($char){
    $char   = trim( $char );
    $return = null;
    switch ( $char ){
        case null :
        case FALSE:
        case '0'  :
        case 0    : $return = FALSE;break;
        case TRUE :
        case 1    : $return = TRUE;break;
    }
    return (boolean) $return;
}

function mask_string( $str, $mask='CELLPHONE' ){
    $tmp  = $mask;
    $mask = null;

    if( strlen( $str ) === 10 && $tmp === 'CELLPHONE' ){$tmp='PHONE';}

    switch ( $tmp ){
        case 'PF'       :
        case 'CPF'      :$mask = '###.###.###-##';break;
        case 'PJ'       :
        case 'CNPJ'     :$mask = '##.###.###/####-##';break;
        case 'CEP'      :$mask = '#####-###';break;
        case 'CELLPHONE':$mask = '(##) #####-####';break;
        case 'PHONE'    :$mask = '(##) ####-####';break;
    }

    if( !empty( $mask ) ) {
        $str = str_replace(" ", "", $str);
        $str = only_number($str);
        if( !empty( $str ) ){
            $cont=0;
            foreach ( str_split( $mask, 1 ) as $loop => $caracter ) {
                if ($caracter == '#') {
                    $mask[$loop] = isset( $str[$cont] ) ? $str[$cont] : ' ';
                    $cont++;
                }
            }
        } else {
            $mask = $str;
        }
    }
    else {
        $mask = $str;
    }
    return trim( $mask );
}

function get_first_name( $fullName, $upperFirstLetter=false ){
    if( !empty( $fullName ) ){
        $fullName = current( explode(' ', trim( $fullName )) );
    }
    return !$upperFirstLetter ? $fullName : ucfirst( strtolower( $fullName ) );
}

function format_paginate( $resultPaginateModel ){
    if( empty( $resultPaginateModel ) ){
        $resultPaginateModel = [];
    } else{

        if( !is_array( $resultPaginateModel ) ) {
            $resultPaginateModel = $resultPaginateModel->toArray();
        }

        array_pull( $resultPaginateModel, 'from' );
        array_pull( $resultPaginateModel, 'first_page_url' );
        $lastPage = array_pull( $resultPaginateModel, 'last_page_url' );
        $nextPage = array_pull( $resultPaginateModel, 'next_page_url' );
        $prevPage = array_pull( $resultPaginateModel, 'prev_page_url' );
        $path     = array_pull( $resultPaginateModel, 'path' );
        $resultPaginateModel['prev_page'] = null;
        $resultPaginateModel['next_page'] = null;

        if( !empty( $nextPage ) ){$resultPaginateModel['next_page'] = (int)str_replace( $path.'?page=', '', $nextPage);}
        if( !empty( $prevPage ) ){$resultPaginateModel['prev_page'] = (int)str_replace( $path.'?page=', '', $prevPage);}

        $resultPaginateModel['limit'] = array_pull( $resultPaginateModel, 'per_page' );
    }

    return $resultPaginateModel;
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

if (!function_exists('toolsLang_get')) {

    /**
     * @param $sessao
     * @param $texto
     * @return mixed
     */
    function magaliApilang_get($texto,$sessao='default'){
        return \Lang::get('ToolsLang::'.$sessao.'.'.$texto);
    }
}
