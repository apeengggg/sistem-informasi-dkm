<?php

namespace App\Utils;

class StringUtil {

    public static function ErrorMessage($validator){
        $errors = $validator->errors();
        $errors = $errors->toArray();
        $formattedErrors = [];
        foreach ($errors as $messages) {
            array_push($formattedErrors, $messages[0]);
        }
        return $formattedErrors;
    }

    public static function validateIndonesianPhoneNumber($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
    
        // Check if it starts with 08 or +62
        if (preg_match('/^(08|\+62)/', $phoneNumber)) {
            // If starts with +62, ensure the format is correct (e.g., +628 followed by numbers)
            if (strpos($phoneNumber, '+62') === 0) {
                // Check if +62 is followed by 8 and digits (minimum length of 10 digits after +62)
                if (preg_match('/^\+628[0-9]{8,12}$/', $phoneNumber)) {
                    return true;
                }
            }
            // If starts with 08, ensure it follows with the correct number of digits
            elseif (strpos($phoneNumber, '08') === 0) {
                // Check if 08 is followed by at least 8 and no more than 12 digits
                if (preg_match('/^08[0-9]{8,12}$/', $phoneNumber)) {
                    return true;
                }
            }
        }
    
        // If none of the conditions are met, it's not a valid Indonesian phone number
        return false;
    }
    
}