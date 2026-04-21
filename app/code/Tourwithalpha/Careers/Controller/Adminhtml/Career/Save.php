<?php
/**
 * Tourwithalpha Careers Module
 * Career save controller (create / update)
 */

declare(strict_types=1);

namespace Tourwithalpha\Careers\Controller\Adminhtml\Career;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Tourwithalpha\Careers\Model\CareerFactory;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Tourwithalpha_Careers::careers';

    /**
     * @var CareerFactory
     */
    private CareerFactory $careerFactory;

    /**
     * @param Context $context
     * @param CareerFactory $careerFactory
     */
    public function __construct(Context $context, CareerFactory $careerFactory)
    {
        parent::__construct($context);
        $this->careerFactory = $careerFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id    = isset($data['id']) ? (int) $data['id'] : null;
        $model = $this->careerFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This career listing no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        try {
            $model->setTitle(trim($data['title'] ?? ''));
            $model->setDepartment($data['department'] ?? '');
            $model->setLocation(trim($data['location'] ?? ''));
            $model->setEmploymentType($data['employment_type'] ?? 'full_time');
            $model->setDescription($data['description'] ?? '');
            $model->setRequirements($data['requirements'] ?? '');
            $model->setSalaryRange(trim($data['salary_range'] ?? ''));
            $model->setIsActive((int) ($data['is_active'] ?? 1));
            $model->save();

            $this->messageManager->addSuccessMessage(__('Career listing saved successfully.'));
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the career listing.'));
        }

        return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
    }
}
