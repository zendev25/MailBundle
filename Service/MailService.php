<?php

namespace ZEN\MailBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Config\Definition\Exception\Exception;
use Swift_Image;

class MailService {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    //Recup les url des images
    protected function getImages() {
        $urls = array();
        $urlFb = str_replace('app_dev.php/', '', $this->container->get('request')->getUriForPath('/bundles/zenmail/images/facebook.png'));
        $urlInstag = str_replace('app_dev.php/', '', $this->container->get('request')->getUriForPath('/bundles/zenmail/images/instagram.png'));
        $urlPint = str_replace('app_dev.php/', '', $this->container->get('request')->getUriForPath('/bundles/zenmail/images/pinterest.png'));
        $urlTwit = str_replace('app_dev.php/', '', $this->container->get('request')->getUriForPath('/bundles/zenmail/images/twitter.png'));
        $urlLogo = str_replace('app_dev.php/', '', $this->container->get('request')->getUriForPath($this->container->getParameter('zen_mail.logo')));

        $urls['fb'] = $urlFb;
        $urls['instag'] = $urlInstag;
        $urls['pint'] = $urlPint;
        $urls['twit'] = $urlTwit;
        $urls['logo'] = $urlLogo;

        return $urls;
    }

    //ajoute les images au mails
    protected function embedImages($message, $urls) {
        $cids = array();
        $cids['fb'] = $message->embed(Swift_Image::fromPath($urls['fb']));
        $cids['instag'] = $message->embed(Swift_Image::fromPath($urls['instag']));
        $cids['pint'] = $message->embed(Swift_Image::fromPath($urls['pint']));
        $cids['twit'] = $message->embed(Swift_Image::fromPath($urls['twit']));
        $cids['logo'] = $message->embed(Swift_Image::fromPath($urls['logo']));

        return $cids;
    }

    public function sendMailUser($subject, $user, $pathChildTemplate, $param = array()) {
        $urls = $this->getImages();
        $to = $user->getEmail();
        $from = $this->container->getParameter('mailer_user');

        $message = $this->container->get('templating')->render('ZENMailBundle::layout-mail.html.twig', [
            'pathChildTemplate' => $pathChildTemplate,
            'subject' => $subject,
            'user' => $user,
            'urls' => $urls,
            'param' => $param
        ]);


        return $this->sendMail($from, $to, $subject, $message);
    }

    public function sendMail($from, $to, $subject, $message) {

        $mail = \Swift_Message::newInstance();

        $mail->setSubject($subject)
                ->setFrom($from)
                ->setTo($to)
                ->setCc('dev@hall-inn.com')
                ->setBody($message, 'text/html')
        ;

        return $this->container->get('mailer')->send($mail);
    }

}
