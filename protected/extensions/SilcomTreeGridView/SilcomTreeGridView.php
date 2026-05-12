<?php

/**
*   @author Jhonatan Bianchi
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
        if (count($orderedData) == 0)
        {
            // add the roots
            foreach ($remainingNodes as $node)
            {
                if ($node->{$this->parentColumn} == 1)
                {
                    $orderedData[] = $node;
                }
            }

            if (count($orderedData) == 0)
            {
                throw new Exception("Error, no roots found");
            }
        }
        else
        {
            $oldOrderedData = $orderedData;
            foreach ($remainingNodes as $node)
            {
                // look for the parent
                foreach ($orderedData as $i => $insertedNode)
                {
                    if ($insertedNode->getPrimaryKey() == $node->{$this->parentColumn}
                        && in_array($insertedNode, $oldOrderedData))
                    {
                        // parent is in position $i
                        $positionToInsert = null;
                        $j = $i + 1;
                        while ($j < count($orderedData) && $positionToInsert == null)
                        {
                            $orderedNode = $orderedData[$j];
                            // if the parent is the same, proceed
                            if ($orderedNode->{$this->parentColumn} == $node->{$this->parentColumn})
                            {
                                $j++;
                            }
                            else
                            {
                                $positionToInsert = $j;
                            }
                        }

                        if ($positionToInsert != null)
                        {
                            array_splice($orderedData, $positionToInsert, 0, array($node));
                            break;
                        }
                        else
                        {
                            $orderedData[] = $node;
                            break;
                        }
                    }
                }
            }
        }

        $remainingNodes = array_udiff($remainingNodes, $orderedData, function ($nodeA, $nodeB) 
                                                                    {
                                                                        return $nodeA->getPrimaryKey() - $nodeB->getPrimaryKey();
                                                                    }
                                    );

        if ($remainingNodes != null)
        {
            return $this->orderData($remainingNodes, $orderedData);
        }
        else
        {
            return $orderedData;
        }
    }

    public function registerClientScript()
    {
        parent::registerClientScript();

        if ($this->extensionBaseUrl == null)
        {
            $this->extensionBaseUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.SilcomTreeGridView'));
        }

        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile($this->extensionBaseUrl . '/js/jquery.treeTable.js', CClientScript::POS_END);

        if ($this->customCssFile == null)
        {
            $cs->registerCssFile($this->extensionBaseUrl . '/css/jquery.treetable.css');
            $cs->registerCssFile($this->extensionBaseUrl . '/css/jquery.treetable.theme.default.css');
        }
        else
        {
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

        if ($model->{$this->parentColumn} != null)
        {
            // data-tt-parent-id indicates the parent
            $row .= 'data-tt-parent-id="' . $model->{$this->parentColumn} . '"';

            // add child class and class attribute remains open
            $row .= ' class="' . $this->childClass;
        }
        else
        {
            // add parent class and class attribute remains open
            $row .= ' class="' . $this->parentClass;
        }

        // check if there are more classes to be add
        if ($this->rowCssClassExpression !== null)
        {
            $row .= ' ' . $this->evaluateExpression($this->rowCssClassExpression, array('rowIndex'=>$rowIndex, 'data'=>$model));
        }
        else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
        {
            $row .= ' ' . $this->parentClass . $this->rowCssClass[$rowIndex % $n];
        }

        // closes class attribute and the tr tag
        $row .= '">';
        
        echo $row;

        // render columns
        foreach ($this->columns as $column) 
        {
            $column->renderDataCell($rowIndex);
        }

        echo "</tr> \n";
    }
}