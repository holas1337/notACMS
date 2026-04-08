<?php

declare(strict_types=1);

namespace NotACms\Form;

use NotACms\Service\SiteConfigServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $locale = $options['locale'] ?? $this->siteConfigService->getDefaultLocale();

        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('contact.form.name', locale: $locale),
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)],
                'attr' => ['autocomplete' => 'name'],
            ])
            ->add('email', EmailType::class, [
                'label' => $this->translator->trans('contact.form.email', locale: $locale),
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('website', UrlType::class, [
                'label' => $this->translator->trans('contact.form.website', locale: $locale),
                'required' => false,
                'default_protocol' => null,
                'constraints' => [new Assert\Url(requireTld: true)],
                'attr' => ['autocomplete' => 'url'],
            ])
            ->add('subject', TextType::class, [
                'label' => $this->translator->trans('contact.form.subject', locale: $locale),
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 3, max: 200)],
            ])
            ->add('message', TextareaType::class, [
                'label' => $this->translator->trans('contact.form.message', locale: $locale),
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 10, max: 5000)],
                'attr' => ['rows' => 6],
            ])
            ->add('turnstile_token', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locale' => $this->siteConfigService->getDefaultLocale(),
            'csrf_protection' => false,
            'attr' => ['id' => 'contact-form', 'novalidate' => 'novalidate'],
        ]);
    }
}
