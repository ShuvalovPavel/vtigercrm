<?php

include_once 'vtlib/Vtiger/Module.php';
require_once('vtlib/Vtiger/Package.php');

$Vtiger_Utils_Log = true;

$MODULENAME = 'PatientCard';

$oldInstance = Vtiger_Module::getInstance($MODULENAME);
if ($oldInstance) $oldInstance->delete();

$moduleInstance = new Vtiger_Module();
$moduleInstance->name = $MODULENAME;
$moduleInstance->parent = 'Tools';
$moduleInstance->save();
$moduleInstance->initTables();

$info_block = new Vtiger_Block();
$info_block->label = 'LBL_' . strtoupper($moduleInstance->name) . '_INFORMATION';
$moduleInstance->addBlock($info_block);

$contactModule = Vtiger_Module::getInstance('Contacts');
$relLabel = 'Contacts';
$moduleInstance->setRelatedList($contactModule, $relLabel, Array('ADD', 'SELECT'));

//Название организации*  Поле-ссылка на карточку “Контрагенты”
$organization_field = new Vtiger_Field();
$organization_field->name = 'name_of_the_organization';
$organization_field->label = 'Name of the organization';
$organization_field->uitype = 10;
$organization_field->summaryfield = 1;
$organization_field->column = $organization_field->name;
$organization_field->columntype = 'INT(19)';
$organization_field->typeofdata = 'I~M';
$info_block->addField($organization_field);
$organization_field->setRelatedModules(Array('Accounts'));


//Фамилия*  Текстовое поле
$surname_field = new Vtiger_Field();
$surname_field->name = 'surname';
$surname_field->label = 'Surname';
$surname_field->uitype = 2;
$surname_field->summaryfield = 1;
$surname_field->column = $surname_field->name;
$surname_field->columntype = 'VARCHAR(255)';
$surname_field->typeofdata = 'V~M';
$info_block->addField($surname_field);
$moduleInstance->setEntityIdentifier($surname_field);

//Рост  Целочисленное поле
$growth_field = new Vtiger_Field();
$growth_field->name = 'growth';
$growth_field->label = 'Growth';
$growth_field->column = $growth_field->name;
$growth_field->columntype = 'VARCHAR(255)';
$growth_field->typeofdata = 'I~O';
$info_block->addField($growth_field);

//Вес  Целочисленное поле
$weight_field = new Vtiger_Field();
$weight_field->name = 'weight';
$weight_field->label = 'Weight';
$weight_field->column = $weight_field->name;
$weight_field->columntype = 'VARCHAR(255)';
$weight_field->typeofdata = 'I~O';
$info_block->addField($weight_field);

//Цвет глаз  Выпадающий список.
$eye_color_field = new Vtiger_Field();
$eye_color_field->name = 'eye_color';
$eye_color_field->label = 'Eye color';
$eye_color_field->uitype = 16;
$eye_color_field->summaryfield = 1;
$eye_color_field->column = $eye_color_field->name;
$eye_color_field->columntype = 'VARCHAR(255)';
$eye_color_field->typeofdata = 'V~O';
$info_block->addField($eye_color_field);
$eye_color_field->setPicklistValues(Array('Синие', 'Карие', 'Зеленые'));

//Дата регистрации*  Дата
$registration_date_field = new Vtiger_Field();
$registration_date_field->name = 'registration_date';
$registration_date_field->label = 'Registration date';
$registration_date_field->uitype = 5;
$registration_date_field->column = $registration_date_field->name;
$registration_date_field->columntype = 'DATE';
$registration_date_field->typeofdata = 'D~M';
$info_block->addField($registration_date_field);

//Ответственный*  Ответственный
$responsible_field = new Vtiger_Field();
$responsible_field->name = 'responsible';
$responsible_field->label = 'Responsible';
$responsible_field->table = 'vtiger_crmentity'; 
$responsible_field->column = 'smownerid';
$responsible_field->uitype = 53;
$responsible_field->typeofdata = 'V~M';
$info_block->addField($responsible_field);

//second block
$characterristic_block = new Vtiger_Block();
$characterristic_block->label = 'LBL_' . strtoupper($moduleInstance->name) . '_CHARACTERRISTIC';
$moduleInstance->addBlock($characterristic_block);

$characteristic_field = new Vtiger_Field();
$characteristic_field->name = 'characteristic';
$characteristic_field->label = 'Characteristic';
$characteristic_field->column = $characteristic_field->name;
$characteristic_field->columntype = 'VARCHAR(255)';
$characteristic_field->uitype = 19;
$characteristic_field->typeofdata = 'V~O';
$characterristic_block->addField($characteristic_field);

$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$moduleInstance->addFilter($filter1);
$filter1->addField($surname_field)->addField($registration_date_field, 1)->addField($responsible_field, 2);


$moduleInstance->setDefaultSharing();

$moduleInstance->initWebservice();

$package = new Vtiger_Package();
$package->export($moduleInstance, 'test/vtlib', 'PatientCard.zip', false);