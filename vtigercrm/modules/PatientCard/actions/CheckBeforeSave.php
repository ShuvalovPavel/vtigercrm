<?php

class PatientCard_CheckBeforeSave_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        $dataArr = (array) json_decode(urldecode($request->get('checkBeforeSaveData')));
        if ($request->get('EditViewAjaxMode')) {
            $mode = $request->get('CreateMode');
            $selected_date = new DateTime($dataArr['registration_date']);
            $сurrent_date = new DateTime(date("d-m-Y"));
            if ($selected_date < $сurrent_date) {
                $response = "ALERT";
                $message = "Выбранная Дата меньше текущей. Измените Дату.";
            }
            if ($dataArr['eye_color'] == '' && $dataArr['characteristicd'] == '') {
                $response = "CONFIRM";
                $message = "Вы действительно хотите сохранить запись без указания Цвета глаз и Характеристики?";
            }
            echo json_encode(array("response" => $response, "message" => $message));
        }

        return;
    }

}


