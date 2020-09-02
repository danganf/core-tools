<?php

namespace IntercaseTools\MyClass;

use Carbon\Carbon;

class Validator
{
    private $carbon;
    private $validatorProcess;
    private $rules    = [];
    private $message  = [];
    private $msgError = null;

    public function __construct(Carbon $carbon){
        $this->carbon = $carbon;
        $this->setDefaultMessages();
    }

    public function valid($params, $rulesColumn=[]){

        $flag = TRUE;
        foreach ( $rulesColumn AS $column ){
            $method = 'column'.ucfirst( camel_case( str_replace('.','_',$column ) ) );
            if( method_exists( $this, $method ) ) {
                call_user_func_array( [ $this, $method ], [] );
            } else {
                if( !array_has( $this->rules, $column ) ) {
                    $this->rules[$column] = 'required';
                }
            }
        }

        if( $flag ) {//dd($params, $this->rules, $this->message);
            $validator              = \Validator::make($params, $this->rules, $this->message);
            $flag                   = !$validator->fails();
            $this->validatorProcess = $validator;
        }

        $this->rules = [];

        return $flag;
    }

    public function validPassword($pass1, $pass2){
        $flag = $this->valid( [ 'password' => $pass1, 'password_repeat' => $pass2 ], ['password','password_repeat'] );
        if( $flag  ){
            if( $pass1 !== $pass2 ) {
                $this->msgError = \Lang::get('passwords.different');
                $flag = false;
            }
        }
        return $flag;
    }

    public function error(){
        $return = '';
        if( !empty( $this->msgError ) ){
            $return         = $this->msgError;
            $this->msgError = null;
        } else if( is_object($this->validatorProcess) ){
            $return = $this->validatorProcess->errors()->first();
        }
        return $return;
    }

    public function extendCpfCnpj(){

        \Validator::extend('valid_format_cpf', function($attribute, $value, $parameters)
        {
            try { return preg_match('/^\d{4}-\d{4}$/', $value) > 0; } catch (\Exception $e){ return FALSE; }
        });

        \Validator::extend('valid_format_cnpj', function($attribute, $value, $parameters)
        {
            try { return preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $value) > 0; } catch (\Exception $e){ return FALSE; }
        });

        \Validator::extend('valid_cpf', function($attribute, $value, $parameters)
        {
            $c = preg_replace('/\D/', '', $value);
            if (strlen($c) != 11 || preg_match("/^{$c[0]}{11}$/", $c)) { return false; }
            for ($s = 10, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);
            if ($c[9] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { return false; }
            for ($s = 11, $n = 0, $i = 0; $s >= 2; $n += $c[$i++] * $s--);
            if ($c[10] != ((($n %= 11) < 2) ? 0 : 11 - $n)) { return false; }

            return true;
        });

        \Validator::extend('valid_cnpj', function($attribute, $value, $parameters)
        {
            $c = preg_replace('/\D/', '', $value);
            $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            if (strlen($c) != 14) { return false;}
            elseif (preg_match("/^{$c[0]}{14}$/", $c) > 0) {return false;}
            for ($i = 0, $n = 0; $i < 12; $n += $c[$i] * $b[++$i]);
            if ($c[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {return false;}
            for ($i = 0, $n = 0; $i <= 12; $n += $c[$i] * $b[$i++]);
            if ($c[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {return false;}
            return true;
        });

        return $this;

    }

    public function extendDateGte(){
        \Validator::extend('date_gte', function($attribute, $value, $parameters)
        {
            try {
                $data = $this->carbon->createFromFormat('d/m/Y H:i:s', $value . ':00');
                $dataNow = $this->carbon->now();
                return $dataNow->timestamp == $data->min($dataNow)->timestamp ? TRUE : FALSE;
            } catch (\Exception $e){
                return FALSE;
            }
        });
        \Validator::extend('date_br', function($attribute, $value, $parameters = array())
        {
            if ($value instanceof DateTime) return true;
            $date = date_parse_from_format("d/m/Y",$value);
            return checkdate($date['month'], $date['day'], $date['year']);
        });
        \Validator::extend('valid_status', function($attribute, $value, $parameters = array())
        {
            return $value === 1 || $value === 0 || is_bool( $value ) ? true : false;
        });

        return $this;
    }

    private function columnType()          {$this->rules['type']            = 'required|in:SIMPLES,ITEM,BALCONY,DELIVERY,PICKUP';}
    private function columnPassword()      {$this->rules['password']        = 'required|min:5|max:20';}
    private function columnPasswordRepeat(){$this->rules['password_repeat'] = 'required|min:5|max:20';}
    private function columnStatus()        {$this->rules['status']          = 'required|boolean';}
    private function columnEmail()         {$this->rules['email']           = 'required|email';}
    private function columnMethod()        {$this->rules['method']          = 'required|in:POST,GET,PUT,DELETE,PATCH';}
    private function columnCellphone()     {$this->rules['cellphone']       = 'required|min:10|max:11';}
    private function columnTelephone()     {$this->rules['telephone']       = 'required|min:10|max:11';}
    private function columnPhone()         {$this->rules['phone']           = 'required|min:10|max:14';}
    private function columnState()         {$this->rules['state']           = 'required|min:2|max:2';}
    private function columnItems()         {$this->rules['items']           = 'required|array';}
    private function columnTags()          {$this->rules['tags']            = 'required|array';}

    public function setRule($field, $express)   {$this->rules[$field]   = $express;}
    public function setMessage($field, $message){$this->message[$field] = $message;}

    private function columnDateIn(){
        $this->rules['date_in']    = 'date_format:d/m/Y H:i|date_gte';
        $this->message['date_gte'] = 'Date must be greater than or equal to current datetime.';
    }

    private function setDefaultMessages(){
        $this->message['required'] = 'Campo :attribute é obrigatório.';
        $this->message['in']       = 'O campo :attribute is not as expected.';
        $this->message['min']      = 'O campo :attribute tem que ter no mínimo :min characteres.';
        $this->message['max']      = 'O campo :attribute tem que ter no máximo :max characteres.';
    }
}
