<?php

/**
 * Nette Framework
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license" that is bundled
 * with this package in the file license.txt, and/or GPL license.
 *
 * For more information please see http://nette.org
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */



/**
 * Check and reset PHP configuration.
 */
/*5.2*
if (!defined('PHP_VERSION_ID')) {
	$tmp = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($tmp[0] * 10000 + $tmp[1] * 100 + $tmp[2]));
}

if (PHP_VERSION_ID < 50200) {
	throw new Exception('Nette Framework requires PHP 5.2.0 or newer.');
}
*/

error_reporting(E_ALL | E_STRICT);
@set_magic_quotes_runtime(FALSE); // @ - deprecated since PHP 5.3.0
iconv_set_encoding('internal_encoding', 'UTF-8');
extension_loaded('mbstring') && mb_internal_encoding('UTF-8');
header('X-Powered-By: Nette Framework');



/**
 * Load and configure Nette Framework
 */
define('NETTE', TRUE);
define('NETTE_VERSION_ID', 10000); // v1.0.0
define('NETTE_PACKAGE', '5.3');



require __DIR__ . '/ComponentModel/IComponent.php';
require __DIR__ . '/ComponentModel/IComponentContainer.php';
require __DIR__ . '/Forms/INamingContainer.php';
require __DIR__ . '/Application/ISignalReceiver.php';
require __DIR__ . '/Application/IStatePersistent.php';
require __DIR__ . '/Application/IRenderable.php';
require __DIR__ . '/Application/IPresenter.php';
require __DIR__ . '/Application/IPresenterLoader.php';
require __DIR__ . '/Application/IPresenterResponse.php';
require __DIR__ . '/Application/IRouter.php';
require __DIR__ . '/Debug/IDebugPanel.php';
require __DIR__ . '/Caching/ICacheStorage.php';
require __DIR__ . '/Config/IConfigAdapter.php';
require __DIR__ . '/Environment/IServiceLocator.php';
require __DIR__ . '/Forms/IFormControl.php';
require __DIR__ . '/Forms/ISubmitterControl.php';
require __DIR__ . '/Forms/IFormRenderer.php';
require __DIR__ . '/Mail/IMailer.php';
require __DIR__ . '/Reflection/IAnnotation.php';
require __DIR__ . '/Security/IAuthenticator.php';
require __DIR__ . '/Security/IAuthorizator.php';
require __DIR__ . '/Security/IIdentity.php';
require __DIR__ . '/Security/IResource.php';
require __DIR__ . '/Security/IRole.php';
require __DIR__ . '/Templates/ITemplate.php';
require __DIR__ . '/Templates/IFileTemplate.php';
require __DIR__ . '/Utils/ITranslator.php';
require __DIR__ . '/Web/IHttpRequest.php';
require __DIR__ . '/Web/IHttpResponse.php';
require __DIR__ . '/Web/IUser.php';
require __DIR__ . '/Utils/Object.php';
require __DIR__ . '/ComponentModel/Component.php';
require __DIR__ . '/ComponentModel/ComponentContainer.php';
require __DIR__ . '/Forms/FormContainer.php';
require __DIR__ . '/Forms/Form.php';
require __DIR__ . '/Application/AppForm.php';
require __DIR__ . '/Application/Application.php';
require __DIR__ . '/Application/PresenterComponent.php';
require __DIR__ . '/Application/Control.php';
require __DIR__ . '/Application/Exceptions/AbortException.php';
require __DIR__ . '/Application/Exceptions/ApplicationException.php';
require __DIR__ . '/Application/Exceptions/BadRequestException.php';
require __DIR__ . '/Application/Exceptions/BadSignalException.php';
require __DIR__ . '/Application/Exceptions/ForbiddenRequestException.php';
require __DIR__ . '/Application/Exceptions/InvalidLinkException.php';
require __DIR__ . '/Application/Exceptions/InvalidPresenterException.php';
require __DIR__ . '/Application/Link.php';
require __DIR__ . '/Application/Presenter.php';
require __DIR__ . '/Reflection/ClassReflection.php';
require __DIR__ . '/Application/PresenterComponentReflection.php';
require __DIR__ . '/Application/PresenterLoader.php';
require __DIR__ . '/Utils/FreezableObject.php';
require __DIR__ . '/Application/PresenterRequest.php';
require __DIR__ . '/Application/Responses/DownloadResponse.php';
require __DIR__ . '/Application/Responses/ForwardingResponse.php';
require __DIR__ . '/Application/Responses/JsonResponse.php';
require __DIR__ . '/Application/Responses/RedirectingResponse.php';
require __DIR__ . '/Application/Responses/RenderResponse.php';
require __DIR__ . '/Application/Routers/CliRouter.php';
require __DIR__ . '/Application/Routers/MultiRouter.php';
require __DIR__ . '/Application/Routers/Route.php';
require __DIR__ . '/Application/Routers/SimpleRouter.php';
require __DIR__ . '/Debug/DebugPanel.php';
require __DIR__ . '/Application/RoutingDebugger.php';
require __DIR__ . '/Caching/Cache.php';
require __DIR__ . '/Caching/DummyStorage.php';
require __DIR__ . '/Caching/FileStorage.php';
require __DIR__ . '/Caching/MemcachedStorage.php';
require __DIR__ . '/Config/Config.php';
require __DIR__ . '/Config/ConfigAdapterIni.php';
require __DIR__ . '/Config/ConfigLoaderIni.php';
require __DIR__ . '/Debug/Debug.php';
require __DIR__ . '/Environment/Configurator.php';
require __DIR__ . '/Environment/Environment.php';
require __DIR__ . '/Environment/ServiceLocator.php';
require __DIR__ . '/Forms/Controls/FormControl.php';
require __DIR__ . '/Forms/Controls/Button.php';
require __DIR__ . '/Forms/Controls/Checkbox.php';
require __DIR__ . '/Forms/Controls/FileUpload.php';
require __DIR__ . '/Forms/Controls/HiddenField.php';
require __DIR__ . '/Forms/Controls/SubmitButton.php';
require __DIR__ . '/Forms/Controls/ImageButton.php';
require __DIR__ . '/Forms/Controls/SelectBox.php';
require __DIR__ . '/Forms/Controls/MultiSelectBox.php';
require __DIR__ . '/Forms/Controls/RadioList.php';
require __DIR__ . '/Forms/Controls/TextBase.php';
require __DIR__ . '/Forms/Controls/TextArea.php';
require __DIR__ . '/Forms/Controls/TextInput.php';
require __DIR__ . '/Forms/FormGroup.php';
require __DIR__ . '/Forms/Renderers/ConventionalRenderer.php';
require __DIR__ . '/Forms/Renderers/InstantClientScript.php';
require __DIR__ . '/Forms/Rule.php';
require __DIR__ . '/Forms/Rules.php';
require __DIR__ . '/Loaders/LimitedScope.php';
require __DIR__ . '/Loaders/RobotLoader.php';
require __DIR__ . '/Mail/MailMimePart.php';
require __DIR__ . '/Mail/Mail.php';
require __DIR__ . '/Mail/SendmailMailer.php';
require __DIR__ . '/Reflection/Annotation.php';
require __DIR__ . '/Reflection/AnnotationsParser.php';
require __DIR__ . '/Reflection/ExtensionReflection.php';
require __DIR__ . '/Reflection/FunctionReflection.php';
require __DIR__ . '/Reflection/MethodReflection.php';
require __DIR__ . '/Reflection/ParameterReflection.php';
require __DIR__ . '/Reflection/PropertyReflection.php';
require __DIR__ . '/Security/AuthenticationException.php';
require __DIR__ . '/Security/Identity.php';
require __DIR__ . '/Security/Permission.php';
require __DIR__ . '/Security/SimpleAuthenticator.php';
require __DIR__ . '/Templates/BaseTemplate.php';
require __DIR__ . '/Templates/Filters/CachingHelper.php';
require __DIR__ . '/Templates/Filters/LatteFilter.php';
require __DIR__ . '/Templates/Filters/LatteMacros.php';
require __DIR__ . '/Templates/Filters/SnippetHelper.php';
require __DIR__ . '/Templates/Filters/TemplateFilters.php';
require __DIR__ . '/Templates/Filters/TemplateHelpers.php';
require __DIR__ . '/Templates/Template.php';
require __DIR__ . '/Templates/TemplateCacheStorage.php';
require __DIR__ . '/Utils/ArrayList.php';
require __DIR__ . '/Utils/ArrayTools.php';
require __DIR__ . '/Utils/Callback.php';
require __DIR__ . '/Utils/DateTime53.php';
require __DIR__ . '/Utils/exceptions.php';
require __DIR__ . '/Utils/Framework.php';
require __DIR__ . '/Utils/Image.php';
require __DIR__ . '/Utils/ImageMagick.php';
require __DIR__ . '/Utils/Iterators/GenericRecursiveIterator.php';
require __DIR__ . '/Utils/Iterators/InstanceFilterIterator.php';
require __DIR__ . '/Utils/Iterators/SmartCachingIterator.php';
require __DIR__ . '/Utils/NeonParser.php';
require __DIR__ . '/Utils/ObjectMixin.php';
require __DIR__ . '/Utils/Paginator.php';
require __DIR__ . '/Utils/SafeStream.php';
require __DIR__ . '/Utils/String.php';
require __DIR__ . '/Utils/Tools.php';
require __DIR__ . '/Web/Html.php';
require __DIR__ . '/Web/HttpContext.php';
require __DIR__ . '/Web/HttpRequest.php';
require __DIR__ . '/Web/HttpResponse.php';
require __DIR__ . '/Web/HttpUploadedFile.php';
require __DIR__ . '/Web/Session.php';
require __DIR__ . '/Web/SessionNamespace.php';
require __DIR__ . '/Web/Uri.php';
require __DIR__ . '/Web/UriScript.php';
require __DIR__ . '/Web/User.php';
require __DIR__ . '/Utils/shortcuts.php';
