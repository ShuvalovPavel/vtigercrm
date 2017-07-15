<?php

include_once 'modules/Vtiger/CRMEntity.php';

class PatientCard extends Vtiger_CRMEntity {

    var $table_name = 'vtiger_patientcard';
    var $table_index = 'patientcardid';
    var $customFieldTable = Array('vtiger_patientcardcf', 'patientcardid');
    var $tab_name = Array('vtiger_crmentity', 'vtiger_patientcard', 'vtiger_patientcardcf');
    var $tab_name_index = Array('vtiger_crmentity' => 'crmid',
        'vtiger_patientcard' => 'patientcardid',
        'vtiger_patientcardcf' => 'patientcardid');

    function vtlib_handler($modulename, $event_type) {
        $salesOrder = Vtiger_Module::getInstance('SalesOrder');
        $contacts = Vtiger_Module::getInstance('Contacts');
        $patientCard = Vtiger_Module::getInstance('PatientCard');

        switch ($event_type) {
            case 'module.postinstall':
                //регистрирование планировщика
                vimport('~~vtlib/Vtiger/Cron.php');
                Vtiger_Cron::register('PortalUserTask', '/modules/PatientCard/task/PortalUser.service', 86400, 'PatientCard', 1, 1, 'Рекомендуемая частота обновления - 24 часа.');

                //регистрирование обработчика
                require 'modules/com_vtiger_workflow/VTEntityMethodManager.inc';
                $emm = new VTEntityMethodManager($adb);
                $emm->addEntityMethod("PatientCard", "Update Account Surname", "modules/PatientCard/workflow/processAccountsSurname.php", "UpdateAccountSurname");

                //Добавление для Модуля "Contacts" поля-ссылки на модуль "PatientCard"
                $this->addField($contacts);
                //Добавить для модуля “SalesOrder” ссылку на связанный список “PatientCard”
                $salesOrder->setRelatedList($patientCard, 'PatientCard', Array('ADD', 'SELECT'));

                //добавление обработчика в систему
                vimport("~~modules/com_vtiger_workflow/include.inc");
                vimport("~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc");
                vimport("~~modules/com_vtiger_workflow/VTEntityMethodManager.inc");
                vimport("~~modules/com_vtiger_workflow/VTTaskManager.inc");
                global $adb;
                $vtWorkFlow = new VTWorkflowManager($adb);

                $myWorkFlow = $vtWorkFlow->newWorkFlow("PatientCard");
                $myWorkFlow->test = '';
                $myWorkFlow->description = "Перенос Фамилии";
                $myWorkFlow->executionCondition = VTWorkflowManager::$ON_EVERY_SAVE;
                $myWorkFlow->defaultworkflow = 1;

                $vtWorkFlow->save($myWorkFlow);

                $tm = new VTTaskManager($adb);
                $task = $tm->createTask('VTEntityMethodTask', $myWorkFlow->id);
                $task->active = true;
                $task->methodName = "UpdateAccountSurname";
                $task->subject = "Уведомление о переносе Фамилии";
                $task->summary = 'Перенос Фамилии из Модуля "PatientCard" в связанный Модуль "Контрагенты"';
                $tm->saveTask($task);
                break;
            case 'module.disabled':
                //Удаление связи между Модулями “SalesOrder” и “PatientCard”
                $salesOrder->unsetRelatedList($patientCard);
                //Отключение из Модуля "Contacts" поля-ссылки на модуль "PatientCard"
                $field = Vtiger_Field::getInstance('patientcardid', $contacts);
                if (isset($field)) {
                    $field->setPresence(1);
                }
                break;
            case 'module.enabled':
                //Добавить для модуля “SalesOrder” ссылку на связанный список “PatientCard”
                $salesOrder->setRelatedList($patientCard, 'PatientCard', Array('ADD', 'SELECT'));
                //Включение в Модуле "Contacts" поля-ссылки на модуль "PatientCard"
                $field = Vtiger_Field::getInstance('patientcardid', $contacts);
                if (isset($field)) {
                    $field->setPresence(2);
                }
                break;
            case 'module.preuninstall':
                break;
            case 'module.preupdate':
                break;
            case 'module.postupdate':
                break;
        }
    }

    function addField($patientCard_) {
        $block = Vtiger_Block::getInstance('LBL_CONTACT_INFORMATION', $patientCard_);

        //Добавление для Модуля "Contacts" поля-ссылки на модуль "PatientCardс"
        $patientCard_field = new Vtiger_Field();
        $patientCard_field->name = 'patientcardid';
        $patientCard_field->label = 'Patient Card';
        $patientCard_field->uitype = 10;
        $patientCard_field->summaryfield = 1;
        $patientCard_field->column = $patientCard_field->name;
        $patientCard_field->columntype = 'VARCHAR(255)';
        $patientCard_field->typeofdata = 'V~O';
        $block->addField($patientCard_field);
        $patientCard_field->setRelatedModules(Array('PatientCard'));
    }

}
