<?php

/**
 * @author Jhonatan Bianchi
 *
 *   Updated options can be found at:
 *   http://ludo.cubicphuse.nl/jquery-treetable/#configuration
 *
 *   Those  were the option at 2014-09-02
 *
 *   branchAttr          string  "ttBranch"              Optional data attribute that can be used to force the expander icon to be rendered on a node. This allows us to define a node as a branch node even though it does not have children yet. This translates to a data-tt-branch attribute in your HTML.
 *   clickableNodeNames  bool    false                   Set to true to expand branches not only when expander icon is clicked but also when node name is clicked.
 *   column              int     0                       The number of the column in the table that should be displayed as a tree.
 *   columnElType        string  "td"                    The types of cells that should be considered for the tree (td, th or both).
 *   expandable          bool    false                   Should the tree be expandable? An expandable tree contains buttons to make each branch with children collapsible/expandable.
 *   expanderTemplate    string  <a href="#">&nbsp;</a>  The HTML fragment used for the expander.
 *   indent              int     19                      The number of pixels that each branch should be indented with.
 *   indenterTemplate    string  <span class="indenter"></span>  The HTML fragment used for the indenter.
 *   initialState        string  "collapsed"             Possible values: "expanded" or "collapsed".
 *   nodeIdAttr          string  "ttId"                  Name of the data attribute used to identify node. Translates to data-tt-id in your HTML.
 *   parentIdAttr        string  "ttParentId"            Name of the data attribute used to set parent node. Translates to data-tt-parent-id in your HTML.
 *   stringCollapse      string  "Collapse"              For internationalization.
 *   stringExpand        string  "Expand"                For internationalization.
 *
 */
Yii::import('zii.widgets.grid.CGridView');

class SilcomTreeGridView extends CGridView
{
    public $parentColumn = 'parent_id'; // Attribute that points to the parent
    public $parentClass = 'parent'; // Class added do each <tr> that is a parent (only roots)
    public $childClass = 'child'; // Class added to each <tr> that is a child
    public $customCssFile;
    public $treeViewOptions = array();
    private $extensionBaseUrl;
    //column values are merged independently
    const MERGE_SIMPLE = 'simple';
    //column values are merged if at least one value of nested columns changes (makes sense when several columns in $mergeColumns option)
    const MERGE_NESTED = 'nested';
    //column values are merged independently, but value is shown in first row of group and below cells just cleared (instead of `rowspan`)
    const MERGE_FIRSTROW = 'firstrow';

    public $mergeColumns = array();
    public $mergeType = self::MERGE_NESTED;
    public $mergeCellCss = 'text-align: center; vertical-align: middle';

    //list of columns on which change extrarow will be triggered
    public $extraRowColumns = array();
    //expression to get value shown in extrarow
    public $extraRowExpression;
    //position of extraRow relative to group: 'above' | 'below'
    public $extraRowPos = 'above';
    //totals expression: function($data, $row, &$totals)
    public $extraRowTotals;

    //array with groups
    private $_groups = array();

    public function init()
    {
        parent::init();
        // parent must be followed by their children, so, order the dataProvider
        $data = $this->dataProvider->getData();
        $newData = $this->orderData($data);
        $this->dataProvider->setData($newData);
    }

    private function orderData($remainingNodes, $orderedData = array())
    {
//        MyHelper::dump($remainingNodes);
        if (count($orderedData) == 0) {
            // add the roots
            foreach ($remainingNodes as $node) {
                if ($node->{$this->parentColumn} == 0 || $node->{$this->parentColumn} == 1) {
                    $orderedData[] = $node;
                }
            }

            if (count($orderedData) == 0) {
                throw new Exception("Error, no roots found");
            }
        } else {
            $oldOrderedData = $orderedData;
            foreach ($remainingNodes as $node) {
                // look for the parent
                foreach ($orderedData as $i => $insertedNode) {
                    if ($insertedNode->getPrimaryKey() == $node->{$this->parentColumn}
                        && in_array($insertedNode, $oldOrderedData)
                    ) {
                        // parent is in position $i
                        $positionToInsert = null;
                        $j = $i + 1;
                        while ($j < count($orderedData) && $positionToInsert == null) {
                            $orderedNode = $orderedData[$j];
                            // if the parent is the same, proceed
                            if ($orderedNode->{$this->parentColumn} == $node->{$this->parentColumn}) {
                                $j++;
                            } else {
                                $positionToInsert = $j;
                            }
                        }

                        if ($positionToInsert != null) {
                            array_splice($orderedData, $positionToInsert, 0, array($node));
                            break;
                        } else {
                            $orderedData[] = $node;
                            break;
                        }
                    }
                }
            }
        }

        $remainingNodes = array_udiff($remainingNodes, $orderedData, function ($nodeA, $nodeB) {
            return $nodeA->getPrimaryKey() - $nodeB->getPrimaryKey();
        }
        );

        if ($remainingNodes != null) {
            return $this->orderData($remainingNodes, $orderedData);
        } else {
            return $orderedData;
        }
    }

