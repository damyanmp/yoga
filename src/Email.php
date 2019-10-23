<?php

namespace Yoga;

/**
 * @method static Email service()
 */
class Email extends \Yoga\Service {

    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $bodyHtml
     * @param string[] $cc
     * @param string[] $bcc
     * @throws \Exception
     */
    public function mail(
        $from,
        $to,
        $subject,
        $bodyHtml,
        array $cc = [],
        array $bcc = []
    ) {
        $emailConfiguration = \Yoga\Configuration::service()->getEmailConfiguration();
        if (!$emailConfiguration instanceof \Yoga\Configuration\Email\Ses) {
            throw new \Exception(
                'Unsupported email configuration class: `' .
                    get_class($emailConfiguration) . '`'
            );
        }
        $transport = \Swift_SmtpTransport::newInstance(
            $emailConfiguration->getHost(),
            $emailConfiguration->getPort(),
            $emailConfiguration->getSecurity()
        );
        $transport->setUsername($emailConfiguration->getUserName());
        $transport->setPassword($emailConfiguration->getPassword());

        $mailer = \Swift_Mailer::newInstance($transport);

        $message = \Swift_Message::newInstance($subject)
            ->setFrom($this->convertEmailAddressForSwiftmailer($from))
            ->setTo($this->convertEmailAddressForSwiftmailer($to))
            ->setCc($this->convertEmailAddressForSwiftmailer($cc))
            ->setBcc($this->convertEmailAddressForSwiftmailer($bcc))
            ->setBody($this->convertBodyHtmlToText($bodyHtml))
            ->addPart($bodyHtml, 'text/html');

        $mailer->send($message);
    }

    /**
     * @param array|string $fullEmail
     * @return array
     */
    public function convertEmailAddressForSwiftmailer($fullEmail) {
        if (!$fullEmail) {
            return null;
        }
        if (is_array($fullEmail)) {
            $result = [];
            foreach ($fullEmail as $email) {
                $result = array_merge($result, $this->convertEmailAddressForSwiftmailer($email));
            }
            return sizeof($result) ? $result : ['', null];
        }

        list($email, $name) = \Yoga\Strings::service()->parseFullEmail($fullEmail);
        if (!$name) {
            return [$email];    // swiftmailer is weird like that
        }
        return [$email => $name];
    }

    private function convertBodyHtmlToText($bodyHtml) {
        $bodyText = $bodyHtml;
        $bodyText = preg_replace('~[\s]+~', ' ', $bodyText);
        $bodyText = str_replace('&nbsp', ' ', $bodyText);
        $bodyText = str_replace('<br />', "\n", $bodyText);
        $bodyText = str_replace('<br/>', "\n", $bodyText);
        $bodyText = str_replace('<br>', "\n", $bodyText);
        $bodyText = strip_tags($bodyText);
        return trim($bodyText);
    }

}