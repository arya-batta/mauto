<?php

namespace Mautic\EmailBundle\Model;

class TransportType
{
    public function getTransportTypes()
    {
        return [
            'le.transport.amazon'       => 'le.email.config.mailer_transport.amazon',
            'le.transport.elasticemail' => 'le.email.config.mailer_transport.elasticemail',
            'gmail'                         => 'le.email.config.mailer_transport.gmail',
            'le.transport.mandrill'     => 'le.email.config.mailer_transport.mandrill',
            'le.transport.mailjet'      => 'le.email.config.mailer_transport.mailjet',
            'smtp'                          => 'le.email.config.mailer_transport.smtp',
            'mail'                          => 'le.email.config.mailer_transport.mail',
            'le.transport.postmark'     => 'le.email.config.mailer_transport.postmark',
            'le.transport.sendgrid'     => 'le.email.config.mailer_transport.sendgrid',
            'le.transport.sendgrid_api' => 'le.email.config.mailer_transport.sendgrid_api',
            'sendmail'                      => 'le.email.config.mailer_transport.sendmail',
            'le.transport.sparkpost'    => 'le.email.config.mailer_transport.sparkpost',
        ];
    }

    public function getCustomTransportType($needLeTransport)
    {
        $leTrans         = ['le.transport.vialeadsengage'   => 'le.transport.vialeadsengage'];
        $customTransport = ['le.transport.amazon'       => 'le.transport.amazon',
            'le.transport.elasticemail'                 => 'le.email.config.mailer_transport.elasticemail',
            'le.transport.sendgrid_api'                 => 'le.email.config.mailer_transport.sendgrid_api',
            'le.transport.sparkpost'                    => 'le.email.config.mailer_transport.sparkpost', ];

        if ($needLeTransport) {
            $customTransport = array_merge($leTrans, $customTransport);
        }

        return $customTransport;
    }

    public function getSmtpService()
    {
        return '"smtp"';
    }

    public function getAmazonService()
    {
        return '"le.transport.amazon"';
    }

    public function getCustomServiceForUser()
    {
        return '"le.transport.amazon",
                "le.transport.elasticemail"';
    }

    public function getCustomService()
    {
        return '"le.transport.amazon",
                "le.transport.sparkpost",
                "le.transport.mandrill",
                "le.transport.sendgrid_api",
                "le.transport.elasticemail"';
    }

    public function getLeadsEngageService()
    {
        return '"le.transport.vialeadsengage"';
    }

    public function getMailjetService()
    {
        return '"le.transport.mailjet"';
    }

    public function getServiceRequiresLogin()
    {
        return '"le.transport.mandrill",
                "le.transport.mailjet",
                "le.transport.sendgrid",
                "le.transport.elasticemail",
                "le.transport.amazon",
                "le.transport.postmark",
                "le.transport.sendgrid_api",
                "le.transport.vialeadsengage",
                "gmail"';
    }

    public function getServiceDoNotNeedLogin()
    {
        return '"mail",
                "sendmail",
                "le.transport.sparkpost"';
    }

    public function getServiceRequiresPassword()
    {
        return '"le.transport.elasticemail",
                "le.transport.sendgrid",
                "le.transport.amazon",
                "le.transport.postmark",
                "le.transport.mailjet",
                "le.transport.vialeadsengage",
                "gmail"';
    }

    public function getServiceDoNotNeedPassword()
    {
        return '"mail",
                "sendmail",
                "le.transport.sparkpost",
                "le.transport.mandrill",
                "le.transport.sendgrid_api"';
    }

    public function getServiceRequiresApiKey()
    {
        return '"le.transport.sparkpost",
                "le.transport.mandrill",
                "le.transport.sendgrid_api"';
    }
}
