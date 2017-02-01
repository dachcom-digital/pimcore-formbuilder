<?php

use Pimcore\Controller\Action\Admin;

use Formbuilder\Lib\Form\Backend\Builder;
use Formbuilder\Model\Form;

class Formbuilder_Admin_SettingsController extends Admin {

    public $languages = null;

    public function getTreeAction()
    {
        $mainList = new Form();
        $mains = $mainList->getAll();

        $mainItems = array();

        foreach ($mains as $mainItem)
        {
            $mainItems[] = [

                'id'            => (int) $mainItem['id'],
                'text'          => $mainItem['name'],
                'icon'          => '',
                'leaf'          => TRUE,
                'iconCls'       => 'Formbuilder_icon_root',
                'allowChildren' => FALSE

            ];
        }

        $this->_helper->json($mainItems);

    }

    public function getSettingsAction()
    {
        $config = \Formbuilder\Model\Configuration::get('form.admin.settings');

        $parsedConfig = [
            'activeElements' => [
                'fields' => []
            ]
        ];

        if( isset( $config['activeElements'] ) )
        {
            if( isset( $config['activeElements']['fields'] ) )
            {
                $parsedConfig['activeElements']['fields'] = $config['activeElements']['fields'];
            }
        }

        $this->_helper->json( $parsedConfig );

    }

    public function getAction()
    {
        $id = $this->_getParam('id');

        $formPath = FORMBUILDER_DATA_PATH . '/main_' . $id . '.json';

        if (file_exists( $formPath ))
        {
            try
            {
                $data = $this->loadFormData( $formPath );
            }
            catch(\Exception $e)
            {
                $data = [ 'success' => FALSE, 'message' => $e->getMessage() ];
            }

            $this->_helper->json($data);

        }
        else
        {
            $this->_helper->json(NULL);
        }

    }

