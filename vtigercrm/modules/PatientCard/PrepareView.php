<?php
  function prepareEditView_PatientCard($focus, $request, $smarty) {
      
      $sourceModule = $request['sourceModule'];
      $sourceRecordId = $request['sourceRecord'];
    
      if (!empty($sourceRecordId) && $sourceModule == 'SalesOrder' ) {
          //Подключаем Модуль-источник
          require_once ("modules/$sourceModule/$sourceModule.php");                
          $sourceFocus = new $sourceModule();
          $sourceFocus->id = $sourceRecordId; 
          $sourceFocus->retrieve_entity_info($sourceRecordId, $sourceModule);
          
          //Заполняем поля текущего Модуля     
          $focus->set('eye_color', 'Зеленые');
          $focus->set('surname', $sourceFocus->column_fields['subject']);               
      }
      return $focus;
  }  
