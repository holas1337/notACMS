<?php

declare(strict_types=1);

namespace NotACms\Tests\Integration\Form;

use NotACms\Form\ContactType;
use NotACms\Service\SiteConfigServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;

final class ContactTypeTest extends TestCase
{
    private SiteConfigServiceInterface $siteConfigService;

    protected function setUp(): void
    {
        $this->siteConfigService = $this->createStub(SiteConfigServiceInterface::class);
        $this->siteConfigService->method('getDefaultLocale')->willReturn('en');
    }

    private function getFactory(): \Symfony\Component\Form\FormFactoryInterface
    {
        $validator = Validation::createValidator();

        return Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->addType(new ContactType($this->siteConfigService))
            ->getFormFactory();
    }

    public function testSubmitValidData(): void
    {
        $factory = $this->getFactory();
        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content',
            'turnstile_token' => 'test-token',
        ];

        $form = $factory->create(ContactType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
    }

    public function testNameIsRequired(): void
    {
        $factory = $this->getFactory();
        $form = $factory->create(ContactType::class);
        $form->submit([
            'name' => '',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            'turnstile_token' => '',
        ]);

        self::assertFalse($form->isValid());
    }

    public function testEmailIsRequired(): void
    {
        $factory = $this->getFactory();
        $form = $factory->create(ContactType::class);
        $form->submit([
            'name' => 'John',
            'email' => '',
            'subject' => 'Test',
            'message' => 'Test message',
            'turnstile_token' => '',
        ]);

        self::assertFalse($form->isValid());
    }

    public function testEmailMustBeValid(): void
    {
        $factory = $this->getFactory();
        $form = $factory->create(ContactType::class);
        $form->submit([
            'name' => 'John',
            'email' => 'not-an-email',
            'subject' => 'Test',
            'message' => 'Test message',
            'turnstile_token' => '',
        ]);

        self::assertFalse($form->isValid());
    }

    public function testMessageIsRequired(): void
    {
        $factory = $this->getFactory();
        $form = $factory->create(ContactType::class);
        $form->submit([
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => 'Test',
            'message' => '',
            'turnstile_token' => '',
        ]);

        self::assertFalse($form->isValid());
    }

    public function testSubjectIsRequired(): void
    {
        $factory = $this->getFactory();
        $form = $factory->create(ContactType::class);
        $form->submit([
            'name' => 'John',
            'email' => 'john@example.com',
            'subject' => '',
            'message' => 'Test message',
            'turnstile_token' => '',
        ]);

        self::assertFalse($form->isValid());
    }
}