    public function registerClientScript()
    {
        parent::registerClientScript();

        if ($this->extensionBaseUrl == null) {
            $this->extensionBaseUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.SilcomTreeGridView'));
        }

        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile($this->extensionBaseUrl . '/js/jquery.treeTable.js', CClientScript::POS_END);

        if ($this->customCssFile == null) {
            $cs->registerCssFile($this->extensionBaseUrl . '/css/jquery.treetable.css');
            $cs->registerCssFile($this->extensionBaseUrl . '/css/jquery.treetable.theme.default.css');
        } else {
            $cs->registerCssFile($this->customCssFile);
        }

        $options = CJavaScript::encode($this->treeViewOptions);

        $cs->registerScript('treeTable', '$(document).ready(function()  
                                        {
                                            $("#' . $this->getId() . ' .items").treetable(' . $options . ');
                                        });'
        );
    }

    public function renderTableRow($rowIndex)
    {
        $model = $this->dataProvider->data[$rowIndex];

        // data-tt-id must be unique
        $row = '<tr data-tt-id="' . $model->getPrimaryKey() . '"';

        if ($model->{$this->parentColumn} != null) {
            // data-tt-parent-id indicates the parent
            $row .= 'data-tt-parent-id="' . $model->{$this->parentColumn} . '"';

            // add child class and class attribute remains open
            $row .= ' class="' . $this->childClass;
        } else {
            // add parent class and class attribute remains open
            $row .= ' class="' . $this->parentClass;
        }

        // check if there are more classes to be add
        if ($this->rowCssClassExpression !== null) {
            $row .= ' ' . $this->evaluateExpression($this->rowCssClassExpression, array('rowIndex' => $rowIndex, 'data' => $model));
        } else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0) {
            $row .= ' ' . $this->parentClass . $this->rowCssClass[$rowIndex % $n];
        }

        // closes class attribute and the tr tag
        $row .= '">';

        echo $row;

        // render columns
        foreach ($this->columns as $column) {
            $isGroupColumn = property_exists($column, 'name') && in_array($column->name, $this->mergeColumns);

            if (!$isGroupColumn) {
                $column->renderDataCell($rowIndex);
                continue;
            }

            //is curent row appears on edge of group
            $edge = $this->isGroupEdge($column->name, $rowIndex);
            switch ($this->mergeType) {
                case self::MERGE_SIMPLE:
                case self::MERGE_NESTED:
                    if (isset($edge['start'])) {
                        $options = $column->htmlOptions;
                        $column->htmlOptions['rowspan'] = $edge['group']['end'] - $edge['group']['start'] + 1;
                        $column->htmlOptions['class'] = 'merge';
                        $style = isset($column->htmlOptions['style']) ? $column->htmlOptions['style'] : '';
                        $column->htmlOptions['style'] = $style . ';' . $this->mergeCellCss;
                        $column->renderDataCell($rowIndex);
                        $column->htmlOptions = $options;
                    }
                    break;

                case self::MERGE_FIRSTROW:
                    if (isset($edge['start'])) {
                        $column->renderDataCell($rowIndex);
                    } else {
                        echo '<td></td>';
                    }
                    break;
            }
        }
        echo "</tr> \n";
    }

