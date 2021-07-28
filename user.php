<?php
    include_once 'util.php';
    include_once 'db.php';

    class User {
        protected $phoneNumber;
        protected $name;

        public function allPhoneNumbers($pdo){
            //returns an array of phone number
            try{
                $stmt = $pdo->prepare("SELECT phone_number FROM user");
                $stmt->execute();
                $result = $stmt->fetchAll();
                $phoneNumbers = array();
                foreach ($result as $row){
                    array_push($phoneNumbers, $row['phone_number']);
                }
                return $phoneNumbers;
            }catch(PDOException $e){
                echo $e->getMessage();
            }

        }
        public function getUserIdByPhoneNumber($pdo, $phoneNumber){
            try{
                $stmt = $pdo->prepare("SELECT user_id FROM user WHERE phone_number = ?");
                $stmt->execute([$phoneNumber]);
                $row = $stmt->fetch();
                return $row['user_id'];
            }catch(PDOException $e){
                echo $e->getMessage();
           }

        }
        public function getUserLevelByPhoneNumber ($pdo, $phoneNumber, $surveyId){
            $userId = $this->getUserIdByPhoneNumber($pdo, $phoneNumber);
            //use the userId and surveyId to ge the use level 
            $stmt = $pdo->prepare("SELECT level FROM user_survey WHERE user_id = ? AND survey_id = ?");
            $stmt->execute([$userId, $surveyId]);
            $row = $stmt->fetch();
            return $row['level'];
        }    

        public function getUserInvitationStatusByPhoneNumber ($pdo, $phoneNumber, $surveyId){
            $userId =  $this->getUserIdByPhoneNumber($pdo,$phoneNumber);
            $stmt = $pdo->prepare("SELECT invitations_status FROM user_survey WHERE user_id=? AND survey_id=?");
            $stmt->execute([$userId, $surveyId]);
            $row = $stmt->fetch();
            return $row['invitations_status']; 
        }
        public function updateUserLevel($pdo,$surveyId,$level,$userId){
            try{
                $stmt = $pdo->prepare("UPDATE user_survey SET level = ? WHERE user_id = ? AND survey_id = ?");
                $stmt->execute([$level, $userId, $surveyId]);
             }catch(PDOException $e){
                echo $e->getMessage();
            }

        }
        public function updateInvitationStatus($pdo, $invitationStatus, $phoneNumber, $surveyId){
            $userId = $this->getUserIdByPhoneNumber($pdo, $phoneNumber);
            try{
                $stmt = $pdo->prepare("UPDATE user_survey SET invitations_status = ? WHERE user_id = ? AND survey_id = ?");
                $stmt->execute([$invitationStatus,$userId, $surveyId]);
            }catch(PDOException $e){
                echo $e->getMessage();
            }
        }
    }
?>