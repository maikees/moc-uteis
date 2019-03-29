<?php

namespace MOCUtils\Helpers;

use App\Http\Classes\File;
use App\Http\Classes\Globais;
use App\Http\Models\Auth\TokenSenha;
use App\Http\Models\Auth\Usuario;
use App\Http\Models\Fatura\Fatura;
use App\Http\Models\Fatura\Tipo;
use App\Http\Models\Mail\Template;
use App\Http\Models\Nucleo\Projeto;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class MailTemplate
 * @package MOCUtils\Helpers
 */
class Email
{
    /**
     * @var array
     */
    private $map = [];
    /**
     * @var array
     */
    private $models = [];
    /**
     * @var string
     */
    private $mail = "";
    /**
     * @var App\Http\Models\Mail\Template\Template|null
     */
    private $shared;
    /**
     * @var string
     */
    private $result = "";

    /**
     * MailTemplate constructor.
     * @param string $mail
     */
    public function __construct($mail = "", $shared = null)
    {
        $this->shared = (new Template)->byCodigo($shared);
        $this->mail = (new Template)->byCodigo($mail);

        $this->map = [
            "usuario" => Usuario::class,
            "fatura" => Fatura::class,
            "fatura.tipo" => Tipo::class,
            "global" => Globais::class,
            "template" => Template::class,
            "token" => TokenSenha::class,
            "documento" => File::class,
            "projeto" => Projeto::class
        ];

        $this->setModel($this->mail);
        $this->setModel(new Globais());

        return $this;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->models[get_class($model)] = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function replaceAll()
    {
        $this->result = $this->mail->email;

        if ($this->shared) {
            $this->result = str_replace(['{content}'], [$this->result], $this->shared->email);
        }

        $this->result = $this->replaceMarks($this->result);

        return $this->getResult();
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $to
     * @param $toName
     * @return bool
     * @throws SlackException
     */
    public function send($to, $toName)
    {
        if (!$this->result) $this->replaceAll();
        try {
            $mail = new PHPMailer;
            //Tell PHPMailer to use SMTP
            $mail->isSMTP();
            /**
             * Enable SMTP debugging
             * 0 = off (for production use)
             * 1 = client messages
             * 2 = client and server messages
             */
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            //Ask for HTML-friendly debug output
            $mail->Debugoutput = 'html';
            //Set the hostname of the mail server
            $mail->Host = env('MAIL_HOST');
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = env('MAIL_PORT');
            $mail->SMTPSecure = 'ssl';
            //Formatação de e-mail
            $mail->CharSet = 'utf-8';
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //Username to use for SMTP authentication
            $mail->Username = env('MAIL_USERNAME');
            //Password to use for SMTP authentication
            $mail->Password = env('MAIL_PASSWORD');
            //Set who the message is to be sent from
            $mail->setFrom(env('MAIL_USERNAME'), "MOC Soluções");
            //Set who the message is to be sent to
            $mail->addAddress($to, $toName);
            //Set the subject line
            $mail->Subject = $this->mail->assunto;
            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $mail->isHTML(true);
            $mail->Body = $this->result;

            //send the message, check for errors
            $mail->send();
        } catch (\Exception $e) {
            throw new SlackException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return true;
    }

    /**
     * @param string $string
     * @return string
     */
    private function replaceMarks($string)
    {
        preg_match_all("/{(.*?)_(.*?)}/i", $string, $out);

        $objetos = $out[1];
        $var = $out[2];
        $change = $out[0];
        $changed = [];

        /**
         * Trata todos os objetos para mudança
         */
        foreach ($objetos as $key => $objeto) {
            $result = "";

            if (
                isset($this->map[$objeto]) and
                isset($this->models[$this->map[$objeto]]) and
                isset($var[$key]) and
                isset($this->models[$this->map[$objeto]]->{$var[$key]})) {
                $result = $this->models[$this->map[$objeto]]->{$var[$key]};
            }

            $changed[$key] = $result;
        }

        return str_replace($change, $changed, $string);
    }
}
