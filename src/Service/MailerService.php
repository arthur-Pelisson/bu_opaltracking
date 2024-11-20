<?php


namespace App\Service;


use App\Dto\UpdateETDLinesMessageCounters;
use App\Entity\ETD;
use App\Entity\User;
use App\Type\ETDStatusType;
use App\Type\UserType;
use http\Exception\UnexpectedValueException;
use InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class MailerService
{
    private MailerInterface $_mailer;
    private ConfigurationService $_configurationService;
    private UrlGeneratorInterface $_urlGeneratorInterface;

    public function __construct(MailerInterface $mailer, ConfigurationService $configurationService, UrlGeneratorInterface $urlGeneratorInterface)
    {
        $this->_mailer = $mailer;
        $this->_configurationService = $configurationService;
        $this->_urlGeneratorInterface = $urlGeneratorInterface;
    }


    public function sendUpdateVendorPeriodEmail()
    {
        $from = $this->getFromEmail();
        $subject = 'Opal Tracking Orders - ';
        // $to = [$etd->getVendor()->getEmail()];
        $to = ['pelisson.arthur@gmail.com'];
        $homeUrl = $this->getHomeURL();
        $context = [
            'title' => $subject,
            'homeUrl' => $homeUrl,
        ];
        $this->sendEmail($to, $from, $subject, 'emails/vendor_period_updated.html.twig', $context);
    }

    private function getFromEmail(): Address
    {
        $from = $this->_configurationService->getNotificationsFromEmail();
        if($from === null) {
            throw new UnexpectedValueException('The configuration getNotificationsFromEmail() is null');
        }
        return new Address($from, 'Opal Tracking Orders');
    }

    private function getHomeURL() {
        return $this->_urlGeneratorInterface->generate(
            'app_home',
            null,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    private function getETDURL($etdId): string
    {
        return $this->_urlGeneratorInterface->generate(
            'app_etdlines_home',
            ['etdId' => $etdId],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     */
    public function sendUpdateETDLinesEmail(ETD $etd, UpdateETDLinesMessageCounters $counters, ?string $message, User $user): void
    {
        $dateFormat = $this->_configurationService->getFormatDate();
        // $from = $this->getFromEmail();
        new Address('mailer@opal.fr', 'Opal Tracking Orders Mailer');

        if($dateFormat === null) {
            throw new UnexpectedValueException('The configuration getFormatDate() is null');
        }

        $subject = 'Opal Tracking Orders - Vendor ' . $etd->getVendor()->getNo() . ' - ';
        $etdUrl = $this->getETDURL($etd->getId());
//        $to = [$this->_configurationService->getNotificationsToPurchaserEmail(), $etd->getVendor()->getEmail()];
        $to = [$this->_configurationService->getNotificationsToPurchaserEmail(), 'pelisson.arthur@gmail.com'];


        if($etd->getStatus() === ETDStatusType::CLOSED) {
            $subject .= 'ETD ' . $etd->getEtdDate()->format($dateFormat) . ' has been closed';
        } else {
            $subject .= 'ETD ' . $etd->getEtdDate()->format($dateFormat) . ' has been updated';
        }

        $context = [
            'title' => $subject,
            'etd' => $etd,
            'counters' => $counters,
            'etdUrl' => $etdUrl,
            'message' => $message
        ];
        try {
            $this->sendEmail($to, $from, $subject, 'emails/etdupdated.html.twig', $context);
        } catch(\Exception $e) {
            // $this->logger->error($e->getMessage());
        }
        
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(array $to, Address $from, string $subject, string $template, array $context): void
    {
        $countToAddress = count($to);
        if($countToAddress >= 1) {
            $email = (new TemplatedEmail())
                ->from($from)
                ->to($to[0])
                ->subject($subject)
                ->htmlTemplate($template)
                ->context($context);
            for($i = 1; $i < $countToAddress; $i++) {
                $email->addTo($to[$i]);
            }
            $this->_mailer->send($email);
        }

        throw new InvalidArgumentException('The email does not have email to');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testMail($from, $to, $subject): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->html('<h2>Test de mail </h2>');
        try {
            $this->_mailer->send($email);
        } catch(InvalidArgumentException $e) {
            throw new InvalidArgumentException('The email does not have email to');
        }
    }
}