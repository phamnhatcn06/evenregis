<?php

/**
 * EDataTables - DataTables widget for Yii1
 *
 * Usage:
 * $this->widget('ext.edatatables.EDataTables', array(
 *     'id' => 'events-grid',
 *     'dataProvider' => $model->search(),
 *     'filter' => true,
 *     'columns' => array(
 *         array('name' => 'id', 'header' => 'ID'),
 *         array('name' => 'name', 'header' => 'Tên', 'filter' => true),
 *         array('name' => 'status', 'header' => 'Trạng thái', 'filter' => array('active' => 'Hoạt động', 'inactive' => 'Không hoạt động')),
 *         array(
 *             'header' => 'Thao tác',
 *             'type' => 'raw',
 *             'filter' => false,
 *             'value' => function($data) {
 *                 return CHtml::link('Xem', array('view', 'id' => $data->id));
 *             }
 *         ),
 *     ),
 * ));
 */
class EDataTables extends CWidget
{
    public $id = 'datatable';
    public $dataProvider;
    public $columns = array();
    public $options = array();
    public $tableClass = 'table table-striped table-bordered';
    public $serverSide = false;
    public $ajaxUrl;
    public $language = 'vi';
    public $filter = false;

    private $_languages = array('vi', 'en');

    public function init()
    {
        parent::init();
        if ($this->dataProvider === null && !$this->serverSide) {
            throw new CException('dataProvider is required');
        }
    }

    public function run()
    {
        $this->renderTable();
        $this->registerScripts();
    }

    protected function renderTable()
    {
        echo '<table id="' . $this->id . '" class="' . $this->tableClass . '" style="width:100%">';

        // Header
        echo '<thead>';
        echo '<tr>';
        foreach ($this->columns as $column) {
            $header = isset($column['header']) ? $column['header'] : (isset($column['name']) ? $column['name'] : '');
            $width = isset($column['width']) ? ' style="width:' . $column['width'] . '"' : '';
            echo '<th' . $width . '>' . CHtml::encode($header) . '</th>';
        }
        echo '</tr>';

        // Filter row
        if ($this->filter) {
            echo '<tr class="filters">';
            foreach ($this->columns as $index => $column) {
                echo '<th>';
                $colFilter = isset($column['filter']) ? $column['filter'] : $this->filter;
                if ($colFilter !== false) {
                    if (is_array($colFilter)) {
                        // Dropdown filter
                        echo '<select class="form-select form-select-sm column-filter" data-column="' . $index . '">';
                        echo '<option value="">-- Tất cả --</option>';
                        foreach ($colFilter as $value => $label) {
                            echo '<option value="' . CHtml::encode($value) . '">' . CHtml::encode($label) . '</option>';
                        }
                        echo '</select>';
                    } else {
                        // Text input filter
                        echo '<input type="text" class="form-control form-control-sm column-filter" data-column="' . $index . '" placeholder="Tìm...">';
                    }
                }
                echo '</th>';
            }
            echo '</tr>';
        }
        echo '</thead>';

        // Body
        echo '<tbody>';
        if (!$this->serverSide && $this->dataProvider) {
            $data = $this->dataProvider->getData();
            foreach ($data as $row) {
                echo '<tr>';
                foreach ($this->columns as $column) {
                    echo '<td>' . $this->getColumnValue($row, $column) . '</td>';
                }
                echo '</tr>';
            }
        }
        echo '</tbody>';

        echo '</table>';
    }

    protected function getColumnValue($row, $column)
    {
        // Raw value with callback
        if (isset($column['value']) && is_callable($column['value'])) {
            $value = call_user_func($column['value'], $row);
            return isset($column['type']) && $column['type'] === 'raw' ? $value : CHtml::encode($value);
        }

        // Attribute name
        if (isset($column['name'])) {
            $name = $column['name'];
            $value = $this->getNestedValue($row, $name);

            // Format
            if (isset($column['type'])) {
                switch ($column['type']) {
                    case 'date':
                        $format = isset($column['format']) ? $column['format'] : 'd/m/Y';
                        return $value ? date($format, is_numeric($value) ? $value : strtotime($value)) : '';
                    case 'datetime':
                        $format = isset($column['format']) ? $column['format'] : 'd/m/Y H:i';
                        return $value ? date($format, is_numeric($value) ? $value : strtotime($value)) : '';
                    case 'boolean':
                        return $value ? '<span class="badge bg-success">Có</span>' : '<span class="badge bg-secondary">Không</span>';
                    case 'number':
                        return number_format($value, isset($column['decimals']) ? $column['decimals'] : 0, ',', '.');
                    case 'raw':
                        return $value;
                }
            }

            return CHtml::encode($value);
        }

        return '';
    }

    protected function getNestedValue($row, $name)
    {
        // Support nested attributes like 'organization.name'
        $parts = explode('.', $name);
        $value = $row;
        foreach ($parts as $part) {
            if (is_object($value)) {
                $value = isset($value->$part) ? $value->$part : null;
            } elseif (is_array($value)) {
                $value = isset($value[$part]) ? $value[$part] : null;
            } else {
                return null;
            }
        }
        return $value;
    }

    protected function registerScripts()
    {
        $cs = Yii::app()->clientScript;

        // Build options
        $options = array_merge(array(
            'responsive' => true,
            'pageLength' => 25,
            'order' => array(array(0, 'desc')),
        ), $this->options);

        // Handle non-sortable columns
        $nonSortable = array();
        foreach ($this->columns as $index => $column) {
            if (isset($column['sortable']) && $column['sortable'] === false) {
                $nonSortable[] = $index;
            }
        }
        if (!empty($nonSortable)) {
            $options['columnDefs'] = isset($options['columnDefs']) ? $options['columnDefs'] : array();
            $options['columnDefs'][] = array('orderable' => false, 'targets' => $nonSortable);
        }

        // Language (local files)
        if ($this->language && in_array($this->language, $this->_languages)) {
            $baseUrl = Yii::app()->theme->baseUrl;
            $options['language'] = array('url' => $baseUrl . '/assets/js/plugins/datatables/i18n/' . $this->language . '.json');
        }

        // Server-side
        if ($this->serverSide && $this->ajaxUrl) {
            $options['processing'] = true;
            $options['serverSide'] = true;
            $options['ajax'] = $this->ajaxUrl;
        }

        $optionsJson = CJavaScript::encode($options);

        $filterScript = '';
        if ($this->filter) {
            $filterScript = "
    // Column filters
    $('#{$this->id} thead').on('keyup change', '.column-filter', function() {
        var column = table.column($(this).data('column'));
        if (column.search() !== this.value) {
            column.search(this.value).draw();
        }
    });
";
        }

        $script = "
$(document).ready(function() {
    var table = $('#{$this->id}').DataTable({$optionsJson});
    {$filterScript}
});
";
        $cs->registerScript('datatable-' . $this->id, $script, CClientScript::POS_END);
    }
}
