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

final class ContactType extends AbstractType
{
    public const string FIELD_TURNSTILE = 'turnstile_token';

    public function __construct(
        private readonly SiteConfigServiceInterface $siteConfigService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'contact.form.name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 2, max: 100)],
                'attr' => ['autocomplete' => 'name', 'aria-describedby' => 'error-name'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'contact.form.email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
                'attr' => ['autocomplete' => 'email', 'aria-describedby' => 'error-email'],
            ])
            ->add('website', UrlType::class, [
                'label' => 'contact.form.website',
                'required' => false,
                'default_protocol' => null,
                'constraints' => [new Assert\Url(requireTld: true)],
                'attr' => ['autocomplete' => 'url', 'aria-describedby' => 'error-website'],
            ])
            ->add('subject', TextType::class, [
                'label' => 'contact.form.subject',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 3, max: 200)],
                'attr' => ['aria-describedby' => 'error-subject'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'contact.form.message',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(min: 10, max: 5000)],
                'attr' => ['rows' => 6, 'aria-describedby' => 'error-message'],
            ])
            ->add(self::FIELD_TURNSTILE, HiddenType::class, [
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
