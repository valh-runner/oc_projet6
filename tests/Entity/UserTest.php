<?php
namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class InvitationCodeTest extends KernelTestCase
{

    public function getEntity(): User
    {
        return (new User())
            ->setUsername("Elliot")
            ->setEmail("elliot@protonmail.com")
            ->setPassword("w@ddqsf#")
            ->setCreationMoment(new \Datetime());
    }

    public function assertHasErrors(User $code, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($code);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidUsername()
    {
        $this->assertHasErrors($this->getEntity()->setUsername("E"), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->getEntity()->setEmail("elliotprotonmail.com"), 1);
    }

    public function testInvalidPassword()
    {
        $this->assertHasErrors($this->getEntity()->setPassword("w@ddqsf"), 1);
    }

    public function testBlankUsername()
    {
        $this->assertHasErrors($this->getEntity()->setUsername(""), 2);
    }

    public function testBlankEmail()
    {
        $this->assertHasErrors($this->getEntity()->setEmail(""), 2);
    }

    public function testBlankPassword()
    {
        $this->assertHasErrors($this->getEntity()->setPassword(""), 2);
    }
}