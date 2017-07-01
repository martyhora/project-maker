<?php

namespace CrudMaker;

use Transform\ITransformation;

require_once __DIR__ . '/../ITransformation.php';

require_once __DIR__ . '/Tools.php';

class CrudMaker implements ITransformation
{
    const BASE_CRUD = 'crud';

    const FORM_FIELDS_ANNOTATION          = "/** @formFields */";
    const LIST_FIELDS_ANNOTATION          = "/** @listFields */";
    const LIST_DEFINITIONS_ANNOTATION     = "{* @additionalRowDefinions *}";
    const FILTER_FORM_FIELDS_ANNOTATION   = "/** @filterFormFields */";
    const LIKE_FILTER_COLUMNS_ANNOTATION  = "/** @likeFilterColumns */";
    const EQUAL_FILTER_COLUMNS_ANNOTATION = "/** @equalFilterColumns */";

    const DEFAULT_OPTION_CAPTION_COLUMN = 'title';

    const DATE_CREATED_FIELD = 'date_created';

    private $buildPath = './';
    private $transformPath = './';

    private $projectName = '';

    /**
     * Konfigurace CRUDu
     *
     * $config = [
     *     'title' => '',
     * ];
     *
     * @var array
     */
    protected $config = [];

    /**
     * Konfigurace CRUDů
     * @var array
     */
    protected $configs = [];

    public function setConfigs($configs)
    {
        $this->configs = $configs;
    }

    public function getTitle()
    {
        return $this->config['title'];
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
    }

    private function createFolders()
    {
        @mkdir($this->buildPath . '/app');
        @mkdir($this->buildPath . '/app/config');
        @mkdir($this->buildPath . '/app/presenters');
        @mkdir($this->buildPath . '/app/presenters/templates');
        @mkdir($this->buildPath . '/app/presenters/templates/' . ucfirst($this->getTitle()));
        @mkdir($this->buildPath . '/app/model');
        @mkdir($this->buildPath . '/app/component');
        @mkdir($this->buildPath . '/app/component/' . ucfirst($this->getTitle()) . 'Form');
        @mkdir($this->buildPath . '/app/component/' . ucfirst($this->getTitle()) . 'List');
        @mkdir($this->buildPath . '/sql');
    }

    private function makePresenter()
    {
        $this->processFolder(__DIR__ . '/app/presenters/');
    }