//    public function renderTableRow($row)
//    {
//        $extraRowEdge = null;
//        if(count($this->extraRowColumns)) {
//            $colName = $this->extraRowColumns[0];
//            $extraRowEdge = $this->isGroupEdge($colName, $row);
//            if($this->extraRowPos == 'above' && isset($extraRowEdge['start'])) {
//                $this->renderExtraRow($row, $extraRowEdge['group']['totals']);
//            }
//        }
//        /*
//        if($this->_changes && array_key_exists($row, $this->_changes)) {
//            $change = $this->_changes[$row];
//            //if change in extracolumns --> put extra row
//            $columnsInExtra = array_intersect(array_keys($change['columns']), $this->extraRowColumns);
//            //extraRowPos = before
//            if(count($columnsInExtra) > 0 && $this->extraRowPos == 'before') {
//                $this->renderExtraRow($row, $this->_changes[$row], $columnsInExtra);
//            }
//        }
//        */
//
//        // original CGridView code
//        if($this->rowCssClassExpression!==null)
//        {
//            $data=$this->dataProvider->data[$row];
//            echo '<tr class="'.$this->evaluateExpression($this->rowCssClassExpression,array('row'=>$row,'data'=>$data)).'">';
//        }
//        else if(is_array($this->rowCssClass) && ($n=count($this->rowCssClass))>0)
//            echo '<tr class="'.$this->rowCssClass[$row%$n].'">';
//        else
//            echo '<tr>';
//
//
//        foreach($this->columns as $column) {
//            $isGroupColumn = property_exists($column, 'name') && in_array($column->name, $this->mergeColumns);
//            if(!$isGroupColumn) {
//                $column->renderDataCell($row);
//                continue;
//            }
//
//            //is curent row appears on edge of group
//            $edge = $this->isGroupEdge($column->name, $row);
//
//            switch($this->mergeType) {
//                case self::MERGE_SIMPLE:
//                case self::MERGE_NESTED:
//                    if(isset($edge['start'])) {
//                        $options = $column->htmlOptions;
//                        $column->htmlOptions['rowspan'] = $edge['group']['end'] - $edge['group']['start'] + 1;
//                        $column->htmlOptions['class'] = 'merge';
//                        $style = isset($column->htmlOptions['style']) ? $column->htmlOptions['style'] : '';
//                        $column->htmlOptions['style'] = $style.';'.$this->mergeCellCss;
//                        $column->renderDataCell($row);
//                        $column->htmlOptions = $options;
//                    }
//                    break;
//
//                case self::MERGE_FIRSTROW:
//                    if(isset($edge['start'])) {
//                        $column->renderDataCell($row);
//                    } else {
//                        echo '<td></td>';
//                    }
//                    break;
//            }
//
//        }
//
//        echo "</tr>\n";
//
//        //extraRowPos = after
//        if(count($this->extraRowColumns) && $this->extraRowPos == 'below' && isset($extraRowEdge['end'])) {
//            $this->renderExtraRow($row, $extraRowEdge['group']['totals']);
//        }
//    }

    public function renderTableBody()
    {
        if (!empty($this->mergeColumns) || !empty($this->extraRowColumns)) {
            $this->groupByColumns();
        }
        parent::renderTableBody();
    }

    /**
     * find and store changing of group columns
     *
     * @param mixed $data
     */
    public function groupByColumns()
    {
        $data = $this->dataProvider->getData();
        if (count($data) == 0) return;

        if (!is_array($this->mergeColumns)) $this->mergeColumns = array($this->mergeColumns);
        if (!is_array($this->extraRowColumns)) $this->extraRowColumns = array($this->extraRowColumns);

        //store columns for group. Set object for existing columns in grid and string for attributes
        $groupColumns = array_unique(array_merge($this->mergeColumns, $this->extraRowColumns));
        foreach ($groupColumns as $key => $colName) {
            foreach ($this->columns as $column) {
                if (property_exists($column, 'name') && $column->name == $colName) {
                    $groupColumns[$key] = $column;
                    break;
                }
            }
        }

        //storage for groups in each column
        $groups = array();

        //values for first row
        $values = $this->getRowValues($groupColumns, $data[0], 0);
        foreach ($values as $colName => $value) {
            $groups[$colName][] = array(
                'value' => $value,
                'column' => $colName,
                'start' => 0,
                //end - later
                //totals - later
            );
        }

        //calc totals for the first row
        $totals = array();
        if ($this->extraRowTotals) {
            $this->evaluateExpression($this->extraRowTotals, array('data' => $data[0], 'row' => 0, 'totals' => &$totals));
        }

        //iterate data
        for ($i = 1; $i < count($data); $i++) {
            //save row values in array
            $current = $this->getRowValues($groupColumns, $data[$i], $i);

            //define is change occured. Need this extra foreach for correctly proceed extraRows
            $changedColumns = array();
            foreach ($current as $colName => $curValue) {
                $prev = end($groups[$colName]);
                if ($curValue != $prev['value']) {
                    $changedColumns[] = $colName;
                }
            }

            /*
             if this flag = true -> we will write change for all grouping columns.
             It's required when change of any column from extraRowColumns occurs
            */
            $extraRowColumnChanged = (count(array_intersect($changedColumns, $this->extraRowColumns)) > 0);

            /*
             this changeOccured related to foreach below. It is required only for mergeType == self::MERGE_NESTED,
             to write change for all nested columns when change of previous column occured
            */
            $changeOccured = false;
            foreach ($current as $colName => $curValue) {
                //value changed
                $valueChanged = in_array($colName, $changedColumns);
                //change already occured in this loop and mergeType set to MERGETYPE_NESTED
                $saveChange = $valueChanged || ($changeOccured && $this->mergeType == self::MERGE_NESTED);

                if ($extraRowColumnChanged || $saveChange) {
                    $changeOccured = true;
                    $lastIndex = count($groups[$colName]) - 1;

                    //finalize prev group
                    $groups[$colName][$lastIndex]['end'] = $i - 1;
                    $groups[$colName][$lastIndex]['totals'] = $totals;

                    //begin new group
                    $groups[$colName][] = array(
                        'start' => $i,
                        'column' => $colName,
                        'value' => $curValue,
                    );
                }
            }

            //if change in extrarowcolumn --> reset totals
            if ($extraRowColumnChanged) {
                $totals = array();
            }

            //calc totals for that row
            if ($this->extraRowTotals) {
                $this->evaluateExpression($this->extraRowTotals, array('data' => $data[$i], 'row' => $i, 'totals' => &$totals));
            }
        }

        //finalize group for last row
        foreach ($groups as $colName => $v) {
            $lastIndex = count($groups[$colName]) - 1;
            $groups[$colName][$lastIndex]['end'] = count($data) - 1;
            $groups[$colName][$lastIndex]['totals'] = $totals;
        }

        $this->_groups = $groups;
    }


    /**
     * returns array of rendered column values (TD)
     *
     * @param mixed $columns
     * @param mixed $rowIndex
     */
    private function getRowValues($columns, $data, $rowIndex)
    {
        foreach ($columns as $column) {
            if ($column instanceOf CGridColumn) {
                $result[$column->name] = $this->getDataCellContent($column, $data, $rowIndex);
            } elseif (is_string($column)) {
                if (is_array($data) && array_key_exists($column, $data)) {
                    $result[$column] = $data[$column];
                } elseif ($data instanceOf CModel && $data->hasAttribute($column)) {
                    $result[$column] = $data->getAttribute($column);
                } else {
                    throw new CException('Column or attribute "' . $column . '" not found!');
                }
            }
        }

        return $result;
    }

    /**
     * renders extra row
     *
     * @param mixed $row
     * @param mixed $change
     */
    private function renderExtraRow($row, $totals)
    {
        $data = $this->dataProvider->data[$row];
        if ($this->extraRowExpression) { //user defined expression, use it!
            $content = $this->evaluateExpression($this->extraRowExpression, array('data' => $data, 'row' => $row, 'totals' => $totals));
        } else {  //generate value
            $values = array();
            foreach ($this->extraRowColumns as $colName) {
                $values[] = CHtml::encode(CHtml::value($data, $colName));
            }
            $content = '<strong>' . implode(' :: ', $values) . '</strong>';
        }

        $colspan = count($this->columns);

        echo '<tr>';
        echo '<td class="extrarow" colspan="' . $colspan . '">' . $content . '</td>';
        echo '</tr>';
    }

    /**
     * need to rewrite this function as it is protected in CDataColumn: it is strange as all methods inside are public
     *
     * @param mixed $column
     * @param mixed $row
     * @param mixed $data
     */
    private function getDataCellContent($column, $data, $row)
    {
        if ($column->value !== null)
            $value = $column->evaluateExpression($column->value, array('data' => $data, 'row' => $row));
        else if ($column->name !== null)
            $value = CHtml::value($data, $column->name);

        return $value === null ? $column->grid->nullDisplay : $column->grid->getFormatter()->format($value, $column->type);
    }

    /**
     * Is current row start or end of group in particular column
     */
    private function isGroupEdge($colName, $row)
    {
        $result = array();
        foreach ($this->_groups[$colName] as $index => $v) {
            if ($v['start'] == $row) {
                $result['start'] = $row;
                $result['group'] = $v;
            }
            if ($v['end'] == $row) {
                $result['end'] = $row;
                $result['group'] = $v;
            }
            if (count($result)) break;
        }
        return $result;
    }

}