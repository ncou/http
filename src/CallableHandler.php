<?php

declare(strict_types=1);

namespace Chiron\Http;

use Chiron\Container\ContainerAwareInterface;
use Chiron\Container\ContainerAwareTrait;
use Chiron\Http\Exception\Client\BadRequestHttpException;
use Chiron\Http\Exception\MissingResponseException;
use Chiron\Injector\Exception\InjectorException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

// TODO : transformer les exceptions des controllers sous forme de http exception :
//https://github.com/spiral/router/blob/8ebf43cddf4e71802ba19e470bb7d26aed2d482f/src/CoreHandler.php#L186
//https://github.com/cakephp/cakephp/blob/f43b3f58680ae869fb2e9fa56e65406cd1250702/src/Error/Renderer/WebExceptionRenderer.php#L112

// TODO : Il se peut qu'on utilise des objets "Reference::class" dans les paramétres lors de l'appel du invoke, il faudrait surement résoudre ces valeurs via un Reference->resolve($container), car sinon on risque d'avoir une incompatibilité du parameterNamedType lorsqu'on va vouloir appeller le callback final !!!

// TODO : lors du invoke on passe tous les attributs stockés dans la request histoire de résoudre les paramétres lors du invoke, mais on devrait seulement utiliser un unique attribut qui stock les paramétres comme c'est fait dans cakephp via l'attribut "pass", cela permettra aussi de caster correctement ces paramétres de string vers le bon format !!!!
//https://github.com/cakephp/cakephp/blob/289a8cca66740c23af3d9ee7372b2c6f014129bc/src/Controller/ControllerFactory.php#L138
//https://github.com/cakephp/cakephp/blob/289a8cca66740c23af3d9ee7372b2c6f014129bc/src/Controller/ControllerFactory.php#L264
//https://github.com/cakephp/cakephp/blob/289a8cca66740c23af3d9ee7372b2c6f014129bc/src/Controller/ControllerFactory.php#L264
//https://github.com/cakephp/cakephp/blob/289a8cca66740c23af3d9ee7372b2c6f014129bc/src/Controller/ControllerFactory.php#L507
//https://github.com/cakephp/cakephp/blob/29afeef7d45c2d54be7b69aa378af31574c61475/tests/test_app/TestApp/Controller/DependenciesController.php#L46
//https://github.com/cakephp/cakephp/blob/4981fcd4de9941174a9e3f4430278f71d2eb81b9/src/Routing/Route/Route.php#L486

// TODO : attention dans la request on a seulement des attributs string qui ont été passé dans l'url, on devra donc les "caster" au bon format si on les passe en paramétre de la fonction du controller !!!!
//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/src/Controller/ControllerFactory.php#L203
//https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/src/Controller/ControllerFactory.php#L255

// TODO : PHP8 Attributes. Utiliser un attribut genre #[JsonOuput] ou #[HtmlOutput] pour forcer la conversion de la response au format texte ou array en ResponsaInterface. Pour ce faire il faudra ajouter un trigger Event($request, $controller, $response) lorsque la response n'est pas une instanceof ResponseInterface, et ajouter un listener qui se chargera de récupérer les attributs attachés au callable via une reflection, et ensuite de formater la réponse à retourner.
//https://github.com/windwalker-io/core/blob/master/src/Core/Attributes/Json.php
//https://github.com/windwalker-io/core/blob/master/src/Core/Attributes/JsonApi.php
//https://github.com/windwalker-io/core/tree/master/src/Core/Attributes

/*
// TODO : code à utiliser dans le if lorsqu'on n'a pas recu de réponse pour aller chercher les attributs de la méthode utilisée en callable !!!!
$callable = \Closure::fromCallable($controller);
$reflection = new \ReflectionFunction($callable);
die(var_dump($reflection->getAttributes()));
*/


// TODO : eventuellement forcer la création d'une response. Eventuellement permettre à un EVENT / EVENTDISPATCHER de gérer ce cas là !!!!
//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Http/src/CallableHandler.php#L66
//https://github.com/symfony/http-kernel/blob/409eba7fa9eccaeb419bd2f35edc9c81fb56323f/HttpKernel.php#L162
//https://github.com/symfony/http-kernel/blob/0996d531074e0fb3f60b2af0a0d758c03fc47396/Tests/HttpKernelTest.php#L228

