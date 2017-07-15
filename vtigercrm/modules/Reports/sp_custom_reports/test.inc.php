<?php

function prepareTestReport($filterhash, $params) {
    global $adb;

    // Создание таблицы генерации отчета
    $adb->query("CREATE TABLE IF NOT EXISTS `sp_Test_report` (
            `row_id` int(19) NOT NULL auto_increment,
            `salesOrder_count` int(11) default NULL,
            `growth_count` int(11) default NULL,
            `filterhash` varchar(200) collate utf8_unicode_ci default NULL,
            `sequence` int(11) NOT NULL,
            PRIMARY KEY  (`row_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8");

    //$filterhash = rand();
    // Очистка старых данных отчета
    $adb->pquery("delete from sp_Test_report where filterhash=?", array($filterhash)); 
    
    $startdate = date('Y-m-d', (int) time() - 315360000);
    $enddate = date('Y-m-d', (int) time() + 315360000);

    if ($params == 0 or count($params) == 0) {
        // без фильтра
        $Quantity_SalesOrder = $adb->query("SELECT COUNT(*) AS Count_SalesOrder FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid=vtiger_crmentity.crmid
                WHERE vtiger_crmentity.deleted=0");

        $Quantity_Growth = $adb->query("SELECT COUNT(*) AS Count_Growth FROM vtiger_patientcard
               INNER JOIN vtiger_crmentity ON vtiger_patientcard.patientcardid=vtiger_crmentity.crmid
               WHERE  vtiger_patientcard.growth>170 and vtiger_crmentity.deleted=0");
    } else {

        foreach ($params as $paramsValues) {
            //фильтрация по дате
            if (isset($paramsValues['columnname']) && $paramsValues['columnname'] == 'date' && 
                    isset($paramsValues['comparator']) && $paramsValues['comparator'] == 'custom' && 
                    isset($paramsValues['value']) && !empty($paramsValues['value']) && 
                    strpos($paramsValues['value'], ',')) {

                $paramsDates = explode(',', $paramsValues['value']);
                if (!strtotime($paramsDates[0]) || !strtotime($paramsDates[1])) {
                    break;
                }

                $startdate = $paramsDates[0];
                $enddate = $paramsDates[1];
                $startdate = date('Y-m-d H:i:s', strtotime($startdate));
                $enddate = date('Y-m-d H:i:s', strtotime($enddate));

                $Quantity_SalesOrder = $adb->query("SELECT COUNT(*) AS Count_SalesOrder FROM vtiger_salesorder
                        INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid=vtiger_crmentity.crmid
                        WHERE vtiger_crmentity.deleted=0 and 
                        vtiger_crmentity.createdtime BETWEEN ' " . $startdate . " ' AND ' " . $enddate . "'");

                $Quantity_Growth = $adb->query("SELECT COUNT(*) AS Count_Growth FROM vtiger_patientcard 
                        INNER JOIN vtiger_crmentity ON vtiger_patientcard.patientcardid=vtiger_crmentity.crmid
                        WHERE vtiger_patientcard.growth>170 and vtiger_crmentity.deleted=0 and 
                        vtiger_crmentity.createdtime BETWEEN ' " . $startdate . " ' AND ' " . $enddate . "'");
                break;
            }
//фильтрация по ответственному
            if (isset($paramsValues['columnname']) && $paramsValues['columnname'] == 'owner' &&
                    isset($paramsValues['comparator']) && $paramsValues['comparator'] == 'e' && 
                    isset($paramsValues['value']) && !empty($paramsValues['value'])) {

                $paramsVal = $paramsValues['value'];
                $string_responsible = "'" . implode("','", explode(',', $paramsVal)) . "'";

                $Quantity_SalesOrder = $adb->query("SELECT COUNT(*) AS Count_SalesOrder FROM vtiger_salesorder 
                        INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid=vtiger_crmentity.crmid 
                        LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
                        LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
                        WHERE vtiger_crmentity.deleted=0 and 
                            ((vtiger_groups.groupname in (" . $string_responsible . ")) OR (CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) in (" . $string_responsible . "))
                                OR (vtiger_users.last_name in (" . $string_responsible . ")))");

                $Quantity_Growth = $adb->query("SELECT COUNT(*) AS Count_Growth FROM vtiger_patientcard
                        INNER JOIN vtiger_crmentity ON vtiger_patientcard.patientcardid=vtiger_crmentity.crmid
                        LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
                        LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
                        WHERE vtiger_patientcard.growth>170 and vtiger_crmentity.deleted=0 and 
                            ((vtiger_groups.groupname in (" . $string_responsible . ")) OR (CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) in (" . $string_responsible . "))
                                OR (vtiger_users.last_name in (" . $string_responsible . ")))");
            }
        }
    }

    $quantity_SalesOrder = $adb->query_result($Quantity_SalesOrder, 0, 0);
    $quantity_Growth = $adb->query_result($Quantity_Growth, 0, 0);

    $insertSql = 'insert into sp_Test_report(salesOrder_count,growth_count,filterhash,sequence) values(?,?,?,?)';

    $result = $adb->pquery($insertSql, array($quantity_SalesOrder, $quantity_Growth, $filterhash, 1));

    return "select salesOrder_count as recordsSalesOrder, growth_count as recordsGrowth from sp_Test_report where filterhash='$filterhash' order by sequence";
}
