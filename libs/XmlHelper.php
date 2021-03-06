<?php

require 'models/Employee.php';
require 'models/Task.php';

class XmlHelper
{
    private static $empsDoc, $tasksDoc, $empsPath, $tasksPath;

    public static function loadData()
    {
        try
        {
            self::loadEmpsDoc(); self::loadTasksDoc();
        }
        catch(Exception $e)
        {

        }
    }

    private static function loadEmpsDoc()
    {
        try
        {
            //echo dirname(__FILE__) . '<br>'; 
            //print_r(glob('data/*.*')); echo '<br>';
            //echo dirname(dirname(__FILE__)) . '<br>';

            self::$empsDoc = new DOMDocument();

            if (file_exists("data/Employees.xml"))
            {
                self::$empsDoc->load("data/Employees.xml");

                //echo 'Employees successfully loaded';
            }
            else
                self::$empsDoc->loadXML("<Employees></Employees>");

            self::$empsPath = new DOMXPath(self::$empsDoc);
        }
        catch(Exception $e)
        {
            echo 'XmlHelper.loadEmpsDoc ERROR -- ' . $e->getMessage();
        }
    }

    private static function loadTasksDoc()
    {
        try
        {
            self::$tasksDoc = new DOMDocument();

            if (file_exists("data/EmployeesTasks.xml"))
                self::$tasksDoc->load("data/EmployeesTasks.xml");
            else
                self::$tasksDoc->loadXML("<EmployeesTasks></EmployeesTasks>");

            self::$tasksPath = new DOMXPath(self::$tasksDoc);
        }
        catch(Exception $e)
        {

        }
    }

    public static function authorizeUser($userName, $password)
    {
        try
        {
            //TODO: Add password check
            $empsElms = self::$empsPath->query("//Employees/Employee[@userName='" . $userName . "']");

            if ($empsElms->length > 0)
                return self::createEmployee($empsElms->item(0));
            else
                return null;
        }
        catch(Exception $e)
        {

        }
    }

    public static function getEmployees()
    {
        try
        {
            $employees = array();

            foreach(self::$empsDoc->getElementsByTagName('Employee') as $empElm)
            {
                //$emp = createEmployee($empElm);

                //$fnm = $empElm->getAttribute('firstName');
                //$ftnm = $empElm->getAttribute('fatherName');
                //$fmnm = $empElm->getAttribute('familyName');
                
                //$emp = new Employee($fnm, $ftnm, $fmnm);
                //$empId = $empElm->getAttribute('id'); $emp->setId($empId);
                //$emp->setNationalityId($empElm->getAttribute('natId'));

                //$subEmpsElms = self::$empsPath->query("//Employees/Employee[@managerId='" . $empId . "']");

                //$emp->setIsManager($subEmpsElms->length > 0);

                $employees[] = self::createEmployee($empElm);;
            }

            return $employees;
        }
        catch(Exception $e)
        {

        }
    }

    private static function createEmployee($empElm)
    {
        try
        {
            $fnm = $empElm->getAttribute('firstName');
            $ftnm = $empElm->getAttribute('fatherName');
            $fmnm = $empElm->getAttribute('familyName');
            
            $emp = new Employee($fnm, $ftnm, $fmnm);
            $empId = $empElm->getAttribute('id'); $emp->setId($empId);
            $emp->setNationalityId($empElm->getAttribute('natId'));
            
            if ($empElm->hasAttribute('managerId'))
                $emp->setManagerId($empElm->getAttribute('managerId'));

            $subEmpsElms = self::$empsPath->query("//Employees/Employee[@managerId='" . $empId . "']");

            $emp->setIsManager($subEmpsElms->length > 0);

            return $emp;
        }
        catch(Exception $e)
        {

        }
    }

    public static function getEmployee($empId)
    {
        try
        {
            $anEmpElm = self::$empsPath->query("//Employees/Employee[@id='" . $empId . "']");

            return self::createEmployee($anEmpElm->item(0));
        }
        catch(Exception $e)
        {

        }
    }

