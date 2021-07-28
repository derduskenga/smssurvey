<?php
        include_once 'util.php';
        include_once 'db.php';
        include_once 'user.php';
        include_once 'sms.php';
        include_once 'survey.php';

        $survey = new Survey();
        $con = new DBConnector();
        $pdo = $con->connectToDB();

           
        $phoneNumber = $_POST['from'];          
        $text = $_POST['text'];

        $surveyId = 1;

        $survey->updateUserToSurvey($pdo,$phoneNumber,$text,$surveyId);

        
?>