<?php

class TestPdfCommand extends CConsoleCommand
{
    public function run($args)
    {
        $model = new Registrations;
        $model->id = 999999;
        $model->property_name = 'Đơn vị Test';
        $model->property_code = 'TEST';
        $model->event_name = 'Đại hội 2026';
        $model->period_name = 'Đợt 1';

        $data = array(
            'model' => $model,
            'periodContentCodes' => array('sports'),
            'isDot1' => true,
            'isDot2' => false,
            'registeredCategories' => 'Thể thao',
            'contentSummaryLines' => array('Bóng đá - 1 đội - 5 VĐV'),
            'sportTeams' => array(),
            'competitionRegistrations' => array(),
            'attendeesCount' => 5,
        );

        try {
            $path = PdfHelper::generateRegistrationPdf(999999, $data);
            echo "OK: {$path}\n";
            echo "exists=" . (file_exists($path) ? 'yes' : 'no') . " size=" . (file_exists($path) ? filesize($path) : 0) . "\n";
        } catch (Exception $e) {
            echo "PDF ERROR: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}