    public static function addEmployee()
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function updateEmployee()
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function getSubordinates($mgrId)
    {
        try
        {
            //echo '<br>Into getSubordinates...<br>';

            $subEmpsElms = self::$empsPath->query("//Employees/Employee[@managerId='" . $mgrId . "']");

            $subordinates = array();

            if ($subEmpsElms->length > 0)
            {
                foreach($subEmpsElms as $empElm)
                {
                    $fnm = $empElm->getAttribute('firstName');
                    $ftnm = $empElm->getAttribute('fatherName');
                    $fmnm = $empElm->getAttribute('familyName');
                    
                    $emp = new Employee($fnm, $ftnm, $fmnm);
                    $emp->setId($empElm->getAttribute('id'));
                    $emp->setNationalityId($empElm->getAttribute('natId'));

                    $subordinates[] = $emp;
                }

                //var_dump($subordinates);

                return $subordinates;
            }
            else
                return null;
        }
        catch(Exception $e)
        {

        }
    }

    public static function addUser()
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function assignNewTask($newTask)
    {
        try
        {
            $empId = $newTask->getEmpId(); $empHasTasks = false;

            $empTasks = self::$empsPath->query("//EmployeesTasks/EmployeeTasks[@empId='" . $empId . "']");

            if ($empTasks->count() > 0)
            {
                $lastTask = $empTasks->item($empTasks->length - 1);
                $lastTaskId = $lastTask->getAttribute("id");
                $idPart = intval(explode('.', $lastTaskId)[1]);
            }
            else
            {
                $idPart = 0;
            }
            
            $newId = $idPart + 1;
            $newTaskElm = $tasksDoc->createElement('Task');
            $newTaskElm->setAttribute('id', $empId . $newId);
            $newTaskElm->setAttribute('assignedOn', date('j/n/Y'));
            $newTaskElm->setAttribute('assignedAt', date('G:i:s'));
            $newTaskElm->setAttribute('dueDate', $newTask->getDueDate());

            $descElm = $tasksDoc->createElement('Description', $newTask->getDescription());
            $newTaskElm->appendChild($descElm);

            if (!is_null($newTask->getAttachments()))
            {
                $attachmentsElm = $tasksDoc->createElement('Attachments');

                foreach($newTask->getAttachments() as $attch)
                {
                    $attchElm = $tasksDoc->createElement('Attachment');
                    $attchElm->setAttribute('path', $attch->getPath());

                    $attachmentsElm->appendChild($attchElm);
                }

                $newTaskElm->appendChild($attachmentsElm);
            }
        }
        catch(Exception $e)
        {

        }
    }

    public static function addTaskNote($taskId, $note)
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function setTaskProgress($taskId, $progVal, $note)
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function setTaskEvaluation($taskId, $evaluation)
    {
        try
        {

        }
        catch(Exception $e)
        {

        }
    }

    public static function getEmployeeCurrentTasks($empId)
    {
        try
        {
            $empTasksElms = self::$tasksPath->query("//EmployeesTasks/EmployeeTasks[@empId=" . $empId . "]");

            if ($empTasksElms != null && $empTasksElms->item(0)->childNodes->length > 0)
            {
                //var_dump($empTasksElms->item(0));
                //echo "<br> --- <br>";
                $theTasksElms = $empTasksElms->item(0)->getElementsByTagName("Task");
                //var_dump($theTasksElms);

                $empTasks = array();

                foreach($theTasksElms as $taskElm)
                {
                    $taskPrgElm = self::getXmlElm($taskElm, "Progress");
                    $taskPrg = $taskPrgElm->getAttribute("val");

                    if (strcmp($taskPrg, "100") != 0)
                        $empTasks[] = self::createTask($taskElm);
                }

                return $empTasks;
            }
            else
                return null;
        }
        catch(Exception $e)
        {

        }
    }