// TODO : retourner plutot une HandlerException ????  https://github.com/zendframework/zend-stratigility/blob/master/src/Exception/MissingResponseException.php
// https://github.com/symfony/http-kernel/blob/409eba7fa9eccaeb419bd2f35edc9c81fb56323f/Exception/ControllerDoesNotReturnResponseException.php
// https://github.com/symfony/http-kernel/blob/409eba7fa9eccaeb419bd2f35edc9c81fb56323f/DataCollector/RequestDataCollector.php#L440

// TODO : créer un package avec un faux ResponseInterface qui stock en raw la valeur de retour et qui via un middleware formatera la réponse.
// https://github.com/yiisoft/data-response/blob/45937cb06c1bd057da6c6a18d65fab3182e87b55/src/DataResponse.php#L61
// https://github.com/yiisoft/data-response/blob/master/src/Middleware/FormatDataResponse.php

// TODO : gérer le cas ou la valeur de retour n'est pas un objet de type ResponseInterface, on pourra wrapper le résultat (tableau en json par exemple) pour retourner un objet Resposne.
// https://github.com/top-think/framework/blob/4de6f58c5e12a1ca80c788887b5208a6705f85d3/src/think/route/Dispatch.php#L93
// https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Router/src/CoreHandler.php#L144
// https://github.com/middlewares/utils/blob/a9ef1e5365020ead0ae343b95602bd877a9bf852/src/CallableHandler.php#L68

// TODO : mieux gérer les exceptions dans le cas ou il y a une erreur lors du $injector->call()    exemple :   https://github.com/spiral/framework/blob/e63b9218501ce882e661acac284b7167b79da30a/src/Hmvc/src/AbstractCore.php#L67       +         https://github.com/spiral/framework/blob/master/src/Router/src/CoreHandler.php#L199

//https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Http/src/CallableHandler.php#L66

/**
 * Callback wraps arbitrary PHP callback into object matching [[MiddlewareInterface]].
 * Usage example:
 *
 * ```php
 * $middleware = new CallbackMiddleware(function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
 *     if ($request->getParams() === []) {
 *         return new Response();
 *     }
 *     return $handler->handle($request);
 * });
 * $response = $middleware->process(Yii::$app->getRequest(), $handler);
 * ```
 *
 * @see MiddlewareInterface
 */
