<?php
class DebugTalentCommand extends CConsoleCommand
{
    public function run($args)
    {
        $eventId = 3;
        $currentPropertyId = '42';
        $allEntriesData = TalentEntries::getApiDataProvider(array('event_id' => $eventId), 200)->getData();
        echo "Total entries loaded: " . count($allEntriesData) . "\n";
        $loaded = array();
        foreach ($allEntriesData as $entry) {
            $id = isset($entry->id) ? $entry->id : null;
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
            if ($id == 47 || $id == 41) {
                echo "Entry $id: prop=[$entryPropertyId] allianceRaw=" . var_export($allianceIds, true)
                    . " allianceArr=" . json_encode($allianceIdArray)
                    . " isOwner=" . var_export($isOwner, true)
                    . " isAlliance=" . var_export($isAlliance, true) . "\n";
            }
            if ($isOwner || $isAlliance) $loaded[] = $id;
        }
        echo "Loaded entry ids for prop 42: " . json_encode($loaded) . "\n";
    }
}
