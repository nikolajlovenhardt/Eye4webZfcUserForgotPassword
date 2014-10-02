<?php

namespace Eye4web\ZfcUser\ForgotPasswordTest\Controller;

use Eye4web\ZfcUser\ForgotPassword\Controller\ForgotController;
use PHPUnit_Framework_TestCase;

class ForgotControllerTest extends PHPUnit_Framework_TestCase
{
    /** @var ForgotController */
    protected $controller;

    /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\RequestForm */
    protected $requestForm;

    /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\ChangePasswordForm */
    protected $changePasswordForm;

    /** @var \Eye4web\ZfcUser\ForgotPassword\Service\ForgotService */
    protected $forgotService;

    /** @var \Zend\Mvc\Controller\PluginManager */
    protected $pluginManager;

    public $pluginManagerPlugins = [];

    public function setUp()
    {
        /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\RequestForm $requestForm */
        $requestForm = $this->getMock('Eye4web\ZfcUser\ForgotPassword\Form\Forgot\RequestForm');
        $this->requestForm = $requestForm;

        /** @var \Eye4web\ZfcUser\ForgotPassword\Form\Forgot\ChangePasswordForm $changePasswordForm */
        $changePasswordForm = $this->getMock('Eye4web\ZfcUser\ForgotPassword\Form\Forgot\ChangePasswordForm');
        $this->changePasswordForm = $changePasswordForm;

        /** @var \Eye4web\ZfcUser\ForgotPassword\Service\ForgotService $forgotService */
        $forgotService = $this->getMockBuilder('Eye4web\ZfcUser\ForgotPassword\Service\ForgotService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->forgotService = $forgotService;

        /** @var \Zend\Mvc\Controller\PluginManager $pluginManager */
        $pluginManager = $this->getMock('Zend\Mvc\Controller\PluginManager', array('get'));

        $pluginManager->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this, 'helperMockCallbackPluginManagerGet')));

        $this->pluginManager = $pluginManager;

        $controller = new ForgotController($requestForm, $changePasswordForm, $forgotService);
        $controller->setPluginManager($pluginManager);

        $this->controller = $controller;
    }

    public function helperMockCallbackPluginManagerGet($key)
    {
        return (array_key_exists($key, $this->pluginManagerPlugins))
            ? $this->pluginManagerPlugins[$key]
            : null;
    }

    public function testIndexNoRequest()
    {
        $redirectUrl = 'test-url';
        $data = false;

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $result = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);

        $this->assertSame('zfc-user-forgot-password/request.phtml', $result->getTemplate());
    }

    public function testIndexRequestWrongRequest()
    {
        $redirectUrl = 'test-url';
        $data = ['a' => 'b'];

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $this->forgotService->expects($this->once())
            ->method('request')
            ->with($data)
            ->willReturn(false);

        $result = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);

        $this->assertSame('zfc-user-forgot-password/request.phtml', $result->getTemplate());
    }

    public function testIndexRequestSuccess()
    {
        $redirectUrl = 'test-url';
        $data = ['a' => 'b'];

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $this->forgotService->expects($this->once())
            ->method('request')
            ->with($data)
            ->willReturn(true);

        $result = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);

        $this->assertSame('zfc-user-forgot-password/confirmation/sent-email.phtml', $result->getTemplate());
    }

    public function testChangePasswordIncorrectToken()
    {
        $token = 'test-token';

        $params = $this->getMock('Zend\Mvc\Controller\Plugin\Params', ['__invoke']);

        $params->expects($this->once())
            ->method('__invoke')
            ->with('token')
            ->willReturn($token);

        $this->pluginManagerPlugins['params'] = $params;

        $this->forgotService->expects($this->once())
            ->method('getUserFromToken')
            ->with($token)
            ->willReturn(false);

        $result = $this->controller->changePasswordAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertSame('zfc-user-forgot-password/expired.phtml', $result->getTemplate());
    }

    public function testChangePasswordNoRequest()
    {
        $token = 'test-token';
        $redirectUrl = 'test-url';
        $data = false;

        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $params = $this->getMock('Zend\Mvc\Controller\Plugin\Params', ['__invoke']);

        $params->expects($this->once())
            ->method('__invoke')
            ->with('token')
            ->willReturn($token);

        $this->pluginManagerPlugins['params'] = $params;

        $this->forgotService->expects($this->once())
            ->method('getUserFromToken')
            ->with($token)
            ->willReturn($userMock);

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $result = $this->controller->changePasswordAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertSame('zfc-user-forgot-password/change-password.phtml', $result->getTemplate());
    }

    public function testChangePasswordWrongData()
    {
        $token = 'test-token';
        $redirectUrl = 'test-url';
        $data = ['a' => 'b'];

        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $params = $this->getMock('Zend\Mvc\Controller\Plugin\Params', ['__invoke']);

        $params->expects($this->once())
            ->method('__invoke')
            ->with('token')
            ->willReturn($token);

        $this->pluginManagerPlugins['params'] = $params;

        $this->forgotService->expects($this->once())
            ->method('getUserFromToken')
            ->with($token)
            ->willReturn($userMock);

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $this->forgotService->expects($this->once())
            ->method('changePassword')
            ->with($data, $userMock)
            ->willReturn(false);

        $this->changePasswordForm->expects($this->once())
            ->method('setData')
            ->with([
                'new_password' => null,
                'confirm_new_password' => null,
            ]);

        $result = $this->controller->changePasswordAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertSame('zfc-user-forgot-password/change-password.phtml', $result->getTemplate());
    }

    public function testChangePasswordSuccess()
    {
        $token = 'test-token';
        $redirectUrl = 'test-url';
        $data = ['a' => 'b'];

        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $params = $this->getMock('Zend\Mvc\Controller\Plugin\Params', ['__invoke']);

        $params->expects($this->once())
            ->method('__invoke')
            ->with('token')
            ->willReturn($token);

        $this->pluginManagerPlugins['params'] = $params;

        $this->forgotService->expects($this->once())
            ->method('getUserFromToken')
            ->with($token)
            ->willReturn($userMock);

        $url = $this->getMock('Zend\Mvc\Controller\Plugin\Url', ['fromRoute']);

        $url->expects($this->once())
            ->method('fromRoute')
            ->willReturn($redirectUrl);

        $this->pluginManagerPlugins['url'] = $url;

        $prg = $this->getMock('Zend\Mvc\Controller\Plugin\PostRedirectGet', ['__invoke']);

        $prg->expects($this->once())
            ->method('__invoke')
            ->willReturn($data);

        $this->pluginManagerPlugins['prg'] = $prg;

        $this->forgotService->expects($this->once())
            ->method('changePassword')
            ->with($data, $userMock)
            ->willReturn(true);

        $result = $this->controller->changePasswordAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $result);
        $this->assertSame('zfc-user-forgot-password/confirmation/changed-password.phtml', $result->getTemplate());
    }
}
