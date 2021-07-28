<?php
    include_once '../sms.php';
    include_once '../db.php';
    include_once '../user.php';
    include_once '../survey.php';

    $sms = new Sms();
    $survey = new Survey();
    $user = new User();
    $con = new DBConnector();
    $pdo = $con->connectToDB();
    $surveyId = 1; //pick a survey manually, but can be read from DB
    //____________________________________________________________________________________________
        $questionLevel = 1;
    //____________________________________________________________________________________________
    //Select question 1, which is always asking the user if they accept to take the survey. 
       
   $message = $survey->readSurveyQuestion($pdo, $surveyId, $questionLevel); //We are readin the first question
   
    $recipients = $user->allPhoneNumbers($pdo);
    $sms->sendSMS($message,$recipients);   
   
    //add these users to a survey 
    $survey->addUserToSurvey($pdo,$recipients,$surveyId, 1, 1); 
    /*level and invitation status set to 1 because first question have been 
    sent, which means user have been invited*/
?>