<?php

declare(strict_types=1);

namespace NotACms\Controller;

use NotACms\Attribute\LocalizedRoute;
use NotACms\Form\ContactType;
use NotACms\Service\Content\ContentServiceInterface;
use NotACms\Service\SiteSettingsInterface;
use NotACms\Service\TurnstileValidatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContentServiceInterface $contentService,
        private readonly SiteSettingsInterface $siteSettings,
        private readonly TurnstileValidatorInterface $turnstileValidator,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        #[Autowire(service: 'monolog.logger.contact')]
        private readonly LoggerInterface $logger,
    ) {
    }

    #[LocalizedRoute('contact', path: '/contact/')]
    public function contact(string $locale): Response
    {
        $url = $this->generateUrl('contact_'.$locale);
        $page = $this->contentService->findByUrl($url, $locale);

        $form = $this->createForm(ContactType::class);

        $contactFormAvailable = $this->siteSettings->getContactFormConfig()->isComplete();
        if (!$contactFormAvailable) {
            $this->logger->error('Contact form misconfigured: contact_form.email and contact_form.from must be set in _site.yaml');
        }

        return $this->render('page/contact.html.twig', [
            'content' => $page,
            'form' => $form,
            'locale' => $locale,
            'contact_form_available' => $contactFormAvailable,
        ]);
    }

    #[LocalizedRoute('api_contact', path: '/api/contact', methods: ['POST'])]
    public function submit(Request $request, string $locale): JsonResponse
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];
            foreach ($form->all() as $fieldName => $field) {
                if (ContactType::FIELD_TURNSTILE === $fieldName) {
                    continue;
                }

                foreach ($field->getErrors() as $error) {
                    $errors[$fieldName] = $error->getMessage();

                    break;
                }
            }

            return new JsonResponse([
                'error' => $this->translator->trans('contact.form.error', locale: $locale),
                'errors' => $errors,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = (string) $form->get(ContactType::FIELD_TURNSTILE)->getData();
        if (!$this->turnstileValidator->verify($token, $request->getClientIp())) {
            $this->logger->warning('Contact form CAPTCHA failed', [
                'ip' => $request->getClientIp(),
                'locale' => $locale,
            ]);

            return new JsonResponse([
                'error' => $this->translator->trans('contact.form.error', locale: $locale),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $form->getData();

        $contactFormConfig = $this->siteSettings->getContactFormConfig();
        if (!$contactFormConfig->isComplete()) {
            $this->logger->error('Contact form submission rejected: contact_form.email and contact_form.from must be set in _site.yaml');

            return new JsonResponse([
                'error' => $this->translator->trans('contact.form.error', locale: $locale),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        try {
            $email = new TemplatedEmail()
                ->from(new Address($contactFormConfig->from, $contactFormConfig->fromName))
                ->replyTo(new Address($data['email'], $data['name']))
                ->to($contactFormConfig->email)
                ->subject($contactFormConfig->topic.' '.$data['subject'])
                ->htmlTemplate('email/contact.html.twig')
                ->context(['contact' => $data, 'locale' => $locale]);

            $this->mailer->send($email);

            $this->logger->info('Contact form email sent', [
                'from' => $data['email'],
                'subject' => $data['subject'],
                'locale' => $locale,
            ]);

            return new JsonResponse([
                'success' => true,
                'message' => $this->translator->trans('contact.form.success', locale: $locale),
            ]);
        } catch (\Throwable $throwable) {
            $this->logger->error('Contact form email failed', ['exception' => $throwable]);

            return new JsonResponse([
                'error' => $this->translator->trans('contact.form.error', locale: $locale),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
