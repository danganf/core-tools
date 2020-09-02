<?php

namespace IntercaseTools\MyClass;

class MyCript
{
    //Definindo um número chave, importante colocar sempre um número PRIMO maior que 3
    private $chave = 97;

    //Texto chave para dificultar a decriptografia, pois além de precisar da chave, precisa também do texto chave.
    private $add_text;

    public function __construct() {
    }

    private function setKey( $chave ) {
        $this->add_text = md5(sha1($chave));
    }

    public function encode( $key, $dados ) {

        $this->setKey( $key );
        if( is_array( $dados ) && !empty( $dados ) ){$dados = json_encode( $dados );}
        $dados     = $this->enc($dados);
        $dados     = base64_encode($dados);
        $procura   = array('A','Z','W','a','S','e','I','x','U','d','m','N','B','G','P','9','t','D','Q','F','M','H','E','X','C','V','z','Y','R','T','b','s','h','J','2');
        $substitui = array('/','-','.','@','$','&','|','#','_','(',')','%','?',',','[',']','{','}',';',':','>','<','!','"',"'",'§','£','¢','¬','ª','º','~','^','`','´');
        $dados     = str_replace($procura,$substitui,$dados);
        $dados     = str_replace('=','',addslashes($dados));
        return base64_encode( $dados );
    }

    public function decode( $key, $token ) {
        $this->setKey( $key );
        $dados=stripslashes( base64_decode( $token ) );
        $substitui=array('A','Z','W','a','S','e','I','x','U','d','m','N','B','G','P','9','t','D','Q','F','M','H','E','X','C','V','z','Y','R','T','b','s','h','J','2');
        $procura=array('/','-','.','@','$','&','|','#','_','(',')','%','?',',','[',']','{','}',';',':','>','<','!','"',"'",'§','£','¢','¬','ª','º','~','^','`','´');
        $dados=str_replace($procura,$substitui,$dados);
        $dados=base64_decode($dados);
        $dados=str_replace('  ','',$dados);

        return base64_decode( $this->dec( $dados ) );

        //echo "<pre>".base64_decode($mc -> dec( decode($criptografado) ))."</pre>";
    }

    /**
     * @param string Palavra
     * @return string
     */
    private function enc($word) {

        $word = base64_encode($word);

        $word .= $this->add_text;
        $s = strlen($word)+1;
        $nw = "";
        $n = $this->chave;
        for ($x = 1; $x < $s; $x++){
            $m = $x*$n;
            if ($m > $s){
                $nindex = $m % $s;
            }
            else if ($m < $s){
                $nindex = $m;
            }
            if ($m % $s == 0){
                $nindex = $x;
            }
            $nw = $nw.$word[$nindex-1];
        }
        return $nw;
    }

    /**
     * @param string Palavra
     * @return string
     */
    private function dec($word){
        $s = strlen($word)+1;
        $nw = "";
        $n = $this->chave;
        for ($y = 1; $y < $s; $y++){
            $m = $y*$n;
            if ($m % $s == 1){
                $n = $y;
                break;
            }
        }
        for ($x = 1; $x < $s; $x++){
            $m = $x*$n;
            if ($m > $s){
                $nindex = $m % $s;
            }
            else if ($m < $s){
                $nindex = $m;
            }
            if ($m % $s == 0){
                $nindex = $x;
            }
            $nw = $nw.$word[$nindex-1];
        }
        $t = strlen($nw) - strlen($this->add_text);
        return substr($nw, 0, $t);
    }
}