    public static function getEmployeeAllTasks($empId)
    {
        try
        {
            $empTasksElms = self::$tasksPath->query("//EmployeesTasks/EmployeeTasks[@empId=" . $empId . "]");

            if ($empTasksElms != null && $empTasksElms->item(0)->childNodes->length > 0)
            {
                //var_dump($empTasksElms->item(0));
                //echo "<br> --- <br>";
                $theTasksElms = $empTasksElms->item(0)->getElementsByTagName("Task");
                //var_dump($theTasksElms);

                //$theTasksElms = $empTasksElms->item(0)->childNodes;

                $empTasks = array();

                foreach($theTasksElms as $taskElm)
                {
                    /*
                    $taskPrgElm = self::getXmlElm($taskElm, "Progress");
                    $taskPrg = $taskPrgElm->getAttribute("val");

                    $aTask = new Task;

                    $aTask->setId($taskElm->getAttribute("id"));
                    $aTask->setAssignDate($taskElm->getAttribute("assignedOn"));
                    $aTask->setDueDate($taskElm->getAttribute("dueDate"));
                    $aTask->setDescription(self::getXmlElmVal($taskElm, "Description"));
                    $aTask->setProgress($taskPrg);
                    */
                    $empTasks[] = self::createTask($taskElm);
                }

                //echo "<br>From XmlHelper: " . count($empTasks);
                //var_dump($empTasks);

                return $empTasks;
            }
            else
                return null;
        }
        catch(Exception $e)
        {

        }
    }

    public static function getEmployeeDatedTasks($empId, $fromDate, $toDate)
    {
        try
        {
            $empTasksElms = self::$tasksPath->query("//EmployeesTasks/EmployeeTasks[@empId=" . $empId . "]");

            if ($empTasksElms != null && $empTasksElms->item(0)->childNodes->length > 0)
            {
                //var_dump($empTasksElms->item(0));
                //echo "<br> --- <br>";
                $theTasksElms = $empTasksElms->item(0)->getElementsByTagName("Task");
                //var_dump($theTasksElms);

                $fromDt = new DateTime($fromDate); $toDt = new DateTime($toDate);
                //$theTasksElms = $empTasksElms->item(0)->childNodes;

                $empTasks = array();

                foreach($theTasksElms as $taskElm)
                {
                    $aTask = self::createTask($taskElm);
                    $taskAssignDate = new DateTime($aTask->getAssignDate());
                    /*
                    $taskPrgElm = self::getXmlElm($taskElm, "Progress");
                    $taskPrg = $taskPrgElm->getAttribute("val");

                    $aTask = new Task;

                    $aTask->setId($taskElm->getAttribute("id"));
                    $aTask->setAssignDate($taskElm->getAttribute("assignedOn"));
                    $aTask->setDueDate($taskElm->getAttribute("dueDate"));
                    $aTask->setDescription(self::getXmlElmVal($taskElm, "Description"));
                    $aTask->setProgress($taskPrg);
                    */
                    if ($taskAssignDate >= $fromDt && $taskAssignDate <= $toDt)
                        $empTasks[] = $aTask;
                }

                //echo "<br>From XmlHelper: " . count($empTasks);
                //var_dump($empTasks);

                return $empTasks;
            }
            else
                return null;
        }
        catch(Exception $e)
        {

        }
    }

    private static function createTask($taskElm)
    {
        try
        {
            $taskPrgElm = self::getXmlElm($taskElm, "Progress");
            $taskPrg = $taskPrgElm->getAttribute("val");

            $aTask = new Task;

            $aTask->setId($taskElm->getAttribute("id"));
            $aTask->setAssignDate($taskElm->getAttribute("assignedOn"));
            $aTask->setDueDate($taskElm->getAttribute("dueDate"));
            $aTask->setDescription(self::getXmlElmVal($taskElm, "Description"));
            $aTask->setProgress($taskPrg);

            return $aTask;
        }
        catch(Exception $e)
        {

        }
    }

    private static function getXmlElm($parent, $tagName)
    {
        try
        {
            return $parent->getElementsByTagName($tagName)[0];
        }
        catch(Exception $e)
        {

        }
    }

    private static function getXmlElmVal($parent, $tagName)
    {
        try
        {
            return $parent->getElementsByTagName($tagName)[0]->nodeValue;
        }
        catch(Exception $e)
        {

        }
    }
}