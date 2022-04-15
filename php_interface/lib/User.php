<?php

namespace lib;

use CUser;

class User
{
    public static function registerUser($params): bool
    {
        if (!self::checkEmail($params['email'])) {
            $user = new CUser;
            $arFields = [
                'LOGIN' => $params['email'],
                'NAME' => $params['name'],
                'EMAIL' => $params['email'],
                'PASSWORD' => $params['password'],
                'CONFIRM_PASSWORD' => $params['confirm_password'],
                'ACTIVE' => 'Y',
                'GROUP_ID' => [3, 5, 8],
            ];

            $ID = $user->Add($arFields);
            if ((int)$ID > 0) {
                return true;
            }
            return $user->LAST_ERROR;
        }
        return false;
    }

    public static function restorePassword($params)
    {
        global $USER;
        $login = $params['email'];
        $email = $login;

        return $USER->SendPassword($login, $email);
    }

    public static function updateUserInfo($params): bool
    {
        global $USER;
        $user = new CUser;

        $ID = $USER->GetID();

        $fields = [
            "NAME" => $params['name'],
            "EMAIL" => $params['email'],
            "LOGIN" => $params['email'],
            "PERSONAL_PHONE" => $params['phone'],
        ];

        if ($user->Update($ID, $fields)) {
            return true;
        } else {
            return $user->LAST_ERROR;
        }
    }

    public static function updateUserPassword($params): bool
    {
        global $USER;
        $user = new CUser;

        $ID = $USER->GetID();

        $fields = [
            "PASSWORD" => $params['new_password'],
            "CONFIRM_PASSWORD" => $params['new_password_confirm'],
        ];

        if ($user->Update($ID, $fields)) {
            return true;
        }

        return $user->LAST_ERROR;
    }

    public static function showUserInfo(): array
    {
        global $USER;

        $id = $USER->GetID();

        $name = $USER->GetFirstName();
        $email = $USER->GetEmail();
        $user = $USER->GetByID($id)->Fetch();
        $phone = $user['PERSONAL_PHONE'];

        return [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];
    }

    public static function authorizeUser($params)
    {
        $user = new CUser();

        return $user->Login($params['login'], $params['password']);
    }

    public static function checkEmail($email): bool
    {
        $user = new CUser();

        if ($user->GetByLogin($email)->Fetch()) {
            return true;
        }
        return false;
    }

    public static function logoutUser()
    {
        $user = new CUser();
        $user->Logout();
    }
}