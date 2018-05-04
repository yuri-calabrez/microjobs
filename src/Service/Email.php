<?php

namespace App\Service;


class Email
{
    public $mailer;
    public $view;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $view)
    {
        $this->mailer = $mailer;
        $this->view = $view;
    }

    /**
     * @param string $assunto
     * @param array $destinatario
     * @param string $template
     * @param array $parametros
     */
    public function enviar(string $assunto, array $destinatario, string $template, array $parametros)
    {
        $mensagem = (new \Swift_Message($assunto))
            ->setFrom('noreplay@microjobs.com.br')
            ->setTo($destinatario)
            ->setBody($this->view->render($template, $parametros), 'text/html');

        $this->mailer->send($mensagem);
    }
}