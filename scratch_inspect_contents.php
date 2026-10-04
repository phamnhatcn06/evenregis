<?php
require 'E:/even_API/MTRegistrationPortal/vendor/autoload.php';
$app = require_once 'E:/even_API/MTRegistrationPortal/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Registration\Entities\FinalAttendeeRoster;
use Modules\Registration\Entities\FinalAttendeeContent;
use Modules\Registration\Entities\Attendee;

$nonFinalists = FinalAttendeeRoster::where('event_id', 3)->whereNull('attendee_type')->take(5)->get();
foreach ($nonFinalists as $r) {
    $att = Attendee::find($r->attendee_id);
    echo "- #{$r->id} {$r->full_name}, pos={$r->position_display}, note={$r->note}, att_type={$att->attendee_type}, att_role=" . ($att->role->name ?? '-') . "\n";
}
