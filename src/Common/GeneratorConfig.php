<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorConfig
{
    /* Namespace variables */
    public $nsApp;
    public $nsRepository;
    public $nsModel;
    public $nsForms;
    public $nsPolicies;
    public $nsDataTables;
    public $nsModelExtend;

    public $nsApiController;
    public $nsApiRequest;

    public $nsRequest;
    public $nsRequestBase;
    public $nsController;
    public $nsBaseController;

    public $nsApiTests;
    public $nsRepositoryTests;
    public $nsTestTraits;
    public $nsTests;

    /* Path variables */
    public $pathRepository;
    public $pathModel;
    public $pathDataTables;

    public $pathApiController;
    public $pathApiRequest;
    public $pathApiRoutes;
    public $pathApiTests;
    public $pathApiTestTraits;

    public $pathController;
    public $pathForms;
    public $pathRequest;
    public $pathRoutes;
    public $pathViews;
    public $modelJsPath;

    public $pathPolicies;

    /* Model Names */
    public $mName;
    public $mPlural;
    public $mCamel;
    public $mCamelPlural;
    public $mSnake;
    public $mSnakePlural;
    public $mDashed;
    public $mDashedPlural;
    public $mSlash;
    public $mSlashPlural;
    public $mHuman;
    public $mHumanPlural;

    /* Generator Options */
    public $options;

    /* Prefixes */
    public $prefixes;

    /** @var CommandData */
    private $commandData;

    /* Command Options */
    public static $availableOptions = [
        'fieldsFile',
        'jsonFromGUI',
        'tableName',
        'fromTable',
        'ignoreFields',
        'save',
        'primary',
        'prefix',
        'paginate',
        'skip',
        'datatables',
        'views',
        'relations',
        'plural',
        'softDelete',
        'forceMigrate',
    ];

    public $tableName;

    /** @var string */
    protected $primaryName;

    /* Generator AddOns */
    public $addOns;

    public function init(CommandData &$commandData, $options = null)
    {
        if (!empty($options)) {
            self::$availableOptions = $options;
        }

        $this->mName = $commandData->modelName;

        $this->prepareAddOns();
        $this->prepareOptions($commandData);
        $this->prepareModelNames();
        $this->preparePrefixes($commandData);
        $this->loadPaths($commandData);
        $this->prepareTableName();
        $this->preparePrimaryName();
        $this->loadNamespaces($commandData);
        $commandData = $this->loadDynamicVariables($commandData);
        $this->commandData = &$commandData;

    }

    public function loadNamespaces(CommandData &$commandData)
    {
        $prefix = $this->prefixes['ns'];

        if (!empty($prefix)) {
            $prefix = '\\'.$prefix;
        }

        $realPrefix = $this->prefixes['real_ns'];


        $this->nsApp = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsApp = substr($this->nsApp, 0, strlen($this->nsApp) - 1);
        $this->nsRepository = $realPrefix . config('infyom.laravel_generator.namespace.repository', 'Repositories').$prefix;
        $this->nsModel = $realPrefix . config('infyom.laravel_generator.namespace.model', 'Models').$prefix;
        if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            $this->nsModel = $realPrefix . config('infyom.laravel_generator.namespace.model', 'Models');
        }
        $this->nsForms = $realPrefix . config('infyom.laravel_generator.namespace.forms', 'Forms');
        $this->nsPolicies = $realPrefix . config('infyom.laravel_generator.namespace.policies', 'Policies');
        $this->nsDataTables = $realPrefix . config('infyom.laravel_generator.namespace.datatables', 'DataTables').$prefix;
        $this->nsModelExtend = config(
            'infyom.laravel_generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );

        $this->nsApiController = $realPrefix . config(
            'infyom.laravel_generator.namespace.api_controller',
            'App\Http\Controllers\API'
        ).$prefix;
        $this->nsApiRequest = $realPrefix . config('infyom.laravel_generator.namespace.api_request', 'Http\Requests\API').$prefix;

        $this->nsRequest = $realPrefix . config('infyom.laravel_generator.namespace.request', 'Http\Requests').$prefix;
        $this->nsRequestBase = $realPrefix . config('infyom.laravel_generator.namespace.request', 'Http\Requests');
        $this->nsBaseController = $realPrefix . config('infyom.laravel_generator.namespace.controller', 'Http\Controllers');
		$this->nsController = $realPrefix . config('infyom.laravel_generator.namespace.controller', 'Http\Controllers');

        $this->nsTestTraits = config('infyom.laravel_generator.namespace.test_trait', 'Tests\Traits');
        $this->nsApiTests = config('infyom.laravel_generator.namespace.api_test', 'Tests\APIs');
        $this->nsRepositoryTests = config('infyom.laravel_generator.namespace.repository_test', 'Tests\Repositories');
        $this->nsTests = config('infyom.laravel_generator.namespace.tests', 'Tests');
    }

    public function loadPaths(CommandData &$commandData)
    {
        $prefix = $this->prefixes['path'];

        $realPrefix = $this->prefixes['real_path'];

        if (!empty($prefix)) {
            $prefix .= '/';
        }

        $viewPrefix = $this->prefixes['view'];

        if (!empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        $this->pathRepository = $realPrefix . config(
            'infyom.laravel_generator.path.repository',
            'Repositories/'
        ).$prefix;

        $this->pathModel = $realPrefix . config('infyom.laravel_generator.path.model', 'Models/').$prefix;
        if (config('infyom.laravel_generator.ignore_model_prefix', false)) {
            $this->pathModel = $realPrefix . config('infyom.laravel_generator.path.model', 'Models/');
        }

        $this->pathDataTables = $realPrefix . config('infyom.laravel_generator.path.datatables', 'DataTables/').$prefix;

        $this->pathApiController = $realPrefix . config(
            'infyom.laravel_generator.path.api_controller',
            'Http/Controllers/API/'
        ).$prefix;

        $this->pathApiRequest = $realPrefix . config(
            'infyom.laravel_generator.path.api_request',
            'Http/Requests/API/'
        ).$prefix;

	    if ($commandData->isModule) {
		    $this->pathApiRoutes = $realPrefix . config('infyom.laravel_generator.path.api_routes', 'routes/api.php');
	    } else {
		    $this->pathApiRoutes = base_path(config('infyom.laravel_generator.path.api_routes', 'routes/api.php'));
	    }

        $this->pathApiTests = config('infyom.laravel_generator.path.api_test', base_path('tests/APIs/'));

        $this->pathApiTestTraits = config('infyom.laravel_generator.path.test_trait', base_path('tests/Traits/'));

        $this->pathController = $realPrefix . config(
            'infyom.laravel_generator.path.controller',
            app_path('Http/Controllers/')
        ).$prefix;

        $this->pathForms = $realPrefix . config(
                'infyom.laravel_generator.path.forms',
                app_path('Forms/')
            ).$prefix;

        $this->pathPolicies = $realPrefix . config(
                'infyom.laravel_generator.path.policies',
                app_path('Policies/')
            ) . $prefix;

        $this->pathRequest = $realPrefix . config('infyom.laravel_generator.path.request', app_path('Http/Requests/')).$prefix;

        if ($commandData->isModule) {
	        $this->pathRoutes = $realPrefix . config('infyom.laravel_generator.path.routes', 'routes/web.php');
        } else {
	        $this->pathRoutes = base_path(config('infyom.laravel_generator.path.routes', 'routes/web.php'));
        }

        if ($commandData->isModule) {
	        $this->pathViews = $realPrefix . config(
			        'infyom.laravel_generator.path.views_module',
			        'views/')
		        .$this->mSnakePlural.'/';
        } else {
	        $this->pathViews = config(
			        'infyom.laravel_generator.path.views',
			        base_path('resources/views/')
		        ).$viewPrefix.$this->mSnakePlural.'/';
        }

        $this->modelJsPath = config(
                'infyom.laravel_generator.path.modelsJs',
                base_path('resources/assets/js/models/')
        );
    }

    public function loadDynamicVariables(CommandData &$commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORY$', $this->nsRepository);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_FORMS$', $this->nsForms);
        $commandData->addDynamicVariable('$NAMESPACE_POLICIES$', $this->nsPolicies);
        $commandData->addDynamicVariable('$NAMESPACE_DATATABLES$', $this->nsDataTables);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_API_CONTROLLER$', $this->nsApiController);
        $commandData->addDynamicVariable('$NAMESPACE_API_REQUEST$', $this->nsApiRequest);

        $commandData->addDynamicVariable('$NAMESPACE_BASE_CONTROLLER$', $this->nsBaseController);
        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST$', $this->nsRequest);
        $commandData->addDynamicVariable('$NAMESPACE_REQUEST_BASE$', $this->nsRequestBase);

        $commandData->addDynamicVariable('$NAMESPACE_API_TESTS$', $this->nsApiTests);
        $commandData->addDynamicVariable('$NAMESPACE_TEST_TRAITS$', $this->nsTestTraits);
        $commandData->addDynamicVariable('$NAMESPACE_REPOSITORIES_TESTS$', $this->nsRepositoryTests);
        $commandData->addDynamicVariable('$NAMESPACE_TESTS$', $this->nsTests);

        $commandData->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $commandData->addDynamicVariable('$TABLE_NAME_TITLE$', Str::studly($this->tableName));
        $commandData->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);
        $commandData->addDynamicVariable('$MODEL_NAME_DASHED$', $this->mDashed);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_DASHED$', $this->mDashedPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SLASH$', $this->mSlash);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SLASH$', $this->mSlashPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_HUMAN$', $this->mHuman);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_HUMAN$', $this->mHumanPlural);

        $commandData->addDynamicVariable('$PERMISSION_PREFIX$', $commandData->boundTo === EBoundTo::NONE ? 'admin' : 'project');

        if ($commandData->isModule) {
	        $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $commandData->modulePrefix.'::');
	        $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $commandData->modulePrefix).'/');
	        $commandData->addDynamicVariable('$RAW_ROUTE_PREFIX$', $commandData->modulePrefix);
        } else if (!empty($this->prefixes['route'])) {
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $this->prefixes['route'].'.');
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $this->prefixes['route']).'/');
            $commandData->addDynamicVariable('$RAW_ROUTE_PREFIX$', $this->prefixes['route']);
        } else {
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', '');
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', '');
        }

        if (!empty($this->prefixes['ns'])) {
            $commandData->addDynamicVariable('$PATH_PREFIX$', $this->prefixes['ns'].'\\');
        } else {
            $commandData->addDynamicVariable('$PATH_PREFIX$', '');
        }

	    if ($commandData->isModule) {
		    $commandData->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $commandData->modulePrefix).'::');
	    } elseif (!empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $this->prefixes['view']).'.');
        } else {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', '');
        }

        if (!empty($this->prefixes['public'])) {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', $this->prefixes['public']);
        } else {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', '');
        }

        $commandData->addDynamicVariable(
            '$API_PREFIX$',
            config('infyom.laravel_generator.api_prefix', 'api')
        );

        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('infyom.laravel_generator.api_version', 'v1')
        );

        return $commandData;
    }

    public function prepareTableName()
    {
        if ($this->getOption('tableName')) {
            $this->tableName = $this->getOption('tableName');
        } else {
            $this->tableName = $this->mSnakePlural;
        }
    }

    public function preparePrimaryName()
    {
        if ($this->getOption('primary')) {
            $this->primaryName = $this->getOption('primary');
        } else {
            $this->primaryName = 'id';
        }
    }

    public function prepareModelNames()
    {
        if ($this->getOption('plural')) {
            $this->mPlural = $this->getOption('plural');
        } else {
            $this->mPlural = Str::plural($this->mName);
        }
        $this->mCamel = Str::camel($this->mName);
        $this->mCamelPlural = Str::camel($this->mPlural);
        $this->mSnake = Str::snake($this->mName);
        $this->mSnakePlural = Str::snake($this->mPlural);
        $this->mDashed = str_replace('_', '-', Str::snake($this->mSnake));
        $this->mDashedPlural = str_replace('_', '-', Str::snake($this->mSnakePlural));
        $this->mSlash = str_replace('_', '/', Str::snake($this->mSnake));
        $this->mSlashPlural = str_replace('_', '/', Str::snake($this->mSnakePlural));
        $this->mHuman = Str::title(str_replace('_', ' ', Str::snake($this->mSnake)));
        $this->mHumanPlural = Str::title(str_replace('_', ' ', Str::snake($this->mSnakePlural)));
    }

    public function prepareOptions(CommandData &$commandData)
    {
        foreach (self::$availableOptions as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }

        if (isset($options['fromTable']) and $this->options['fromTable']) {
            if (!$this->options['tableName']) {
                $commandData->commandError('tableName required with fromTable option.');
                exit;
            }
        }

        $this->options['softDelete'] = config('infyom.laravel_generator.options.softDelete', false);
        if (!empty($this->options['skip'])) {
            $this->options['skip'] = array_map('trim', explode(',', $this->options['skip']));
        }

        if (!empty($this->options['datatables'])) {
            if (strtolower($this->options['datatables']) == 'true') {
                $this->addOns['datatables'] = true;
            } else {
                $this->addOns['datatables'] = false;
            }
        }
    }

    public function preparePrefixes(CommandData &$commandData)
    {
        $this->prefixes['route'] = explode('/', config('infyom.laravel_generator.prefixes.route', ''));
        $this->prefixes['path'] = explode('/', config('infyom.laravel_generator.prefixes.path', ''));
        $this->prefixes['view'] = explode('.', config('infyom.laravel_generator.prefixes.view', ''));
        $this->prefixes['public'] = explode('/', config('infyom.laravel_generator.prefixes.public', ''));

        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode(',', $this->getOption('prefix'));

            $this->prefixes['route'] = array_merge($this->prefixes['route'], $multiplePrefixes);
            $this->prefixes['path'] = array_merge($this->prefixes['path'], $multiplePrefixes);
            $this->prefixes['view'] = array_merge($this->prefixes['view'], $multiplePrefixes);
            $this->prefixes['public'] = array_merge($this->prefixes['public'], $multiplePrefixes);
        }

        $this->prefixes['route'] = array_diff($this->prefixes['route'], ['']);
        $this->prefixes['path'] = array_diff($this->prefixes['path'], ['']);
        $this->prefixes['view'] = array_diff($this->prefixes['view'], ['']);
        $this->prefixes['public'] = array_diff($this->prefixes['public'], ['']);

        $routePrefix = '';

        foreach ($this->prefixes['route'] as $singlePrefix) {
            $routePrefix .= Str::camel($singlePrefix).'.';
        }

        if (!empty($routePrefix)) {
            $routePrefix = substr($routePrefix, 0, strlen($routePrefix) - 1);
        }

        $this->prefixes['route'] = $routePrefix;

        $nsPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            $nsPrefix .= Str::title($singlePrefix).'\\';
        }

        if (!empty($nsPrefix)) {
            $nsPrefix = substr($nsPrefix, 0, strlen($nsPrefix) - 1);
        }

        $this->prefixes['ns'] = $nsPrefix;

        $pathPrefix = '';

        foreach ($this->prefixes['path'] as $singlePrefix) {
            $pathPrefix .= Str::title($singlePrefix).'/';
        }

        if (!empty($pathPrefix)) {
            $pathPrefix = substr($pathPrefix, 0, strlen($pathPrefix) - 1);
        }

        $this->prefixes['path'] = $pathPrefix;

        $viewPrefix = '';

        foreach ($this->prefixes['view'] as $singlePrefix) {
            $viewPrefix .= Str::camel($singlePrefix).'/';
        }

        if (!empty($viewPrefix)) {
            $viewPrefix = substr($viewPrefix, 0, strlen($viewPrefix) - 1);
        }

        $this->prefixes['view'] = $viewPrefix;

        $publicPrefix = '';

        foreach ($this->prefixes['public'] as $singlePrefix) {
            $publicPrefix .= Str::camel($singlePrefix).'/';
        }

        if (!empty($publicPrefix)) {
            $publicPrefix = substr($publicPrefix, 0, strlen($publicPrefix) - 1);
        }

        $this->prefixes['public'] = $publicPrefix;

        $this->prefixes['real_path'] = $commandData->realPathPrefix;
	    $this->prefixes['real_ns'] = $commandData->namespacePrefix;
    }

    public function overrideOptionsFromJsonFile($jsonData)
    {
        $options = self::$availableOptions;

        foreach ($options as $option) {
            if (isset($jsonData['options'][$option])) {
                $this->setOption($option, $jsonData['options'][$option]);
            }
        }

        // prepare prefixes than reload namespaces, paths and dynamic variables
        if (!empty($this->getOption('prefix'))) {
            $this->preparePrefixes();
            $this->loadPaths();
            $this->loadNamespaces($this->commandData);
            $this->loadDynamicVariables($this->commandData);
        }

        $addOns = ['swagger', 'tests', 'datatables'];

        foreach ($addOns as $addOn) {
            if (isset($jsonData['addOns'][$addOn])) {
                $this->addOns[$addOn] = $jsonData['addOns'][$addOn];
            }
        }
    }

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    public function prepareAddOns()
    {
        $this->addOns['swagger'] = config('infyom.laravel_generator.add_on.swagger', false);
        $this->addOns['tests'] = config('infyom.laravel_generator.add_on.tests', false);
        $this->addOns['datatables'] = config('infyom.laravel_generator.add_on.datatables', false);
        $this->addOns['menu.enabled'] = config('infyom.laravel_generator.add_on.menu.enabled', false);
        $this->addOns['menu.menu_file'] = config('infyom.laravel_generator.add_on.menu.menu_file', 'layouts.menu');
    }
}
