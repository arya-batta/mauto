<?php

namespace Mautic\EmailBundle\Model;

class TransportType
{
    public function getTransportTypes()
    {
        return [
            'mautic.transport.amazon'       => 'mautic.email.config.mailer_transport.amazon',
            'mautic.transport.elasticemail' => 'mautic.email.config.mailer_transport.elasticemail',
            'gmail'                         => 'mautic.email.config.mailer_transport.gmail',
            'mautic.transport.mandrill'     => 'mautic.email.config.mailer_transport.mandrill',
            'mautic.transport.mailjet'      => 'mautic.email.config.mailer_transport.mailjet',
            'smtp'                          => 'mautic.email.config.mailer_transport.smtp',
            'mail'                          => 'mautic.email.config.mailer_transport.mail',
            'mautic.transport.postmark'     => 'mautic.email.config.mailer_transport.postmark',
            'mautic.transport.sendgrid'     => 'mautic.email.config.mailer_transport.sendgrid',
            'mautic.transport.sendgrid_api' => 'mautic.email.config.mailer_transport.sendgrid_api',
            'sendmail'                      => 'mautic.email.config.mailer_transport.sendmail',
            'mautic.transport.sparkpost'    => 'mautic.email.config.mailer_transport.sparkpost',
        ];
    }

    public function getCustomTransportType($needLeTransport)
    {
        $leTrans         = ['le.transport.vialeadsengage'   => 'le.transport.vialeadsengage'];
        $customTransport = ['mautic.transport.amazon'       => 'mautic.transport.amazon',
            'mautic.transport.elasticemail'                 => 'mautic.email.config.mailer_transport.elasticemail',
            'mautic.transport.sendgrid_api'                 => 'mautic.email.config.mailer_transport.sendgrid_api',
            'mautic.transport.sparkpost'                    => 'mautic.email.config.mailer_transport.sparkpost', ];

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
        return '"mautic.transport.amazon"';
    }

    public function getCustomServiceForUser()
    {
        return '"mautic.transport.amazon",
                "mautic.transport.elasticemail"';
    }

    public function getCustomService()
    {
        return '"mautic.transport.amazon",
                "mautic.transport.sparkpost",
                "mautic.transport.mandrill",
                "mautic.transport.sendgrid_api",
                "mautic.transport.elasticemail"';
    }

    public function getLeadsEngageService()
    {
        return '"le.transport.vialeadsengage"';
    }

    public function getMailjetService()
    {
        return '"mautic.transport.mailjet"';
    }

    public function getServiceRequiresLogin()
    {
        return '"mautic.transport.mandrill",
                "mautic.transport.mailjet",
                "mautic.transport.sendgrid",
                "mautic.transport.elasticemail",
                "mautic.transport.amazon",
                "mautic.transport.postmark",
                "mautic.transport.sendgrid_api",
                "le.transport.vialeadsengage",
                "gmail"';
    }

    public function getServiceDoNotNeedLogin()
    {
        return '"mail",
                "sendmail",
                "mautic.transport.sparkpost"';
    }

    public function getServiceRequiresPassword()
    {
        return '"mautic.transport.elasticemail",
                "mautic.transport.sendgrid",
                "mautic.transport.amazon",
                "mautic.transport.postmark",
                "mautic.transport.mailjet",
                "le.transport.vialeadsengage",
                "gmail"';
    }

    public function getServiceDoNotNeedPassword()
    {
        return '"mail",
                "sendmail",
                "mautic.transport.sparkpost",
                "mautic.transport.mandrill",
                "mautic.transport.sendgrid_api"';
    }

    public function getServiceRequiresApiKey()
    {
        return '"mautic.transport.sparkpost",
                "mautic.transport.mandrill",
                "mautic.transport.sendgrid_api"';
    }
}
