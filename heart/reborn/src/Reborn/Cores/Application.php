<?php

namespace Reborn\Cores;

use Reborn\Http\Request;
use Reborn\Http\Response;
use Reborn\Http\Uri;
use Reborn\Routing\Router;
use Reborn\Routing\RouteCollection;
use Reborn\MVC\View\ViewManager;
use Reborn\Config\Config;
use Reborn\Filesystem\File;
use Reborn\Widget\Widget;
use Reborn\Util\Security;
use Reborn\Translate\Loader\PHPFileLoader;
use Reborn\Event\EventManager as Event;
use Reborn\Cache\CacheManager as Cache;
use Reborn\Connector\Log\LogManager as Log;
use Reborn\Connector\DB\DBManager as DB;
use Reborn\Module\ModuleManager as Module;
use Reborn\Exception\RbException;
use Reborn\Exception\HttpNotFoundException;
use Reborn\Exception\TokenNotMatchException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Session\Session as Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

/**
 * Main Application Class for Reborn.
 * This class is extend the Illuminate\Container (IOC Container).
 *
 * @package Reborn\Cores
 * @author Myanmar Links Professional Web Development Team
 **/
class Application extends \Illuminate\Container\Container
{

    /**
     * Marking for application started or not
     *
     * @var bool
     **/
    protected $started = false;

    /**
     * Constructor Method.
     * Create new object for Reborn Application
     *
     * @return void
     **/
    public function __construct()
    {
        $this['error_handler'] = new ErrorHandler($this);

        $this['request'] = Request::createFromGlobals();

        // Enable Http Method Override for (_method)
        Request::enableHttpMethodParameterOverride();

        $this['route_collection'] =  $this->share(function ($this) {
            return new RouteCollection();
        });

        $this['router'] =  $this->share(function ($this) {
            return new Router($this);
        });

        $this['log'] = $this->share(function () {
            return new Log();
        });

        $this['view_manager'] = $this->share( function() {
            return new ViewManager();
        });

        $this['view'] = $this->share( function($app) {
            return $app['view_manager']->getView();
        });

        $this['theme'] = $this->share( function($app) {
            return $app['view_manager']->getTheme();
        });

        $this['template'] = $this->share( function($app) {
            return $app['view_manager']->getTemplate();
        });

        $this['session'] = $this->share( function() {
            $lifetime = Config::get('app.session_lifetime', 30) * 60;
            $options = array('gc_maxlifetime' => $lifetime);

            return new Session(new NativeSessionStorage($options));
        });

        $this['cache'] = $this->share( function() {
            return new Cache();
        });

        $this['widget'] = $this->share( function() {
            return new Widget();
        });

        $this['profiler'] = $this->share( function() {
            return new Profiler();
        });

        $this['translate_loader'] = $this->share( function($app) {
            return new PHPFileLoader($app);
        });

        // Set the Application Object into the Registry
        Registry::set('app', $this);
    }

    /**
     * Set the Reborn CMS Environment.
     * Reborn accept 3 type of environment
     * <code>
     *  (1) - dev (For Development Stage)
     *  (2) - test (For Testing Stage)
     *  (3) - production (For Production Stage)
     * </code>
     *
     * @param string $env
     * @return void
     **/
    public function setAppEnvironment($env)
    {
        $accept_envs = array('dev', 'test', 'production');
        $this['env'] = in_array($env, $accept_envs) ? $env : 'production';
    }

    /**
     * Check Application Environment is "dev".
     *
     * @return boolean
     **/
    public function runInDevelopment()
    {
        return ($this['env'] === 'dev');
    }

    /**
     * Check Application Environment is "production".
     *
     * @return boolean
     **/
    public function runInProduction()
    {
        return ($this['env'] === 'production');
    }

    /**
     * Check Application Environment is "test".
     *
     * @return boolean
     **/
    public function runInTesting()
    {
        return ($this['env'] === 'test');
    }

    /**
     * Check Reborn is already installed
     *
     * @return boolean
     **/
    public function installed()
    {
        if(! File::is(APP.'config'.DS.'db.php'))
        {
            if (!\Reborn\Filesystem\Directory::is(BASE.'installer')) {
                throw new RbException("Can't find db.php at config folder");
            }

            return false;
        }

        return true;
    }

    /**
     * Installation for Reborn CMS
     *
     * @return void
     **/
    public function install()
    {
        if(File::is(APP.'config'.DS.'db.php')) {
            throw new RbException("Reborn CMS is already installed!");
        }

        require BASE.'installer'.DS.'Installer.php';

        \Installer::init();

        \Installer::start();

        exit;
    }

