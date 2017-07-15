<?php
    function UpdateAccountSurname($ws_entity){
        $ws_id = $ws_entity->getId();
        $module = $ws_entity->getModuleName();
        if (empty($ws_id) || empty($module)) {
            return;
        }
    
        // CRM id
        $crmid = vtws_getCRMEntityId($ws_id);
        if ($crmid <= 0) {
            return;
        }
    
        //получение объекта со всеми данными о текущей записи Модуля "PatientCard"
        $patientCardInstance = Vtiger_Record_Model::getInstanceById($crmid);    
    
        //получение id account
        $acId = $patientCardInstance->get('name_of_the_organization');
    
        if($acId) {
            //получение surname текущей записи Модуля "PatientCard"
            $surname = $patientCardInstance->get('surname');
        
            //получение объекта со всеми данными о текущей записи Модуля "account"        
            $acInstance = Vtiger_Record_Model::getInstanceById($acId);  
        
            //объект в режиме редактирования          
            $acInstance->set('mode', 'edit');
        
            //запись фамилии в поле “Контрагент”
            $acInstance->set('accountname', $surname);

            //сохранение
            $acInstance->save();
        }
    }