    private function makeModel()
    {
        $modelPath = __DIR__ . '/app/model/';

        $this->processFolder($modelPath);

        $tools = new Tools;

        $tableNameString = 'protected $tableName = \'' . $this->getTitle() . '\'';
        $newTableNameString = str_replace($this->getTitle(), $tools->fromCamelCase($this->getTitle()), $tableNameString);

        $filename = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), str_replace(__DIR__ . '/app', $this->buildPath . '/app', $modelPath . ucfirst($this->getTitle()) . '.php'));

        $content = file_get_contents($filename);

        $content = str_replace($tableNameString, $newTableNameString, $content);

        $likeFilterColumns  = [];
        $equalFilterColumns = [];

        foreach ($this->config['fields'] as $field => $params)
        {
            if (!empty($params['disableList']))
            {
                continue;
            }

            switch ($params['type']) {
                case 'select_db':
                case 'radio_db':
                    $equalFilterColumns[] = "'" . $field . "'";
                    break;

                case 'text':
                case 'textarea':
                    $likeFilterColumns[] = "'" . $field . "'";
                    break;
            }

            $listField = '$grid->addColumn(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\')->enableSort();';

            $listFields[] = $listField;
        }

        $content = str_replace(self::LIKE_FILTER_COLUMNS_ANNOTATION, implode(", ", $likeFilterColumns), $content);
        $content = str_replace(self::EQUAL_FILTER_COLUMNS_ANNOTATION, implode(", ", $equalFilterColumns), $content);

        file_put_contents($filename, $content);
    }

    private function getRequiredMethodCall($params)
    {
        if (empty($params['required']))
        {
            return "";
        }

        return "->addRule(Form::FILLED, \"Pole '{$params['caption']}' je povinné.\")";
    }

    private function makeTemplates()
    {
        $this->processFolder(__DIR__ . '/app/presenters/templates/' . ucfirst(self::BASE_CRUD));
    }

    private function getTableByField($field)
    {
        return str_replace('_id', '', $field);
    }

    private function getModelNameFromSelectDbField($field)
    {
        $tools = new Tools;

        return $tools->underscoreToCamelCase($this->getTableByField($field));
    }

    private function getOptionsForSelectBoxLine($field, $params)
    {
        $modelName = $this->getModelNameFromSelectDbField($field);

        $optionCaption = !empty($params['caption_column']) ? $params['caption_column'] : self::DEFAULT_OPTION_CAPTION_COLUMN;

        $options = '          $options = $this->' . $modelName . 'Repository->findAll()->order(\'' . $optionCaption . '\')->fetchPairs(\'id\', \'' . $optionCaption . '\');';

        return $options;
    }

    private function getSelectDbField($field, $params)
    {
        $formField = $this->getOptionsForSelectBoxLine($field, $params) . '

        $form->addSelect(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\', $options)->setPrompt(\'- Vyberte -\')->setAttribute(\'class\', \'form-control\')';

        $formField .= $this->getRequiredMethodCall($params) . ";";

        return $formField;
    }

    private function getRadioDbField($field, $params)
    {
        $formField = $this->getOptionsForSelectBoxLine($field, $params) . '

        $form->addRadioList(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\', $options)';

        $formField .= $this->getRequiredMethodCall($params) . ";";

        return $formField;
    }

    private function getTextField($field, $params)
    {
        $formField = '          $form->addText(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\')->setAttribute(\'class\', \'form-control\')';

        $formField .= $this->getRequiredMethodCall($params) . ";";

        return $formField;
    }

    private function makeForm()
    {
        $formFolder = __DIR__ . '/app/component/' . ucfirst(self::BASE_CRUD) . 'Form';

        $this->processFolder($formFolder);

        if (empty($this->config['fields']))
        {
            return;
        }

        $formFields = [];

        $tools = new Tools;

        $dependencyModels = [];

        foreach ($this->config['fields'] as $field => $params)
        {
            if (!empty($params['disableForm']))
            {
                continue;
            }

            $formField = "";

            switch ($params['type']) {
                case 'text':
                    $formField = $this->getTextField($field, $params);
                    break;

                case 'textarea':
                    $formField = '          $form->addTextarea(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\')->setAttribute(\'class\', \'form-control\');';
                    break;

                case 'select_db':
                case 'radio_db':
                    $modelName = $this->getModelNameFromSelectDbField($field);

                    $formField = 'select_db' == $params['type'] ? $this->getSelectDbField($field, $params) : $this->getRadioDbField($field, $params);

                    $dependencyModels[] = $modelName;
                    break;
            }


            $formFields[] = $formField;
        }

        $formFile = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), str_replace(__DIR__ . '/app', $this->buildPath . '/app', $formFolder . '/' . ucfirst($this->getTitle()) . 'Form.php'));

        $content = file_get_contents($formFile);

        $content = str_replace(self::FORM_FIELDS_ANNOTATION, implode(PHP_EOL . PHP_EOL, $formFields), $content);

        $content = $this->injectDependencyModels($dependencyModels, $content);

        file_put_contents($formFile, $content);
    }

    private function injectDependencyModels(array $dependencyModels, $content)
    {
        if (!$dependencyModels)
        {
            return $content;
        }

        $dependencyDeclaration = [];
        $dependencyHint        = [];
        $dependencyAssertion   = [];

          foreach ($dependencyModels as $model)
            {
                $modelClass = 'Model\\' . ucfirst($model);
                $variableName = '$' . $model . 'Repository';

                $dependencyDeclaration[] = '/** @var ' . $modelClass . ' */
    protected ' . $variableName . ';';

                $dependencyHint[]        = $modelClass . ' ' . $variableName;
                $dependencyAssertion[] = '$this->' . str_replace('$', '', $variableName) . ' = ' . $variableName . ';';
            }

        $content = str_replace('/** @additionalComponentDependencyDeclaration */', implode(PHP_EOL . PHP_EOL, $dependencyDeclaration), $content);
        $content = str_replace('/** @additionalComponentDependencyHint */', ', ' . implode(', ', $dependencyHint), $content);
        $content = str_replace('/** @additionalComponentDependencyAssertion */', implode(PHP_EOL . PHP_EOL, $dependencyAssertion), $content);

        return $content;
    }

    private function makeList()
    {
        $tools = new Tools;

        $listFolder = __DIR__ . '/app/component/' . ucfirst(self::BASE_CRUD) . 'List';

        $this->processFolder($listFolder);

        if (empty($this->config['fields']))
        {
            return;
        }

        $listFields = [];

        $additionalDefinitions = [];

        $dependencyModels = [];

        foreach ($this->config['fields'] as $field => $params)
        {
            if (!empty($params['disableList']))
            {
                continue;
            }

            $listField = "";

            switch ($params['type']) {
                case 'select_db':
                case 'radio_db':
                    $optionCaption = !empty($params['caption_column']) ? $params['caption_column'] : self::DEFAULT_OPTION_CAPTION_COLUMN;

                    $modelName = $this->getTableByField($field);

                    $additionalDefinitions[] = '{define col-' . $field . '}
    <td>{$row->'. $modelName .'->' . $optionCaption . '}</td>
{/define}';

                    $filterFormFields[] = $this->getSelectDbField($field, $params);

                    $dependencyModels[] = $this->getModelNameFromSelectDbField($field);
                    break;

                case 'text':
                case 'textarea':
                    $filterFormFields[] = $this->getTextField($field, $params);
                    break;
            }

            $listField = '$grid->addColumn(\'' . $field . '\', \'' . ucfirst($params['caption']) . '\')->enableSort();';

            $listFields[] = $listField;
        }

        $listFile = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), str_replace(__DIR__ . '/app', $this->buildPath . '/app', $listFolder . '/' . ucfirst($this->getTitle()) . 'List.php'));

        $content = file_get_contents($listFile);

        $content = str_replace(self::LIST_FIELDS_ANNOTATION, implode(PHP_EOL . PHP_EOL, $listFields), $content);

        $content = str_replace(self::FILTER_FORM_FIELDS_ANNOTATION, implode(PHP_EOL . PHP_EOL, $filterFormFields), $content);

        $content = $this->injectDependencyModels($dependencyModels, $content);

        file_put_contents($listFile, $content);

        $listFile = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), str_replace(__DIR__ . '/app', $this->buildPath . '/app', $listFolder . '/' . ucfirst($this->getTitle()) . 'List_grid.latte'));

        $content = file_get_contents($listFile);

        $content = str_replace(self::LIST_DEFINITIONS_ANNOTATION, implode(PHP_EOL . PHP_EOL, $additionalDefinitions), $content);

        file_put_contents($listFile, $content);
    }

    private function processFolder($path)
    {
        $this->createFolders();

        foreach (scandir($path) as $file)
        {
            if (in_array($file, ['.', '..']) || is_dir($path . '/' . $file))
            {
                continue;
            }

            $content = file_get_contents($path . '/' . $file);

            $content = $this->transFormContent($content);

            $newPath = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), str_replace(__DIR__ . '/app', $this->buildPath . '/app', $path . '/' . $file));

            file_put_contents($newPath, $content);
        }
    }

    private function transFormContent($originalContent)
    {
        $content = str_replace(ucfirst(self::BASE_CRUD), ucfirst($this->getTitle()), $originalContent);
        $content = str_replace(lcfirst(self::BASE_CRUD), lcfirst($this->getTitle()), $content);

        return $content;
    }

    private function makeSql()
    {
        $tools = new Tools;
        $table = $tools->fromCamelCase($this->getTitle());

        $fieldsArr  = [];
        $indexesArr = [];
        $foreginArr = [];

        foreach ($this->config['fields'] as $field => $params)
        {
            switch ($params['type']) {
                case 'select_db':
                case 'radio_db':
                    $fieldsArr[] = "`{$field}` INT(11) NULL DEFAULT NULL";

                    $parentTable = $this->getTableByField($field);

                    $indexesArr[] = "INDEX `{$field}` (`{$field}`)";
                    $foreginArr[] = "CONSTRAINT `{$parentTable}_ibfk_1` FOREIGN KEY (`{$field}`) REFERENCES `{$parentTable}` (`id`)";
                    break;

                case 'text':
                    if (self::DATE_CREATED_FIELD == $field)
                    {
                        $fieldsArr[] = "`{$field}` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP";
                    }
                    else
                    {
                        $fieldsArr[] = "`{$field}` VARCHAR(255) NULL DEFAULT NULL";
                    }
                    break;
                case 'textarea':
                    $fieldsArr[] = "`{$field}` TEXT NULL DEFAULT NULL";
                    break;
            }
        }

        $fields      = $fieldsArr ? ", " . implode(", ", $fieldsArr) : "";
        $indexes  = $indexesArr ? ", " . implode(", ", $indexesArr) : "";
        $foreignKeys = $foreginArr ? ", " . implode(", ", $foreginArr) : "";

        $sql = "DROP TABLE IF EXISTS `{$table}`;

                CREATE TABLE `{$table}` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT
                    {$fields},
                    PRIMARY KEY (`id`)
                    {$indexes}
                    {$foreignKeys}                    
                )
                COLLATE='utf8_general_ci'
                ENGINE=InnoDB
                ROW_FORMAT=COMPACT;

                ";

        file_put_contents($this->buildPath . '/sql/db.sql', $sql, FILE_APPEND);
    }

    public function make($includeProjectBase = false)
    {
        if (empty($this->config))
        {
            throw new \Exception('Je nutné zadat konfiguraci CRUDu');
        }

        $this->makePresenter();
        $this->makeModel();
        $this->makeTemplates();
        $this->makeForm();
        $this->makeList();

        $this->makeSql();

        $this->makeConfig();
        $this->makeMenu();

        if ($includeProjectBase) {
            $this->copyProjectBase();
        }
    }

    private function makeConfig()
    {
        $title = ucfirst($this->getTitle());

        $configs = [
            'models' => "
    - App\\Model\\{$title}",
            'components' => "
    - App\Component\\I{$title}FormFactory
    - App\Component\\I{$title}ListFactory",
        ];

        $firstConfig = key($configs);

        if (!file_exists("{$this->buildPath}/app/config/{$firstConfig}.neon")) {
            $this->processFolder(__DIR__ . '/app/config/');
        }

        foreach ($configs as $configFile => $config)
        {
            $configPath = "{$this->buildPath}/app/config/{$configFile}.neon";

            file_put_contents($configPath, $config, FILE_APPEND);
        }
    }

    public function makeMenu()
    {
        $title = ucfirst($this->getTitle());

        $menu = '
    <li>
    <a href=""><i class="fa fa-book fa-fw"></i> ' . $this->config['name'] . ' <span class="fa arrow"></span> <i class="fa fa-angle-left pull-right"></i></a>
        <ul class="treeview-menu">                                
            <li>
                <a n:href="' . $title . ':"><i class="fa fa-circle-o"></i> Přehled záznamů </a>
            </li>
            <li>
                <a n:href="' . $title . ':add"><i class="fa fa-circle-o"></i> Nový záznam </a>
            </li>
        </ul>
    </li>            

    ';

        file_put_contents($this->buildPath . '/app/presenters/templates/menu.latte', $menu, FILE_APPEND);
    }

    private function copyProjectBase()
    {
        $tools = new Tools;

        $tools->copyFolder(__DIR__ . '/project-base', $this->buildPath);

        $layoutPath = $this->buildPath . '/app/presenters/templates/@layout.latte';

        $content = str_replace('[PROJECT_TITLE]', $this->projectName, file_get_contents($layoutPath));

        file_put_contents($layoutPath, $content);


        $layoutPath = $this->buildPath . '/app/presenters/templates/Auth/login.latte';

        $content = str_replace('[PROJECT_TITLE]', $this->projectName, file_get_contents($layoutPath));

        file_put_contents($layoutPath, $content);
    }

    private function clean()
    {
        $tools = new Tools;

        $tools->deleteFolder($this->buildPath . '/app');
    }

    public function setBuildPath($path)
    {
        $this->buildPath = $path;
    }

    public function setTransformPath($path)
    {
        $this->transformPath = $path;
    }
}