    /**
     * Start the application
     *
     * @return void
     **/
    public function start()
    {
        if ($this->started) {
            throw new RbException("Reborn CMS Application is already started!");
        }

        try {
            // Set Exception and Error Handler
            $this->setErrorHandler();

            // call the appInitialize method
            $this->appInitialize();

            // Call the Event Name App Start
            Event::call('reborn.app.starting');

            // Set Timezone for Application
            $this->setTimezone(\Setting::get('timezone'));

            // Check the Site is Maintainance Stage or not
            // If site is maintainance stage, give the maintain page and exit
            $this->siteIsMaintain();

            $response = $this['router']->dispatch();

            $this->started = true;

            if(!$response instanceof SymfonyResponse) {
                $response = new Response($response);
            }

            // Send response to the end method
            $this->end($response);
        } catch(TokenNotMatchException $e) {
            // For CSRF Fail
            \Translate::load('global');
            \Flash::error(t('global.csrf_fail'));

            $basepath = $this['request']->getBasePath();
            $redirect_url = str_replace($basepath, '', \Input::server('REDIRECT_URL'));

            return \Redirect::to($redirect_url);
        }
    }

    /**
     * Start the Initialize method from require classes.
     * But this method is call from application start method only.
     * Don't call more than once.
     *
     */
    public function appInitialize()
    {
        if ($this->started) {
            return true;
        }

        // Start the Session
        if (isset($this['session'])) {
            $this['session']->start();

            // Check and Make CSRF Token
            $csrf = Config::get('app.security.csrf_key');

            if ( ! $this['session']->has($csrf)) {
                \Security::makeCSRFToken();
            }
        }

        // Start the Profiler
        if (('dev' == $this['env']) and Config::get('dev.profiler')) {
            $this['profiler']->start();
        }

        // Start the Database initialize
        DB::initialize();

        // Start the Event initialize
        Event::initialize();

        // Start the Setting initialize
        Setting::initialize();

        // Start the Uri initialize
        Uri::initialize($this->request);

        // Start the Module initialize
        Module::initialize();

        // Start the Widget initialize
        $this['widget']->initialize();
    }

    /**
     * End point of application
     *
     * @return void
     **/
    public function end(SymfonyResponse $response)
    {
        // Stop the Profiler
        if (('dev' == $this['env']) and Config::get('dev.profiler')) {
            $this['profiler']->stop();

            if (!$response instanceof JsonResponse ||
                !$response instanceof StreamedResponse) {
                // Call the Event Name App Profiling
                if (Event::has('reborn.app.profiling')) {
                    $result = Event::call('reborn.app.profiling', $response->getContent());
                    $response->setContent($result[0]);
                }
            }
        }

        // Call the Event Name App Ending
        Event::call('reborn.app.ending', $response);

        return $response->send();
    }

    /**
     * Set Locale for application.
     * Default locale is en
     *
     * @param string $locale
     * @return void
     **/
    public function setLocale($locale = 'en')
    {
        $this['locale'] = $locale;

        \Translate::setLocale($locale);

        Event::call('reborn.app.locale_change', array($locale));
    }

    /**
     * Set Timezone for application.
     * Default timezone is UTC
     *
     * @param string $tz
     * @return void
     **/
    public function setTimezone($tz = 'UTC')
    {
        date_default_timezone_set($tz);
    }

    /**
     * Set the Error Handler for Reborn CMS
     *
     * @return void
     */
    public function setErrorHandler()
    {
        $this['error_handler']->register();
    }

    /**
     * Check the site is maintainance stage or not.
     * If site is maintainance stage, response the maintainance mode.
     *
     * @return void
     **/
    protected function siteIsMaintain()
    {
        $maintain = Setting::get('site_is_maintain');

        if (! $maintain) {
            return false;
        } else {
            $theme = Setting::get('public_theme');
            $file = THEMES.$theme.DS.'views'.DS.'layout'.DS.'maintain'.EXT;
            if (file_exists($file)) {
                $content = File::getContent($file);
            } else {
               $content = File::getContent(APP.'views'.DS.'maintain.php');
            }

            $response = new Response($content, 503);
            $this->end($response);

            exit(1);
        }
    }

    /**
     * Magic setter method
     *
     * @param string $key
     * @param mixed $value
     * @return void
     **/
    public function __set($key, $value)
    {
        $this[$key] = $value;
    }

    /**
     * Magic getter method
     *
     * @param string $key
     * @return mixed
     **/
    public function __get($key)
    {
        return isset($this[$key]) ? $this[$key] : null;
    }

    /**
     * Inject CSRF Token
     *
     * @param \Reborn\Http\Response $response Response Object
     * @return string
     **/
    public function injectCSRFToken($response)
    {
        $token = Security::CSRField();

        preg_match('/(value\s*=\s*"(.*)")/', $token, $m);

        $meta = \Html::meta('csrf-token', $m[2]);

        $body = $response->getContent();

        preg_match('/(<\/(head|HEAD)>)/', $body, $m);

        $body = preg_replace('/(<\/(head|HEAD)>)/', $meta."\n".'$0', $body);

        $pattern = '/(<(form|FORM)[^>]*(method|METHOD)="(post|POST)"[^>]*>)/';

        preg_match_all($pattern, $body, $matches, PREG_SET_ORDER);

        if (is_array($matches)) {
            foreach ($matches as $match) {
                if (false == strpos($match[0], 'nocsrf')) {
                    $body = str_replace($match[0], $match[0]."\n\t" .$token, $body);
                }
            }
        }

        $response->setContent($body);

        return $response;
    }

} // END class Application
