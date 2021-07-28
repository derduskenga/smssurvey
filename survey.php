<?php

    include_once 'util.php';
    include_once 'db.php';
    include_once 'user.php';
    include_once 'sms.php';

    class Survey {
        protected $title;
        protected $description;

        public function addUserToSurvey ($pdo, $users, $surveyId, $level,$invitationsStatus){
            //$users is an indexed array of  phone numbers. 
            $user = new User();
            try{
                $stmt = $pdo->prepare("INSERT INTO user_survey (user_id,survey_id,level,invitations_status) VALUES (?,?,?,?)");

                for ($i = 0; $i < count($users); $i++){
                    $userId = $user->getUserIdByPhoneNumber($pdo,$users[$i]);
                    $stmt->execute([$userId, $surveyId, $level, $invitationsStatus]);
                }
            }catch(PDOException $e){
                echo $e->getMessage();
           }

        }

        public function updateUserToSurvey ($pdo, $phoneNumber, $userAnswer, $surveyId){
            $user = new User();
            $sms = new Sms();
            $level = $user->getUserLevelByPhoneNumber($pdo,$phoneNumber,$surveyId);
            $invitationsStatus = $user->getUserInvitationStatusByPhoneNumber($pdo,$phoneNumber,$surveyId);

            if($level == 1){
                //first question or invitation was sent 
                //check if user said yes or how you asked the user to respond
                //This can be set in the util.php file
                if(strcasecmp("yes", $userAnswer) == 0){ //compare with case insensive 
                    //update invitation status to 2
                    $user->updateInvitationStatus($pdo, 2, $phoneNumber, $surveyId);
                    
                    //add $userAnswer an answer to the survey of $level
                    $surveyQuestionId = $this->getSurveyQuestionIdByLevelAndSurveyId($pdo, $level, $surveyId);
                    $this->addAnswerToSurveyQuestion($pdo, $surveyQuestionId, $userAnswer);
                    //$level = $level + 1;
                     //Fetch survey question i.e. $message at $level and send it as a message
                    $message = $this->readSurveyQuestion($pdo,$surveyId,$level+1);
                    $sms->sendSMS($message, $phoneNumber);         
                    //update level
                    $userId = $user->getUserIdByPhoneNumber($pdo,$phoneNumber);
                    $user->updateUserLevel($pdo, $surveyId, $level + 1, $userId);
                }else{
                    //means the user declined to take the interview 
                    //update invitation status to 3
                    $user->updateInvitationStatus($pdo, 3, $phoneNumber, $surveyId);
                    //Send a message to thank the user anyway
                    $message = "Thank you for your reply.";
                    $sms->sendSMS($message,$phoneNumber);
                }
            }else{
                /*$maxLevel is the total number of questions a user will answer. 
                It can be fixed or read it from the DB; You will read the number of questions for a given survey*/
                $maxLevel = 3; //say 3
                $userId = $user->getUserIdByPhoneNumber($pdo,$phoneNumber);

                if ($level == $maxLevel){
                    
                    //$user->updateUserLevel($pdo, $surveyId, $level+1, $userId);
                    $message = "Thank you for taking our survey. We apprecite it";
                    $sms->sendSMS($message,$phoneNumber);
                    $user->updateInvitationStatus($pdo, 4, $phoneNumber, $surveyId);
                    //add survey question answer
                    $surveyQuestionId = $this->getSurveyQuestionIdByLevelAndSurveyId($pdo, $level, $surveyId);
                    $this->addAnswerToSurveyQuestion($pdo,$surveyQuestionId,$userAnswer);
                }else {
                    //Read a survey question, which $level+1 and send it to the user
                    $message = $this->readSurveyQuestion($pdo,$surveyId,$level+1);
                    $sms->sendSMS($message,$phoneNumber);
                    //update question level for this user in this particular survey
                    
                    $user->updateUserLevel($pdo,$surveyId,$level + 1,$userId);
                    //add survey question answer
                    $surveyQuestionId = $this->getSurveyQuestionIdByLevelAndSurveyId($pdo, $level, $surveyId);
                    $this->addAnswerToSurveyQuestion($pdo,$surveyQuestionId,$userAnswer);
                }
            }
        }
        public function readSurveyQuestion($pdo, $surveyId, $questionLevel){
            try{
                $stmt = $pdo->prepare ("SELECT question_text FROM survey_question WHERE survey_id = ? AND question_level = ?");
                $stmt->execute([$surveyId, $questionLevel]);
                $row = $stmt->fetch();
                return $row['question_text'];
            }catch(PDOException $e){
                echo $e->getMessage();
           }
        }
        public function addAnswerToSurveyQuestion($pdo, $surveyQuestionId, $answer){
            # code...
            try{
                $stmt = $pdo->prepare ("INSERT INTO survey_questions_answer (survey_question_id, answer_text) VALUES (?,?)");
                $stmt->execute([$surveyQuestionId,$answer]);
            }catch(PDOException $e){
                echo $e->getMessage();
           }
        }
        public function getSurveyQuestionIdByLevelAndSurveyId($pdo, $questionLevel, $surveyId){
            $stmt = $pdo->prepare ("SELECT survey_question_id FROM survey_question WHERE survey_id = ? AND question_level = ?");
            $stmt->execute([$surveyId, $questionLevel]);
            $row = $stmt->fetch();
            return $row['survey_question_id'];
        }
    }
?>