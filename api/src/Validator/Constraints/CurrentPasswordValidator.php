<?php

namespace App\Validator\Constraints;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class CurrentPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @param CurrentPassword $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        if (null === $value || '' === $value || !is_string($value) || !$this->passwordHasher->isPasswordValid($user, $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
