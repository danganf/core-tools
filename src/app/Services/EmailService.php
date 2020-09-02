<?php

namespace DanganfTools\App\Service;

use Illuminate\Support\Facades\Mail;
use DanganfTools\Exceptions\ApiException;

class EmailService
{
    private $toEmail, $toName, $template, $subject, $body;
    private $params = [
        'default_body_styles'    => '',
        'default_link_support'   => '',
        'default_contact_phone'  => '',
        'default_contact_email'  => '',
        'default_base_home'      => '',
    ];

    public function __construct()
    {
        $baseUrl = config('app.url_version_web');
        $this->params['default_base_home']    = $baseUrl;
        $this->params['default_link_support'] = $baseUrl . 'contact/';
    }

    private function init(){

        $result = get_instace_repository('Store')
                    ->setFields('name, telephone, celphone, email')
                    ->find( \Request::get('store_id') )->toArray();

        $this->params['default_contact_email'] = $result['email'];
        $this->params['default_contact_phone'] = $result['telephone'];
        if(  $result['celphone'] ){
            $this->params['default_contact_phone'] = $result['celphone'];
        }

    }

    public function toEmail($value){
        $this->toEmail = $value;
        return $this;
    }
    public function toName( $value){
        $this->toName = $value;
        $this->setParams(  [ 'first_name' => get_first_name( $value, true ) ] );
        return $this;
    }

    public function template($value){
        $this->template = $value;
        return $this;
    }

    public function setParams( $arrvalues ){
        $this->params = array_merge( $this->params, $arrvalues );
        return $this;
    }

    public function send(){
        $this->init();
        $this->bindTemplate();
        if( $this->toEmail ){

            $template = $this->body;
            $toEmail  = $this->toEmail;
            $toName   = $this->toName;
            $subject  = $this->subject;
            //dd($this);

            Mail::send([], [], function($message) use ( $template, $subject, $toName, $toEmail )
            {
                $message->to( $toEmail, $toName );
                $message->subject($subject);
                $message->setBody( $template, 'text/html' );
            });

        } else {
            throw new ApiException('E-mail destino não fornecido');
        }
    }

    private function bindTemplate(){

        if( !empty( $this->template ) ){

            $arrEmailTemp = get_instace_repository('EmailTemplate')
                                ->setFields('temp_subject, temp_body, temp_styles, from_name, from_email')
                                ->setFilterStore()
                                ->setStatusTrue(null, null, true)
                                ->findBy( 'slug', $this->template )->toArray();

            if( !empty( $arrEmailTemp ) ){

                if( !empty( $arrEmailTemp['temp_styles'] ) ){
                    $this->setParams( [ 'default_body_styles' => minify_css($arrEmailTemp['temp_styles']) ] );
                }

                $this->body    = $this->renderBladeCompile( minify_html($arrEmailTemp['temp_body']), $this->params );
                $this->subject = $arrEmailTemp['temp_subject'];

            } else {
                $msgError = 'Template não localizado';
            }

        } else {
            $msgError = 'Template mail not set';
        }

        if( isset( $msgError ) ) {
            throw new ApiException($msgError);
        }
    }

    private function renderBladeCompile( $templateHtml, $__data ){

        $arrKeys   = array_keys($__data);
        $arrValues = array_values($__data);

        $arrKeys = array_map( function($value){
                    return '{{'.$value.'}}';
                }, $arrKeys );

        return str_replace( $arrKeys, $arrValues, $templateHtml );

       /* $__php = \Illuminate\Support\Facades\Blade::compileString($templateHtml);
        dd($__php);

        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        try {
            eval('?' . '>' . $__php);
        } catch (\Exception $e) {
            dd('Exception', $e->getMessage());while (ob_get_level() > $obLevel) ob_end_clean();
        } catch (\Throwable $e) {
            dd('Throwable', $e->getMessage());while (ob_get_level() > $obLevel) ob_end_clean();
        }
        return ob_get_clean();*/
    }
}
