<?php
/**
 * CTreeGridView class file.
 *
 * Used:
 * YiiExt - http://code.google.com/p/yiiext/
 * treeTable - http://plugins.jquery.com/project/treeTable
 * jQuery ui - http://jqueryui.com/
 *
 * @author quantum13
 * @link http://quantum13.ru/
 */


Yii::import('zii.widgets.grid.CGridView');
Yii::import('ext.booster.widgets.TbGridView');


class CQTreeGridView extends TbGridView
{

    /**
     * @var string the base script URL for all treeTable view resources (e.g. javascript, CSS file, images).
     * Defaults to null, meaning using the integrated grid view resources (which are published as assets).
     */
    public $baseTreeTableUrl;

    /**
     * @var string the base script URL for jQuery ui draggable and droppable.
     * Defaults to null, meaning using the integrated grid view resources (which are published as assets).
     */
    public $baseJuiUrl;


    /**
     * Initializes the tree grid view.
     */
    public function init()
    {
        parent::init();
        if ($this->baseTreeTableUrl === null)
            $this->baseTreeTableUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.QTreeGridView.treeTable'));

        if ($this->baseJuiUrl === null)
            $this->baseJuiUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.QTreeGridView.jui'));

        //Calc parent id from nesteD set
        if (count($this->dataProvider->data)) {
            $left = $this->dataProvider->data[0]->id;
            $right = $this->dataProvider->data[0]->parent_id;
            $level = 1;//"parentId";
            $stack = array();
            $currentLevel = 0;
            $previousModel = null;
            try {
                foreach ($this->dataProvider->data as $model) {
                    if ($model->$level == 1) { //root with level=1
                        $model->parentId = 0;
                        $currentLevel = 1;
                    } else {
                        if ($model->$level == $currentLevel) {
                            if (is_null($stack[count($stack) - 1])) {
                                throw new Exception('Tree is corrupted');
                            }
                            $model->parentId = $stack[count($stack) - 1]->getPrimaryKey();
                        } elseif ($model->$level > $currentLevel) {
                            if (is_null($previousModel)) {
                                throw new Exception('Tree is corrupted');
                            }
                            $currentLevel = $model->$level;
                            $model->parentId = $previousModel->getPrimaryKey();
                            array_push($stack, $previousModel);
                        } elseif ($model->$level < $currentLevel) {
                            for ($i = 0; $i < $currentLevel - $model->$level; $i++) {
                                array_pop($stack);
                            }
                            if (is_null($stack[count($stack) - 1])) {
                                throw new Exception('Tree is corrupted');
                            }
                            $currentLevel = $model->$level;
                            $model->parentId = $stack[count($stack) - 1]->getPrimaryKey();
                        }
                    }
                    $previousModel = $model;
                }
            } catch (Exception $e) {
                // Yii::app()->user->setFlash('CQTeeGridView', $e->getMessage());
            }

        }
    }

    /**
     * Registers necessary client scripts.
     */
    public function registerClientScript()
    {
        parent::registerClientScript();

        $cs = Yii::app()->getClientScript();
//        $cs->registerScriptFile(Yii::app()->theme->baseUrl . '/dist/js/jquery.treeTable.js', CClientScript::POS_END);
//        $cs->registerScriptFile(Yii::app()->theme->baseUrl . '/dist/js/jquery.treeTable.js', CClientScript::POS_END);
//        //$cs->registerScriptFile($this->baseJuiUrl.'/jquery.ui.core.min.js',CClientScript::POS_END);
//        //$cs->registerScriptFile($this->baseJuiUrl.'/jquery.ui.widget.min.js',CClientScript::POS_END);
//        $cs->registerScriptFile(Yii::app()->theme->baseUrl . '/dist/js/jquery.ui.mouse.min.js', CClientScript::POS_END);
//        $cs->registerScriptFile(Yii::app()->theme->baseUrl . '/dist/js/jquery.ui.droppable.min.js', CClientScript::POS_END);
//        $cs->registerScriptFile(Yii::app()->theme->baseUrl . '/dist/js/jquery.ui.draggable.min.js', CClientScript::POS_END);
        //$cs->registerCssFile(Yii::app()->theme->baseUrl . '/dist/css/jquery.treeTable.css');

//        $cs->registerScript('treeTable', '
//            $(document).ready(function()  {
//              $("#' . $this->getId() . ' .items").treeTable();
//            });
//            ', CClientScript::POS_END);

//        $cs->registerScript('draganddrop', '
//            $(document).ready(function()  {
//                $("#' . $this->getId() . ' .items tr.initialized").draggable({
//                  helper: "clone",
//                  opacity: .75,
//                  refreshPositions: true, // Performance?
//                  revert: "invalid",
//                  revertDuration: 300,
//                  scroll: true
//                });
//            });
//
//            ', CClientScript::POS_END);
    }

    /**
     * Renders the data items for the grid view.
     */
    public function renderItems()
    {

        if (Yii::app()->user->hasFlash('CQTeeGridView')) {
            print '<div style="background-color:#ffeeee;padding:7px;border:2px solid #cc0000;">' . Yii::app()->user->getFlash("CQTeeGridView") . '</div>';
        }
        parent::renderItems();
    }


    /**
     * Renders a table body row with id and parentId, needed for ActsAsTreeTable
     * jQuery extension.
     * @param integer $row the row number (zero-based).
     */
    public function renderTableRow($row)
    {
        $model = $this->dataProvider->data[$row];
        $parentClass = $model->parent_id
            ? 'child-of-' . $model->parent_id . ' '
            : '';

        echo '<tr style="display:none;" class="before" id="before-' . $model->getPrimaryKey() . '"><td style="padding:0;"><div style="height:3px;"></div></td></tr>';
        if ($this->rowCssClassExpression !== null) {
            echo '<tr id="' . $model->getPrimaryKey() . '" class="' . $parentClass . $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $model)) . '">';
        } else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
            echo '<tr id="' . $model->getPrimaryKey() . '" class="' . $parentClass . $this->rowCssClass[$row % $n] . '">';
        else
            echo '<tr id="' . $model->getPrimaryKey() . '" class="' . $parentClass . '">';
        foreach ($this->columns as $column) {
            $column->renderDataCell($row);
        }

        echo "</tr>\n";
        echo '<tr style="display:none;" class="after" id="after-' . $model->getPrimaryKey() . '"><td style="padding:0;"><div style="height:3px;"></div></td></tr>';

    }

}