// TODO : corriger le phpdoc de la classe !!!! Et indiquer qu'elle doit rester en classe NON FINAL !!!!
class CallableHandler implements RequestHandlerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var callable|array|string a PHP callback matching signature of [RequestHandlerInterface->handle(ServerRequestInterface $request)]]. // TODO : non c'est faux ce n'est pas obligatoirement une signature de type requesthandler !!!!
     */
    protected $callable;

    /**
     * @param callable|array|string $callable A PHP callback matching signature of [RequestHandlerInterface->handle(ServerRequestInterface $request)]]. // TODO : non c'est faux ce n'est pas obligatoirement une signature de type requesthandler !!!!
     */
    // TODO : renommer la variable $callable en $callback ????
    public function __construct($callable)
    {
        // TODO : ajouter une vérification si le callable a le bon format ? par exemple si c'est un is_callable ou is_object ou is_string ou is_array (éventeullement vérifier que la tableau a une taille de 2 éléments et que le 1er élément est une string ou un objet et que le 2eme élement est une string) ???? ou alors indiquer qu'une NotCallableException sera levée par le package Invoker lors du call !!!!
        $this->callable = $callable;
    }

    // TODO : indiquer dans la phpDoc tous les typehints possibles pour $callable !!!
    // TODO : indiquer qu'une exception est levée si le container n'est pas défini par la méthode getContainer() !!!
    //https://github.com/cakephp/cakephp/blob/32e3c532fea8abe2db8b697f07dfddf4dfc134ca/src/Controller/ControllerFactory.php#L124
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO : faire un $this->hasContainer et si le résultat est false dans ce cas lever une une HandlerException en indiquant que le container doit être setter pour executer le handler ????
        /*
        if (! $this->hasContainer()) {
            throw new RouteException('Unable to configure route pipeline without associated container');
            // throw new MissingContainerException('Container is missing, use setContainer() method to set it.');
        }*/


        // TRY/CATCH
        //https://github.com/spiral/framework/blob/d17c175e85165456fbd2d841c8e81165e371675c/src/Router/src/CoreHandler.php#L200
        // TODO : améliorer le code pour permettre de passer en paramétre l'exception précédente ($e) à cette http exception
        // TODO : il faudrait surement lever une exception NotFoundHttpException dans le cas ou la mathode du callable n'existe pas dans la classe du callable, mais il faut pour cela séparer ce type d'exception dans la classe Injector pour ne pas remonter systématiquement une Exception InvocationException qui gére à la fois les probléme de callable qui n'existent pas et les callables qui n'ont pas le bon nombre d'arguments en paramétres.
        //https://github.com/symfony/http-kernel/blob/6.0/Exception/BadRequestHttpException.php

        // TODO : EVENT pour remplacer la response !!!!
        //https://github.com/symfony/http-kernel/blob/409eba7fa9eccaeb419bd2f35edc9c81fb56323f/HttpKernel.php#L162

        $injector = $this->getContainer()->injector();

        $outputLevel = ob_get_level(); // TODO : renommer la variable en $level ???
        ob_start();

        $output = '';

        // TODO : indiquer qu'une exception du type NotCallableException peut être levée si la callable n'est pas au bon format !!!! <= en fait ch'est une exception de type InjectorException
        try {
            // Resolve the callback in a valid php callable.
            $controller = $injector->resolve($this->callable);
            // Use the request attributes as an array to help during the callable parameters resolutions.
            $response = $injector->invoke($controller, $request->getAttributes()); // TODO : au lieu de passer les requestAttributes, il faudrait plutot récupérer l'objet CurrentRoute::class et passer les MatchedParameters + les route valeur par defaults dans les paramétres de l'appel à l'injector !!!
        } catch (InjectorException $e) {
            ob_get_clean(); // TODO : c'est pas plutot des ob_end_clean() ????
            throw new BadRequestHttpException($e->getMessage()); // TODO : je pense qu'il faudrait simplement laisser l'exception se propager et ne pas la transformer en BadRequestHttpException !!!! Eventuellement prévoir un mapping des exception du controller: exemple : https://github.com/spiral/router/blob/8ebf43cddf4e71802ba19e470bb7d26aed2d482f/src/CoreHandler.php#L186   ou    https://github.com/spiral/hmvc/blob/2a626ad6d96026827ecb728ba8ab54e3d8333361/src/AbstractCore.php#L43          Eventuellement créer une ControllerException pour gérer ce type de cas d'errreurs !!!!
        } catch (\Throwable $e) {
            // TODO : vérifier si ce catch sert à quelque chose car en cas d'exception on passera dans le finally qui va faire un clean() et ensuite on propagera l'exception, donc ca devrait être inutile de faire ce catch Throwable !!!
            ob_get_clean(); // TODO : c'est pas plutot des ob_end_clean() ????
            throw $e;
        } finally {
            while (ob_get_level() > $outputLevel) { // while (ob_get_level() > $outputLevel + 1) {
                $output = ob_get_clean() . $output;
            }
        }

        // Always glue buffered output.
        if ($response instanceof ResponseInterface) {
            if ($output !== '' && $response->getBody()->isWritable()) {
                $response->getBody()->write($output);
            }
        }

        // Throw an exception if the return type is not a valid response.
        if (! $response instanceof ResponseInterface) {
            $message = sprintf('The controller must return a "%s" object but it returned %s.',
                ResponseInterface::class,
                $this->varToString($response)
            );

            // The user may have forgotten to return something!
            if ($response === null) {
                $message .= ' Did you forget to add a return statement somewhere in your controller?';
            }

            throw new MissingResponseException($message, $controller, __FILE__, __LINE__ - 33);
        }

        return $response;
    }

    /**
     * Returns a human-readable string for the specified variable.
     */
    private function varToString($var): string
    {
        if (\is_object($var)) {
            return sprintf('an object of type %s', \get_class($var));
        }

        if (\is_array($var)) {
            $a = [];
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => ...', $k);
            }

            return sprintf('an array ([%s])', mb_substr(implode(', ', $a), 0, 255));
        }

        if (\is_resource($var)) {
            return sprintf('a resource (%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'a boolean value (false)';
        }

        if (true === $var) {
            return 'a boolean value (true)';
        }

        if (\is_string($var)) {
            return sprintf('a string ("%s%s")', mb_substr($var, 0, 255), mb_strlen($var) > 255 ? '...' : '');
        }

        if (is_numeric($var)) {
            return sprintf('a number (%s)', (string) $var);
        }

        return (string) $var;
    }
}
