<?php
/**
 * Tourwithalpha Careers Module
 * GraphQL resolver – submit job application and notify admin
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Tourwithalpha\Careers\Model\CareerFactory;
use Tourwithalpha\Careers\Model\EmailNotification;
use Tourwithalpha\Careers\Model\FileUploader;
use Tourwithalpha\Careers\Model\JobApplicationFactory;

class SubmitJobApplication implements ResolverInterface
{
    /**
     * @var JobApplicationFactory
     */
    private JobApplicationFactory $applicationFactory;

    /**
     * @var CareerFactory
     */
    private CareerFactory $careerFactory;

    /**
     * @var EmailNotification
     */
    private EmailNotification $emailNotification;

    /**
     * @var FileUploader
     */
    private FileUploader $fileUploader;

    /**
     * @param JobApplicationFactory $applicationFactory
     * @param CareerFactory $careerFactory
     * @param EmailNotification $emailNotification
     * @param FileUploader $fileUploader
     */
    public function __construct(
        JobApplicationFactory $applicationFactory,
        CareerFactory $careerFactory,
        EmailNotification $emailNotification,
        FileUploader $fileUploader
    ) {
        $this->applicationFactory = $applicationFactory;
        $this->careerFactory      = $careerFactory;
        $this->emailNotification  = $emailNotification;
        $this->fileUploader       = $fileUploader;
    }

    /**
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $input = $args['input'] ?? [];

        $this->validateInput($input);

        $careerId = (int) $input['career_id'];
        $career   = $this->careerFactory->create()->load($careerId);

        if (!$career->getId() || !$career->getIsActive()) {
            throw new GraphQlInputException(__('The selected career listing is not available.'));
        }

        // Handle optional resume upload
        $resumePath = null;
        if (!empty($input['resume_base64']) && !empty($input['resume_filename'])) {
            try {
                $resumePath = $this->fileUploader->save(
                    $input['resume_base64'],
                    $input['resume_filename']
                );
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()));
            }
        }

        try {
            $application = $this->applicationFactory->create();
            $application->setCareerId($careerId);
            $application->setFirstName(trim($input['first_name']));
            $application->setLastName(trim($input['last_name']));
            $application->setEmail(trim($input['email']));
            $application->setPhone(trim($input['phone'] ?? ''));
            $application->setCoverLetter($input['cover_letter'] ?? '');
            $application->setResumePath($resumePath ?? '');
            $application->setStatus('new');
            $application->save();

            $this->emailNotification->sendApplicationNotification($application, $career);

            return [
                'success'        => true,
                'message'        => __('Your application has been submitted successfully. We will be in touch soon!')->render(),
                'application_id' => (int) $application->getId(),
            ];
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __('Unable to submit your application. Please try again later.')
            );
        }
    }

    /**
     * @param array $input
     * @throws GraphQlInputException
     */
    private function validateInput(array $input): void
    {
        foreach (['career_id', 'first_name', 'last_name', 'email'] as $field) {
            if (empty($input[$field])) {
                throw new GraphQlInputException(__('Field "%1" is required.', $field));
            }
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new GraphQlInputException(__('Please provide a valid email address.'));
        }

        // If one resume field is provided, both must be present
        $hasBase64   = !empty($input['resume_base64']);
        $hasFilename = !empty($input['resume_filename']);

        if ($hasBase64 !== $hasFilename) {
            throw new GraphQlInputException(
                __('Both "resume_base64" and "resume_filename" must be provided together.')
            );
        }
    }
}
