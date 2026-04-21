<?php
/**
 * Tourwithalpha Careers Module
 * Sends admin email notification on new job application
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class EmailNotification
{
    private const EMAIL_TEMPLATE_ID        = 'tourwithalpha_careers_job_application';
    private const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_general/email';
    private const XML_PATH_EMAIL_NAME      = 'trans_email/ident_general/name';
    private const MEDIA_URL_PATH           = 'pub/media/';

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        LoggerInterface $logger
    ) {
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig       = $scopeConfig;
        $this->storeManager      = $storeManager;
        $this->urlBuilder        = $urlBuilder;
        $this->logger            = $logger;
    }

    /**
     * Send a notification email to the store admin when a job application is received.
     *
     * @param JobApplication $application
     * @param Career $career
     * @return void
     */
    public function sendApplicationNotification(JobApplication $application, Career $career): void
    {
        try {
            $storeId       = (int) $this->storeManager->getStore()->getId();
            $adminEmail    = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_RECIPIENT,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $adminName     = $this->scopeConfig->getValue(
                self::XML_PATH_EMAIL_NAME,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::EMAIL_TEMPLATE_ID)
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'applicant_name'  => $application->getFirstName() . ' ' . $application->getLastName(),
                    'applicant_email' => $application->getEmail(),
                    'applicant_phone' => $application->getPhone() ?: 'N/A',
                    'job_title'       => $career->getTitle(),
                    'department'      => $career->getDepartment(),
                    'location'        => $career->getLocation(),
                    'employment_type' => $career->getEmploymentType(),
                    'cover_letter'    => $application->getCoverLetter() ?: 'Not provided',
                    'resume_url'      => $this->buildResumeUrl($application->getResumePath()),
                    'application_id'  => $application->getId(),
                    'submitted_at'    => $application->getCreatedAt(),
                ])
                ->setFromByScope('general', $storeId)
                ->addTo($adminEmail, $adminName)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->logger->error(
                '[Tourwithalpha_Careers] Failed to send job application email: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }

    /**
     * Build a public URL for the stored resume file, or return an empty string if none uploaded.
     */
    private function buildResumeUrl(?string $resumePath): string
    {
        if (!$resumePath) {
            return '';
        }

        $baseUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
        return rtrim($baseUrl, '/') . '/' . ltrim($resumePath, '/');
    }
}
