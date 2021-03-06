<?php

namespace Eye4web\ZfcUser\ForgotPassword\Factory\Controller;

use Eye4web\ZfcUser\ForgotPassword\Controller\ForgotPasswordController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ForgotPasswordControllerFactory implements FactoryInterface
{
    /**
     * Create controller
     *
     * @param ServiceLocatorInterface $controllerManager
     * @return ForgotPasswordController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\RequestForm $requestForm */
        $requestForm = $serviceLocator->get('Eye4web\ZfcUser\ForgotPassword\Form\Forgot\RequestForm');

        /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\ChangePasswordForm $changePassword */
        $changePassword = $serviceLocator->get('Eye4web\ZfcUser\ForgotPassword\Form\Forgot\ChangePasswordForm');

        /** @var \Eye4web\ZfcUser\ForgotPassword\Service\ForgotPasswordService $forgotPasswordService */
        $forgotPasswordService = $serviceLocator->get('Eye4web\ZfcUser\ForgotPassword\Service\ForgotPasswordService');

        return new ForgotPasswordController($requestForm, $changePassword, $forgotPasswordService);
    }
}
