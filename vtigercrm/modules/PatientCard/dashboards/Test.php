    <?php

class PatientCard_Test_Dashboard extends Vtiger_IndexAjax_View {

    public function process(Vtiger_Request $request) {

        global $adb;

        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $linkId = $request->get('linkid');

        $Result = $adb->pquery("SELECT smownerid, count(smownerid) AS 'COUNT' FROM vtiger_crmentity
                INNER JOIN vtiger_leaddetails ON vtiger_crmentity.crmid=vtiger_leaddetails.leadid
                INNER JOIN vtiger_leadscf ON vtiger_leaddetails.leadid=vtiger_leadscf.leadid
                WHERE vtiger_crmentity.deleted=0
                GROUP BY smownerid");

        $array_count = array();
        $array_responsible = array();
        $array_responsible_names = array();
        
        $resultGroups = $adb->pquery("SELECT * FROM vtiger_groups");
        $record_count_groups = $adb->num_rows($resultGroups);
        for ($i = 0; $i < $record_count_groups; $i++) {
            $array_responsible_names[getTranslatedString($adb->query_result($resultGroups, $i, 'groupid'), $moduleName)] = getTranslatedString($adb->query_result($resultGroups, $i, 'groupname'), $moduleName);
        }
        
        $resultUsers = $adb->pquery("SELECT * FROM vtiger_users");
        $record_count_users = $adb->num_rows($resultUsers);
        for ($i = 0; $i < $record_count_users; $i++) {
            $array_responsible_names[getTranslatedString($adb->query_result($resultUsers, $i, 'id'), $moduleName)] = getTranslatedString($adb->query_result($resultUsers, $i, 'last_name'), $moduleName);
        }
        
        $record_count = $adb->num_rows($Result);
        for ($i = 0; $i < $record_count; $i++) {
            array_push($array_count, $adb->query_result($Result, $i, 'count'));
            array_push($array_responsible, $array_responsible_names[getTranslatedString($adb->query_result($Result, $i, 'smownerid'), $moduleName)]);
        }

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('COUNT', $record_count);
        $viewer->assign('ARRAY_STAGE', $array_responsible);
        $viewer->assign('ARRAY_COUNT', $array_count);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/TestContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/Test.tpl', $moduleName);
        }
    }

}
