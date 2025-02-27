<?php

declare(strict_types=1);
namespace In2code\Powermail\Domain\Validator;

use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Form;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\FormRepository;
use In2code\Powermail\Utility\FrontendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PasswordValidator
 */
class PasswordValidator extends AbstractValidator
{
    /**
     * Validation of given Params
     *
     * @param Mail $mail
     * @return bool
     */
    public function isValid($mail)
    {
        if (!$this->formHasPassword($mail->getForm()) || $this->ignoreValidationIfConfirmation()) {
            return true;
        }

        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()->getType() !== 'password') {
                continue;
            }
            if ($answer->getValue() !== $this->getMirroredValueOfPasswordField($answer->getField())) {
                $this->setErrorAndMessage($answer->getField(), 'password');
            }
        }

        return $this->isValidState();
    }

    /**
     * Get mirror value from POST params
     *
     * @param Field $field
     * @return string
     */
    protected function getMirroredValueOfPasswordField(Field $field): string
    {
        return (string)FrontendUtility::getArguments()['field'][$field->getMarker() . '_mirror'];
    }

    /**
     * Checks if given form has a password field
     *
     * @param Form $form
     * @return bool
     */
    protected function formHasPassword(Form $form): bool
    {
        $formRepository = GeneralUtility::makeInstance(FormRepository::class);
        $form = $formRepository->hasPassword($form);
        return (bool)count($form);
    }

    /**
     * Stop validation if confirmation step is active on create
     *
     * @return bool
     */
    protected function ignoreValidationIfConfirmation(): bool
    {
        return FrontendUtility::getArguments()['__referrer']['@action'] === 'confirmation'
            && FrontendUtility::getArguments()['action'] === 'create';
    }
}
