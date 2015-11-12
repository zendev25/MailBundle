<?php

namespace ZEN\MailBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Swift_Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MailController extends Controller {

    //Recup les url des images
    protected function getImages() {
        $urls = array();
        $urlFb = str_replace('app_dev.php/', '', $this->getRequest()->getUriForPath('/bundles/zenmail/images/facebook.png'));
        $urlInstag = str_replace('app_dev.php/', '', $this->getRequest()->getUriForPath('/bundles/zenmail/images/instagram.png'));
        $urlPint = str_replace('app_dev.php/', '', $this->getRequest()->getUriForPath('/bundles/zenmail/images/pinterest.png'));
        $urlTwit = str_replace('app_dev.php/', '', $this->getRequest()->getUriForPath('/bundles/zenmail/images/twitter.png'));
        $urlLogo = str_replace('app_dev.php/', '', $this->getRequest()->getUriForPath('/bundles/zenmail/images/logo.png'));

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

    public function sendMailUserAction($subject, $user, $pathChildTemplate, $confirmationUrl = "") {
        $urls = $this->getImages();
        $to = $user->getEmail();
        $from = $this->container->getParameter('mailer_user');

        $message = $this->renderView('ZENMailBundle::layout-mail.html.twig', array('pathChildTemplate' => $pathChildTemplate, 'user' => $user, 'urls' => $urls, 'confirmationUrl' => $confirmationUrl));

        return $this->sendMailAction($from, $to, $subject, $message);
    }

    public function sendMailAction($from, $to, $subject, $message) {

        
        $mail = 1;
        if ($mail) {
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf8\n';
        
            return mail($to, $subject, $message, $headers);
        } else {
            $message = \Swift_Message::newInstance();
            $cids = $this->embedImages($message, $urls);
            $message->setSubject($subject)
                    ->setFrom($from)
                    ->setTo($to)
                    ->setBody($this->renderView('ZENMailBundle::layout-mail.html.twig', array('pathChildTemplate' => $pathChildTemplate, 'user' => $user, 'urls' => $cids)
                            ), 'text/html'
                    )
            ;
            return $this->get('mailer')->send($message);
        }
    }

}
