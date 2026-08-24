<?php
class DebugTalent2Command extends CConsoleCommand
{
    public function run($args)
    {
        $p = require(dirname(__FILE__) . '/../config/params.php');
        Yii::app()->params->mergeWith($p);

        $model = Registrations::fetchFromApi(203);
        echo "Registration 203: property_id=" . $model->property_id . " event_id=" . $model->event_id . "\n";

        // attendeesMap giả lập rỗng để enrichment tự fetch
        $attendeesMap = array();

        $talentEntries = array();
        $talentEntryMembers = array();
        $loadedEntryIds = array();

        $allEntriesData = TalentEntries::getApiDataProvider(array('event_id' => $model->event_id), 200)->getData();
        $currentPropertyId = (string)$model->property_id;

        foreach ($allEntriesData as $entry) {
            $entryPropertyId = isset($entry->property_id) ? (string)$entry->property_id : '';
            $allianceIds = isset($entry->alliance_org_ids) ? $entry->alliance_org_ids : '';
            $allianceIdArray = array();
            if (!empty($allianceIds)) {
                if (is_array($allianceIds)) {
                    $allianceIdArray = array_map('strval', $allianceIds);
                } elseif (is_string($allianceIds)) {
                    $decoded = json_decode($allianceIds, true);
                    if (is_array($decoded)) {
                        $allianceIdArray = array_map('strval', $decoded);
                    } else {
                        $allianceIdArray = array_map('trim', explode(',', $allianceIds));
                    }
                }
            }
            $isOwner = ($entryPropertyId === $currentPropertyId);
            $isAlliance = in_array($currentPropertyId, $allianceIdArray, true);
            if ($isOwner || $isAlliance) {
                $eid = isset($entry->id) ? $entry->id : null;
                if ($eid && !in_array($eid, $loadedEntryIds)) {
                    $talentEntries[] = $entry;
                    $loadedEntryIds[] = $eid;
                }
            }
        }

        $ids = array();
        foreach ($talentEntries as $e) $ids[] = $e->id;
        echo "FINAL talentEntries count=" . count($talentEntries) . " ids=" . json_encode($ids) . "\n";
    }
}