    public function addAction()
    {
        $form = new Form();

        $name = $this->getSaveName( $this->_getParam('name') );

        $error = FALSE;
        $message = '';
        $id = NULL;

        $existingForm = FALSE;

        try
        {
            $existingForm = $form->getIdByName($name);
        }
        catch(\Exception $e ) {  }

        if( $existingForm !== FALSE)
        {
            $error = TRUE;
            $message = 'Form already exists!';
        }
        else
        {
            $form->setDate(time());
            $form->setName($name);

            $form->save();

            $id = $form->getId();

            $settings = array(
                'id' => $id,
                'name' => $name
            );

            if (file_exists(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json'))
            {
                unlink(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json');
            }

            $config = new Zend_Config($settings, TRUE);

            $writer = new Zend_Config_Writer_Json(
                array(
                    'config' => $config,
                    'filename' => FORMBUILDER_DATA_PATH . '/main_' . $id . '.json'
                )
            );

            $writer->setPrettyPrint( TRUE );
            $writer->write();

        }

        $this->_helper->json(
            [
                'success'   => !$error,
                'message'   => $message,
                'id'        => (int) $id,
            ]
        );

    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');

        $form = Form::getById($id);

        if ($form instanceof Form)
        {
            $form->delete();
        }

        //remove json
        if (file_exists(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json'))
        {
            unlink(FORMBUILDER_DATA_PATH . '/main_' . $id . '.json');
        }

        //remove json language files!
        $files = glob(FORMBUILDER_DATA_PATH . '/lang/form_' . $id . '_*.json');

        foreach($files as $file)
        {
            if (file_exists( $file ))
            {
                unlink( $file );
            }
        }

        $this->_helper->json(
            [
                'success'   => TRUE,
                'id'        => (int) $id,
            ]
        );

    }

    public function saveAction()
    {
        $id = $this->_getParam('id');

        $table = new Form();
        $name = $table->getName($id);

        $configuration = Zend_Json::decode($this->_getParam('configuration'));
        $values = Zend_Json::decode($this->_getParam('values'));

        $formName = $values['name'];

        if ($formName != $name)
        {
            $formName = $this->getSaveName( $formName );
            $values['name'] = $formName;

            $form = Form::getById($id);

            if ($form instanceof Form)
            {
                $form->rename( $values['name'] );
            }

        }

        $formPath = FORMBUILDER_DATA_PATH . '/main_' . $id . '.json';

        if (file_exists( $formPath ))
        {
            unlink( $formPath );
        }

        $settings = $values;
        $settings['mainDefinitions'] = $configuration;

        $config = new Zend_Config($settings, TRUE);
        $writer = new Zend_Config_Writer_Json(
            [
                'config'    => $config,
                'filename'  => $formPath
            ]
        );

        $writer->setPrettyPrint( TRUE );
        $writer->write();

        $data = $config->toArray();

        $builder = new Builder();

        if( !isset($data['attrib']) )
        {
            $data['attrib'] = [];
        }

        $builder->setDatas($data);
        $builder->buildForm($id);

        $this->_helper->json(
            [
                'formId' => (int) $id,
                'formName' => $formName,
                'success' => TRUE
            ]
        );

    }

    public function importAction()
    {
        $this->disableViewAutoRender();

        $data = file_get_contents($_FILES['Filedata']['tmp_name']);

        $encoding = \Pimcore\Tool\Text::detectEncoding($data);

        if ($encoding)
        {
            $data = iconv($encoding, 'UTF-8', $data);
        }

        if (!is_dir(FORMBUILDER_DATA_PATH . '/import/'))
        {
            mkdir(FORMBUILDER_DATA_PATH . '/import/');
        }

        $importFile = FORMBUILDER_DATA_PATH . '/import/import_' . $this->getParam('id');

        file_put_contents($importFile, $data);

        chmod($importFile, 0766);

        $res = [];
        $res['success'] = TRUE;

        $this->_helper->json(
            [
                'success' => TRUE,
                'msg' => $res['success'] ? 'Success' : 'Error',
            ]
        );

    }

    public function getImportAction()
    {
        $id = $this->_getParam('id');

        if (file_exists(FORMBUILDER_DATA_PATH . '/import/import_' . $id))
        {
            $config = new Zend_Config_Json(FORMBUILDER_DATA_PATH . '/import/import_' . $id);

            unlink(FORMBUILDER_DATA_PATH . '/import/import_' . $id);

            $data = $config->toArray();
            unset($data['name'], $data['id']);

            $this->_helper->json($data);

        }
        else
        {
            $this->_helper->json(NULL);
        }
    }

    public function getExportFileAction()
    {
        $id = $this->getParam('id');
        $name = $this->getParam('name');

        if (is_numeric($id))
        {
            $exportName = 'export_' . $name;

            $exportFile = FORMBUILDER_DATA_PATH . '/main_' . $id . '.json';

            $this->getResponse()->setHeader('Content-Type', 'application/json', TRUE);
            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="form_' . $exportName . '.json"');

            echo file_get_contents($exportFile);

        }

        $this->removeViewRenderer();

    }

    public function checkpathAction()
    {
        $path = $this->getParam('path');

        $pathIsValid = is_dir( PIMCORE_WEBSITE_PATH . '/' . ltrim($path, '/') );

        $this->_helper->json(
            [
                'success' => $pathIsValid
            ]
        );

    }

    public function getGroupTemplatesAction()
    {
        $config = \Formbuilder\Model\Configuration::get('form.area.groupTemplates');

        $templates = [ ['key' => NULL, 'label' => '--' ] ];

        if( !empty( $config ))
        {
            foreach( $config as $configName => $element)
            {
                $templates[] = ['key' => $configName, 'label' => $element['niceName']];
            }
        }
        $this->_helper->json(
            $templates
        );
    }

    protected function getSaveName( $name = '' )
    {
        return preg_replace('/[^A-Za-z0-9aäüöÜÄÖß \-]/', '', $name);

    }

    protected function loadFormData( $path )
    {
        if( is_file( $path ) )
        {
            return json_decode( file_get_contents( $path ));
        }

        return [];
    }

}